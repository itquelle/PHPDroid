<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */
define('base_url', $base_href);
define('modal_dir', 'app/modal/');
define('view_dir', 'res/');
define('cache_dir', 'res/layout/@cache/');
define('controller_dir', 'app/controller/');

require("template_engine/index.php");

spl_autoload_register(function ($requireClass){

    $OrginalClassName = $requireClass;

    if(strpos($requireClass, "Database") !== false){
        require("database/index.php");
    }else {

        $requireClass = str_replace('\\', '', $requireClass);
        $requireClass = strtolower($requireClass);
        $requireClass = str_replace('_', '/', $requireClass);
        $requireClassModal = modal_dir . $requireClass . '.php';
        $requireClassController = controller_dir . $requireClass . '.php';

        // Prüfe ob Modal Ordner vorhanden ist
        if (!is_dir("app/modal/" . dirname($requireClass))) {
            //Class
            mkdir("app/modal/" . dirname($requireClass));
            $Builder = file_get_contents("app/controller/builder_class/builder.txt");
            $Builder = str_replace(
                ["{ClassName}", "{TemplatePage}", "{Date}", "{Copyright}"],
                [$OrginalClassName, "layout/" . dirname($requireClass) . "/index", date("d.m.Y"), "2020 ITQuelle.de"],
                $Builder
            );
            file_put_contents("app/modal/" . dirname($requireClass) . "/index.php", $Builder, FILE_APPEND || LOCK_EX);

            // Template
            mkdir("res/layout/" . dirname($requireClass));
            mkdir("res/layout/" . dirname($requireClass) . "/css");

            // Create SCSS File
            file_put_contents("res/layout/" . dirname($requireClass) . "/css/index.scss", '@import "../../../styles/font";'."\n".'@import "../../../styles/func";' . "\n\n");
            file_put_contents("res/layout/" . dirname($requireClass) . "/css/index.css", "/** @auto **/");
            // Create Template File
            file_put_contents("res/layout/" . dirname($requireClass) . "/index.html", '<include file="../header/index"></include><link rel="stylesheet" href="css/index.css">'."\n\n\n".'<include file="../footer/index"></include>', FILE_APPEND || LOCK_EX);
        }

        if (!file_exists($requireClassModal)) {
            require($requireClassController);
        } else {
            require($requireClassModal);
        }

    }

});
