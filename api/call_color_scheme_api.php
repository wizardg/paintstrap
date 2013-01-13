<?php
require_once(dirname(__FILE__) . "/common.inc.php");

header("Content-type: text/json");

if (!isset($_REQUEST["api_type"])) {
	die("null");
}

if (!isset($_REQUEST["id"])) {
	die("null");
}

$result = call_color_scheme_api($_REQUEST["api_type"], $_REQUEST["id"]);
if (!$result) {
	die("null");
}

print json_encode($result);
?>