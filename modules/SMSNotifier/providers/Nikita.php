<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class SMSNotifier_Nikita_Provider implements SMSNotifier_ISMSProvider_Model {

    private $userName;
    private $password;
    private $parameters = array();

    const SERVICE_URI = 'https://smspro.nikita.kg';
    private static $REQUIRED_PARAMETERS = array('sender_name');

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'Nikita';
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get service URL to use for a given type
     * @param <String> $type like SEND, PING, QUERY
     */
    public function getServiceURL($type = false) {
        if($type) {
            switch(strtoupper($type)) {
                case self::SERVICE_AUTH: return  self::SERVICE_URI . '/api/message';
                case self::SERVICE_SEND: return  self::SERVICE_URI . '/api/message';
                case self::SERVICE_QUERY: return self::SERVICE_URI . '/api/dr';
            }
        }
        return false;
    }

    /**
     * Function to set authentication parameters
     * @param <String> $userName
     * @param <String> $password
     */
    public function setAuthParameters($userName, $password) {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false) {
        if(isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to prepare parameters
     * @return <Array> parameters
     */
    protected function prepareParameters() {
        $params = array('user' => $this->userName, 'pwd' => $this->password);
        foreach (self::$REQUIRED_PARAMETERS as $key) {
            $params[$key] = $this->getParameter($key);
        }
        return $params;
    }

    private function post_content($url, $postdata)
    {
        $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "c://coo.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE,"c://coo.txt");

        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch);
        curl_close($ch);

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $header;
    }

    private function sendSMS($params, $toNumber, $id) // $phoneNumber, $message, $transactionID
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
            "<message>".
            "<login>" . $params['user'] . "</login>".
            "<pwd>" . $params['pwd'] . "</pwd>".
            "<sender>" . $params['sender_name'] . "</sender>".
            "<phones>".
            "<phone>" . $toNumber . "</phone>".
            "</phones>".
            "<text>" . $params['text'] . "</text>".
            "<id>" . $id . "</id>".
            "</message>";

        try
        {
            $url = $this->getServiceURL(self::SERVICE_SEND);
            $result = $this->post_content($url ,$xml);
            $responseXML = $result['content'];
            $response = new SimpleXMLElement($responseXML);
            return $response;
        }
        catch(Exception $e)
        {
            $message = $e->getMessage();
            $trace = $e->getTrace();
        }
        return false;
    }

    private function sendAllSMS($params) {

        global $adb;

        $adb->query("update max_sms_id set id = @id := id + 1");
        $id = $adb->run_query_field("select @id");

        $toNumbers = $params['to'];
        $response = array();

        foreach ($toNumbers as $toNumber) {
            $toNumber = preg_replace('/\D+/', '', $toNumber);
            $toNumber = '996' . substr($toNumber, -9);
            $response[] = $this->sendSMS($params, $toNumber, $id);
        }

        return $response;
    }

    /**
     * Function to handle SMS Send operation
     * @param <String> $message
     * @param <Mixed> $toNumbers One or Array of numbers
     */
    public function send($message, $toNumbers) {
        if(!is_array($toNumbers)) {
            $toNumbers = array($toNumbers);
        }

        $params = $this->prepareParameters();
        $params['text'] = $message;
        $params['to'] = $toNumbers;

        $response = $this->sendAllSMS($params);
        $results = array();
        $i = 0;
        foreach($response as $responseLine) {
            $result = array();
            if($responseLine->status == 0) {
                $result['id'] = $responseLine->id;
                $result['to'] = $toNumbers[$i++];
                $result['status'] = self::MSG_STATUS_DISPATCHED;
            } else if($responseLine->status == 1) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Ошибка в формате запроса';
            } else if($responseLine->status == 2) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Неверная авторизация';
            } else if($responseLine->status == 3) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Недопустимый IP-адрес отправителя';
            } else if($responseLine->status == 4) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Недостаточно средств на счету клиента';
            } else if($responseLine->status == 5) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Недопустимое имя отправителя';
            } else if($responseLine->status == 6) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Сообщение заблокировано по стоп-словам';
            } else if($responseLine->status == 7) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Некорректное написание номера телефона';
            } else if($responseLine->status == 8) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Неверный формат времени отправки';
            } else if($responseLine->status == 9) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Отправка заблокирована из-за срабатывания SPAM фильтра';
            } else if($responseLine->status == 10) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Отправка заблокирована из-за последовательного повторения id';
            } else if($responseLine->status == 11) {
                $result['error'] = true;
                $result['to'] = $toNumbers[$i++];
                $result['statusmessage'] = 'Сообщение успешно обработано, но не принято к отправке по причине test=true';
            }
            $results[] = $result;
        }
        return $results;
    }

    private function getReport($params) {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".
            "<dr>".
            "<login>" . $params['user'] . "</login>".
            "<pwd>" . $params['pwd'] . "</pwd>".
            "<id>" . $params['apimsgid'] . "</id>".
            "</dr>";
        try {
            $url = $this->getServiceURL(self::SERVICE_QUERY);
            $result = $this->post_content($url ,$xml);
            $responseXML = $result['content'];
            $response = new SimpleXMLElement($responseXML);
            return $response;
        } catch(Exception $e) {
            /*echo 'Caught exception: ',  $e->getMessage(), "\n";
            var_dump($e->getTrace());*/
        }
        return false;
    }

    /**
     * Function to get query for status using messgae id
     * @param <Number> $messageId
     */
    public function query($messageId) {
        $params = $this->prepareParameters();
        $params['apimsgid'] = $messageId;
        $response = $this->getReport($params);

        $result = array( 'error' => false, 'needlookup' => 1);
        if($response->status == 0) {
            $result['id'] = $messageId;
            $status = $response->phone->report;
            $result['status'] = "CODE: $status";
            if($status == 0) {
                $result['statusmessage'] = "Сообщение находится в очереди на отправку";
            } elseif($status == 1) {
                $result['statusmessage'] = "Сообщение передано оператору";
            } elseif ($status == 2) {
                $result['statusmessage'] = "Сообщение отклонено";
                $result['needlookup'] = 0;
            } elseif ($status == 3) {
                $result['statusmessage'] = "Сообщение успешно доставлено";
                $result['needlookup'] = 0;
            } elseif ($status == 4) {
                $result['statusmessage'] = "Сообщение не доставлено";
                $result['needlookup'] = 0;
            } elseif ($status == 5) {
                $result['statusmessage'] = "Сообщение не отправлено из-за нехватки средств на счету партнера";
                $result['needlookup'] = 0;
            } elseif ($status == 6) {
                $result['statusmessage'] = "Неизвестный (новый) статус отправки";
                $result['needlookup'] = 0;
            }
        } elseif($response->status == 1) {
            $result['error'] = true;
            $result['needlookup'] = 0;
            $result['statusmessage'] = "Ошибка в формате запроса";
        } elseif ($response->status == 2) {
            $result['error'] = true;
            $result['needlookup'] = 0;
            $result['statusmessage'] = "Неверная авторизация";
        } elseif ($response->status == 3) {
            $result['error'] = true;
            $result['needlookup'] = 0;
            $result['statusmessage'] = "Недопустимый IP-адрес отправителя";
        } elseif ($response->status == 4) {
            $result['error'] = true;
            $result['needlookup'] = 0;
            $result['statusmessage'] = "Отчет для указанного ID не найден";
        }
        return $result;
    }
}
?>
