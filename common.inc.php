<?php
ini_set("include_path",
	dirname(__FILE__) . "/config/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/vendor/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/vendor/PEAR/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/lib/"
	. PATH_SEPARATOR . ini_get("include_path")
);

require_once("config.php");

require_once("Cache/Lite.php");

define("COLOR_INPUT_NUM", 7);

define("OUTPUT_VERSION", "5");

function connect_db() {
	try {
		$dbh = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
	}
	catch (PDOException $e) {
		error_log($e->getMessage());
		throw $e;
	}
	return $dbh;
}

function check_colors() {
	// api_type
	if (!isset($_REQUEST["api_type"]) || !is_string($_REQUEST["api_type"]) || $_REQUEST["api_type"] == "") {
		return false;
	}
	
	$api_type = $_REQUEST["api_type"];
	if (!isset($GLOBALS["api_type_list"][$api_type])) {
		return false;
	}
	
	// id
	if (!isset($_REQUEST["id"]) || !is_string($_REQUEST["id"]) || $_REQUEST["id"] == "") {
		return false;
	}
		
	// c
	if (!isset($_REQUEST["c"]) || !is_array($_REQUEST["c"])) {
		return false;
	}
	
	$c = $_REQUEST["c"];
	if (count($c) != COLOR_INPUT_NUM) {
		return false;
	}
	
	foreach ($c as $val) {
		if (!preg_match('/^[0-9A-F]{6}$/', $val)) {
			return false;
		}
	}
	
	return true;
}

function get_style_path_parts($api_type, $id, $c) {
	ksort($c);
	$ret = $api_type . "/" . substr($c[0], 0, 2) . "/" . $id . "-" . implode("-", $c) . "/";
	return $ret; 
}

function fix_directory_separater($value, $ds = null) {
	if ($ds === null) {
		if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
			$value = str_replace("/", DIRECTORY_SEPARATOR, $value);
		}
		else {
			$value = str_replace("\\", DIRECTORY_SEPARATOR, $value);
		}
	}
	else {
		$value = str_replace("/", $ds, $value);
		$value = str_replace("\\", $ds, $value);
	}
	return $value;
}

function make_theme_dir_name($theme_name) {
	$ret = "";
	$len = strlen($theme_name);
	for ($i = 0; $i < $len; $i++) {
		$c = substr($theme_name, $i, 1);
		if (ctype_alnum($c)) {
			$ret .= $c;
		}
		else if ($c == " " || $c == "_") {
			$ret .= "_";
		}
	}

	if (strlen($ret) == 0) {
		$ret = "_";
	}

	return $ret;
}

function generate($api_type, $id, $c, $preview = false) {
	$api_result = call_color_scheme_api($api_type, $id);
	if (!$api_result) {
		return null;
	}

	$path_less = "../../../../less/";
	
	$path_parts = get_style_path_parts($api_type, $id, $c);
	$path_parts_md5_8 = substr(md5($path_parts), 0, 8);
	
	$path_output = dirname(__FILE__) . "/output/" . OUTPUT_VERSION . "/" . $path_parts;
	
	$path_bootstrap_css = $path_output . "bootstrap.css";
	$path_bootstrap_min_css = $path_output . "bootstrap.min.css";
	$path_bootstrap_responsive_css = $path_output . "bootstrap-responsive.css";
	$path_bootstrap_responsive_min_css = $path_output . "bootstrap-responsive.min.css";
	$path_bootstrap_less = $path_output . "bootstrap.less";
	$path_bootstrap_responsive_less = $path_output . "bootstrap-responsive.less";
	$path_variables_less = $path_output . "variables.less";
	$path_zip = $path_output . "paintstrap-" . $api_type . "-" . $id . "-" . $path_parts_md5_8 . ".zip";
	$path_zip_kickstrap = $path_output . "paintstrap-k-" . $api_type . "-" . $id . "-" . $path_parts_md5_8 . ".zip";
	
	$url_output = BASE_URL . "output/" . OUTPUT_VERSION . "/" . $path_parts;
	
	$url_bootstrap_css = $url_output . "bootstrap.css";
	$url_bootstrap_min_css = $url_output . "bootstrap.min.css";
	$url_bootstrap_responsive_css = $url_output . "bootstrap-responsive.css";
	$url_bootstrap_responsive_min_css = $url_output . "bootstrap-responsive.min.css";
	$url_variables_less = $url_output . "variables.less";
	$url_zip = $url_output . basename($path_zip);
	$url_zip_kickstrap = $url_output . basename($path_zip_kickstrap);
	
	if (!USE_LESSC_CACHE || 
			!file_exists($path_bootstrap_min_css) ||
			!file_exists($path_bootstrap_less) ||
			!file_exists($path_bootstrap_responsive_less)) 
	{
		if (!file_exists(dirname($path_bootstrap_less))) {
			mkdir(dirname($path_bootstrap_less), 0777, true);
		}
		if (!file_exists(dirname($path_bootstrap_min_css))) {
			mkdir(dirname($path_bootstrap_min_css), 0777, true);
		}

		ob_start();
		require(dirname(__FILE__) . "/less/bootstrap.less");
		$bootstrap_less = ob_get_clean();
		file_put_contents($path_bootstrap_less, $bootstrap_less);

		ob_start();
		require(dirname(__FILE__) . "/less/responsive.less");
		$bootstrap_responsive_less = ob_get_clean();
		file_put_contents($path_bootstrap_responsive_less, $bootstrap_responsive_less);

		$command = sprintf(COMMAND_LESSC_COMPRESS, escapeshellarg($path_bootstrap_less), escapeshellarg($path_bootstrap_min_css));
		$command = fix_directory_separater($command);
		$output = array();
		exec($command, $output);
		
		/*
		$command = sprintf(COMMAND_LESSC_COMPRESS, escapeshellarg($path_bootstrap_responsive_less), escapeshellarg($path_bootstrap_responsive_min_css));
		$command = fix_directory_separater($command);
		$output = array();
		exec($command, $output);
		*/
	}
		
	if (!$preview) {
		ob_start();
		require(dirname(__FILE__) . "/less/variables.less");
		$variables_less = ob_get_clean();
		
		if (!USE_LESSC_CACHE || 
				!file_exists($path_variables_less) ||
				!file_exists($path_bootstrap_css) ||
				!file_exists($path_bootstrap_responsive_css) || 
				!file_exists($path_bootstrap_responsive_min_css) ||
				!file_exists($path_zip) ||
				!file_exists($path_zip_kickstrap))
		{
			file_put_contents($path_variables_less, $variables_less);

			$command = sprintf(COMMAND_LESSC_COMPRESS, escapeshellarg($path_bootstrap_responsive_less), escapeshellarg($path_bootstrap_responsive_min_css));
			$command = fix_directory_separater($command);
			$output = array();
			exec($command, $output);

			$command = sprintf(COMMAND_LESSC, escapeshellarg($path_bootstrap_less), escapeshellarg($path_bootstrap_css));
			$command = fix_directory_separater($command);
			$output = array();
			exec($command, $output);

			$command = sprintf(COMMAND_LESSC, escapeshellarg($path_bootstrap_responsive_less), escapeshellarg($path_bootstrap_responsive_css));
			$command = fix_directory_separater($command);
			$output = array();
			exec($command, $output);
			
			$command = sprintf(COMMAND_ZIP, 
				escapeshellarg(basename($path_zip)), 
				implode(" ", array(
					escapeshellarg(basename($path_bootstrap_css)), 
					escapeshellarg(basename($path_bootstrap_min_css)), 
					escapeshellarg(basename($path_bootstrap_responsive_css)), 
					escapeshellarg(basename($path_bootstrap_responsive_min_css)), 
					escapeshellarg(basename($path_variables_less))
				))
			);
			$command = fix_directory_separater($command);
			chdir($path_output);
			$output = array();
			exec($command, $output);
			
			$path_kickstrap_theme = "themes/" . $api_result["themeDirName"] . "/";
			chdir($path_output);
			@mkdir($path_kickstrap_theme, 0777, true);
			copy($path_variables_less, $path_output . $path_kickstrap_theme . basename($path_variables_less));
			$command = sprintf(COMMAND_ZIP_KICKSTRAP, 
				escapeshellarg(basename($path_zip_kickstrap)), 
				"themes"
			);
			$command = fix_directory_separater($command);
			$output = array();
			exec($command, $output);
		}
	}
	
	$ret = array(
		"url_bootstrap_css" => $url_bootstrap_css,
		"url_bootstrap_min_css" => $url_bootstrap_min_css,
		"url_bootstrap_responsive_css" => $url_bootstrap_responsive_css,
		"url_bootstrap_responsive_min_css" => $url_bootstrap_responsive_min_css,
		"url_variables_less" => $url_variables_less,
		"url_zip" => $url_zip,
		"url_zip_kickstrap" => $url_zip_kickstrap,
		"file_name_zip" => basename($path_zip),
		"file_name_zip_kickstrap" => basename($path_zip_kickstrap)
	);
	return $ret;
}

function call_color_scheme_api($api_type, $id) {
	switch ($api_type) {
		case "kuler":
			$result = call_kuler_api($id);
			break;
		case "colourlovers":
			$result = call_colourlovers_api($id);
			break;
		default:
			return null;
	}
	return $result;
}

function create_cache_lite_object() {
	$options = array (
		"cacheDir" => BASE_PATH . "var/cache/",
		"lifeTime" => COLOR_SCHEME_API_CACHE_LIFE_TIME,
		"hashedDirectoryLevel" => 2
	);
	$obj = new Cache_Lite($options);
	return $obj;
}

function call_kuler_api($kuler_id) {
	if (!ctype_digit($kuler_id)) {
		return null;
	}
	
	$cache_lite = create_cache_lite_object();
	
	$xml_txt = $cache_lite->get($kuler_id, "kuler_api");
	if (!USE_COLOR_SCHEME_API_CACHE || !$xml_txt) {
		$params = array(
			"searchQuery" => "themeID:" . $kuler_id,
			"key" => KULER_API_KEY
		);
		$url = KULER_API_URL . "rss/search.cfm?" . http_build_query($params);
		$xml_txt = @file_get_contents($url);
		if (!$xml_txt) {
			return null;
		}
	
		$xml_txt = str_replace("kuler:", "kuler_", $xml_txt);
		$xml_txt = str_replace("xmlns:", "xmlns_", $xml_txt);
		
		$xml = @simplexml_load_string($xml_txt);
		if (!$xml || !isset($xml->channel->recordCount)) {
			return null;
		}
		
		if ($xml->channel->recordCount > 0) {
			$cache_lite->save($xml_txt, $kuler_id, "kuler_api");
		}
	}
	else {
		$xml = @simplexml_load_string($xml_txt);
	}
	
	if ($xml->channel->recordCount == 0) {
		$ret = array(
			"apiType" => "kuler",
			"record" => 0
		);
	}
	else {	
		$hex = array();
		foreach ($xml->channel->item->kuler_themeItem->kuler_themeSwatches->kuler_swatch as $key => $val) {
			$hex[] = (string)$val->kuler_swatchHexColor;
		}
		
		$ret = array(
			"apiType" => "kuler",
			"record" => 1,
			"hex" => $hex,
			"title" => (string)$xml->channel->item->enclosure->title,
			"link" => (string)$xml->channel->item->link,
			"themeID" => (string)$xml->channel->item->kuler_themeItem->kuler_themeID,
			"authorLabel" => (string)$xml->channel->item->kuler_themeItem->kuler_themeAuthor->kuler_authorLabel,
			"themeDirName" => make_theme_dir_name((string)$xml->channel->item->enclosure->title)
		);
	}
	return $ret;
}

function call_colourlovers_api($palette_id) {
	if (!ctype_digit($palette_id)) {
		return null;
	}
	
	$cache_lite = create_cache_lite_object();
		
	$xml_txt = $cache_lite->get($palette_id, "colourlovers_api");
	if (!USE_COLOR_SCHEME_API_CACHE || !$xml_txt) {
		$url = COLOURLOVERS_API_URL . "palette/" . $palette_id;
		$xml_txt = @file_get_contents($url);
		if (!$xml_txt) {
			return null;
		}
		
		$xml = @simplexml_load_string($xml_txt);
		if (!$xml) {
			// do nothing
		}
		else {
			$cache_lite->save($xml_txt, $palette_id, "colourlovers_api");
		}
	}
	else {
		$xml = @simplexml_load_string($xml_txt);
	}

	if (!$xml) {
		$ret = array(
			"apiType" => "colourlovers",
			"record" => 0
		);
	}
	else {	
		$hex = array();
		foreach ($xml->palette->colors->hex as $key => $val) {
			$hex[] = (string)$val;
		}
		
		$ret = array(
			"apiType" => "colourlovers",
			"record" => 1,
			"hex" => $hex,
			"title" => (string)$xml->palette->title,
			"link" => (string)$xml->palette->url,
			"themeID" => (string)$xml->palette->id,
			"authorLabel" => (string)$xml->palette->userName,
			"themeDirName" => make_theme_dir_name((string)$xml->palette->title)
		);
	}
	
	return $ret;
}
?>