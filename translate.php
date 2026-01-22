<?php
if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['module'])) {
	$val = $_POST['value'];
	$module = $_POST['module'];
	$root = realpath(".");
	require $root . "/languages/ru_ru/$module.php";
	echo json_encode(array("status" => true, "returnString" => $languageStrings[$val]));
}
else {
	echo json_encode(array("status" => false));
}
