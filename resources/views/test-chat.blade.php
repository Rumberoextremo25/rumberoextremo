<!DOCTYPE html>
<html>
<head>
    <title>Test Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Chat API</h1>
    <div id="result"></div>
    
    <script>
    async function testAPI() {
        try {
            const response = await fetch('/api/ia/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ mensaje: 'Hola prueba' })
            });
            
            const data = await response.json();
            document.getElementById('result').innerHTML = `
                <h3>Status: ${response.status}</h3>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
        } catch (error) {
            document.getElementById('result').innerHTML = `
                <h3 style="color: red">ERROR</h3>
                <pre>${error.message}</pre>
            `;
        }
    }
    
    testAPI();
    </script>
</body>
</html>