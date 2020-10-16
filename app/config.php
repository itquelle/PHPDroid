<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */
#@ Log Reporting
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-Type: text/html");

define("options", [
    "version_code"          => 1.0,
    "locale_timezone"       => "de_DE.UTF-8",
    "error_reporting_mode"  => E_ALL & ~E_NOTICE,
    "display_errors"        => 1,
    "http_protocol"         => "http://"
]);


$languageArray = [];
require ("config.database.php");

setlocale(LC_TIME, options["locale_timezone"]);

error_reporting(options["error_reporting_mode"]);
ini_set("display_errors", options["display_errors"]);

// System Settings
define("initialize",    "");
define("version_code",  options["version_code"]);
define("project_name",  "ITQuelle Template Engine");
define("http_protocol", options["http_protocol"]);

#@ Get Base Dir
$BaseDir = function(){
    return dirname(parse_url($_SERVER["PHP_SELF"])["path"]);
};
$basedir = $BaseDir();

$RoutePath = $BaseDir();
$base_href = http_protocol . $_SERVER["SERVER_NAME"] . $basedir . "/";

define("base_href", $base_href);

#@ Languages
$available_languages = ["de", "en"];

#@ Super Functions
function _Replace($e){ return str_replace(array(chr(0x27), chr(0xbf)), array("", ""), $e); }
function _Get($e){ return (!empty($_GET[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_GET[$e])))) : ""; }
function _Post($e){ return (!empty($_POST[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_POST[$e])))) : ""; }
function _Session($e){ return (!empty($_SESSION[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_SESSION[$e])))) : ""; }
function _Cookie($e){ return (!empty($_COOKIE[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_COOKIE[$e])))) : ""; }
function _Price($val){ $val = str_replace(",",".",$val); $val = preg_replace('/\.(?=.*\.)/', '', $val); return floatval($val); }