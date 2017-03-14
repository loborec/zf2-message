zf2-message
===========

A very simple PHP helper class to compose your e-mail messages with zend-mail and sign with DKIM. 

This class is based on the work:
https://github.com/zendframework/zend-mail

https://github.com/louisameline/php-mail-signature

https://github.com/soundasleep/html2text

## Installing

You can use [Composer](http://getcomposer.org/) to add the [package](https://packagist.org/packages/loborec/zf2-message) to your project:

```json
{
  "require": {
    "loborec/zf2-message": "dev-master"
  }
}
```

## Examples

```php
$compose=new Loborec\Message\Compose();

//always use array
$compose->from=['yourname@acme.com']; 

//for e-mail with name write:
//$compose->from=[['yourname@acme.com', 'Rogger Rabbit']]; 

$compose->to=[['donald@acme.com', 'Donald Duck']];

$compose->subject='Test';

$compose->text='Today is a nice day';

//you can add html part, and MIME type will be automatically changed to 'multipart/alternative':
//$compose->html='<b>Today is a nice day'</b>';

//you can also use only html part

//if you use htm2 like $compose->html2='<b>Today is a nice day'</b>'; then text part will be automatically converted from html part

//you can add array of attachments, and MIME type will be automatically changed to 'multipart/related'
/*
$compose->attachments=[[
  'file_name'=>'Untitled.pdf',
  'mime'=>'application/pdf',
  'content'=> 'attachment data...' ,
 ]];
*/

//for signing provide additional params
            $compose->private_key=params::zf2_message['private_key'];
            $compose->passphrase=params::zf2_message['passphrase'];
            $compose->domain=params::zf2_message['domain'];
            $compose->selector=params::zf2_message['selector'];
            

```
