<?php  

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


/*$day_yes = date('Y-m-d', strtotime($dstart.' - 1 day'));

if (!$dstart) {
	$dstart = date('Y-m-d');
}*/
$count = count($year);

?>
<table cellspacing="50">
<tr>
    <th>Наименование </th>
    <?php foreach ($year as $key => $value) :?>
   	 	<th><?php echo $value; ?></th>
	<?php endforeach; ?>
</tr>
<tr>
    <td>Доходы </td>
    <td colspan="<?=$count ?>"></td>
</tr>

<?php 
	// Приход
	$pores = mysqli_query($db, "SELECT cf_1414 as pay_details FROM sp_paymentscf group by cf_1414");
	$totalPrihod = [];

	while ( $row = mysqli_fetch_assoc($pores) ):
		$pay_details = trim($row['pay_details']);
 	?>
	<tr>
		<td><?php echo $pay_details ?></td>

		<?php for ($i=0; $i < $count; $i++): 
			$date_start = $year[$i].date('-01-01 00:00:00');
			$date_end = $year[$i].date('-12-31 23:59:59');
	
			$sql = "SELECT SUM(a.amount) AS prihod
FROM sp_payments AS a
INNER JOIN vtiger_crmentity AS b ON b.crmid=a.payid
WHERE b.deleted=0 AND a.pay_details='$pay_details' and a.pay_type='Receipt' AND b.createdtime BETWEEN '$date_start' AND '$date_end'";
				$sqlres = mysqli_query($db, $sql);
				$sqlrow = mysqli_fetch_assoc($sqlres);
				$totalPrihod[$i] += $sqlrow['prihod'];
			?>
			<td><?=number_format($sqlrow['prihod'], 2, '.', ',') ?></td>
		<?php endfor; ?>
	</tr>
<?php endwhile; ?>
<tr>
	<td><strong>Итого</strong></td>

	<?php foreach ($totalPrihod as $value): ?>
		<td><b><?php echo number_format($value, 2, '.', ','); ?></b></td>
	<?php endforeach; ?>
</tr>

<tr>
    <td colspan="<?=$count+1 ?>">&nbsp;</td>
</tr>

<tr>
    <td>Расходы </td>
    <td colspan="<?=$count ?>"></td>
</tr>

<?php 
	$pores = mysqli_query($db, "SELECT cf_1414 as pay_details FROM sp_paymentscf group by cf_1414");
	$totalRashod = [];

	while ( $row = mysqli_fetch_assoc($pores) ):
		$pay_details = trim($row['pay_details']);
 ?>
	<tr>
		<td><?php echo $pay_details ?></td>

		<?php for ($i=0; $i < $count; $i++): 
			$date_start = $year[$i].date('-01-01 00:00:00');
			$date_end = $year[$i].date('-12-31 23:59:59');
		
			$sql = "SELECT SUM(a.amount) AS rashod
FROM sp_payments AS a
INNER JOIN vtiger_crmentity AS b ON b.crmid=a.payid
WHERE b.deleted=0 AND a.pay_details='$pay_details' and a.pay_type='Expense' AND b.createdtime BETWEEN '$date_start' AND '$date_end'";
				$sqlres = mysqli_query($db, $sql);
				$sqlrow = mysqli_fetch_assoc($sqlres);
				$totalRashod[$i] += $sqlrow['rashod'];
			?>
			<td><?=number_format($sqlrow['rashod'], 2, '.', ',') ?></td>
		<?php endfor; ?>
	</tr>
<?php endwhile; ?>
<tr>
	<td><strong>Итого</strong></td>

	<?php foreach ($totalRashod as $value): ?>
		<td><b><?php echo number_format($value, 2, '.', ','); ?></b></td>
	<?php endforeach; ?>
</tr>
<tr>
    <td colspan="<?=$count+1 ?>">&nbsp;</td>
</tr>
<tr>
    <td><b>Всего</b></td>

    <?php foreach ($totalPrihod as $key => $value): ?>
    	<?php 
    		$total = $value-$totalRashod[$key];
    	 ?>
		<td><b><?php echo number_format($total, 2, '.', ','); ?></b></td>
	<?php endforeach; ?>
</tr>
<!-- <tr>
    <td colspan="<?=$count+1 ?>">&nbsp;</td>
</tr>
<tr>
    <td><b>Движение денежных средств</b></td>
    
    <?php foreach ($totalPrihod as $key => $value): ?>
    	<?php 
    		$total = $value-$totalRashod[$key];
    	 ?>
		<td><b><?php echo number_format($total, 2, '.', ','); ?></b></td>
	<?php endforeach; ?>
</tr>
 -->

	</table>
	<!--  <pre>
	 	<?php var_dump($payed); ?>
	 	<?php var_dump($table); ?>
	 </pre> -->