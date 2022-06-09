<?php
namespace App\Service;

use Swift_Mailer;
use Swift_Message;
use Swift_Transport;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommonService
 *
 * @author acer
 */
class CommonService {
    private $transport;
    
    public function __construct(Swift_Transport $transport) {
        $this->transport = $transport;
    }
    public function getRandomStr() {
       return sha1(rand(10000000,99999999));
    }
    public function sendMail($to,$subject,$html) {

       $mailer = new Swift_Mailer($this->transport);
       $message = (new Swift_Message($subject))
               ->setTo($to)
               ->setFrom('iantdilpreet@gmail.com')
               ->setBody($html,'text/html');
       $mailer->send($message);
       return true;
    }
}
