<?php
error_reporting(E_ALL);
ini_set("display_errors", "0");

define("BASE_URL", "http://paintstrap.com/");
define("BASE_PATH", realpath(dirname(__FILE__) . "/../") . "/");


$GLOBALS["api_type_list"] = array(
	"kuler" => array(
		"name" => "Adobe kuler"
	),
	"colourlovers" => array(
		"name" => "COLOURlovers"
	)
);

define("DB_DSN", "mysql:dbname=paintstrap;host=localhost;port=3306");
define("DB_USER", "*db_user*");
define("DB_PASSWORD", "*db_password*");


define("KULER_API_URL", "http://kuler-api.adobe.com/");
define("KULER_API_KEY", "*api_key*");

define("COLOURLOVERS_API_URL", "http://www.colourlovers.com/api/");

define("USE_COLOR_SCHEME_API_CACHE", true);
define("COLOR_SCHEME_API_CACHE_LIFE_TIME", 60 * 60 * 3);


define("COMMAND_LESSC", "lessc %s %s");
define("COMMAND_LESSC_COMPRESS", "lessc %s -compress %s");

define("USE_LESSC_CACHE", true);


define("COMMAND_ZIP", "zip %s %s");
define("COMMAND_ZIP_KICKSTRAP", "zip -r %s %s");

?>
