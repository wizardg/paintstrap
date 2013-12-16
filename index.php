<?php
require_once(dirname(__FILE__) . "/common.inc.php");

define("GALLERY_MAX_PER_PAGE", 18);

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

\Slim\Route::setDefaultConditions(array(
    'lang' => 'en|ja'
));

$twigView = new \Slim\Extras\Views\Twig();

$app = new \Slim\Slim(array(
	'debug' => SLIM_DEBUG,
    'view' => $twigView,
    'templates.path' => dirname(__FILE__) . "/templates"
));

$app->hook('slim.before', function () use ($app) {
    $app->view()->appendData(array('base_url' => BASE_URL));
});

$app->get("/", function() use ($app) {
	index($app, "en");
});

$app->get("/:lang/?", function($lang) use ($app) {
	index($app, $lang);
});

function index($app, $lang) {
	if (!file_exists($app->view()->getTemplatesDirectory() . "/" . $lang . "/index.html")) {
		$app->notFound();
		return;
	}

	$app->render($lang . "/index.html", array(
		"lang" => get_lang_url_dir($lang),
		"this_url" => "",
		"bootstrap_version" => BOOTSTRAP_VERSION,
		"active_pages" => array($lang, "home")
	));
}

$app->get("/preview/?", function() use ($app) {
	$url_bootstrap_min_css = BASE_URL . "css/bootstrap.min.css";
	
	if (check_colors($app->request())) {
		$api_type = $app->request()->params("api_type");
		$id = $app->request()->params("id");
		$c = $app->request()->params("c");
		
		$result_generate = generate($api_type, $id, $c, true);
		if ($result_generate) {
			$url_bootstrap_min_css = $result_generate["url_bootstrap_min_css"];
		}
	}
	
	$design = $app->request()->params("design");
	if (is_null($design) || !in_array($design, array_keys($GLOBALS["valid_preview_designs"]))) {
		$design = "default";
	}
	
	$hiddens = $app->request()->get();
	unset($hiddens["design"]);
	
	$app->render("common/" . $GLOBALS["valid_preview_designs"][$design], array(
		"url_bootstrap_min_css" => $url_bootstrap_min_css,
		"form_hiddens" => $hiddens
	));
});

$app->get("/preview_by_id/:theme_id/?", function($theme_id) use ($app) {
	$url_bootstrap_min_css = BASE_URL . "css/bootstrap.min.css";
	
	$theme = find_theme($theme_id);
	if (!is_null($theme)) {
		$api_type = $theme["api_type"];
		$id = $theme["cs_id"];
		$c = $theme["_c"];
		
		$result_generate = generate($api_type, $id, $c, true);
		if ($result_generate) {
			$url_bootstrap_min_css = $result_generate["url_bootstrap_min_css"];
		}
	}
	
	$design = $app->request()->params("design");
	if (is_null($design) || !in_array($design, array_keys($GLOBALS["valid_preview_designs"]))) {
		$design = "default";
	}
	
	$hiddens = $app->request()->get();
	unset($hiddens["design"]);

	$app->render("common/" . $GLOBALS["valid_preview_designs"][$design], array(
		"url_bootstrap_min_css" => $url_bootstrap_min_css
	));
});

$app->get("/preview_large/?", function() use ($app) {
	$url_bootstrap_min_css = $app->request()->params("url_bootstrap_min_css");
	if (!check_url($url_bootstrap_min_css, ".css")) {
		$url_bootstrap_min_css = BASE_URL . "css/bootstrap.min.css";
	}
	
	$design = $app->request()->params("design");
	if (is_null($design) || !in_array($design, array_keys($GLOBALS["valid_preview_large_designs"]))) {
		$design = "jumbotron";
	}

	$app->render("common/" . $GLOBALS["valid_preview_large_designs"][$design], array(
		"url_bootstrap_min_css" => $url_bootstrap_min_css
	));
});

$app->get("/api/get_color_scheme/?", function() use ($app) {
	header("Content-type: text/json");
	
	// api_type
	$api_type = $app->request()->params("api_type");
	if (!is_string($api_type) || $api_type == "") {
		return false;
	}
	else if (!isset($GLOBALS["api_type_list"][$api_type])) {
		return false;
	}
	
	// id
	$id = $app->request()->params("id");
	if (!is_string($id) || $id == "") {
		return false;
	}
		
	$result = call_color_scheme_api($api_type, $id);
	if (!$result) {
		die("null");
	}
	
	print json_encode($result);
});

$app->get("/api/package/?", function() use ($app) {
	header("Content-type: text/json");
	
	if (!check_colors($app->request())) {
		die("null");
	}
	
	$api_type = $app->request()->params("api_type");
	$id = $app->request()->params("id");
	$c = $app->request()->params("c");
	$share = $app->request()->params("share");
	
	$result_generate = generate($api_type, $id, $c);
	if (!$result_generate) {
		die("null");
	}
	
	$theme_id = save_theme($api_type, $id, $c, $share);
	if (!$theme_id) {
		die("null");
	}
	
	if ($share) {
		$result = generate_for_gallery($theme_id);
		if (!$result) {
			die("null");
		}
	}
	
	print json_encode($result_generate);
});

$app->get("/api/package_by_id/:theme_id/?", function($theme_id) use ($app) {
	header("Content-type: text/json");
	
	$result_generate = generate_by_id($theme_id);
	if (!$result_generate) {
		die("null");
	}

	$result = add_download_count($theme_id);
	if (!$result) {
		die("null");
	}
	
	print json_encode($result_generate);
});

$app->get("/api/generate_for_gallery/:theme_id/?", function($theme_id) use ($app) {
	header("Content-type: text/json");
	
	$result = generate_for_gallery($theme_id);
	if (!$result) {
		die("null");
	}
	
	$ret = 1;
	print json_encode($ret);
});

$app->get("/contact/?", function() use ($app) {
	$app->render("common/contact.html");
});

$app->get("/changelog/?", function() use ($app) {
	$app->render("common/changelog.html");
});

$app->get("/gallery/?", function() use ($app) {
	gallery($app, "en");
});

$app->get("/:lang/gallery/?", function($lang) use ($app) {
	gallery($app, $lang);
});

function gallery($app, $lang) {
	if (!file_exists($app->view()->getTemplatesDirectory() . "/" . $lang . "/gallery.html")) {
		$app->notFound();
		return;
	}
	
	$tag_names = $app->request()->params("tag_names");
	if (!is_array($tag_names)) {
		$tag_names = array();
	}
	
	$tag_ids = get_tag_ids($tag_names);

	$themes_count = get_themes_count($tag_ids);
	$array = array();
	for ($i = 0; $i < $themes_count; $i++) {
		$array[$i] = $i;
	}

	$page = $app->request()->params("page");
	if (is_null($page)) {
		$page = 1;
	}
	
	$adapter = new ArrayAdapter($array);
	$pagerfanta = new Pagerfanta($adapter);
	$pagerfanta->setMaxPerPage(GALLERY_MAX_PER_PAGE);
	try {
		$pagerfanta->setCurrentPage($page);

		$current_page_results = $pagerfanta->getCurrentPageResults();
		if (count($current_page_results) == 0) {
			$themes = array();
		}
		else {
			$themes = find_themes($tag_ids, count($current_page_results), $current_page_results[0]);
		}
	}
	catch (Exception $e) {
		$themes = array();
	}
	
	$pagerfanta_view = new TwitterBootstrap3View();
	$pager_html = $pagerfanta_view->render($pagerfanta, function($page) use ($app, $lang) {
		$params = array(
			"page" => $page
		);
		$tag_names = $app->request()->params("tag_names");
		if (count($tag_names) > 0) {
			$params["tag_names"] = $tag_names;
		}
		return BASE_URL . get_lang_url_dir($lang) . "gallery?" . http_build_query($params);
	});
	
	$app->render($lang . "/gallery.html", array(
		"lang" => get_lang_url_dir($lang),
		"this_url" => "gallery/",
		"bootstrap_version" => BOOTSTRAP_VERSION,
		"active_pages" => array($lang, "gallery"),
		"body_class" => "gallery",
		"themes" => $themes,
		"pagerfanta" => $pagerfanta,
		"pager_html" => $pager_html,
		"tag_names" => $tag_names
	));
}

$app->run();

?>