<?php
header('Content-Type: application/json;');
// date_default_timezone_set('Asia/Bishkek');

// require_once './payTest/DataBase.php';
// require_once './payTest/CRM.php';
// require './payTest/DataBase.php';
// require './payTest/CRM.php';


define('URL', 'https://etalon.billing.mycloud.kg/webservice.php');
define('KEY', 'BxEZilsTVwrDQrl9');
function get_token(){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL . '?operation=getchallenge&username=admin');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    return $result->result->token;
}
function get_sessionName($accessKey){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'operation=login&username=admin&accessKey=' . md5($accessKey . KEY));
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
    $accessKey=$decoded['accessKey'];
    $test=md5($accessKey . KEY);
    // $token = get_token();
    // echo json_encode($decoded);
    // $sessionName = get_sessionName($token);
    // $dbConn = DataBase::getConn();
    // $CRM = new CRM();
    echo json_encode($decoded);
    // // echo json_encode($sessionName);
    // $data = $decoded;
    // $answer = json_decode(create_entity($sessionName, $data, 'Flats'));
    // if($answer->success){
    //     echo json_encode(array('status'=>true, "result"=>$answer->result));
    // }
    // else{
    //     echo json_encode(array('status'=>false,'message'=>$answer));
    // } 
    // // echo json_encode($decoded,true);


}


?>

