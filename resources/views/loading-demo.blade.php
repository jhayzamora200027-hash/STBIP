<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Screen Demo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .demo-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .demo-controls button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
            transition: background 0.3s;
        }

        .demo-controls button:hover {
            background: #764ba2;
        }

        .demo-info {
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 20px;
        }

        .demo-info h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .demo-info p {
            font-size: 1.2rem;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-overlay.hidden {
            display: none;
        }

        /********************  Preloader Demo-10 *******************/
        .loader10{width:100px;height:100px;margin:50px auto;overflow:hidden;position:relative}
        .loader10 span{display:inline-block;position:absolute;animation:loading-10 9s cubic-bezier(.45,.05,.55,.95) infinite}
        .loader10 span:nth-child(1){background:#ff4b7d;animation-name:loading-10}
        .loader10 span:nth-child(2){background:#3485ef;animation-name:loading-102}
        .loader10 span:nth-child(3){background:#5fad56;animation-name:loading-103}
        .loader10 span:nth-child(4){background:#e9573d;animation-name:loading-104}
        @keyframes loading-10{
            0%,5%{width:25%;height:25%;border-radius:100% 0 0;background:#ff4b7d;bottom:50%;left:25%}
            10%{width:25%;height:25%;border-radius:100% 100% 0 0;background:#ff4b7d;bottom:50%;left:25%}
            13%,18%{width:25%;height:25%;border-radius:100% 100% 0 0;background:#5fad56;bottom:50%;left:12.5%}
            20%{width:32.5%;height:32.5%;border-radius:50%;background:#5fad56;bottom:50%;left:6.25%}
            25%,30%{width:25%;height:25%;border-radius:50%;background:#3485ef;bottom:62.5%;left:12.5%}
            35%{width:14%;height:10%;border-radius:999px;background:#ff4b7d;left:0;bottom:0}
            40%,60%{height:100%}
            55%{height:10%}
            70%{width:14%;height:25%;border-radius:999px;background:#ff4b7d;bottom:0;left:0}
            75%,97%{width:25%;height:25%;border-radius:100%;bottom:57.5%;left:17.5%}
            100%{width:50%;height:50%;border-radius:100%;bottom:25%;left:25%}
        }
        @keyframes loading-102{
            0%,5%{width:25%;height:25%;background:#ff4b7d;border-radius:0 0 0 100%;bottom:25%;left:25%}
            10%{width:25%;height:25%;background:#ff4b7d;border-radius:0 0 100% 100%;bottom:25%;left:25%}
            13%,18%{width:25%;height:25%;background:#5fad56;border-radius:0 0 100% 100%;bottom:25%;left:12.5%}
            20%{width:32.5%;height:32.5%;background:#5fad56;border-radius:50%;bottom:25%;left:6.25%}
            25%{width:25%;height:25%;background:#3485ef;border-radius:50%;bottom:12.5%;left:12.5%}
            30%{left:12.5%;bottom:12.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:28%;bottom:0;border-radius:999px;height:10%;width:14%;background:#3485EF}
            40%,60%{height:10%}
            45%,65%{height:100%}
            75%{left:28%;bottom:0;border-radius:999px;height:25%;width:14%;background:#3485EF}
            80%{left:17.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%}
            97%{left:17.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #3485EF;border-radius:100%;left:25%;bottom:-50%;height:50%;width:50%}
        }
        @keyframes loading-103{
            0%,5%{left:50%;bottom:50%;border-radius:0 100% 0 0;height:25%;width:25%;background:#FF4B7D}
            10%{left:50%;bottom:50%;border-radius:100% 100% 0 0;height:25%;width:25%;background:#FF4B7D}
            13%,18%{left:62.5%;bottom:50%;border-radius:100% 100% 0 0;height:25%;width:25%;background:#5FAD56}
            20%{left:66.25%;bottom:50%;border-radius:50%;height:32.5%;width:32.5%;background:#5FAD56}
            25%,30%{left:62.5%;bottom:62.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:56%;bottom:0;border-radius:999px;height:10%;width:14%;background:#5FAD56}
            45%,65%{height:10%}
            50%,70%{height:100%}
            80%{left:56%;bottom:0;border-radius:999px;height:25%;width:14%;background:#5FAD56}
            85%{left:57.5%;bottom:57.5%;border-radius:100%;height:25%;width:25%}
            97%{left:57.5%;bottom:57.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #5FAD56;border-radius:100%;left:100%;bottom:25%;height:50%;width:50%}
        }
        @keyframes loading-104{
            0%,5%{left:50%;bottom:25%;border-radius:0 0 100%;height:25%;width:25%;background:#FF4B7D}
            10%{left:50%;bottom:25%;border-radius:0 0 100% 100%;height:25%;width:25%;background:#FF4B7D}
            13%,18%{left:62.5%;bottom:25%;border-radius:0 0 100% 100%;height:25%;width:25%;background:#5FAD56}
            20%{left:66.25%;bottom:25%;border-radius:50%;height:32.5%;width:32.5%;background:#5FAD56}
            25%,30%{left:62.5%;bottom:12.5%;border-radius:50%;height:25%;width:25%;background:#3485EF}
            35%{left:84%;bottom:0;border-radius:999px;height:10%;width:14%;background:#e9573d}
            50%,70%{height:10%}
            55%,75%{height:100%}
            85%{left:84%;bottom:0;border-radius:999px;height:25%;width:14%;background:#E9573D}
            90%{left:57.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%}
            97%{left:57.5%;bottom:17.5%;border-radius:100%;height:25%;width:25%;box-shadow:none}
            100%{box-shadow:-3px -3px 5px -5px #e9573d;border-radius:100%;left:100%;bottom:-50%;height:50%;width:50%}
        }
    </style>
</head>
<body>
    {{-- Demo Controls --}}
    <div class="demo-controls">
        <h3 style="margin: 0 0 10px 0; color: #667eea;">Loading Demo Controls</h3>
        <button onclick="showLoader()">Show Loading</button>
        <button onclick="hideLoader()">Hide Loading</button>
        <button onclick="toggleLoader()">Toggle Loading</button>
    </div>

    {{-- Page Content --}}
    <div class="demo-info">
        <h1>🎨 Loading Screen Demo</h1>
        <p>Use the controls in the top-right corner to show or hide the loading animation.</p>
        <p>This is how your loading screen will appear throughout the application.</p>
    </div>

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="loading-overlay">
        <div class="loader10">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <script>
        // Show loader function
        function showLoader() {
            document.getElementById('loading-overlay').classList.remove('hidden');
        }

        // Hide loader function
        function hideLoader() {
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        // Toggle loader function
        function toggleLoader() {
            document.getElementById('loading-overlay').classList.toggle('hidden');
        }

        // Show loader initially for demo
        window.addEventListener('load', function() {
            showLoader();
            // Auto hide after 2 seconds
            setTimeout(hideLoader, 2000);
        });
    </script>
</body>
</html>
