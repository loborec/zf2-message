<?php

    namespace Loborec\Message;

    class Compose{
        
        public $from;
        public $to;
        public $cc;
        public $bcc;
        public $reply;
        public $subject;
        public $text;
        public $html;
        public $html2;
        public $attachments=null;

        public $sign=true;
        public $private_key;
        public $passphrase;
        public $domain;
        public $selector;

        public function getMessage(){

            if (isset($this->html2)){
                $this->html=$this->html2;
                $this->text=\Html2Text\Html2Text::convert($this->html2);
            }

            // Create a base message:
            $message = new \Zend\Mail\Message();

            foreach ($this->from as $record){
                if (is_array($record)){
                    $message->addFrom($record[0], $record[1]);
                } 
                else
                {
                    $message->addFrom($record);
                }  
            }

            foreach ($this->to as $record){
                if (is_array($record)){
                    $message->addTo($record[0], $record[1]);
                } 
                else
                {
                    $message->addTo($record);
                }  
            }

            if (isset($this->cc)){
                foreach ($this->cc as $record){
                    if (is_array($record)){
                        $message->addCc($record[0], $record[1]);
                    } 
                    else
                    {
                        $message->addCc($record);
                    }  
                }
            }

            if (isset($this->bcc)){
                foreach ($this->bcc as $record){
                    if (is_array($record)){
                        $message->addBcc($record[0], $record[1]);
                    } 
                    else
                    {
                        $message->addBcc($record);
                    }  
                }
            }

            if (isset($this->reply)){
                foreach ($this->reply as $record){

                    if (is_array($record)){
                        $message->addReplyTo($record[0], $record[1]);
                    } 
                    else
                    {
                        $message->addReplyTo($record);
                    }  
                }
            }

            $message->setSubject($this->subject);

            if ($this->attachments===null){

                if (isset($this->text) and (! isset($this->html))){
                    $message->setBody($this->text);
                }
                else if (! isset($this->text) and (isset($this->html))){

                    $html = new \Zend\Mime\Part($this->html);
                    $html->type = \Zend\Mime\Mime::TYPE_HTML;
                    $html->charset = 'utf-8';
                    $html->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;

                    $body = new \Zend\Mime\Message();
                    $body->addPart($html);
                    $message->setBody($body);

                }
                else 
                {
                    //multipart/alternative
                    $body = new \Zend\Mime\Message();

                    $text = new \Zend\Mime\Part($this->text);
                    $text->type = \Zend\Mime\Mime::TYPE_TEXT;
                    $text->charset = 'utf-8';
                    $text->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $body->addPart($text);

                    $html = new \Zend\Mime\Part($this->html);
                    $html->type = \Zend\Mime\Mime::TYPE_HTML;
                    $html->charset = 'utf-8';
                    $html->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $body->addPart($html);

                    $message->setBody($body);
                    $contentTypeHeader = $message->getHeaders()->get('Content-Type');
                    $contentTypeHeader->setType('multipart/alternative');
                } 


            }
            else
            {

                $body = new \Zend\Mime\Message(); 

                if (isset($this->text) and (! isset($this->html))){
                    $text = new \Zend\Mime\Part($this->text);
                    $text->type = \Zend\Mime\Mime::TYPE_TEXT;
                    $text->charset = 'utf-8';
                    $text->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $body->addPart($text);

                    $ct='multipart/mixed';
                }
                else if (! isset($this->text) and (isset($this->html))){
                    $html = new \Zend\Mime\Part($this->html);
                    $html->type = \Zend\Mime\Mime::TYPE_HTML;
                    $html->charset = 'utf-8';
                    $html->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $body->addPart($html);

                    $ct='multipart/mixed';
                }
                else
                {
                    $content = new \Zend\Mime\Message();

                    $text = new \Zend\Mime\Part($this->text);
                    $text->type = \Zend\Mime\Mime::TYPE_TEXT;
                    $text->charset = 'utf-8';
                    $text->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $content->addPart($text);

                    $html = new \Zend\Mime\Part($this->html);
                    $html->type = \Zend\Mime\Mime::TYPE_HTML;
                    $html->charset = 'utf-8';
                    $html->encoding = \Zend\Mime\Mime::ENCODING_QUOTEDPRINTABLE;
                    $content->addPart($html);

                    $contentPart = new \Zend\Mime\Part($content->generateMessage());
                    $contentPart->type = "multipart/alternative;\n boundary=\"" .$content->getMime()->boundary() . '"';
                    $body->addPart($contentPart);

                    $ct='multipart/related'; 
                }

                foreach ($this->attachments as $attachment) {
                    $part = new \Zend\Mime\Part($attachment['content']);
                    $part->filename    = $attachment['file_name'];
                    $part->type        = $attachment['mime'];
                    $part->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
                    $part->encoding    = \Zend\Mime\Mime::ENCODING_BASE64;

                    $body->addPart($part);
                } 
                $message->setBody($body);
                $contentTypeHeader = $message->getHeaders()->get('Content-Type');
                $contentTypeHeader->setType($ct);
            }

            //Sign message
            if ($this->sign){
                $signer = new Signer($this->private_key , $this->passphrase, $this->domain, $this->selector);
                $signer->signMessage($message);
            }

            return $message;
        }

}