<!DOCTYPE html>
<html>
<head>
    <title>Test Chart Data API</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a2e; color: #eee; }
        pre { background: #16213e; padding: 15px; border-radius: 5px; overflow: auto; white-space: pre-wrap; }
        .success { color: #0f0; }
        .error { color: #f00; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>🧪 Test Owner Chart Data API</h2>
    
    <div>
        <button onclick="testAPI('7days')">Test 7 Days</button>
        <button onclick="testAPI('30days')">Test 30 Days</button>
        <button onclick="testAPI('12months')">Test 12 Months</button>
    </div>
    
    <h3>Result:</h3>
    <pre id="result">Click a button to test...</pre>
    
    <script>
        async function testAPI(period) {
            const resultDiv = document.getElementById('result');
            resultDiv.textContent = 'Loading...';
            
            try {
                const response = await fetch(`../../api/owner-chart-data.php?period=${period}`);
                const data = await response.json();
                
                resultDiv.innerHTML = `<span class="success">✓ HTTP ${response.status}</span>\n\n`;
                resultDiv.innerHTML += JSON.stringify(data, null, 2);
                
                if (data.success) {
                    resultDiv.innerHTML += `\n\n<span class="success">✓ API SUCCESS</span>\n`;
                    resultDiv.innerHTML += `Labels: ${data.data.labels.length} items\n`;
                    resultDiv.innerHTML += `Income data points: ${data.data.income.length}\n`;
                    resultDiv.innerHTML += `Expense data points: ${data.data.expense.length}\n`;
                } else {
                    resultDiv.innerHTML += `\n\n<span class="error">✗ API FAILED</span>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<span class="error">✗ ERROR: ${error.message}</span>`;
            }
        }
        
        // Auto test on load
        window.onload = () => testAPI('7days');
    </script>
</body>
</html>
