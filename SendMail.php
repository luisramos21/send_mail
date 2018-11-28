<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SendMail
 *
 * @author TI DST
 */
header("Access-Control-Allow-Origin: *");
require_once("./Rest.inc.php");
include("./PHPMailer-master/PHPMailerAutoload.php");

//include("./PHPMailer-master/class.smtp.php");

class SendMail extends REST {

    //put your code here

    private $_host = "smtp.gmail.com";
    private $_port = 25;
    private $_user = "noreply@onlinedst.com";
    private $_pass = "0nl1n3DST";
    private $Users_Accounts_Mails = array(
        'SIMS@onlinedst.com',
        'developer@onlinedst.com'
    );
    private $Users_Accounts_Password = array(
        '9YBR<ngQD'/* pass to index 0 */,
        '0nl1n3DST'
    );

    function __construct() {
        $response = array('msg' => '', 'code' => 404);
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['rquest'])));
        if ((int) method_exists($this, $func) > 0) {
            foreach ($_REQUEST as $key => $value)
                if ($key !== 'rquest')
                    $this->$key = $value;
            $response = $this->$func();
        }
        $this->response($response['msg'], $response['code']);
    }

    public function SimpleSendInfo() {
        $return = array('msg' => 'Correo de destino no aceptados.', 'code' => 200);
        if (isset($this->addresses) && count($this->addresses) > 0) {

            if (isset($this->from)) {
                $this->_user = $this->from;
            }

            if (isset($this->UseAccount) && $this->UseAccount && array_search($this->from, $this->Users_Accounts_Mails)) {
                
                $i = array_search($this->from, $this->Users_Accounts_Mails);                
                if (isset($this->Users_Accounts_Password[$i])) {
                    $this->_pass = $this->Users_Accounts_Password[$i];
                }
            }

            $connectGmail = $this->connectGmail();
            $connectGmail->From = $this->_user;
            $connectGmail->FromName = $this->_user;
            if (!isset($this->name)) {
                $connectGmail->SetFrom($this->_user, 'SIMS');
                $connectGmail->AddReplyTo($this->_user, 'SIMS');
            } else {
                $connectGmail->SetFrom(isset($this->fromName) ? $this->fromName : $this->_user, $this->name);
                $connectGmail->AddReplyTo(isset($this->fromName) ? $this->fromName : $this->_user, $this->name);
            }

            if (!file_exists($this->attachment['path'])) {
                file_put_contents('respinsefile', print_r($this->attachment, true));
            }
            if (isset($this->attachment) && isset($this->attachment['path']) && isset($this->attachment['file_name']) && file_exists($this->attachment['path']))
                $connectGmail->AddAttachment($this->attachment['path'], $this->attachment['file_name']);
            if ($this->attachmentURL && isset($this->attachmentURL['url']) && isset($this->attachmentURL['file_name']))
                $connectGmail->addStringAttachment(file_get_contents($this->attachmentURL['url']), $this->attachmentURL['file_name']);
            foreach ($this->addresses as $address)
                $connectGmail->AddAddress($address, $address);
            if (isset($this->addressesCC) && count($this->addressesCC) > 0)
                foreach ($this->addressesCC as $address)
                    $connectGmail->addCC($address, $address);
            if (isset($this->addressesBCC) && count($this->addressesBCC) > 0)
                foreach ($this->addressesBCC as $address)
                    $connectGmail->addBCC($address, $address);
            if (isset($this->message)) {
//                file_put_contents("message", $this->message);
                $connectGmail->Body = $this->message;
                $connectGmail->MsgHTML($this->message);
                if (isset($this->subject)) {
                    $connectGmail->Subject = $this->subject;
                    $status = $this->sendMail($connectGmail);
                    $return = array('msg' => $status, 'code' => 200);
                } else
                    $return = array('msg' => 'ERROR: Mensaje sin asunto.', 'code' => 200);
            } else
                $return = array('msg' => 'ERROR: Mensaje vacio.', 'code' => 200);
        }
        return $return;
    }

    private function sendMail($connect) {
        if (!$connect->Send()) {
            return "ERROR: " . $connect->ErrorInfo;
        } else {
            return 'OK';
        }
    }

    private function connectGmail() {
        $mail = new PHPMailer();
//        $mail->IsSMTP();
//        $mail->Mailer="smtp";
        $mail->IsHTML(true);
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "ssl";
        $mail->Helo = "smtp.gmail.com";
        $mail->Host = $this->_host;
        $mail->Port = $this->_port;
        $mail->Username = $this->_user;
        $mail->Password = $this->_pass;
        $mail->SMTPDebug = 2;
        if (!isset($this->name)) {
            $mail->AltBody = 'SIMS';
        } else {
            $mail->AltBody = $this->name;
        }
        $mail->Priority = 1;
        $mail->AddCustomHeader("X-MSMail-Priority: High");
        //$mail->AddCustomHeader("Reply-to:{$this->_user}");
        $mail->CharSet = "UTF-8";
        $mail->WordWrap = 50;
        return $mail;
    }

}

new SendMail();
