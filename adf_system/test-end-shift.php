<?php
/**
 * Test End Shift Feature
 * Quick testing page to verify the feature works
 */
session_start();

// For testing, set a test user if not logged in
if (!isset($_SESSION['user_id'])) {
    // Try to auto-login with test user
    $conn = new mysqli('localhost', 'root', '', 'adf_system');
    $result = $conn->query("SELECT id FROM users LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = 'testuser';
        $_SESSION['role'] = 'staff';
    }
    $conn->close();
}

$loggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test End Shift Feature</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: 500;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .test-section {
            margin: 20px 0;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            max-height: 300px;
            overflow-y: auto;
        }
        .button-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 End Shift Feature - Test Page</h1>
        
        <?php if ($loggedIn): ?>
            <div class="status success">✅ You are logged in. Ready to test!</div>
        <?php else: ?>
            <div class="status error">❌ Not logged in. Please login first.</div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>Test 1: API Response Format</h2>
        <p>Test that the API returns valid JSON (not HTML)</p>
        <button onclick="testAPI()">Test API Response</button>
        <pre id="api-response"></pre>
    </div>

    <div class="card">
        <h2>Test 2: Fetch Report Data</h2>
        <p>Test fetching today's transaction report</p>
        <button onclick="testReportData()">Fetch Report</button>
        <pre id="report-response"></pre>
    </div>

    <div class="card">
        <h2>Test 3: JavaScript API Check</h2>
        <p>Verify jQuery and other dependencies are loaded</p>
        <button onclick="testJsEnvironment()">Check JS Environment</button>
        <pre id="js-response"></pre>
    </div>

    <div class="card">
        <h2>Test 4: End Shift Modal</h2>
        <p>Simulate clicking the End Shift button (if included in header.php)</p>
        <button onclick="testEndShiftModal()">Show Modal</button>
    </div>

    <script>
        async function testAPI() {
            const responseEl = document.getElementById('api-response');
            responseEl.textContent = 'Testing...';
            
            try {
                const response = await fetch('/api/end-shift.php', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                const text = await response.text();
                const obj = JSON.parse(text); // Will throw if not valid JSON
                
                responseEl.textContent = JSON.stringify(obj, null, 2);
                if (obj.status === 'success') {
                    responseEl.className = 'status success';
                    responseEl.textContent = '✅ API returned valid JSON\n\n' + JSON.stringify(obj, null, 2);
                } else {
                    responseEl.className = 'status info';
                    responseEl.textContent = '✓ Valid JSON (but error status)\n\n' + JSON.stringify(obj, null, 2);
                }
            } catch (e) {
                responseEl.className = 'status error';
                responseEl.textContent = '❌ API Error:\n' + e.message + '\n\nResponse text:\n' + text;
            }
        }
        
        async function testReportData() {
            const responseEl = document.getElementById('report-response');
            responseEl.textContent = 'Fetching...';
            
            try {
                const response = await fetch('/api/end-shift.php', {
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                if (data.status === 'success' && data.data) {
                    responseEl.className = 'status success';
                    responseEl.textContent = '✅ Report Data Retrieved:\n\n' + JSON.stringify(data.data, null, 2);
                } else {
                    responseEl.className = 'status error';
                    responseEl.textContent = '❌ ' + (data.message || 'No data returned') + '\n\n' + JSON.stringify(data, null, 2);
                }
            } catch (e) {
                responseEl.className = 'status error';
                responseEl.textContent = '❌ Error: ' + e.message;
            }
        }
        
        function testJsEnvironment() {
            const responseEl = document.getElementById('js-response');
            let checks = '';
            
            checks += (typeof fetch !== 'undefined' ? '✅' : '❌') + ' fetch API available\n';
            checks += (typeof XMLHttpRequest !== 'undefined' ? '✅' : '❌') + ' XMLHttpRequest available\n';
            checks += (typeof Promise !== 'undefined' ? '✅' : '❌') + ' Promises available\n';
            checks += (typeof JSON !== 'undefined' ? '✅' : '❌') + ' JSON object available\n';
            checks += (typeof document !== 'undefined' ? '✅' : '❌') + ' DOM available\n';
            checks += (typeof console !== 'undefined' ? '✅' : '❌') + ' Console available\n';
            
            responseEl.textContent = checks;
            responseEl.className = 'status success';
        }
        
        function testEndShiftModal() {
            const responseEl = document.getElementById('api-response');
            responseEl.textContent = 'Modal not implemented in test page.\nCheck if initiateEndShift() is available in your header.';
            
            if (typeof initiateEndShift === 'function') {
                responseEl.textContent = 'Calling initiateEndShift()...';
                initiateEndShift();
            }
        }
    </script>
</body>
</html>
