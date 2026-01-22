<?php
// ini_set('display_errors','on'); error_reporting(E_ALL); // STRICT DEVELOPMENT
$document_root = $_SERVER['DOCUMENT_ROOT'];
require_once $document_root."/config.inc.php";
    $db = new mysqli($dbconfig['db_server'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name']);
    mysqli_set_charset($db,"utf8");

require_once $document_root.'/PHPExcel.php';
require_once $document_root.'/PHPExcel/Writer/Excel5.php';

$yearStart = $_GET['yearstart'];
$yearEnd = $_GET['yearend'];
$today = date('Y-m-d');
$year =[];

if ($yearStart == $yearEnd) {
    $year = array(0 => $yearStart);
}else{
        $j=0; $k=-1;
    for ($i=$yearStart-=1; $i < $yearEnd; $i++) { 
        $j++; $k++;
        $year[$k] = $yearStart+$j;
    }
}


// $day_yes = date('Y-m-d', strtotime($dstart.' - 1 day'));

// if (!$dstart) {
//     $dstart = date('Y-m-d');
// }
$count = count($year);

$alphabet = array(
    0 => 'B',
    1 => 'C',
    2 => 'D',
    3 => 'E',
    4 => 'F',
    5 => 'G',
    6 => 'H',
    7 => 'I',
    8 => 'J',
    9 => 'K',
    10 => 'L',
    11 => 'M',
    12 => 'N',
    13 => 'O',
    14 => 'P',
    15 => 'Q',
    16 => 'R',
    17 => 'S',
    18 => 'T',
    19 => 'U',
    20 => 'V',
    21 => 'W',
    22 => 'X',
    23 => 'Y',
    24 => 'Z'
);

$alphabets = array(
    0 => 'A',
    1 => 'B',
    2 => 'C',
    3 => 'D',
    4 => 'E',
    5 => 'F',
    6 => 'G',
    7 => 'H',
    8 => 'I',
    9 => 'J',
    10 => 'K',
    11 => 'L',
    12 => 'M',
    13 => 'N',
    14 => 'O',
    15 => 'P',
    16 => 'Q',
    17 => 'R',
    18 => 'S',
    19 => 'T',
    20 => 'U',
    21 => 'V',
    22 => 'W',
    23 => 'X',
    24 => 'Y',
    25 => 'Z'
);

$xls = new PHPExcel();
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$sheet->setTitle('Отчет по финансам');

$sheet->getColumnDimension('A')->setWidth(40);

for ($i=0; $i < $count; $i++) { 
    $al = $alphabet[$i]."1";
    $sheet->getColumnDimension($al)->setWidth(30);
}

// $sheet->mergeCells('A1:E5');
$today = date('d.m.Y');
$sheet->setCellValue('A1', 'Отчет по финансам по ');
$sheet->setCellValue('B1', 'ТСЖ "Идейный дом" на '.$today);
// $sheet->mergeCells('B1:F1');



$n = 0;
$cell_row = $n + 2;

$sheet->setCellValue('A'.$cell_row, 'Наименование');

    foreach ($year as $key => $value) :
        $sheet->setCellValue($alphabet[$key].$cell_row, $value);
    endforeach; 

$cell_row++;
$sheet->setCellValue('A'.$cell_row, 'Доходы');
// $cell_row++;
// Приход
$pores = mysqli_query($db, "SELECT cf_1414 as pay_details FROM sp_paymentscf group by cf_1414");
$totalPrihod = [];

        $k=0;
while ( $row = mysqli_fetch_assoc($pores) ):
        $k++;
        $cell_row++;

        $pay_details = trim($row['pay_details']);
        $sheet->setCellValue($alphabets[$k].$cell_row, $pay_details);

        for ($i=0; $i < $count; $i++): 
            $date_start = $year[$i].date('-01-01 00:00:00');
            $date_end = $year[$i].date('-12-31 23:59:59');
    
            $sql = "SELECT SUM(a.amount) AS prihod
FROM sp_payments AS a
INNER JOIN vtiger_crmentity AS b ON b.crmid=a.payid
WHERE b.deleted=0 AND a.pay_details='$pay_details' and a.pay_type='Receipt' AND b.createdtime BETWEEN '$date_start' AND '$date_end'";
                $sqlres = mysqli_query($db, $sql);
                $sqlrow = mysqli_fetch_assoc($sqlres);
                $totalPrihod[$i] += $sqlrow['prihod'];
            
            $sheet->setCellValue($alphabet[$i].$cell_row, number_format($sqlrow['prihod'], 2, '.', ','));
            
        endfor;
endwhile;

// Итого приход
// $cell_row++;
$itogoPrihod = $cell_row++;  
$sheet->setCellValue('A'.$cell_row, 'Итого');

    foreach ($totalPrihod as $key => $value):
        $sheet->setCellValue($alphabet[$key].$cell_row, number_format($value, 2, '.', ','));
    endforeach; 
$cell_row++;

// $cell_row++;
$rashody = $cell_row++;
$sheet->setCellValue('A'.$cell_row, 'Расходы');
// $cell_row++;

$pores = mysqli_query($db, "SELECT cf_1414 as pay_details FROM sp_paymentscf group by cf_1414");
$totalRashod = [];

        $k=0;
while ( $row = mysqli_fetch_assoc($pores) ):
        $k++;
        $cell_row++;

        $pay_details = trim($row['pay_details']);
        $sheet->setCellValue($alphabets[$k].$cell_row, $pay_details);

        for ($i=0; $i < $count; $i++): 
            $date_start = $year[$i].date('-01-01 00:00:00');
            $date_end = $year[$i].date('-12-31 23:59:59');
    
           $sql = "SELECT SUM(a.amount) AS rashod
FROM sp_payments AS a
INNER JOIN vtiger_crmentity AS b ON b.crmid=a.payid
WHERE b.deleted=0 AND a.pay_details='$pay_details' and a.pay_type='Expense' AND b.createdtime BETWEEN '$date_start' AND '$date_end'";
                
                $sqlres = mysqli_query($db, $sql);
                $sqlrow = mysqli_fetch_assoc($sqlres);
                $totalRashod[$i] += $sqlrow['rashod'];
            
            $sheet->setCellValue($alphabet[$i].$cell_row, number_format($sqlrow['rashod'], 2, '.', ','));
            
        endfor;
endwhile;

// Итого расход
// $cell_row++;    
$itogoRashod = $cell_row++;  
$sheet->setCellValue('A'.$cell_row, 'Итого');

    foreach ($totalRashod as $key => $value):
        $sheet->setCellValue($alphabet[$key].$cell_row, number_format($value, 2, '.', ','));
    endforeach; 
$cell_row++;


// $sheet->setCellValue('A'.$cell_row, '');
// $cell_row++;  
$itogoVsego = $cell_row++;  
$sheet->setCellValue('A'.$cell_row, 'Всего');

    foreach ($totalPrihod as $key => $value): 
        
            $total = $value-$totalRashod[$key];
            $sheet->setCellValue($alphabet[$key].$cell_row, number_format($total, 2, '.', ','));
       
    endforeach; 

// $sheet->mergeCells('A'.$cell_row.':B'.$cell_row);

/*$sheet->setCellValue('B'.$cell_row, 'Итого по оборотам');
$sheet->setCellValue('F'.$cell_row, $prihod_kgs);
$sheet->setCellValue('G'.$cell_row, 'KGS');
$sheet->setCellValue('H'.$cell_row, $rashod_kgs);
$sheet->setCellValue('I'.$cell_row, 'KGS');*/
$cell_row++;


$style1 = array(
    'font' => array(
        'size' => 10,
        'name' => 'Calibri',
    ),
    'borders' => array(
        'allborders' => array(
            'style' => 'thin',
            'color' => array('rgb' => '000000')
        )
    ),
    'alignment' => array(
        'horizontal' => 'left',
        'vertical' => 'center'
    )
);

$style2 = array(
    'font' => array(
        'size' => 10,
        'name' => 'Calibri',
        'bold' => true
    ),
    'borders' => array(
        
        'allborders' => array(
            'style' => 'thin',
            'color' => array('rgb' => '000000')
        )
    ),
    'alignment' => array(
        'horizontal' => 'left',
        'vertical' => 'center'
    )
);

$style3 = array(
    'font' => array(
        'size' => 10,
        'name' => 'Calibri'
    ),
    'borders' => array(
      
        'allborders' => array(
            'style' => 'thin',
            'color' => array('rgb' => '000000')
        )
    ),
);

$style4 = array(
    'alignment' => array(
        'wrap' => true,
        'horizontal' => 'left'
    )
);


$style5 = array(
    'font' => array(
        'size' => 10,
        'name' => 'Calibri',
        'bold' => true
    ),
    'borders' => array(
        'outline' => array(
            'style' => 'medium',
            'color' => array('rgb' => '000000')
        ),
        'allborders' => array(
            'style' => 'thin',
            'color' => array('rgb' => '000000')
        )
    ),
);

$style6 = array(
    'borders' => array(
        'outline' => array(
            'style' => 'medium',
            'color' => array('rgb' => '000000')
        )
    )
);

$itogoPrihod+=1;
$rashody+=1;
$itogoRashod+=1;
$itogoVsego+=1;

// echo ; die;
// $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal('center');
// $sheet->getStyle('A2:Q2')->applyFromArray($style1);
$sheet->getStyle('A1:'.$alphabets[$count].$count)->applyFromArray($style2);
$sheet->getStyle('A'.$itogoPrihod.':'.$alphabets[$count].$itogoPrihod)->applyFromArray($style2);
$sheet->getStyle('A'.$rashody.':'.$alphabets[$count].$rashody)->applyFromArray($style2);
$sheet->getStyle('A'.$itogoRashod.':'.$alphabets[$count].$itogoRashod)->applyFromArray($style2);
$sheet->getStyle('A'.$itogoVsego.':'.$alphabets[$count].$itogoVsego)->applyFromArray($style2);
$sheet->getStyle('A4:'.$alphabets[$count].$itogoVsego)->applyFromArray($style1);
// $sheet->getStyle('A1:V1')->applyFromArray($style3);
// $sheet->getStyle('A1:V1')->applyFromArray($style4);

/*$cell_row--;
$sheet->getStyle('A2:V'.$cell_row)->applyFromArray($style1);
// $sheet->getStyle('A2:T'.$cell_row)->applyFromArray($style4);
$cell_row++;*/


// $sheet->getStyle('A'.$cell_row.':Q'.$cell_row)->applyFromArray($style3);
// $sheet->getStyle('A'.$cell_row.':Q'.$cell_row)->applyFromArray($style4);
// $sheet->getStyle('B3:B'.$cell_row)->applyFromArray($style6);
// $sheet->getStyle('F3:Q'.$cell_row)->applyFromArray($style6);
// $sheet->getStyle('A'.$cell_row)->getAlignment()->setHorizontal('center');

header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Отчет по финансам ".$today.".xls");

$objWriter = new PHPExcel_Writer_Excel5($xls);
$objWriter->save('php://output');