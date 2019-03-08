<?php
/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 22/5/2018
 * Time: 11:41
 */

namespace App\Services;

class EmailService
{
    private $host;
    private $port;
    private $username;
    private $password;

    /**
     * EmailService constructor.
     * @param $mailer_url
     */
    public function __construct($mailer_url)
    {
        $this->host='correo.ica.edu.cu';
        $this->port=25;
        $this->username='eduardo';
        $this->password='hkusxpq1*';
    }


    public function sendEmail($from,$to,$subject,$body){
        $transport = (new \Swift_SmtpTransport($this->host, $this->port))
            ->setUsername($this->username)
            ->setPassword($this->password);

        $mailer = new \Swift_Mailer($transport);
        $message = (new \Swift_Message($subject))->setFrom($from)->setTo($to)->setBody($body);
        $mailer->send($message);
    }
}