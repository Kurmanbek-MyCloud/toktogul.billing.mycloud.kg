<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	$userName = substr($_POST['username'], 0, 2);
	$pwd = $_POST['password'];
	$salt = '$1$' . str_pad($userName, 9, '0');
	$hash = array(
	'success' => true,
	'result' => crypt($pwd, $salt)
	);
	echo json_encode($hash);
}