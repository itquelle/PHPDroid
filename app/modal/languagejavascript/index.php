<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */

class LanguageJavascript_Index{

    use Database, SettingsController, SendMail, Language {
        Database::__construct as private __DatabaseConstructor;
        SettingsController::__construct as private __SettingsConstructor;
        Language::__construct as public __LanguageService;
    }

    var $template = "layout/languagejavascript/index";

    var $view, $settings, $mail, $options, $event;

    public function __construct()
    {
        $this->__DatabaseConstructor();
        $this->__SettingsConstructor();
        $this->__LanguageService();

        $args = func_get_args();

        $this->view     = $args[0];
        $this->language = $args[1];
        $this->options  = $args[2];

    }
    public function onLoad(){

        echo 'function lang(key){ ';
        echo 'var LanguageArray = { ';

        $LanguageArrayList = [];

        foreach($this->ressourceString as $key => $value){
            array_push($LanguageArrayList, "$key:'".htmlspecialchars(addslashes($value))."'");
        }

        $LanguageArrayList = join(",", $LanguageArrayList);

        echo $LanguageArrayList;

        echo '}; return LanguageArray[key]; }';

        die;

    }
    public function onExecute(){

        $this->onLoad();

        $this->view->assign($this->settings);
        $this->view->assign($this->language);

        echo $this->view->draw($this->template, $return_string = true);

    }

}
