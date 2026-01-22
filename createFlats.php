<?php
header('Content-Type: application/json;');
require 'config.inc.php';

require './flats/DataBase.php';
require './flats/CRM.php';
$url=$site_URL."webservice.php";
define('URL', $url);
define('KEY', 'BxEZilsTVwrDQrl9');
function get_token($username){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL . "?operation=getchallenge&username=$username");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    return $result->result->token;
}
function get_sessionName($accessKey,$username){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "operation=login&username=$username&accessKey=" . $accessKey);
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    return $result->result->sessionName;
}
function create_entity($sessionName,$element,$type){
    $headers = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'content' => 'operation=create&sessionName='.$sessionName.'&element='.json_encode($element).'&elementType='.$type
                ),
            ));
    return $answer = file_get_contents(URL, false, $headers);
}
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

if ($contentType === "application/json") {
    $content = trim(file_get_contents("php://input"));
    $decoded = json_decode($content, true);
    $dbConn = DataBase::getConn($dbconfig['db_username'],$dbconfig['db_password'],$dbconfig['db_name']);
    $CRM = new CRM();
    // if($decoded['action']==="createPayment"){
    //     $payment=$CRM->createPayment($decoded);
    //     echo json_encode($payment);
    // }
    $token=getallheaders()['accessKey'];
    $username=getallheaders()['username'];
    $sessionName = get_sessionName($decoded['accessKey'],$decoded['username']);
    if($sessionName!=null){
        if($decoded['action']==="createFlat"){
            $result=$CRM->createFlat($decoded);
            echo json_encode($result);
        }
        if($decoded['action']==="updateFlat"){
            $edit=$CRM->updateFlat($decoded);
            echo json_encode($edit);
        }
        if($decoded['action']==="createPayment"){
            $payment=$CRM->createPayment($decoded);
            echo json_encode($payment);
        }

    }else{
        echo json_encode(array("success"=>false,"message"=>"Authorization falled"));
    }
    



}


?>
