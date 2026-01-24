<?php
/**
 * Check if Set-Cookie headers are being sent
 */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/adf_system/login.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=admin&business=narayana-hotel");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);  // Get headers too
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);

// Split headers and body
list($headers, $body) = explode("\r\n\r\n", $response, 2);

echo "=== HTTP RESPONSE HEADERS ===\n";
echo $headers . "\n\n";

echo "=== SET-COOKIE HEADERS ===\n";
if (strpos($headers, 'Set-Cookie') !== false) {
    $lines = explode("\r\n", $headers);
    foreach ($lines as $line) {
        if (stripos($line, 'Set-Cookie') !== false) {
            echo $line . "\n";
        }
    }
} else {
    echo "NO SET-COOKIE HEADERS FOUND!\n";
}
?>
