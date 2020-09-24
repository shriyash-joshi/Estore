<?php

class EmailMessage{
    
    private $accessKey = '8mICk1M75BTfaQzP';
    
    private $to;
    private $subject;
    private $from;
    private $html;
    private $text;
    private $cc;
    private $bcc;
    private $replyto;
    private $attachments = [];
    private $headers = [];
    private $inline_images = [];
    
    private function __construct($accessKey) {
        if($accessKey) $this->accessKey = $accessKey;
    }
    
    public static function getInstance($accessKey = null){
        return new EmailMessage($accessKey);
    }
    
    public function send(){
        
        if(!$this->from) $this->from = ['noreply@univariety.com', 'Univariety'];
        if(!$this->replyto) $this->replyto = ['noreply@univariety.com', 'Univariety'];
        
        $data = ['to' => $this->to, 'from' => $this->from, 'subject' => $this->subject, 'html' => $this->html];
        if($this->text) $data['text'] = $this->text;
        if($this->cc) $data['cc'] = $this->cc;
        if($this->bcc) $data['bcc'] = $this->bcc;
        if($this->replyto) $data['replyto'] = $this->replyto;
        if($this->attachments) $data['attachment'] = $this->attachments;
        if($this->headers) $data['headers'] = $this->headers;
        if($this->inline_images) $data['inline_image'] = $this->inline_images;
        
        $data['headers']['X-Mailin-Tag'] = 'Shop-Counsellor-Dashboard';
        $mailIn = new Mailin("https://api.sendinblue.com/v2.0", $this->accessKey);
        return $mailIn->send_email($data);
    }
    
    public function setInlineImages(array $inlineImages){
        $this->inline_images = $inlineImages;
        return $this;
    }
    
    public function addInlineImage($fileName, $base64EncodedChunkData){
        $this->inline_images[$fileName] = $base64EncodedChunkData;
        return $this;
    }
    
    public function setHeaders(array $headers){
        $this->headers = $headers;
        return $this;
    }
    
    public function addHeader($key, $value){
        $this->headers[$key] = $value;
        return $this;
    }
    
    public function addAttachmentOnFly($fileName, $base64EncodedChunkData){
        $this->attachments[$fileName] = $base64EncodedChunkData;
        return $this;
    }
    
    public function setAttachments(array $attachments){
        $this->attachments = $attachments;
        return $this;
    }
    
    public function addAttachmentUrl($fileUrl){
        array_push($this->attachments, $fileUrl);
        return $this;
    }
    
    public function setReplyto($email, $name){
        $this->replyto = [$email, $name];
        return $this;
    }
    
    public function setBcc($email, $name){
        $this->bcc = [$email => $name];
        return $this;
    }
    
    public function setCc($email, $name){
        $this->cc = [$email => $name];
        return $this;
    }
    
    public function setText($text){
        $this->text = $text;
        return $this;
    }
    
    public function setHtml($html){
        $this->html = $html;
        return $this;
    }
    
    public function setFrom($email, $name){
        $this->from = [$email, $name];
        return $this;
    }
    
    public function setSubject($subject){
        $this->subject = $subject;
        return $this;
    }

    public function setTo($email, $name){
        $this->to = [$email => $name];
        return $this;
    }
    
}