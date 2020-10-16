<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */
$RoutePath = "";
$languageArray = [];
require('app/config.php');
require('app/controller/config.php');

require('app/controller/language/index.php');
require('app/controller/mailer/index.php');
require('app/controller/settings/index.php');
require('app/controller/tools/index.php');

require("app/controller/router/index.php");

$route = new Route();

foreach($route->RouteAdapter as $key => $item){

    $route->add($item["url"], function() use($languageArray, $item){
        $initialize = new $item["page"]( null, $languageArray, func_get_args() );
        $initialize->onExecute();
    }, $item["method"]);

}

/**
 * @page Error
 */
$route->pathNotFound(function($path) use($languageArray){
    $initialize = new Error_Index( null, $languageArray, [
        "error_page" => $path
    ]);
    $initialize->onExecute();
});

$route->run($RoutePath);