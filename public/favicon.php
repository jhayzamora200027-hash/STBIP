<?php
// Serve the STB PNG as the favicon when browsers request /favicon.ico
// This avoids needing a binary .ico file and ensures browsers get the correct image.

$png = __DIR__ . '/images/dattachments/social technology bureau innovating solution logo.png';
if (!file_exists($png)) {
    http_response_code(404);
    exit('Not found');
}

// Send image as PNG (browsers accept PNG favicons)
header('Content-Type: image/png');
// Prevent caching during development to make updates visible
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($png);
exit;
