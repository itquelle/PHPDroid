<?php

interface AppCompatActivity{

    public function onCreate();
    public function onExecute();

}

trait Tools
{

    var $StringRequest__Method = "GET";
    var $StringRequest__Debug = [];
    var $HttpRequest;

    public function StringRequest($url, $response_listener, $fields__post = []){

        $this->HttpRequest = curl_init();

        curl_setopt($this->HttpRequest, CURLOPT_URL, $url);
        curl_setopt($this->HttpRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->HttpRequest, CURLOPT_SSL_VERIFYPEER, true);

        curl_setopt($this->HttpRequest, CURLINFO_HEADER_OUT, true);

        if ($this->StringRequest__Method == "POST" || $fields__post) {
            curl_setopt($this->HttpRequest, CURLOPT_POST, 1);
            curl_setopt($this->HttpRequest, CURLOPT_POSTFIELDS, $fields__post);
        }

        $response = curl_exec($this->HttpRequest);

        $debug_info = curl_getinfo($this->HttpRequest);
        $this->StringRequest__Debug = $debug_info;

        if(curl_errno($this->HttpRequest)){
            throw new Exception(
                curl_error($this->HttpRequest)
            );
        }else{
            $response_listener($response);
        }

        curl_close($this->HttpRequest);

    }

}