<?php
if ($argc < 2) {
    fwrite(STDERR, "Usage: php extract_logo_palette.php <image-path>\n");
    exit(1);
}
$path = $argv[1];
if (!file_exists($path)) {
    fwrite(STDERR, "File not found: $path\n");
    exit(2);
}
$contents = @file_get_contents($path);
if ($contents === false) {
    fwrite(STDERR, "Unable to read file: $path\n");
    exit(3);
}
$img = @imagecreatefromstring($contents);
if ($img === false) {
    fwrite(STDERR, "Unsupported image or GD not available\n");
    exit(4);
}
$w = imagesx($img);
$h = imagesy($img);
$counts = [];
// Sample every pixel but skip large images to speed up
$step = 1;
if ($w * $h > 500000) {
    $step = 3;
}
for ($y = 0; $y < $h; $y += $step) {
    for ($x = 0; $x < $w; $x += $step) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        // reduce precision to cluster similar colors
        $r2 = ($r >> 3) << 3;
        $g2 = ($g >> 3) << 3;
        $b2 = ($b >> 3) << 3;
        $key = sprintf('%02x%02x%02x', $r2, $g2, $b2);
        if (!isset($counts[$key])) $counts[$key] = 0;
        $counts[$key]++;
    }
}
arsort($counts);
$top = array_slice($counts, 0, 5, true);
$hexs = array_keys($top);
echo json_encode(array_values($hexs));

?>