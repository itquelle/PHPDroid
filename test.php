<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require "app/controller/template_engine/index.php";

$tpl = new ITQuelleTPL();

$arrayList = [
    [
        "id" => 1,
        "name" => "Stevie"
    ],
    [
        "id" => 2,
        "name" => "Bilal"
    ]
];

$tpl->assign([
    "test" => "Hallo",
    "list" => $arrayList
]);

$tpl->draw("page");