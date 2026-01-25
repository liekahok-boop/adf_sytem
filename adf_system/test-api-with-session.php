<?php
// Login test script
session_start();

// Set test user
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role'] = 'admin';

// Now test the API
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test End Shift API</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 Test End Shift API</h1>
    
    <div class="box success">
        <h3>✅ Session Created</h3>
        <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
        <p>Role: <?php echo $_SESSION['role']; ?></p>
    </div>
    
    <div class="box">
        <h3>📊 API Test Results</h3>
        <p>Testing: <code>http://localhost:9999/api/end-shift.php</code></p>
        <pre id="result">Loading...</pre>
    </div>
    
    <script>
        async function testAPI() {
            try {
                const response = await fetch('/api/end-shift.php', {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                
                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                    document.getElementById('result').textContent = JSON.stringify(result, null, 2);
                    document.querySelector('.box').classList.add('success');
                } catch (e) {
                    document.getElementById('result').textContent = 'ERROR - NOT JSON:\n' + text.substring(0, 500);
                    document.querySelector('.box').classList.add('error');
                }
            } catch (error) {
                document.getElementById('result').textContent = 'FETCH ERROR: ' + error.message;
                document.querySelector('.box').classList.add('error');
            }
        }
        
        // Auto test on page load
        window.addEventListener('load', testAPI);
    </script>
</body>
</html>
