<?php
/**
 * Proper test of login flow with cookie persistence
 */
echo "=== LOGIN FLOW TEST WITH PROPER COOKIE HANDLING ===\n\n";

// Create temp file for cookies
$cookieFile = sys_get_temp_dir() . '/curl_cookies_' . uniqid() . '.txt';

// STEP 1: POST login
echo "STEP 1: Submit Login Form\n";
echo "URL: http://localhost:8080/adf_system/login.php\n";
echo "POST: username=admin&password=admin&business=narayana-hotel\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/adf_system/login.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=admin&business=narayana-hotel");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);  // SAVE COOKIES
curl_setopt($ch, CURLOPT_COOKIE, '');  // Clear any existing

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Check response
list($headers, $body) = explode("\r\n\r\n", $response, 2);
echo "Response Status: HTTP $httpCode\n";

// Extract Set-Cookie
preg_match('/Set-Cookie: ([^;]+)/i', $headers, $matches);
if (!empty($matches[1])) {
    echo "Set-Cookie Header: " . $matches[1] . "\n";
} else {
    echo "ERROR: No Set-Cookie header!\n";
}

// Check Location header
if (preg_match('/Location: ([^\r\n]+)/i', $headers, $matches)) {
    echo "Redirect To: " . trim($matches[1]) . "\n";
}
echo "\n";

// STEP 2: Check cookie file
echo "STEP 2: Verify Cookies Saved\n";
if (file_exists($cookieFile)) {
    echo "Cookie file created: YES\n";
    $cookieContent = file_get_contents($cookieFile);
    if (strpos($cookieContent, 'NARAYANA_SESSION') !== false) {
        echo "NARAYANA_SESSION cookie present: YES\n";
    } else {
        echo "NARAYANA_SESSION cookie present: NO\n";
    }
} else {
    echo "Cookie file created: NO\n";
}
echo "\n";

// STEP 3: Now access index.php WITH the saved cookies
echo "STEP 3: Access index.php With Saved Cookies\n";
echo "URL: http://localhost:8080/adf_system/index.php\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8080/adf_system/index.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);  // USE SAVED COOKIES
curl_setopt($ch, CURLOPT_COOKIE, '');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

list($headers, $body) = explode("\r\n\r\n", $response, 2);

echo "Response Status: HTTP $httpCode\n";
if ($httpCode == 302) {
    preg_match('/Location: ([^\r\n]+)/i', $headers, $matches);
    echo "Redirected To: " . (isset($matches[1]) ? trim($matches[1]) : 'unknown') . "\n";
    echo "RESULT: ❌ LOGIN FAILED - Still redirected back to login\n";
} elseif ($httpCode == 200) {
    echo "RESULT: ✅ LOGIN SUCCESS - Dashboard page loaded\n";
    // Check if page contains dashboard content
    if (stripos($body, 'logout') !== false) {
        echo "Verification: Logout button found - User IS logged in\n";
    }
} else {
    echo "Unexpected status code: $httpCode\n";
}
echo "\n";

// Cleanup
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

echo "=== TEST COMPLETE ===\n";
?>
