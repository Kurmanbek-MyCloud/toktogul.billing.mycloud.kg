<?php
    define('URL','https://maralfm.crm.mycloud.kg/webservice.php');
    define('KEY','Gqm7zUeEsZCYxfUP');
 

    function get_json($obj){
        return json_decode(json_encode(json_decode($obj)), true);
        }

    function get_token(){
        $token = get_json(file_get_contents(URL.'?operation=getchallenge&username=admin'));
        return $token['result']['token'];
        }
    
    function get_sessionName($accessKey){
        $headers = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                        'content' => 'operation=login&username=admin&accessKey='.md5($accessKey.KEY)
                    ),
                ));
        $sessionName = get_json(file_get_contents(URL, false, $headers));
        return $sessionName['result']['sessionName'];
        }

    function create_entity($sessionName,$element,$type){
        $headers = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                        'content' => 'operation=create&sessionName='.$sessionName.'&element='.$element.'&elementType='.$type
                    ),
                ));
        return $answer = get_json(file_get_contents(URL, false, $headers));
        }

    function get_entity($sessionName,$entity,$id,$print=false){
        $answer = get_json(file_get_contents(URL.'?operation=retrieve&sessionName='.$sessionName.'&id='.$entity.'x'.$id));
        if ($print==false){
            return $answer;
            }
        }

    function logout($sessionName){
        $headers = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                        'content' => 'operation=logout&sessionName='.$sessionName
                    ),
                ));
        $answer = get_json(file_get_contents(URL, false, $headers));
        }

    
        $token = get_token();
        $sessionName = get_sessionName($token);
        $data = array('subject' => 'Заявка на отпуск',
        			'date_start' => date('Y-m-d'),
        			'due_date' => date('Y-m-d'),
        			'activitytype' => 'Заявка на отпуск',
        			'time_start' => date('H:i:s'),
        			'time_end' => date("H:i:s", strtotime('+1 minutes')),
        			'eventstatus' => 'Planned',
        			'visibility' => 'Public',
        			'assigned_user_id' => '19x1',
        			"duration_minutes"=> "0",
					"duration_hours"=> "0",
                    "parent_id"=>"12x128617"
        			 );        
        create_entity($sessionName,json_encode($data),'Events')['success'];
        logout($sessionName);   
?>