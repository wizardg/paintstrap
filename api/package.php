<?php
require_once(dirname(__FILE__) . "/common.inc.php");

header("Content-type: text/json");

if (!check_colors()) {
	die("null");
}

$api_type = $_REQUEST["api_type"];
$id = $_REQUEST["id"];
$c = $_REQUEST["c"];

$result_generate = generate($api_type, $id, $c);
if (!$result_generate) {
	die("null");
}

$now = date("Y-m-d H:i:s");

ksort($c);
$colors = implode(",", $c);

$dbh = connect_db();
$sql = "select count(theme_id) as cnt from theme where api_type = ? and cs_id = ? and colors = ?";
$stmt = $dbh->prepare($sql);
$stmt->bindValue(1, $api_type);
$stmt->bindValue(2, $id);
$stmt->bindValue(3, $colors);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$cnt = $row["cnt"];

if ($cnt == 0) {
	$sql = "insert into theme (api_type, cs_id, colors, created_at, updated_at) values (?, ?, ?, now(), now())";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $api_type);
	$stmt->bindValue(2, $id);
	$stmt->bindValue(3, $colors);
	$stmt->execute();
}

print json_encode($result_generate);
?>
