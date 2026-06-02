<?php
/**
 * ObatKu PWA Icon Generator
 *
 * Generates all required PWA icon sizes from a single source SVG.
 * Run once: php artisan tinker --execute="require base_path('scripts/generate-pwa-icons.php');"
 * Or simply: php scripts/generate-pwa-icons.php
 *
 * Requires: GD extension (bundled with XAMPP)
 */

$outputDir = __DIR__ . '/../public/icons';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "Created directory: $outputDir\n";
}

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// ObatKu brand colors
$primaryBlue  = [24,  95,  165]; // #185FA5
$white        = [255, 255, 255];
$darkBlue     = [4,   44,  83];  // #042C53
$ecoGreen     = [29,  158, 117]; // #1D9E75

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);

    // Enable alpha transparency
    imagealphablending($img, false);
    imagesavealpha($img, true);

    // Background: rounded square using primary blue
    $bg = imagecolorallocate($img, $primaryBlue[0], $primaryBlue[1], $primaryBlue[2]);
    $wh = imagecolorallocate($img, $white[0], $white[1], $white[2]);

    imagefill($img, 0, 0, $bg);

    // Draw a simplified pill/capsule icon (medicine shape)
    $cx = $size / 2;
    $cy = $size / 2;
    $r  = $size * 0.28;

    // Pill body (white)
    imagefilledellipse($img, (int)($cx - $r * 0.5), (int)$cy, (int)($r * 1.1), (int)($r * 2.2), $wh);
    imagefilledellipse($img, (int)($cx + $r * 0.5), (int)$cy, (int)($r * 1.1), (int)($r * 2.2), $wh);
    imagefilledrectangle(
        $img,
        (int)($cx - $r * 0.55),
        (int)($cy - $r * 1.1),
        (int)($cx + $r * 0.55),
        (int)($cy + $r * 1.1),
        $wh
    );

    // Green top half of pill
    $grn = imagecolorallocate($img, $ecoGreen[0], $ecoGreen[1], $ecoGreen[2]);
    imagefilledellipse($img, (int)($cx - $r * 0.5), (int)$cy, (int)($r * 1.1), (int)($r * 2.2), $grn);
    imagefilledellipse($img, (int)($cx + $r * 0.5), (int)$cy, (int)($r * 1.1), (int)($r * 2.2), $grn);
    imagefilledrectangle(
        $img,
        (int)($cx - $r * 0.55),
        (int)($cy - $r * 1.1),
        (int)($cx + $r * 0.55),
        (int)$cy,
        $grn
    );

    // Dividing line (white)
    $lineW = max(1, (int)($size * 0.02));
    imagefilledrectangle(
        $img,
        (int)($cx - $r * 0.55),
        (int)($cy - $lineW),
        (int)($cx + $r * 0.55),
        (int)($cy + $lineW),
        $wh
    );

    $filename = "$outputDir/icon-{$size}x{$size}.png";
    imagepng($img, $filename);
    imagedestroy($img);

    echo "✅ Generated: icon-{$size}x{$size}.png\n";
}

echo "\n🎉 All PWA icons generated in: $outputDir\n";
echo "📋 Add them to your manifest.json icons array.\n";
