<?php

class QRApiRSK {
    private $baseUrl = 'https://elqr.rsktech.kg';
    private $apiKey = '00000000-0000-4000-8000-000000000001';

    private function request($method, $endpoint, $data = null) {
        $url = $this->baseUrl . $endpoint;
        $headers = [
            'x-api-key: ' . $this->apiKey,
        ];
        if ($method === 'POST') {
            $headers[] = 'Content-Type: application/json';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        $result = json_decode($response, true);
        if ($httpCode === 401) {
            throw new Exception('Unauthorized: Invalid API key');
        }
        if (isset($result['err'])) {
            throw new Exception('API error: ' . $result['err']['error']);
        }
        return $result;
    }

    // Генерация UUID v4
    private function generateUUID() {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Генерация QR-кода
     * 
     * @param string|int $account - лицевой счёт клиента
     * @param float $amount - сумма платежа
     * @param int $service - id услуги
     * 
     * Примечание: service передается в поле "name" при запросе к API.
     * В callback можно получить service из поля name в ответе.
     * 
     * Пример: $qrApi->generateQR(100001, 5.11, 1, 180)
     */
    public function generateQR($account, $amount, $service) {
        if (empty($account) || empty($amount)) {
            throw new InvalidArgumentException('account и amount обязательны');
        }
        $payload = [
            'id' => $this->generateUUID(),
            'amount' => $amount,
            'name' => $service,
            'account' => "$account",
            'ttl' => 0
        ];
        return [
            'id' => $payload['id'],
            'response' => $this->request('POST', '/api/v1/qr/generate', $payload)
        ];
    }

    // Получение статуса транзакции
    public function getStatus($transactionId) {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('transactionId обязателен');
        }
        return $this->request('GET', '/api/v1/qr/state/' . urlencode($transactionId));
    }

    // Получение завершённых транзакций за период
    public function getCompletedTransactions($startDateTime, $endDateTime) {
        if (empty($startDateTime) || empty($endDateTime)) {
            throw new InvalidArgumentException('startDateTime и endDateTime обязательны');
        }
        $data = [
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime
        ];
        return $this->request('POST', '/api/v1/qr/completed-transactions', $data);
    }
}
