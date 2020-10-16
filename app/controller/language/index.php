<?php

trait Language{

    var $ressourceString;
    var $DefaultLanguage = "de";

    public function __construct(){

        $Language = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);

        if(!is_dir("res/values/".$Language."/strings.xml")){
            $resourceFile = "res/values/".$Language."/strings.xml";
        }else{
            $resourceFile = "res/values/".$this->DefaultLanguage."/strings.xml";
        }

        $getResourceFileHttp = file_get_contents($resourceFile);
        $getResourceFile = (array)simplexml_load_string($getResourceFileHttp);
        $getResourceFileOrginal = simplexml_load_string($getResourceFileHttp);


        $build = [];
        foreach($getResourceFile["string"] as $key => $value){
            $build[(string)$getResourceFileOrginal->{"string"}[$key]->attributes()["name"]] = (string)$value;
        }

        $this->ressourceString = $build;

    }

    public function lang($string){

        $String = (string)$this->ressourceString[$string];

        if(!empty($String)){
            return $String;
        }

    }

}