<?php
ini_set('display_errors', 'On');
error_reporting(E_ERROR | E_PARSE);
define('ROOT',__DIR__ . '/..' );

define('COUCH_SERVER', 'SERVER_URL_HERE');
define('COUCH_PORT','5984');

require_once ROOT . '/lib/sag/src/Sag.php';
require_once ROOT . '/lib/bootstrap.php';

function get($route, $callback) {
	Bones::register($route, $callback, 'GET');
}
function post($route, $callback) {
	Bones::register($route, $callback, 'POST');
}

function put($route, $callback) {
	Bones::register($route, $callback, 'PUT');
}

function delete($route, $callback) {
	Bones::register($route, $callback, 'DELETE');
}

function resolve()
{
	Bones::resolve();
}


class Bones {
	private static $instance;
	public static $route_found = false;
	public $route = '';
	public $method = '';
	public $content = '';
	public $vars = array();
	public $route_segments = array();
	public $route_variables = array();
	public $couch;
	public $count;
	
	public function request($key){
		return $this->route_variables[$key];
	}
	
	public function form($key) {
		return $_POST[$key];
	}

	public function make_route($path = '') {
	
		$url = explode("/", $_SERVER['PHP_SELF']);
		if($url[1] == "index.php") {
			return $path;
		} else {
			return '/' . $url[1] . $path;
		}
	}

	public static function get_instance() {
		if(!isset(self::$instance)) 
		{
		
			self::$instance = new Bones();
		}
		return self::$instance;
	}

	public function __construct()
    {
		$this->route = $this->get_route();
		$this->route_segments = explode('/', trim($this->route,'/'));
		$this->method = $this->get_method();

		$this->couch = new Sag(COUCH_SERVER, '5984');
		$this->couch->setDatabase(ESM_STORE);
	}

    public function getDatabaseBaseURL()
    {
        return 'http://' . COUCH_SERVER . ':' . COUCH_PORT . '/' . ESM_STORE . '/';
    }

	protected function get_route() {

		parse_str($_SERVER['QUERY_STRING'], $route);
		if($route)
		{

			return '/' . $route['request'];
		}
		else
		{
			return '/';
		}
	}
	
	protected function get_method() {
	
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	public function set($index, $value)
	{
		$this->vars[$index] = $value;
	}
	
	public function render($view, $layout = "layout")
	{
		$this->content = ROOT. '/views/' . $view . '.php';
		
		foreach($this->vars as $key => $value)
		{
			$$key = $value;
		}
		
		if(!$layout)
		{
			include($this->content);
		} else 
		{
			include(ROOT. '/views/' . $layout . '.php');
		}
	}
	
	public static function register($route,$callback,$method) {
			
		if(!static::$route_found)
        {
			$bones = static::get_instance();
			$url_parts = explode('/', trim($route,'/'));
			$matched = null;
            $segmentCount = count($url_parts);
            $validMatchCount = 0;
			
			if(count($bones->route_segments) == count($url_parts))
            {
				foreach($url_parts as $key=>$part)
				{
					if(strpos($part, ":") !== false)
					{
						$bones->route_variables[substr($part,1)] =
						$bones->route_segments[$key];
					} else {
					
						if($part == $bones->route_segments[$key])
                        {
							if(!$matched)
							{
								$matched = true;

							}
                            $validMatchCount++;
						} else {
							$matched = false;
						}
					}
				}
			} else {
				$matched = false;
			}

			if(!$matched || $bones->method != $method ||  $validMatchCount != $segmentCount) {
				return false;
			}
			else {
				static::$route_found = true;
				echo $callback($bones);
			}
		}
	}
	
	
	public function display_alert($variable = 'error')
	{
		if(isset($this->vars[$variable]))
		{
			return "<div style='margin:0px' class='alert alert-" . $variable . "'><a class='close' data-dismiss='alert'>x</a>" . $this->vars[$variable] . "</div>";
		}
	}
	
	public function redirect($path = '/')
	{
		header('Location: ' . $this->make_route($path));
	}
	
	public function error500($exception)
	{
		$this->set('exception', $exception);
		$this->render('errors/500');
		exit;
	}
	
	public function error404($exception)
	{
		$this->render('errors/404');
		exit;
	}
	
	public static function resolve()
	{
		if(!static::$route_found)
		{
			$bones = static::get_instance();
			$bones->error404();
		}
	}
}

?>
