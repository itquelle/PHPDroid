<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */

class Home_Index implements AppCompatActivity {

    use Database, SettingsController, Language, Tools {
        Database::__construct as public __DatabaseConstructor;
        SettingsController::__construct as public __SettingsConstructor;
        Language::__construct as public __LanguageService;
    }

    public $template = "layout/home/index";
    public $view, $settings, $mail, $options, $language;

    public function onCreate(){

        $this->view->assign([
            "test_var" => "Hi wie gehts?"
        ]);

    }

    public function __construct(...$Components){ $this->__DatabaseConstructor(); $this->__SettingsConstructor(); $this->__LanguageService(); $this->view = new ITQuelleTPL(); $this->language = $Components[1]; $this->options = $Components[2]; }
    public function onExecute(){ $this->onCreate(); $this->view->assign($this->settings); $this->view->assign($this->ressourceString); echo $this->view->draw($this->template, $return_string = true); }
}