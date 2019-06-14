<?php
/**
 * csrfToken
 *
 * @author SadSacrifice
 */
class csrfTokenClass
{
    private $tokens = array();
            
    /**
     * @var string
     */    
    protected $hashAlgorithm = 'sha256';
    
    /**
     * @var string
     */ 
    private $secret;
    
    /**
     * @var string
     */ 
    private $server;
    
    /**
     * 
     * @param string $secret
     */
    function __construct($secret){
        $this->secret = $secret.''.openssl_random_pseudo_bytes(32);
        $this->server = $_SERVER['SERVER_ADDR'];
    }
    
    /**
     * 
     * @param type $data
     * @param type $secret
     * @return type
     */
    private function generateToken($data){
        return hash_hmac($this->hashAlgorithm, $data, $this->secret);
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public function addToken($data){
        $token = $this->generateToken($data);
        $this->tokens[$token] = [
            'creation' => time(),
            'expires' => time() + (60 * 60)
        ];
        return $token;
    }
    
    /**
     * 
     * @param type $request
     * @param type $token
     * @return boolean
     */
    public function validateToken($request, $token){
        $known = hash_hmac($this->hashAlgorithm, $request, $this->secret);
        if(hash_equals($known, $token) && array_key_exists($token, $this->tokens)){
            if($this->tokens[$token]['expires'] > time()){
                unset($this->tokens[$token]);
                return true;
            }
        }
        
        return false;
    }
}
