<?php
// Temporary script to check image dimensions in uploads directory

$dirs = [
    'slider' => __DIR__ . '/uploads/slider/',
    'accolades' => __DIR__ . '/uploads/accolades/',
    'service' => __DIR__ . '/uploads/service/'
];

$output = "IMAGE DIMENSION REPORT\n";
$output .= "======================\n\n";

foreach ($dirs as $key => $path) {
    $output .= "Directory: $key ($path)\n";
    $output .= "--------------------------------------------------\n";
    if (!is_dir($path)) {
        $output .= "Directory not found!\n\n";
        continue;
    }
    
    $files = glob($path . '*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $size = getimagesize($file);
                if ($size) {
                    $width = $size[0];
                    $height = $size[1];
                    $ratio = $width / $height;
                    $output .= basename($file) . " -> {$width}x{$height} (Ratio: " . round($ratio, 2) . ")\n";
                    $count++;
                    if ($count >= 5) {
                        $output .= "... and more files\n";
                        break;
                    }
                }
            }
        }
    }
    if ($count === 0) {
        $output .= "No images found.\n";
    }
    $output .= "\n";
}

file_put_contents(__DIR__ . '/check_dimensions.txt', $output);
echo "Report written to check_dimensions.txt";
