<?php
// ini_set('display_errors','on'); version_compare(PHP_VERSION, '5.5.0') <= 0 ? error_reporting(E_WARNING & ~E_NOTICE & ~E_DEPRECATED) : error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);   // DEBUGGING
// ini_set('display_errors','on'); error_reporting(E_ALL); // STRICT DEVELOPMENT
// header("Content-type: text/html; charset=utf-8");
// mb_internal_encoding("UTF-8");
    // define('URL','https://maralfm.crm.mycloud.kg/webservice.php');
    // define('KEY','Gqm7zUeEsZCYxfUP');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if (!isset($_GET['recordid'])) {
        echo 'Ошибка параметров';
        exit();
}
    
    // $type = $_GET['type'];
$id = $_GET['recordid'];
    // $id = 277;
    // $start_date = $_GET['start_date'];
    // $end_date = $_GET['end_date'];
    
require_once 'vendor/autoload.php';
require_once '../config.inc.php';
$db_name = $dbconfig['db_name'];
$db_server = $dbconfig['db_server'];
$db_username = $dbconfig['db_username'];
$db_password = $dbconfig['db_password'];
$dbh = new PDO("mysql:dbname=$db_name;host=$db_server;charset=utf8", $db_username, $db_password);
$PHPWord = new \PhpOffice\PhpWord\PhpWord();

// CONCAT('ул.',cf_1438,', д.',cf_1440,', под.',cf_1482,', кв.', cf_1442) AS address,
$query = array(
    "Request" => "SELECT 
                cf_1438 as street,
                cf_1440 as house,
                cf_1482 as podezd,
                cf_1442 as kvart,
                CONCAT(firstname,' ',lastname) AS fio,
                cf_1414 AS dolg,
                mobile AS mobile,
                phone AS another_phone,
                cf_1486 AS STATUS,
                category,
                cf_1444 AS subcategory,
                description,
                cf_1454 AS ispolnitel,
                round(cf_1488) AS summ,
                ticket_no,
                cf_1432 as date,
                cf_1434 as time
                from vtiger_troubletickets AS T
                INNER JOIN vtiger_ticketcf AS TCF ON T.ticketid = TCF.ticketid
                LEFT join vtiger_contactdetails AS C ON T.contact_id = C.contactid
                LEFT JOIN vtiger_contactaddress AS CO ON C.contactid = CO.contactaddressid
                LEFT JOIN vtiger_contactscf AS CCF ON C.contactid = CCF.contactid
                INNER JOIN vtiger_crmentity AS CE ON T.ticketid = CE.crmid
                WHERE T.ticketid = ?"
);

$sth = $dbh->prepare($query['Request']);
$sth->execute(array($id));
$HD = $sth->fetch(PDO::FETCH_ASSOC);

    $fields_2_check[] = 'street'; 
    $fields_2_check[] = 'house'; 
    $fields_2_check[] = 'podezd'; 
    $fields_2_check[] = 'kvart';
foreach ($HD as $key => $value) {
    if (in_array($key,$fields_2_check)){
        if($value != null){
            if ($key == 'street'){
                $HD['address'].='ул.'.$value.',';
            }
            else if ($key == 'house'){
                $HD['address'].='д.'.$value.',';
            }
            else if ($key == 'podezd'){
                $HD['address'].='под.'.$value.',';
            }
            else if ($key == 'kvart'){
                $HD['address'].='кв.'.$value;
            }
        }
        
    } 
    else {
        $HD['address'].='';
    }   
} 
$HD['summ_word']=getPriceWord($HD['summ']);
$HD['reg_date']=date("d-m-Y h:i",strtotime($HD['date'].$HD['time']));

$document = new \PhpOffice\PhpWord\TemplateProcessor('zaiavka.docx');

foreach ($HD as $key => $value) {
    $document->setValue($key, $value);
    // echo "<pre>";
    // var_dump($key);
    // echo "</pre>";
}
// exit();

$temp_file = tempnam(sys_get_temp_dir(), 'temp');
ob_clean();
$document->saveAs($temp_file);
header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=" .'Наряд-заявка'. ".docx");
readfile($temp_file);
unlink($temp_file);

function getPriceWord($sourceNumber,$rodPadej = false){ 
    if (!($sourceNumber instanceof int)){
        $sourceNumber = (int)$sourceNumber;
	}
	$firstNumbers = array(
            '',
            'один',
            'два',
            'три',
            'четыре',
            'пять',
            'шесть',
            'семь',
            'восемь',
            'девять'
    );
    $secondNumbers = array(
            'десять',
            'одиннадцать',
            'двенадцать',
            'тринадцать',
            'четырнадцать',
            'пятнадцать',
            'шестнадцать',
            'семнадцать',
            'восемнадцать',
            'девятнадцать'
    );
    if ($rodPadej){
        $firstNumbers = array(
            'нулевой',
            'первый',
            'второй',
            'третий',
            'четвертый',
            'пятый',
            'шестой',
            'седьмой',
            'восьмой',
            'девятый'
        );
        $secondNumbers = array(
            'десятый',
            'одиннадцатый',
            'двенадцатый',
            'тринадцатый',
            'четырнадцатый',
            'пятнадцатый',
            'шестнадцатый',
            'семнадцатый',
            'восемнадцатый',
            'девятнадцатый'
        );
    }
    $smallNumbers = array(
        array(
            'ноль'
        ) ,
        $firstNumbers,
        $secondNumbers,
        array(
            '',
            '',
            'двадцать',
            'тридцать',
            'сорок',
            'пятьдесят',
            'шестьдесят',
            'семьдесят',
            'восемьдесят',
            'девяносто'
        ) ,
        array(
            '',
            'сто',
            'двести',
            'триста',
            'четыреста',
            'пятьсот',
            'шестьсот',
            'семьсот',
            'восемьсот',
            'девятьсот'
        ) ,
        array(
            '',
            'одна',
            'две'
        )
    );
    $degrees = array(
        array(
            'больше дециллиона',
            '',
            'а',
            'ов'
        ) ,
        array(
            'тысяч',
            'а',
            'и',
            ''
        ) ,
        array(
            'миллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'миллиард',
            '',
            'а',
            'ов'
        ) ,
        array(
            'триллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'квадриллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'квинтиллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'секстиллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'септиллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'октиллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'нониллион',
            '',
            'а',
            'ов'
        ) ,
        array(
            'дециллион',
            '',
            'а',
            'ов'
        )
	);
	
    if ($sourceNumber == 0) return $smallNumbers[0][0];
    $sign = '';
    if ($sourceNumber < 0){
        $sign = 'минус ';
        $sourceNumber = substr($sourceNumber, 1);
	}
	
    $result = array();

    $digitGroups = array_reverse(str_split(str_pad($sourceNumber, ceil(strlen($sourceNumber) / 3) * 3, '0', STR_PAD_LEFT) , 3));
    foreach ($digitGroups as $key => $value){
        $result[$key] = array();
        foreach ($digit = str_split($value) as $key3 => $value3){
            if (!$value3) continue;
            else{
                switch ($key3){
                    case 0:
                        $result[$key][] = $smallNumbers[4][$value3];
                    break;
                    case 1:
                        if ($value3 == 1)
                        {
                            $result[$key][] = $smallNumbers[2][$digit[2]];
                            break 2;
                        }
                        else $result[$key][] = $smallNumbers[3][$value3];
                        break;
                    case 2:
                        if (($key == 1) && ($value3 <= 2)) $result[$key][] = $smallNumbers[5][$value3];
                        else $result[$key][] = $smallNumbers[1][$value3];
                        break;
				}
			}
		}
		$value *= 1;
		if (!$degrees[$key]) $degrees[$key] = reset($degrees);

		if ($value && $key){
			$index = 3;
			if (preg_match("/^[1]$|^\\d*[0,2-9][1]$/", $value)) $index = 1;
			else if (preg_match("/^[2-4]$|\\d*[0,2-9][2-4]$/", $value)) $index = 2;
			$result[$key][] = $degrees[$key][0] . $degrees[$key][$index];
		}
		$result[$key] = implode(' ', $result[$key]);
	}
	$answer = $sign . implode(' ', array_reverse($result));
	return $answer;
}

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


?>