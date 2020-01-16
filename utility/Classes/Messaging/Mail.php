<?php

namespace Messaging;

class Mail
{
    // smtp host
    private $smtpHost = 'smtp.mailtrap.io';

    // smtp user
    private $smtpUser = '5a3989295069d7';

    // smtp pass
    private $smtpPass = 'df646b75f7ce38';

    // smtp port
    private $smtpPort = 2525;

    // transport instance
    private static $transport = null;

    // build data
    private $mail_data = [];

    // create 
    public static function config(array $data)
    {
        // create a mail instance
        $mail = new Mail;

        // loop through array
        foreach ($data as $key => $val)
        {
            if (property_exists($mail, $key))
            {
                // set property
                $mail->{$key} = $val;
            }
        }

        // create transport
        self::createTransport($mail);

        // return mail class
        return $mail;
    }

    // create transport
    public static function createTransport(&$ins = null)
    {
        if (is_null(self::$transport))
        {
            // Create mail
            if (is_null($ins))
            {
                $ins = new Mail;
            }

            // Create the Transport
            $transport = (new \Swift_SmtpTransport($ins->smtpHost, $ins->smtpPort))
            ->setUsername($ins->smtpUser)
            ->setPassword($ins->smtpPass)
            ;

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // save transport
            self::$transport = $mailer;
        }

        return self::$transport;
    }

    // set subject
    public function subject(string $subject)
    {
        $this->mail_data['setSubject'] = $subject;
        return $this;
    }

    // set from
    public function from()
    {
        $args = func_get_args();
        $this->mail_data['setFrom'] = $args;
        return $this;
    }

    // set reciever
    public function to()
    {
        $args = func_get_args();
        $this->mail_data['setTo'] = $args;
        return $this;
    }

    // set body
    public function body(string $text)
    {
        $this->mail_data['setBody'] = $text;
        return $this;
    }

    // set html
    public function html(string $text)
    {
        $this->mail_data['addPart'] = [$text, 'text/html'];
        return $this;
    }

    // add attachment
    public function attach($file)
    {
        $this->mail_data['attach'] = \Swift_Attachment::fromPath($file);
        return $this;
    }

    // send mail
    public function send()
    {
        // get transport
        $transport = self::createTransport();
        
        // Create the message
        $message = new \Swift_Message();

        // read mail data
        if (count($this->mail_data) > 0)
        {
            foreach ($this->mail_data as $method => $args)
            {
                if (is_array($args))
                {
                    $message = call_user_func_array([$message, $method], $args);
                }
                else
                {
                    $message = call_user_func([$message, $method], $args);
                }
            }
        }

        $message = $transport->send($message);

        return $message;
    }
}