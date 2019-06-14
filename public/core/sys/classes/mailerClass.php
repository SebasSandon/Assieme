<?php
/**
 * mailerClass
 *
 * @author SadSacrifice
 */
class mailerClass
{
    public $from;
    public $replyto;
    
    public function __construct($from, $replyto) {
        $this->from = $from;
        $this->replyto = $replyto;
    }
    
    public function sendMail($to, $subject, $message){
        $headers = "From: " . strip_tags($this->from) . "\r\n";
        $headers .= "Reply-To: ". strip_tags($this->replyto) . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        
        $result = mail($to, $subject, $message, $headers);
        return $result;
    }
}
