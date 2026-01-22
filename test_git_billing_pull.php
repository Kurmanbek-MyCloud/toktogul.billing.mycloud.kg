<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Токтогул Биллинг - Добро пожаловать!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            z-index: 10;
            padding: 40px;
        }

        .logo {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        h1 {
            color: #e94560;
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(233, 69, 96, 0.5);
            animation: glow 2s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 20px rgba(233, 69, 96, 0.5); }
            to { text-shadow: 0 0 40px rgba(233, 69, 96, 0.8), 0 0 60px rgba(233, 69, 96, 0.4); }
        }

        .subtitle {
            color: #a2d5f2;
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .info-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 20px auto;
            max-width: 500px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(50px);
            opacity: 0;
            animation: slideUp 0.8s ease-out 1s forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }

        .info-box p {
            color: #fff;
            margin: 10px 0;
            font-size: 1.1rem;
        }

        .info-box .label {
            color: #a2d5f2;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(45deg, #00b894, #00cec9);
            color: #fff;
            border-radius: 30px;
            font-weight: bold;
            margin-top: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 10px;
            height: 10px;
            background: rgba(233, 69, 96, 0.6);
            border-radius: 50%;
            animation: float 15s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .wave {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 200px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23e94560' fill-opacity='0.2' d='M0,192L48,176C96,160,192,128,288,133.3C384,139,480,181,576,181.3C672,181,768,139,864,128C960,117,1056,139,1152,154.7C1248,171,1344,181,1392,186.7L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E") repeat-x;
            animation: wave 10s linear infinite;
        }

        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .version {
            color: rgba(255, 255, 255, 0.5);
            margin-top: 30px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="particles">
        <?php for ($i = 0; $i < 20; $i++): ?>
            <div class="particle" style="
                left: <?= rand(0, 100) ?>%;
                animation-delay: <?= rand(0, 15) ?>s;
                animation-duration: <?= rand(10, 20) ?>s;
                width: <?= rand(5, 15) ?>px;
                height: <?= rand(5, 15) ?>px;
                background: <?= ['rgba(233, 69, 96, 0.6)', 'rgba(162, 213, 242, 0.6)', 'rgba(0, 184, 148, 0.6)'][rand(0, 2)] ?>;
            "></div>
        <?php endfor; ?>
    </div>

    <div class="wave"></div>

    <div class="container">
        <div class="logo">⚡</div>
        <h1>Токтогул Биллинг</h1>
        <p class="subtitle">Система учёта и управления</p>

        <div class="info-box">
            <p><span class="label">Сервер:</span> <?= gethostname() ?></p>
            <p><span class="label">PHP версия:</span> <?= phpversion() ?></p>
            <p><span class="label">Дата:</span> <?= date('d.m.Y H:i:s') ?></p>
            <p><span class="label">IP:</span> <?= $_SERVER['SERVER_ADDR'] ?? 'localhost' ?></p>
            <div class="status">✓ Система работает</div>
        </div>

        <p class="version">v1.0.0 | Git Pull Test</p>
    </div>

    <script>
        // Добавляем интерактивность - частицы следуют за курсором
        document.addEventListener('mousemove', (e) => {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 5px;
                height: 5px;
                background: rgba(233, 69, 96, 0.8);
                border-radius: 50%;
                pointer-events: none;
                left: ${e.clientX}px;
                top: ${e.clientY}px;
                animation: fadeOut 1s forwards;
            `;
            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 1000);
        });

        // Стиль для затухания частиц
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                0% { transform: scale(1); opacity: 1; }
                100% { transform: scale(0); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
