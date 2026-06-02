<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

/**
 * VapidService — Manual Web Push / VAPID implementation.
 *
 * Implements the VAPID (Voluntary Application Server Identification)
 * protocol for Web Push without any third-party Composer package.
 *
 * Requirements: ext-openssl, ext-mbstring (both bundled with XAMPP/PHP ≥ 8.1)
 *
 * Key steps for each push:
 *  1. Build a JWT signed with the VAPID private key (ES256 / P-256 curve)
 *  2. Encrypt the payload using AES-128-GCM with ECDH-derived keys (aesgcm)
 *  3. POST to the subscription endpoint with proper Authorization header
 *
 * References:
 *  - RFC 8292 (VAPID)
 *  - RFC 8030 (Web Push Protocol)
 *  - https://developers.google.com/web/fundamentals/push-notifications
 */
class VapidService
{
    private string $publicKey;
    private string $privateKey;
    private string $subject;

    public function __construct()
    {
        $this->publicKey  = config('pwa.vapid_public_key', '');
        $this->privateKey = config('pwa.vapid_private_key', '');
        $this->subject    = config('pwa.vapid_subject', 'mailto:noreply@obatku.id');
    }

    /* ─── Public API ──────────────────────────────────────────────────────── */

    /**
     * Send a push notification to a single subscription.
     *
     * @param  PushSubscription $subscription
     * @param  array            $payload  Notification payload (title, body, url, …)
     * @return bool
     */
    public function send(PushSubscription $subscription, array $payload): bool
    {
        if (empty($this->publicKey) || empty($this->privateKey)) {
            Log::warning('[VAPID] VAPID keys not configured — skipping push.');
            return false;
        }

        try {
            $endpoint    = $subscription->endpoint;
            $origin      = $this->extractOrigin($endpoint);
            $audience    = $origin;

            $jwt         = $this->buildVapidJwt($audience);
            $vapidHeader = 'vapid t=' . $jwt . ', k=' . $this->publicKey;

            $body        = json_encode(array_merge($this->defaultPayload(), $payload));

            // Encrypt the payload for the specific subscription
            $encrypted   = $this->encryptPayload(
                $body,
                $subscription->p256dh_key,
                $subscription->auth_token
            );

            $response = $this->postToEndpoint($endpoint, $encrypted, $vapidHeader);

            if ($response['status'] >= 200 && $response['status'] < 300) {
                $subscription->recordNotified();
                Log::info('[VAPID] Push sent', ['endpoint' => substr($endpoint, 0, 60)]);
                return true;
            }

            if (in_array($response['status'], [404, 410])) {
                $subscription->deactivate();
                Log::info('[VAPID] Subscription expired/revoked — deactivated', ['id' => $subscription->id]);
            } else {
                Log::warning('[VAPID] Push failed', [
                    'status' => $response['status'],
                    'body'   => substr($response['body'], 0, 200),
                ]);
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('[VAPID] Exception', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate a new VAPID key pair.
     * Returns ['public_key' => string, 'private_key' => string] (URL-safe base64).
     */
    public static function generateKeys(): array
    {
        $key = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        $details = openssl_pkey_get_details($key);

        // Public key: uncompressed point format (0x04 || x || y) → base64url
        $publicKeyBytes = chr(4)
            . str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        openssl_pkey_export($key, $privateKeyPem);

        // Extract raw d (private scalar) from PEM
        $privateKeyDer     = self::pemToDer($privateKeyPem);
        $privateKeyBytes   = self::extractEcPrivateKeyBytes($privateKeyDer);

        return [
            'public_key'  => self::base64UrlEncode($publicKeyBytes),
            'private_key' => self::base64UrlEncode($privateKeyBytes),
        ];
    }

    /* ─── JWT Building ───────────────────────────────────────────────────── */

    /**
     * Build a VAPID JWT (RFC 8292).
     */
    private function buildVapidJwt(string $audience): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'ES256',
        ]));

        $claims = $this->base64UrlEncode(json_encode([
            'aud' => $audience,
            'exp' => time() + 43200, // 12 hours
            'sub' => $this->subject,
        ]));

        $signingInput = "{$header}.{$claims}";

        // Load private key — support both raw bytes (base64url) and PEM
        $privateKeyPem = $this->loadPrivateKeyPem($this->privateKey);

        openssl_sign($signingInput, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);

        // Convert DER-encoded signature to raw 64-byte (r || s)
        $rawSignature = $this->derToRawSignature($signature);

        return "{$signingInput}." . $this->base64UrlEncode($rawSignature);
    }

    /* ─── Payload Encryption ─────────────────────────────────────────────── */

    /**
     * Encrypt a Web Push payload using aesgcm content-encoding (RFC 8291).
     */
    private function encryptPayload(string $payload, string $clientPublicKeyB64, string $authSecretB64): array
    {
        // Decode client keys
        $clientPublicKey = $this->base64UrlDecode($clientPublicKeyB64);
        $authSecret      = $this->base64UrlDecode($authSecretB64);

        // Generate server ephemeral EC key pair
        $serverKey     = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        $serverDetails = openssl_pkey_get_details($serverKey);
        $serverPublicKey = chr(4)
            . str_pad($serverDetails['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($serverDetails['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        // Reconstruct client public key as OpenSSL resource
        $clientKey = $this->loadPublicKeyFromBytes($clientPublicKey);

        // ECDH shared secret
        openssl_pkey_export($serverKey, $serverPem);
        $sharedSecret = openssl_dh_compute_key(
            $this->extractPublicKeyPoint($clientPublicKey),
            openssl_pkey_get_private($serverPem)
        );
        if ($sharedSecret === false) {
            throw new \RuntimeException('ECDH key exchange failed.');
        }

        // HKDF to derive encryption key and nonce
        $salt  = random_bytes(16);
        $prk   = $this->hkdf($authSecret, $sharedSecret, 'Content-Encoding: auth' . "\x00", 32);
        $key   = $this->hkdf($salt, $prk, $this->buildContext('aesgcm', $clientPublicKey, $serverPublicKey) . "\x01", 16);
        $nonce = $this->hkdf($salt, $prk, $this->buildContext('nonce', $clientPublicKey, $serverPublicKey) . "\x01", 12);

        // Pad payload (2-byte big-endian length prefix)
        $padded    = "\x00\x00" . $payload; // minimal padding
        $encrypted = openssl_encrypt($padded, 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag);

        return [
            'ciphertext'       => $encrypted . $tag,
            'salt'             => $salt,
            'serverPublicKey'  => $serverPublicKey,
        ];
    }

    /* ─── HTTP Transport ─────────────────────────────────────────────────── */

    private function postToEndpoint(string $endpoint, array $encrypted, string $vapidHeader): array
    {
        $body = $encrypted['ciphertext'];

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", [
                    'Content-Type: application/octet-stream',
                    'Content-Encoding: aesgcm',
                    'Authorization: ' . $vapidHeader,
                    'Encryption: salt=' . $this->base64UrlEncode($encrypted['salt']),
                    'Crypto-Key: dh=' . $this->base64UrlEncode($encrypted['serverPublicKey'])
                        . ';p256ecdsa=' . $this->publicKey,
                    'TTL: ' . config('pwa.push_ttl', 86400),
                    'Content-Length: ' . strlen($body),
                ]),
                'content'         => $body,
                'ignore_errors'   => true,
                'timeout'         => 10,
            ],
            'ssl' => ['verify_peer' => true],
        ]);

        $responseBody = @file_get_contents($endpoint, false, $context);
        $statusLine   = $http_response_header[0] ?? 'HTTP/1.1 500 Error';
        preg_match('/HTTP\/\S+\s+(\d+)/', $statusLine, $m);

        return [
            'status' => (int) ($m[1] ?? 500),
            'body'   => (string) $responseBody,
        ];
    }

    /* ─── Helpers ────────────────────────────────────────────────────────── */

    private function defaultPayload(): array
    {
        return [
            'title'             => 'ObatKu',
            'body'              => '',
            'url'               => '/dashboard',
            'icon'              => '/icons/icon-192x192.png',
            'badge'             => '/icons/icon-72x72.png',
            'tag'               => 'obatku-' . time(),
            'requireInteraction'=> false,
        ];
    }

    private function extractOrigin(string $endpoint): string
    {
        $parts = parse_url($endpoint);
        return ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
    }

    private function buildContext(string $type, string $clientKey, string $serverKey): string
    {
        return 'Content-Encoding: ' . $type . "\x00"
            . 'P-256\x00'
            . pack('n', strlen($clientKey)) . $clientKey
            . pack('n', strlen($serverKey)) . $serverKey;
    }

    private function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $t   = '';
        $okm = '';
        for ($i = 1; strlen($okm) < $length; $i++) {
            $t    = hash_hmac('sha256', $t . $info . chr($i), $prk, true);
            $okm .= $t;
        }
        return substr($okm, 0, $length);
    }

    private function loadPrivateKeyPem(string $keyB64): string
    {
        // Try PEM first
        if (str_contains($keyB64, '-----')) {
            return $keyB64;
        }

        // Assume base64url-encoded raw 32-byte EC private key → wrap in PKCS8
        $rawBytes = $this->base64UrlDecode($keyB64);

        // Minimal PKCS#8 DER wrapper for a P-256 private key
        $ecParams   = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"; // OID prime256v1
        $algId      = "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" . $ecParams;
        $privOctet  = "\x04\x22\x04\x20" . $rawBytes; // ECPrivateKey (no public key)
        $pkcs8      = "\x30" . chr(strlen($algId) + strlen($privOctet) + 2)
                    . $algId
                    . "\x04" . chr(strlen($privOctet))
                    . $privOctet;

        $pem = "-----BEGIN PRIVATE KEY-----\n"
             . chunk_split(base64_encode($pkcs8), 64, "\n")
             . "-----END PRIVATE KEY-----\n";

        return $pem;
    }

    private function loadPublicKeyFromBytes(string $bytes): mixed
    {
        // Wrap uncompressed EC point in SubjectPublicKeyInfo DER
        $ecOid  = "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01";
        $curveOid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $algId  = "\x30\x13" . $ecOid . $curveOid;
        $bitStr = "\x03" . chr(1 + strlen($bytes)) . "\x00" . $bytes;
        $spki   = "\x30" . chr(strlen($algId) + strlen($bitStr)) . $algId . $bitStr;

        $pem = "-----BEGIN PUBLIC KEY-----\n"
             . chunk_split(base64_encode($spki), 64, "\n")
             . "-----END PUBLIC KEY-----\n";

        return openssl_pkey_get_public($pem);
    }

    private function extractPublicKeyPoint(string $publicKeyBytes): string
    {
        // Returns just the raw point bytes for openssl_dh_compute_key
        return $publicKeyBytes;
    }

    /** Convert DER-encoded ECDSA signature to raw 64-byte (r || s) */
    private function derToRawSignature(string $der): string
    {
        $offset = 2; // skip SEQUENCE tag + length
        $offset += 1; // INTEGER tag
        $rLen   = ord($der[$offset++]);
        $r      = substr($der, $offset, $rLen);
        $offset += $rLen;
        $offset += 1; // INTEGER tag
        $sLen   = ord($der[$offset++]);
        $s      = substr($der, $offset, $sLen);

        // Pad or trim to 32 bytes each
        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    private static function pemToDer(string $pem): string
    {
        $pem = preg_replace('/-----[^-]+-----/', '', $pem);
        return base64_decode(preg_replace('/\s/', '', $pem));
    }

    private static function extractEcPrivateKeyBytes(string $der): string
    {
        // Find OCTET STRING containing the private key (0x04 0x20 … 32 bytes)
        $pos = strpos($der, "\x04\x20");
        if ($pos === false) {
            throw new \RuntimeException('Cannot extract EC private key bytes from DER.');
        }
        return substr($der, $pos + 2, 32);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $pad  = 4 - (strlen($data) % 4);
        $data = str_pad($data, strlen($data) + ($pad < 4 ? $pad : 0), '=');
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
