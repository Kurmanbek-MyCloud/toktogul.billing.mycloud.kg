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
    
    $type = $_GET['type'];
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

$query = array(
    "ContactInfo" => "SELECT * FROM vtiger_contactscf AS cf 
                INNER JOIN vtiger_crmentity AS crm ON crm.crmid = cf.contactid
                INNER JOIN vtiger_contactdetails AS c ON c.contactid = cf.contactid
                LEFT JOIN vtiger_flats AS f ON f.flatsid = cf.cf_1259
                LEFT JOIN vtiger_flatscf AS fcf ON fcf.flatsid= cf.cf_1259
                LEFT JOIN vtiger_housescf AS hcf ON hcf.housesid = fcf.cf_1203
                WHERE deleted =0 
                AND cf.contactid = ?",

    "JitelsInfo" => "SELECT * FROM vtiger_contactscf AS cf 
                INNER JOIN vtiger_contactdetails AS c ON c.contactid = cf.contactid
                INNER JOIN vtiger_contactsubdetails AS cs ON cs.contactsubscriptionid = cf.contactid
                INNER JOIN vtiger_crmentity AS crm ON crm.crmid = c.contactid 
                WHERE deleted = 0 
                and cf.cf_1259 = (SELECT cf_1259 FROM vtiger_contactscf WHERE contactid = ?)",
    
    "SPPayments" => "SELECT pay_no AS doc_no, 
                pay_date AS date, 
                amount AS summ, 
                lastname AS fio, 
                CONCAT('ул.', h.house,', д.',f.flat) AS address,
                concat('Лицевой счет: ',fcf.cf_1420) AS ls,
                o.organizationname as org_name,
                o.address as org_address,
                o.phone as org_phone

                FROM sp_payments p 
                INNER JOIN sp_paymentscf pcf ON pcf.payid = p.payid
                INNER JOIN vtiger_contactdetails c ON c.contactid = p.payer
                INNER JOIN vtiger_flatscf fcf ON fcf.cf_1235 = c.contactid
                INNER JOIN vtiger_flats f ON f.flatsid = fcf.flatsid
                INNER JOIN vtiger_crmentity fcrm ON fcrm.crmid = fcf.flatsid 
                INNER JOIN vtiger_houses h ON h.housesid = fcf.cf_1203
                join vtiger_organizationdetails o
                WHERE p.payid = ?
                AND fcrm.deleted = 0"
);

$sth = $dbh->prepare($query['ContactInfo']);
$sth->execute(array($id));
$CI = $sth->fetch(PDO::FETCH_ASSOC);

$sth1 = $dbh->prepare($query['JitelsInfo']);
$sth1->execute(array($id));
$JI = $sth1->fetchAll(PDO::FETCH_ASSOC);

$sth1 = $dbh->prepare($query['SPPayments']);
$sth1->execute(array($id));
$SP = $sth1->fetch(PDO::FETCH_ASSOC);

$month_ru = [
  'января',
  'февраля',
  'марта',
  'апреля',
  'мая',
  'июня',
  'июля',
  'августа',
  'сентября',
  'октября',
  'ноября',
  'декабря'
];
// Поскольку от 1 до 12, а в массиве, как мы знаем, отсчет идет от нуля (0 до 11),
// то вычитаем 1 чтоб правильно выбрать уже из нашего массива.
// $month = date('n')-1;
// $date = date('d ').$month_ru[$month].date(' Y').'г.';
// $address = 'г.Бишкек, '.$CI['cf_1167'].', д.'.$CI['cf_1169'].', кв.№'.$CI['flat'];
// $fio = $CI['lastname'].' '.$CI['firstname'].' '.$CI['cf_1223'];

// $section = $PHPWord->addSection();
// $table = $section->addTable('', ['borderSize' => 6, 'borderColor' => 'white']);



// $n = 1;
// for ($i=0; $i < count($JI); $i++) { 
//     $fio_jit = $JI[$i]['lastname'].' '. $JI[$i]['firstname'].' '. $JI[$i]['cf_1223'].' '. date('d-m-Y',strtotime($JI[$i]['birthday'])).' г.р.';
//     $table->addRow();
//     $table->addCell(1000, ['borderSize' => 6,'borderColor' => 'white'])
//         ->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT])
//         ->addText($n.'.', ['bold' => true, 'valign' => 'center','name'=>'Times New Roman','size'=>'14']);
//     $table->addCell(11000, ['borderSize' => 6,'borderColor' => 'white'])
//         ->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT])
//         ->addText($fio_jit, ['bold' => false, 'valign' => 'center','name'=>'Times New Roman','size'=>'14']);        
//     $n++;
// }
//     // var_dump($fio_jit);
    
// $document = new \PhpOffice\PhpWord\TemplateProcessor('PKO.docx');

// $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($PHPWord, 'Word2007');
// $fullxml = $objWriter->getWriterPart('Document')->write();
// $tablexml = preg_replace('/^[\s\S]*(<w:tbl\b.*<\/w:tbl>).*/', '$1', $fullxml);
// $document->setValue('table', $tablexml);

// $document->setValue("date", $date);
// $document->setValue("address", $address);
// $document->setValue("fio", $fio);
if ($type = 'pko'){
    $SP['date'] = date("d-m-Y", strtotime($SP['date']));
    
    $SP['from'] = $SP['fio'].' / '.$SP['address'].' / '.$SP['ls'];
    $SP['summ'] = round($SP['summ'],3);
    $SP['summ_word'] = getPriceWord($SP['summ']);
    $SP['date_day'] = date('d',strtotime($SP['date']));
    $SP['date_month'] = $month_ru[date('n',strtotime($SP['date']))-1];
    $SP['date_year'] = date('Y',strtotime($SP['date']));
    $SP['curdate_day'] = date('d');
    $SP['curdate_month'] = $month_ru[date('n')-1];
    $SP['curdate_year'] = date('Y');
    
    $document = new \PhpOffice\PhpWord\TemplateProcessor('PKO.docx');
    foreach ($SP as $key => $value) {
       $document->setValue($key, $value);
         echo $key ."-key " .$value."<br>";
    }
    $filename = 'ПКО №'.$SP['doc_no'];
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
header("Content-Disposition: attachment; filename=" .$filename.".docx");
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