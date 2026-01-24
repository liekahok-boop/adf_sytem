<?php
/**
 * Test session persistence across requests
 */

echo "=== SESSION PERSISTENCE TEST ===\n\n";

// Step 1: Login and get cookies
echo "STEP 1: Login\n";
$loginUrl = "http://localhost:8080/adf_system/login.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=admin&business=narayana-hotel");
curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookies.txt");  // Save cookies
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);  // Don't follow redirect
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
if ($httpCode == 302 || $httpCode == 301) {
    $locationHeader = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    echo "Redirect Location: " . (isset($locationHeader) ? $locationHeader : 'unknown') . "\n";
}
echo "Response contains 'login': " . (strpos($response, 'login') !== false ? 'YES' : 'NO') . "\n\n";

// Step 2: Check if session file was created
echo "STEP 2: Check Cookies File\n";
if (file_exists("/tmp/cookies.txt")) {
    $cookieContent = file_get_contents("/tmp/cookies.txt");
    echo "Cookies saved: YES\n";
    echo "Cookie file size: " . strlen($cookieContent) . " bytes\n";
    // Show NARAYANA_SESSION cookie
    if (strpos($cookieContent, 'NARAYANA_SESSION') !== false) {
        echo "NARAYANA_SESSION cookie found: YES\n";
    }
} else {
    echo "Cookies saved: NO\n";
}
echo "\n";

// Step 3: Access home page with saved session
echo "STEP 3: Access Home Page With Cookies\n";
$homeUrl = "http://localhost:8080/adf_system/home.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $homeUrl);
curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookies.txt");  // Use saved cookies
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
if ($httpCode == 302 || $httpCode == 301) {
    echo "Redirected (probably to login)\n";
} elseif ($httpCode == 200) {
    echo "Page loaded successfully!\n";
    echo "Contains 'dashboard' or 'logout': " . (preg_match('/dashboard|logout/i', $response) ? 'YES' : 'NO') . "\n";
}
?>
