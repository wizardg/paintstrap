<?php
ini_set("include_path",
	dirname(__FILE__) . "/config/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/vendor/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/vendor/PEAR/"
	. PATH_SEPARATOR . dirname(__FILE__) . "/lib/"
	. PATH_SEPARATOR . ini_get("include_path")
);

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();


require_once("config.php");

require_once("Cache/Lite.php");

require_once("Cocur/Autoloader/Autoloader.php");
Cocur\Autoloader\Autoloader::register();

require_once("Twig/Autoloader.php");
Twig_Autoloader::register();

require_once("phpColors/color.php");
use phpColors\Color;

require_once("snappy/autoload.php");
use Knp\Snappy\Image;

require_once("snappy/autoload.php");

define("COLOR_INPUT_NUM", 7);

define("OUTPUT_VERSION", "18");
define("BOOTSTRAP_VERSION", "3.3.7");

$GLOBALS["valid_preview_designs"] = array(
	"default" => "preview-default.html",
	"thumbnail" => "preview-thumbnail.html",
	"large" => "preview-large.html"
);

$GLOBALS["valid_preview_large_designs"] = array(
	"blog" => "blog/index.html",
	"carousel" => "carousel/index.html",
	//"cover" => "cover/index.html",
	"dashboard" => "dashboard/index.html",
	"grid" => "grid/index.html",
	"jumbotron" => "jumbotron/index.html",
	"jumbotron-narrow" => "jumbotron-narrow/index.html",
	"justified-nav" => "justified-nav/index.html",
	"navbar" => "navbar/index.html",
	"navbar-fixed-top" => "navbar-fixed-top/index.html",
	"navbar-static-top" => "navbar-static-top/index.html",
	"non-responsive" => "non-responsive/index.html",
	"offcanvas" => "offcanvas/index.html",
	"signin" => "signin/index.html",
	"starter-template" => "starter-template/index.html",
	"sticky-footer" => "sticky-footer/index.html",
	"sticky-footer-navbar" => "sticky-footer-navbar/index.html"
	//"theme" => "theme/index.html"
);

$GLOBALS["api_type_list"] = array(
	"kuler" => array(
		"name" => "Adobe kuler"
	),
	"colourlovers" => array(
		"name" => "COLOURlovers"
	)
);


function connect_db() {
	static $dbh = null;
	if (is_null($dbh)) {
		try {
			$dbh = new PDO(DB_DSN, DB_USER, DB_PASSWORD);
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e) {
			error_log($e->getMessage());
			throw $e;
		}
	}
	return $dbh;
}

function check_url($url, $extension = null) {
	if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $url)) {
		return false;
	}

	if (substr($url, 0, strlen(BASE_URL)) !== BASE_URL) {
		return false;
	}

	if (!is_null($extension)) {
		if (substr($url, -strlen($extension)) !== $extension) {
			return false;
		}
	}

	return true;
}

function check_colors($req) {
	// api_type
	$api_type = $req->params("api_type");
	if (!is_string($api_type) || $api_type == "") {
		return false;
	}
	else if (!isset($GLOBALS["api_type_list"][$api_type])) {
		return false;
	}

	// id
	$id = $req->params("id");
	if (!is_string($id) || $id == "") {
		return false;
	}

	// c
	$c = $req->params("c");
	if (!is_array($c)) {
		return false;
	}
	else if (count($c) != COLOR_INPUT_NUM) {
		return false;
	}

	foreach ($c as $key => $val) {
		if (!ctype_digit((string)$key) || $key < 0 || $key >= COLOR_INPUT_NUM) {
			return false;
		}

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
	if (!$api_result || $api_result["record"] == 0) {
		return null;
	}

	$path_less = "../../../../less/";

	$path_parts = get_style_path_parts($api_type, $id, $c);
	$path_parts_md5_8 = substr(md5($path_parts), 0, 8);

	$path_output = dirname(__FILE__) . "/output/" . OUTPUT_VERSION . "/" . $path_parts;

	$path_bootstrap_css = $path_output . "bootstrap.css";
	$path_bootstrap_min_css = $path_output . "bootstrap.min.css";
	$path_bootstrap_less = $path_output . "bootstrap.less";
	$path_variables_less = $path_output . "variables.less";
	$path_zip = $path_output . "paintstrap-" . $api_type . "-" . $id . "-" . $path_parts_md5_8 . ".zip";
	$path_zip_kickstrap = $path_output . "paintstrap-k-" . $api_type . "-" . $id . "-" . $path_parts_md5_8 . ".zip";

	$url_output = BASE_URL . "output/" . OUTPUT_VERSION . "/" . $path_parts;

	$url_bootstrap_css = $url_output . "bootstrap.css";
	$url_bootstrap_min_css = $url_output . "bootstrap.min.css";
	$url_variables_less = $url_output . "variables.less";
	$url_zip = $url_output . basename($path_zip);
	$url_zip_kickstrap = $url_output . basename($path_zip_kickstrap);

	if (!USE_LESSC_CACHE ||
			!file_exists($path_bootstrap_min_css) ||
			!file_exists($path_bootstrap_less))
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

		$command = sprintf(COMMAND_LESSC_COMPRESS, escapeshellarg($path_bootstrap_less), escapeshellarg($path_bootstrap_min_css));
		$command = fix_directory_separater($command);
		$output = array();
		//error_log($command);
		exec($command, $output);
		//error_log(implode(" / ", $output));
	}

	if (!$preview) {
		ob_start();
		require(dirname(__FILE__) . "/less/variables.less");
		$variables_less = ob_get_clean();

		if (!USE_LESSC_CACHE ||
				!file_exists($path_variables_less) ||
				!file_exists($path_bootstrap_css) ||
				!file_exists($path_zip) ||
				!file_exists($path_zip_kickstrap))
		{
			file_put_contents($path_variables_less, $variables_less);

			$command = sprintf(COMMAND_LESSC, escapeshellarg($path_bootstrap_less), escapeshellarg($path_bootstrap_css));
			$command = fix_directory_separater($command);
			$output = array();
			exec($command, $output);

			$command = sprintf(COMMAND_ZIP,
				escapeshellarg(basename($path_zip)),
				implode(" ", array(
					escapeshellarg(basename($path_bootstrap_css)),
					escapeshellarg(basename($path_bootstrap_min_css)),
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
		"url_variables_less" => $url_variables_less,
		"url_zip" => $url_zip,
		"url_zip_kickstrap" => $url_zip_kickstrap,
		"file_name_zip" => basename($path_zip),
		"file_name_zip_kickstrap" => basename($path_zip_kickstrap)
	);
	return $ret;
}

function get_thumbnail_path($theme_id) {
	$theme_id_md5_2 = substr(md5($theme_id), 0, 2);
	$path_image = "gallery/output/" . $theme_id_md5_2 . "/" . $theme_id . ".png";
	return $path_image;
}

function generate_for_gallery($theme_id) {
	$theme = find_theme($theme_id);
	if (is_null($theme)) {
		return false;
	}

	if ($theme["share"] != 1) {
		return false;
	}

	$path_image = BASE_PATH . get_thumbnail_path($theme_id);
	if (!file_exists(dirname($path_image))) {
		mkdir(dirname($path_image), 0777, true);
	}

	if (!file_exists($path_image)) {
		$snappy = new Image(COMMAND_WKHTMLTOIMAGE, array(
			"format" => "png",
			"width" => 200,
			"zoom" => 0.75,
			"quality" => 50,
			"disable-javascript" => true
		));
		$snappy->setDefaultExtension("png");
		$params = array(
			"api_type" => $theme["api_type"],
			"id" => $theme["cs_id"],
			"c" => $theme["_c"],
			"design" => "thumbnail"
		);
		$url = BASE_URL . "preview?" . http_build_query($params);
		$url = str_replace("%5B", "[", $url);
		$url = str_replace("%5D", "]", $url);
		$image = $snappy->getOutput($url);

		file_put_contents($path_image, $image);
	}

	return true;
}

function generate_by_id($theme_id) {
	$theme = find_theme($theme_id);
	if (is_null($theme)) {
		return null;
	}

	return generate($theme["api_type"], $theme["cs_id"], $theme["_c"]);
}

function find_theme($theme_id) {
	$dbh = connect_db();

	$sql = "select * from themes where theme_id = ? and share = 1";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(1, $theme_id);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (empty($row)) {
		return null;
	}

	$row["_c"] = explode(",", $row["colors"]);
	if (count($row["_c"]) != COLOR_INPUT_NUM) {
		return null;
	}

	return $row;
}

function save_theme($api_type, $id, $c, $share) {
	$api_result = call_color_scheme_api($api_type, $id);
	if (!$api_result || $api_result["record"] == 0) {
		return false;
	}

	$now = date("Y-m-d H:i:s");

	ksort($c);
	$colors = implode(",", $c);

	$save_taggings = false;

	$dbh = connect_db();

	$dbh->beginTransaction();
	try {
		$sql = "select * from themes where api_type = ? and cs_id = ? and colors = ?";
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1, $api_type);
		$stmt->bindValue(2, $id);
		$stmt->bindValue(3, $colors);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!empty($row)) {
			$theme_id = $row["theme_id"];

			if ($row["share"] == 0 && $share) {
				$sql = "update themes set cs_name = ?, share = 1, ip_addr = ?, created_at = now(), updated_at = now() where theme_id = ?";
				$stmt = $dbh->prepare($sql);
				$stmt->bindValue(1, $api_result["title"]);
				$stmt->bindValue(2, $_SERVER["REMOTE_ADDR"]);
				$stmt->bindValue(3, $theme_id);
				$stmt->execute();

				$save_taggings = true;
			}
		}
		else {
			$sql = "insert into themes (api_type, cs_id, cs_name, colors, share, ip_addr, created_at, updated_at) values (?, ?, ?, ?, ?, ?, now(), now())";
			$stmt = $dbh->prepare($sql);
			$stmt->bindValue(1, $api_type);
			$stmt->bindValue(2, $id);
			$stmt->bindValue(3, $api_result["title"]);
			$stmt->bindValue(4, $colors);
			$stmt->bindValue(5, ($share ? 1 : 0));
			$stmt->bindValue(6, $_SERVER["REMOTE_ADDR"]);
			$stmt->execute();

			$theme_id = $dbh->lastInsertId();

			if ($share) {
				$save_taggings = true;
			}
		}

		if ($save_taggings) {
			$sql = "delete from taggings where theme_id = ?";
			$stmt = $dbh->prepare($sql);
			$stmt->bindValue(1, $theme_id);
			$stmt->execute();

			$tagging_tag_names = calculate_taggings($c);
			$tags = find_tags();

			$sql = "insert into taggings (theme_id, tag_id, created_at) values (?, ?, now())";
			$stmt = $dbh->prepare($sql);
			foreach ($tagging_tag_names as $tagging_tag_name) {
				$tag_id = array_search($tagging_tag_name, $tags);
				if ($tag_id === false) {
					throw new Exception("Tag not found");
				}

				$stmt->bindValue(1, $theme_id);
				$stmt->bindValue(2, $tag_id);
				$stmt->execute();
			}
		}
		$dbh->commit();
	}
	catch (Exception $e) {
		var_dump($e);
		$dbh->rollback();
		return false;
	}

	return $theme_id;
}

function add_download_count($theme_id) {
	$dbh = connect_db();

	$theme = find_theme($theme_id);
	if (is_null($theme)) {
		return null;
	}

	if ($theme["last_download_ip_addr"] == "" ||
			(string)$theme["last_download_ip_addr"] !== (string)$_SERVER["REMOTE_ADDR"])
	{
		$sql = "update themes set download_count = download_count + 1, last_download_ip_addr = ? where theme_id = ?";
		$stmt = $dbh->prepare($sql);
		$stmt->bindValue(1, $_SERVER["REMOTE_ADDR"]);
		$stmt->bindValue(2, $theme_id);
		$stmt->execute();
	}

	return true;
}

function find_tags() {
	return array(
		"-",
		"white",
		"black",
		"gray",
		"red",
		"yellow",
		"yellow_green",
		"green",
		"aqua_blue",
		"blue",
		"violet",
		"purple",
		"high_saturation",
		"low_saturation",
		"normal_saturation",
		"high_lightness",
		"low_lightness",
		"normal_lightness"
	);
}

function get_tag_ids($tag_names) {
	$tags = find_tags();

	$ret = array();
	foreach ($tag_names as $key => $tag_name) {
		$idx = array_search($tag_name, $tags);
		if ($idx !== false) {
			$ret[] = $idx;
		}
	}
	return $ret;
}

function find_themes($api_types, $cs_id, $tag_ids, $limit = null, $offset = null, $count_only = false) {
	$dbh = connect_db();

	$cs_id_where = "";
	if ($cs_id != "") {
		$cs_id_where = " and cs_id = " . $dbh->quote($cs_id) . " ";
	}

	$api_type_where = "";
	if (!empty($api_types)) {
		$quoted_api_types = array();
		foreach ($api_types as $api_type) {
			$quoted_api_types[] = $dbh->quote($api_type);
		}
		$api_type_where = " and api_type in (" . implode(",", $quoted_api_types) . ") ";
	}

	if ($count_only) {
		$sql = "select count(*) as c from themes where " . _make_find_themes_where($tag_ids) .
				$cs_id_where .
				$api_type_where;
		$result = $dbh->query($sql);
		$row = $result->fetch(PDO::FETCH_ASSOC);
		return $row["c"];
	}
	else {
		$sql = "select * from themes where " . _make_find_themes_where($tag_ids) .
				$cs_id_where .
				$api_type_where .
				" order by created_at desc limit " . $limit . " offset " . $offset;
		$stmt = $dbh->prepare($sql);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $key => $row) {
			$rows[$key]["_url_thumbnail"] = BASE_URL . get_thumbnail_path($row["theme_id"]);

			$created_at_utc = new DateTime($rows[$key]["created_at"]);
			$created_at_utc->setTimezone(new DateTimeZone('UTC'));
			$rows[$key]["_created_at_utc"] = $created_at_utc;
		}

		return $rows;
	}
}

function _make_find_themes_where($tag_ids) {
	$dbh = connect_db();

	$wheres = array();

	if (count($tag_ids) > 0) {
		$sql = "select theme_id from taggings where tag_id = ?";
		$stmt = $dbh->prepare($sql);

		$theme_ids = array();
		foreach ($tag_ids as $tag_id) {
			$tmp_theme_ids = array();

			$stmt->bindValue(1, $tag_id);
			$stmt->execute();
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				$tmp_theme_ids[] = $row["theme_id"];
			}

			if (count($theme_ids) == 0) {
				$theme_ids = $tmp_theme_ids;
			}
			else {
				$theme_ids = array_intersect($theme_ids, $tmp_theme_ids);
			}
		}

		if (count($theme_ids) == 0) {
			$wheres[] = "false";
		}
		else {
			$wheres[] = "theme_id in (" . implode(",", $theme_ids) . ")";
		}
	}

	$wheres[] = "share = 1";

	$where = implode(" and ", $wheres);
	return $where;
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
		"lifeTime" => CACHE_LIFE_TIME,
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

function calculate_taggings($colors) {
	$target_colors = array($colors[4], $colors[5]);

	$tagging_tag_names = array();

	foreach ($target_colors as $color) {
		$color_obj = new Color("#" . $color);

		$hsl = $color_obj->getHsl();

		if ($hsl["L"] > 0.9) {
			$tagging_tag_names[] = "white";
			continue;
		}
		else if ($hsl["L"] < 0.1) {
			$tagging_tag_names[] = "black";
			continue;
		}
		else if ($hsl["S"] < 0.1) {
			$tagging_tag_names[] = "gray";
			continue;
		}

		$hue = (int)(($hsl["H"] + 22.5) / 45.0);
		if ($hue == 8) {
			$hue = 0;
		}
		switch ($hue) {
			case 0:
				$tagging_tag_names[] = "red";
				break;
			case 1:
				$tagging_tag_names[] = "yellow";
				break;
			case 2:
				$tagging_tag_names[] = "yellow_green";
				break;
			case 3:
				$tagging_tag_names[] = "green";
				break;
			case 4:
				$tagging_tag_names[] = "aqua_blue";
				break;
			case 5:
				$tagging_tag_names[] = "blue";
				break;
			case 6:
				$tagging_tag_names[] = "violet";
				break;
			case 7:
				$tagging_tag_names[] = "purple";
				break;
		}

		if ($hsl["S"] > (2.0 / 3.0)) {
			$tagging_tag_names[] = "high_saturation";
		}
		else if ($hsl["S"] < (1.0 / 3.0)) {
			$tagging_tag_names[] = "low_saturation";
		}
		else {
			$tagging_tag_names[] = "normal_saturation";
		}

		if ($hsl["L"] > (2.0 / 3.0)) {
			$tagging_tag_names[] = "high_lightness";
		}
		else if ($hsl["L"] < (1.0 / 3.0)) {
			$tagging_tag_names[] = "low_lightness";
		}
		else {
			$tagging_tag_names[] = "normal_lightness";
		}
	}

	$tagging_tag_names = array_unique($tagging_tag_names);
	return $tagging_tag_names;
}

function get_lang_url_dir($lang) {
	if ($lang == "en") {
		$ret = "";
	}
	else {
		$ret = $lang . "/";
	}
	return $ret;
}
?>
