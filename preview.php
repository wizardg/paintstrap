<?php
require_once(dirname(__FILE__) . "/common.inc.php");

$url_bootstrap_min_css = BASE_URL . "css/bootstrap.min.css";
$url_bootstrap_responsive_min_css = BASE_URL . "css/bootstrap-responsive.min.css";

if (check_colors()) {
	$api_type = $_REQUEST["api_type"];
	$id = $_REQUEST["id"];
	$c = $_REQUEST["c"];
	
	$result_generate = generate($api_type, $id, $c, true);
	if ($result_generate) {
		$url_bootstrap_min_css = $result_generate["url_bootstrap_min_css"];
		//$url_bootstrap_responsive_min_css = $result_generate["url_bootstrap_responsive_min_css"];
		$url_bootstrap_responsive_min_css = BASE_URL . "css/bootstrap-responsive.min.css";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Bootstrap, from Twitter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link rel="stylesheet" type="text/css" href="<?= $url_bootstrap_min_css ?>">
    <link rel="stylesheet" type="text/css" href="<?= $url_bootstrap_responsive_min_css ?>">
    <link rel="stylesheet" type="text/css" href="css/preview.css">
    <!--
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    -->

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <!--
    <link rel="shortcut icon" href="../assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
    -->
    <!--
    <script src="js/less-1.3.0.min.js" type="text/javascript"></script>
    -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script src="js/bootstrap.js"></script>
    
    <script type="text/javascript">
	<!--
	parent.postPreviewLoad();
	//-->
	</script>
  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="javascript:void(0);" onclick="return false;">Navigation</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="javascript:void(0);" onclick="return false;">Home</a></li>
              <!--
              <li><a href="javascript:void(0);" onclick="return false;">About</a></li>
              <li><a href="javascript:void(0);" onclick="return false;">Contact</a></li>
              -->
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    
    <div class="container">
      <div class="hero-unit">
	    <h1>Hero Unit</h1>
	    <div class="btn-group">
	    <a class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">
	    Action
	    <span class="caret"></span>
	    </a>
	    <ul class="dropdown-menu">
	    <!-- dropdown menu links -->
			<li><a tabindex="-1" href="javascript:void(0);" onclick="return false;">Action</a></li>
			<li><a tabindex="-1" href="javascript:void(0);" onclick="return false;">Another action</a></li>
			<li><a tabindex="-1" href="javascript:void(0);" onclick="return false;">Something else here</a></li>
			<li class="divider"></li>
			<li><a tabindex="-1" href="javascript:void(0);" onclick="return false;">Separated link</a></li>
	    </ul>
	    </div>    
      </div>
      
      <p>Text text text</p>
      <p><a href="javascript:void(0);" onclick="return false;">Link</a></p>
      
      <hr>
      
      <form onsubmit="return false;">
      	<p><input type="text" name="input1" value="Input:text"><br>
      		<!--
      		<input type="text" name="input1" value="Input:text disabled" disabled="disabled"><br>
      		-->
      		<button type="submit" class="btn btn-primary">Button</button>
      		<!--
      		<button type="submit" class="btn btn-primary disabled">Disabled</button>
      		-->
      		<!--
      		<input type="submit" class="btn btn-inverse" value="Inverse">
      		-->
      		</p>
      	<!--
      	<p><input type="submit" class="btn btn-primary" value="Primary">
      		<input type="submit" class="btn btn-info" value="Info">
      		<input type="submit" class="btn btn-success" value="Success">
      		<input type="submit" class="btn btn-warning" value="Warning">
      		<input type="submit" class="btn btn-danger" value="Danger">
      		<input type="submit" class="btn btn-inverse" value="Inverse">
      		</p>
      	-->
      </form>
      <!--
      <div class="well">Well</div>
      -->
      <!--
      <table class="table table-striped">
      	<tbody>
      		<tr>
      			<td>111</td>
      			<td>222</td>
      		</tr>
      		<tr>
      			<td>333</td>
      			<td>444</td>
      		</tr>
      	</tbody>
      </table>
         --> 
      </div> <!-- /container -->
  </body>
</html>
