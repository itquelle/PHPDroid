<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */

trait SettingsController{

    public function isInternetExplorer(){
        if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)) { return true; }else{ return false; }
    }

    var $page_title         = "ITQuelle";
    var $page_description   = "ITQuelle";
    var $page_keywords      = "";
    var $page_language      = "de";
    var $theme_color        = "#000000";
    var $facebook_app_id    = "";

    public function __construct(){
        $this->settings = [
            "title"             => $this->page_title,
            "version_code"      => version_code,
            "theme_color"       => $this->theme_color,
            "meta_description"  => $this->page_description,
            "meta_keywords"     => $this->page_keywords,
            "html_language"     => $this->page_language,
            "facebook_app_id"   => $this->facebook_app_id,
            "internet_explorer" => $this->isInternetExplorer(),
            "is_login"          => _Cookie("login"),
            "is_login_field_1"  => _Cookie("login_1"),
            "is_login_field_2"  => _Cookie("login_2"),
            "is_login_field_3"  => _Cookie("login_3"),
            "uniqid"            => uniqid()
        ];
    }

}