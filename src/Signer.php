<?php

    namespace Loborec\Message;

    use Zend\Mail\Message;
    use Zend\Mime\Message as MimeMessage;
    use Zend\Mail\Header;

    /**
    * Signer.
    * @package Dkim\Signer
    */
    class Signer
    {

        private $private_key;
        private $domain;
        private $selector;
        private $options;
        private $canonicalized_headers_relaxed;

        public function __construct($private_key, $passphrase, $domain, $selector, $options = array()){

            // prepare the resource
            $this -> private_key = $private_key;
            $this -> passphrase = $passphrase;
            $this -> domain = $domain;
            $this -> selector = $selector;
            $this -> options=$options;

        }

        public function signMessage(Message &$message){

           require_once APP_LIBRARY.'/louisameline/php-mail-signature/mail-signature.class.php';
            $signature = new \mail_signature(
                $this->private_key,
                $this->passphrase,
                $this->domain,
                $this->selector,
                $this->options
            ); 
            
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // to
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            $data=array();        
            $message->getTo()->rewind();
            do {
                $email=$message->getTo()->current()->getEmail();
                $name=$message->getTo()->current()->getName(); 

                if ($name===null)
                    $data[]=$email;
                else
                    $data[]=$name.' <'.$email.'>';    

            } while ($message->getTo()->next()!==false);
            $to=implode(', ',$data);

            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // subject
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            $subject=$message->getSubject(); 

            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // message
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            $m=$message->getBodyText(); 
            $m = preg_replace('/(?<!\r)\n/', "\r\n", $m);

            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
            // headers
            //\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\            
            $headers=$message->getHeaders()->toString();
            $signed_headers = $signature -> get_signed_headers($to, $subject, $m, $headers);

            //ako DKIM ne prolazi usporediti $to sa email sourceom
            $signed_headers=str_replace (array("\r\n"), ' ', $signed_headers);
            $signed_headers=str_replace (array(chr(9)), '', $signed_headers);

              $header= new Header\GenericHeader('DKIM-Signature', ldel($signed_headers,16));
              //$header= new Header\GenericHeader();
              //$header->fromString($signed_headers);

            $headerSet[] =$header; 

            $headers = $message->getHeaders();
            foreach($headers as $header) {
                $headerSet[] = $header;
            }

            $message->getHeaders()->clearHeaders();
            $message->getHeaders()->addHeaders($headerSet);

        }


}