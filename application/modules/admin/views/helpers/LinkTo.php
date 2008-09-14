<?php 
class Zend_View_Helper_LinkTo 
{ 
    protected static $baseurl = null; 
  
    public function linkTo($path) 
    { 
        if (self::$baseurl === null) { 
            $root = '/' . 
                trim(Zend_Controller_Front::getInstance() 
                ->getBaseUrl(), '/'); 
            if ($root == '/') $root = ''; 
            self::$baseurl = $root . '/'; 
        } 
        
        return self::$baseurl . ltrim($path, '/'); 
    } 
}