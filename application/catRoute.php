<?php
class MyApp_CatRoute implements Zend_Controller_Router_Route_Interface
{
	public $defaults = array();
        public $name = null;
 
        //this class sets the defaults and the name of the controller
        public function __construct($name, $default = array())
        {
            	if(!empty($default)){
            		$this->defaults = $default;
            	} else {
            		$this->defaults = array(
                        'controller' => 'category',
                        'action' => 'index'
                    );
            	}
                $this->name = $name;
        }
 
        public function match($path)
        {
            //splits the URL into an array
            if (preg_match_all('#/([^/]*)#', $path, $matches)) {
                //gets the array from the preg_match
                $segments = $matches[1];
                /*pops off the first element and checks if 
                 *this is the right router, if not, return   
                 *false, Zend framework will continue looking 
                 *for the right router
                 */
                //$category = array_shift($segments);
                $category = $segments[0];
                
                if ($category != $this->defaults['controller']){
                	return false;
                }
                //creates an array, the array with all the categories are indexed by 'cats'
                $return = array(
//                    'category' => $category,
					'category' => 'category',
                    'cats' => $segments 
                );
                //merges the array above with the defaults and returns it.
                $return = array_merge($return,$this->defaults);
                return $return;
            }
            return false;
        }
 
        public function assemble($data = array())
        {
            return $route;
        }
 
        public static function getInstance(Zend_Config $config){
            $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
            return new self($config->route, $defs);
        }
}
?>