<?php
class Zend_View_Helper_BaseUrl 
{
//	function baseUrl() 
//	{
//		$fc = Zend_Controller_Front::getInstance(); 
//		return $fc->getBaseUrl(); 
//	}

    public function baseUrl($file = null)
    {
        return Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl()
                . ($file ? ('/' . trim((string) $file, '/\\')) : '');
    }
}
