<?php
/**
 * notificationClass
 *
 * @author SadSacrifice
 */
class notificationClass
{
    public $type;
    public $code;
    public $message;
    
    public function __construct($type, $message) {
        $this->type = $type;
        $this->message = $message;
    }
    
    public function setCode($code){
        $this->code = $code;
    }
}
