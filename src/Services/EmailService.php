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
    public function __construct($mailer_host, $mailer_port,$mailer_username, $mailer_password)
    {
        $this->host=$mailer_host;
        $this->port=$mailer_port;
        $this->username=$mailer_username;
        $this->password=$mailer_password;
    }


    public function sendEmail($from,$to,$subject,$body){
        try{
            $transport = (new \Swift_SmtpTransport($this->host, $this->port))
                ->setUsername($this->username)
                ->setPassword($this->password);

            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message($subject))->setFrom($from)->setTo($to)->setBody($body);
            $mailer->send($message);
        }
        catch (\Exception $e){
        }
    }
}