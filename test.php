<?php
require_once 'config.inc.php';
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/x-www-form-urlencoded');

define('URL', $site_URL.'webservice.php');
define('KEY', 'SPAMbu20f8fMXr06');
function get_token(){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL . '?operation=getchallenge&username=test_user_1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    return $result->result->token;
}
function get_sessionName($accessKey){
  return md5($accessKey . KEY);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'operation=login&username=test_user_1&accessKey=' . md5($accessKey . KEY));
    $result = json_decode(curl_exec($ch));
    curl_close($ch);

    return $result;
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
$token = get_token();
$sessionName = get_sessionName($token);
  echo json_encode($sessionName);

if ($contentType === "application/json") {
  $token = get_token();
  $sessionName = get_sessionName($token);
  //Receive the RAW post data.
  $content = trim(file_get_contents("php://input"));

  $decoded = json_decode($content, true);
  if($decoded['createLead']==1){
    $data = array(
        'ticket_title' => $decoded['ticket_title'],
        "ticketcategories"=>$decoded['ticketcategories'],
        'ticketstatus' => $decoded['ticketstatus'],
        "contact_id"=> $decoded['contact_id'],
        "ticketpriorities"=>$decoded['ticketpriorities'],
        'cf_1263'=>$decoded['cf_1263'],
        'description'=>$decoded['description'],
        'assigned_user_id' => $decoded['assigned_user_id']
    );
    // $answer = json_decode(create_entity($sessionName, $data, 'HelpDesk'));
    if($answer->success){
        echo json_encode(array('status'=>true, "result"=>$answer->result));
    }
    else{
        echo json_encode(array('status'=>false,'message'=>$answer->error->message));
    }
  }
  if($decoded['modcomment']==1){
    $comment_data=array(
        "commentcontent"=>$decoded['content'],
        "related_to"=>$decoded['related_to'],
        "customer"=>$decoded['customer'],
        "assigned_user_id"=>'19x1'
    );
    // $mod_answer = json_decode(create_entity($sessionName, $comment_data, 'ModComments'));
    if($mod_answer->success){
        echo json_encode(['status'=>true,'result'=>$mod_answer->result]);
    }
    else{
        echo json_encode(array('status'=>false,'message'=>$mod_answer->error->message));
    }
  }
//   echo json_encode($decoded);

}else {
  # code...
  echo json_encode('ASDF');
}


?>