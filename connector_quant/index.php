<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Стягивание показаний Quant</title>
    <link href='https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Roboto', sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; color: #333; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 30px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: relative; overflow: hidden; }
        .header::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #4CAF50, #45a049); }
        .header img { margin-bottom: 20px; transition: transform 0.3s ease; }
        .header img:hover { transform: scale(1.05); }
        .header h1 { color: #2c3e50; margin: 0; font-size: 28px; font-weight: 500; text-shadow: 1px 1px 2px rgba(0,0,0,0.1); }
        .container { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; max-width: 1400px; margin: 0 auto; }
        .table-container { width: 45%; min-width: 300px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .table-container:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
        .table-container h2 { color: #2c3e50; margin-top: 0; font-size: 22px; font-weight: 500; border-bottom: 2px solid #4CAF50; padding-bottom: 12px; display: flex; align-items: center; }
        .table-container h2::before { content: '📊'; margin-right: 10px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 20px; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; border: 1px solid #e0e0e0; text-align: left; }
        th { background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%); color: white; font-weight: 500; text-transform: uppercase; font-size: 14px; letter-spacing: 0.5px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f5f5f5; transition: background-color 0.3s ease; }
        #startButton { padding: 12px 24px; font-size: 16px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; transition: all 0.3s ease; }
        #startButton:hover { background-color: #45a049; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        #startButton:disabled { background-color: #cccccc; cursor: not-allowed; transform: none; box-shadow: none; }
    </style>
</head>
<body>
    <div class='header'>
        <img src='/test/logo/oimo_billing_logo.png' alt='Логотип' class='logo' width='150' height='auto'>
        <h1>Стягивание показаний Quant</h1>
        <div style='margin: 20px 0;'>
            <button onclick='startProcess()' id='startButton'>Начать стягивание</button>
            <button onclick='stopProcess()' id='stopButton' style='margin-left:10px; display:none; background:#f44336;'>Остановить</button>
        </div>
    </div>
    <div class='container'>
        <div class='table-container'>
            <h2>Счетчики для обработки</h2>
            <div id="metersTable"></div>
        </div>
        <div class='table-container'>
            <h2>Логи</h2>
            <div id="logsTable"></div>
        </div>
    </div>
    <script>
    let stopRequested = false;

    function stopProcess() {
        stopRequested = true;
        document.getElementById('stopButton').style.display = 'none';
        document.getElementById('startButton').disabled = false;
        document.getElementById('startButton').innerHTML = 'Начать стягивание';
    }

    function loadData() {
        fetch('connector_quant.php')
            .then(response => response.json())
            .then(data => {
                // Счетчики
                let metersHtml = '<table><thead><tr><th>#</th><th>Номер счетчика</th></tr></thead><tbody>';
                if (data.meters && data.meters.length > 0) {
                    data.meters.forEach((meter, idx) => {
                        metersHtml += `<tr><td>${idx + 1}</td><td>${meter}</td></tr>`;
                    });
                } else {
                    metersHtml += '<tr><td colspan="2" style="text-align:center; color:#999;">Нет счетчиков</td></tr>';
                }
                metersHtml += '</tbody></table>';
                document.getElementById('metersTable').innerHTML = metersHtml;

                // Логи
                let logsHtml = '<table><thead><tr><th>Сообщение</th></tr></thead><tbody>';
                if (data.logs && data.logs.length > 0) {
                    data.logs.forEach(log => {
                        logsHtml += `<tr><td>${log.trim()}</td></tr>`;
                    });
                } else {
                    logsHtml += '<tr><td style="text-align:center; color:#999;">Нет логов</td></tr>';
                }
                logsHtml += '</tbody></table>';
                document.getElementById('logsTable').innerHTML = logsHtml;
            })
            .catch(error => {
                console.error('Ошибка загрузки данных:', error);
            });
    }

    // Загружаем данные при открытии страницы
    loadData();

    function startProcess() {
        stopRequested = false;
        document.getElementById('startButton').disabled = true;
        document.getElementById('startButton').innerHTML = 'Синхронизация...';
        document.getElementById('stopButton').style.display = '';
        
        // Выполняем синхронизацию всех счетчиков одним запросом
        fetch('connector_quant.php?sync_meters')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById('startButton').innerHTML = `Завершено! Обработано: ${data.processed}, Пропущено: ${data.skipped}`;
                } else {
                    document.getElementById('startButton').innerHTML = `Ошибка: ${data.message || 'Неизвестная ошибка'}`;
                }
                document.getElementById('stopButton').style.display = 'none';
                document.getElementById('startButton').disabled = false;
                loadData();
            })
            .catch(error => {
                document.getElementById('startButton').innerHTML = 'Ошибка подключения';
                document.getElementById('stopButton').style.display = 'none';
                document.getElementById('startButton').disabled = false;
                console.error('Ошибка:', error);
                loadData();
            });
    }
    </script>
</body>
</html> 