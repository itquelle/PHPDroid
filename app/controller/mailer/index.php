<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */

require('PHPMailerAutoload.php');

trait SendMail{

    var $error_message;
    var $error_code;
    var $sendMail;
    var $mailText, $mailFrom, $mailTo, $mailSubject, $mailFromName, $mailToName;

    var $sendMailSMTP = false;

    /** @var array */
    var $global = array(
        "host"  => "mail.gmx.net",
        "user"  => "mail@gmx.ch",
        "pass"  => "password",
        "port"  => "587",
        "from"  => "from-mail@gmx.ch",
        "name"  => "ITQuelle",
        "live"  => "error-message@mail.com"
    );

    public function mail(){

        $this->sendMail = new PHPMailer();

        if($this->sendMailSMTP == true) {
            $this->sendMail->isSMTP();
            $this->sendMail->SMTPAuth       = true;
        }else{
            $this->sendMail->SMTPAuth       = false;
        }

        $this->sendMail->SMTPDebug      = false;
        $this->sendMail->Debugoutput    = 'html';
        $this->sendMail->Host           = $this->global['host'];
        $this->sendMail->Port           = $this->global['port'];
        $this->sendMail->Username       = $this->global['user'];
        $this->sendMail->Password       = $this->global['pass'];

        $this->sendMail->isHTML(true);

        return $this;
    }

    public function to($mail, $name = ""){
        $this->mailToName = $name;
        $this->mailTo = $mail;
        return $this;
    }

    public function from($mail, $name = ""){
        $this->mailFromName = $name;
        $this->mailFrom = $mail;
        return $this;
    }

    public function subject($name){
        $this->mailSubject = $name;
        return $this;
    }

    public function text($text){
        $this->mailText = $text;
        return $this;
    }

    public function debug(){
        print_r($this);
    }

    public function send(){

        try{

            $this->sendMail->setFrom( $this->mailFrom, $this->mailFromName );
            $this->sendMail->addReplyTo( $this->mailFrom, $this->mailFromName );
            $this->sendMail->addAddress( $this->mailTo, $this->mailToName );

            $this->sendMail->Subject = $this->mailSubject;
            $this->sendMail->AltBody = $this->mailSubject;
            $this->sendMail->msgHTML = $this->mailText;
            $this->sendMail->Body    = $this->mailText;

            if( $this->sendMail->send() ){

                $this->sendMail->ClearAllRecipients();
                $this->sendMail->ClearCCs();
                $this->sendMail->ClearBCCs();

            }else{
                echo "Error: ...";
            }

        } catch (phpmailerException $e) {
            echo $e->errorMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

}
