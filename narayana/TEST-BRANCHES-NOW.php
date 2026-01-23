<!DOCTYPE html>
<html>
<head>
    <title>Test Owner Branches API - NOW</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a2e; color: #eee; }
        pre { background: #16213e; padding: 15px; border-radius: 5px; overflow: auto; }
        .success { color: #0f0; }
        .error { color: #f00; }
    </style>
</head>
<body>
    <h2>🧪 Test Owner Branches API</h2>
    <p>Testing: <code>api/owner-branches.php</code></p>
    
    <h3>API Response:</h3>
    <pre><?php
    // Start session untuk login
    session_start();
    
    // Simulate logged in as admin
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
    $_SESSION['business_access'] = '[1,2]';
    $_SESSION['logged_in'] = true;
    
    // Call API
    $apiUrl = 'http://localhost:8080/narayana/api/owner-branches.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n\n";
    
    $data = json_decode($response, true);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($data && $data['success']) {
        echo "\n\n";
        echo '<span class="success">✓ API SUCCESS</span>' . "\n";
        echo "Total businesses in DB: " . $data['user_info']['total_businesses'] . "\n";
        echo "Accessible to user: " . $data['user_info']['accessible_businesses'] . "\n";
        
        if (!empty($data['branches'])) {
            echo "\n<span class='success'>✓ BRANCHES FOUND:</span>\n";
            foreach ($data['branches'] as $branch) {
                echo "  - ID {$branch['id']}: {$branch['branch_name']}\n";
            }
        } else {
            echo "\n<span class='error'>✗ NO BRANCHES RETURNED</span>\n";
        }
    } else {
        echo "\n\n<span class='error'>✗ API FAILED</span>\n";
    }
    ?></pre>
    
    <p><a href="modules/owner/dashboard.php">← Back to Owner Dashboard</a></p>
</body>
</html>
