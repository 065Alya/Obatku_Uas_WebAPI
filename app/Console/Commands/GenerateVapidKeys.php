<?php

namespace App\Console\Commands;

use App\Services\VapidService;
use Illuminate\Console\Command;

/**
 * php artisan pwa:generate-vapid-keys
 *
 * Generates a VAPID key pair for Web Push and prints the .env entries.
 * Run once during initial setup; store keys securely in .env.
 */
class GenerateVapidKeys extends Command
{
    protected $signature   = 'pwa:generate-vapid-keys
                                {--show : Show existing keys from config}';

    protected $description = 'Generate VAPID key pair for Web Push notifications';

    public function handle(): int
    {
        if ($this->option('show')) {
            $public  = config('pwa.vapid_public_key');
            $private = config('pwa.vapid_private_key');
            $subject = config('pwa.vapid_subject');

            if (empty($public)) {
                $this->warn('No VAPID keys configured yet. Run without --show to generate them.');
                return self::FAILURE;
            }

            $this->info('Current VAPID configuration:');
            $this->line('VAPID_PUBLIC_KEY='  . $public);
            $this->line('VAPID_PRIVATE_KEY=' . $private);
            $this->line('VAPID_SUBJECT='     . $subject);
            return self::SUCCESS;
        }

        $this->info('Generating VAPID key pair (P-256 / ES256)...');

        // ── Fix for WAMP/XAMPP on Windows ────────────────────────────────────
        // openssl_pkey_new() with EC curves requires OPENSSL_CONF to be set.
        // We auto-detect the cnf path if it is not already set.
        if (!getenv('OPENSSL_CONF')) {
            $cnfPath = $this->resolveOpensslCnf();
            if ($cnfPath) {
                putenv("OPENSSL_CONF={$cnfPath}");
                $this->line("[info] OPENSSL_CONF set to: {$cnfPath}");
            } else {
                $this->warn('Could not auto-detect openssl.cnf. If key generation fails, set OPENSSL_CONF manually.');
            }
        }

        try {
            $keys = VapidService::generateKeys();
        } catch (\Throwable $e) {
            $this->error('Failed to generate keys: ' . $e->getMessage());
            $this->line('Make sure ext-openssl is enabled in PHP.');
            $this->line('Tip: Set OPENSSL_CONF=C:\\wamp64\\bin\\php\\php8.2.18\\extras\\ssl\\openssl.cnf in your environment.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ VAPID keys generated. Add these to your .env file:');
        $this->newLine();

        $this->line('VAPID_PUBLIC_KEY='  . $keys['public_key']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['private_key']);
        $this->line('VAPID_SUBJECT=mailto:noreply@obatku.id');

        $this->newLine();
        $this->warn('⚠  Keep VAPID_PRIVATE_KEY secret. Never commit it to version control.');
        $this->line('After adding to .env, run: php artisan config:clear');

        // Optionally write to .env if it exists and key is empty
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            if (str_contains($envContent, 'VAPID_PUBLIC_KEY=') && empty(config('pwa.vapid_public_key'))) {
                if ($this->confirm('Write keys directly to .env now?', false)) {
                    $envContent = $this->updateEnvValue($envContent, 'VAPID_PUBLIC_KEY',  $keys['public_key']);
                    $envContent = $this->updateEnvValue($envContent, 'VAPID_PRIVATE_KEY', $keys['private_key']);
                    file_put_contents($envPath, $envContent);
                    $this->info('.env updated. Run: php artisan config:clear');
                }
            }
        }

        return self::SUCCESS;
    }

    private function updateEnvValue(string $content, string $key, string $value): string
    {
        $pattern     = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";

        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $replacement, $content);
        }

        return $content . "\n{$replacement}";
    }

    /**
     * Auto-detect openssl.cnf path for WAMP / XAMPP on Windows.
     * Checks PHP version-specific paths first, then Apache conf.
     */
    private function resolveOpensslCnf(): ?string
    {
        $phpBin = PHP_BINARY; // e.g. C:\wamp64\bin\php\php8.2.18\php.exe

        $candidates = [
            // WAMP: PHP-version-specific extras/ssl/openssl.cnf
            dirname($phpBin) . '\\extras\\ssl\\openssl.cnf',
            // WAMP: Apache conf
            'C:\\wamp64\\bin\\apache\\apache2.4.59\\conf\\openssl.cnf',
            // XAMPP: Apache conf
            'C:\\xampp\\apache\\conf\\openssl.cnf',
            // XAMPP: PHP extras
            'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
            // Generic Windows OpenSSL installs
            'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.cfg',
            'C:\\OpenSSL-Win64\\bin\\openssl.cfg',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
