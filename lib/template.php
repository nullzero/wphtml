<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('precision', 4);

function myErrorHandler($errno, $errstr, $errfile, $errline){
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}

set_error_handler("myErrorHandler");

if(isset($_GET["uselang"])){
  setcookie("uselang", $_GET["uselang"]);
  $GLOBALS["uselang"] = $_GET["uselang"];
}else if(empty($_COOKIE["uselang"])){
  setcookie("uselang", "en");
  $GLOBALS["uselang"] = "en";
}

include 'lib/L10N.php';

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

include 'helper.php';

if(!isset($script_name_s)){
  $script_name_s = str_replace("_", " ", ucfirst($script_name));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title> <?php 
    if($script_name != "index") echo "${script_name_s} - " . L10N("Nullzero's tools");
    else echo L10N("Nullzero's tools");
  ?> </title>
  <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="lib/css/bootstrap.css" rel="stylesheet">
  <link href="lib/css/bootstrap-responsive.css" rel="stylesheet">
  <link href="lib/css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet">
  <link href="lib/css/stylesheet.css" rel="stylesheet">
  <?php 
  echo "<link href='${type_page}/${script_name}/stylesheet.css' rel='stylesheet'>"; 
  ?>
</head>
<body>
  <div class="navbar">
    <div class="navbar-inner">
      <div class="container">
        <ul class="nav">
          <li>
            <a href="/" style="margin: 0px; padding: 0px; margin-top: 1px" class="span1">
              <img width="32px" border="0" 
                   title="<?php echo L10N("Powered by Wikimedia Labs"); 
                   ?>" src="//wikitech.wikimedia.org/w/images/c/cf/Labslogo_thumb.png">
            </a>
          </li>
          <li class="active">
            <a class="brand"> <?php
              if($script_name != "index") echo L10N($script_name_s);
              else echo L10N("Nullzero's tools"); ?> </a>
          </li>
          <li><a href="index.php">
              <i class="icon-home"></i>&nbsp;<?php echo L10N("Home"); ?>
          </a></li>
          <li><a href="./Tools:All_tools.php">
              <i class="icon-wrench"></i>&nbsp;<?php echo L10N("Tools"); ?>
          </a></li>
          <li><a href="/?status">
            <i class="icon-warning-sign"></i>&nbsp;<?php echo L10N("Bot's status"); ?>
          </a></li>
          <li><a href="//th.wikipedia.org/wiki/User_talk:Nullzero">
            <i class="icon-user"></i>&nbsp;<?php echo L10N("Contact"); ?>
          </a></li>
        </ul>
        <ul class="nav pull-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="icon-globe"></i>&nbsp;Language<b class="caret"></b>
            </a>
            <ul class="dropdown-menu">
              <li><a href="?uselang=en">English</a></li>
              <li><a href="?uselang=th">ภาษาไทย</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="span12">
        <?php
          include "${type_page}/${script_name}/index.php";
        ?>
      </div>
    </div>
  </div>
  <hr>
  <center>
  <span style="font-size:14">
    <?php echo L10N("If you encounter an error, please contact"); ?>
    <a href="//th.wikipedia.org/wiki/User_talk:Nullzero">
      <?php echo L10N("tools' owner"); ?>
    </a>
  </span>
  </center>
  <br />
  <script src='lib/js/jquery.js'></script>
  <script src='lib/js/jquery-ui-1.10.3.custom.min.js'></script>
  <script src='lib/js/bootstrap.min.js'></script>
  <script src='lib/js/script.js'></script>
  <?php
  echo "<script src='${type_page}/${script_name}/script.js'></script>";
  ?>
  
  <?php
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  $totaltime = ($endtime - $starttime);
  echo '<div id="exetime">' . L10N("Page generated in") . ' ' . 
       number_format($totaltime, 4) . ' ' . L10N("seconds") . '</div>';
  ?>
</body>
</html>
