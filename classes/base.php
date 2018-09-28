<?php
abstract class Base
{
	protected $_id;
	protected $_rev;
	protected $type;
	public function __construct($type)
	{
		$this->type = $type;
	}
	
	public function __get($property)
	{
		return $this->$property;
	}
	
	public function __set($property, $value)
	{
		$this->$property = $value;
	}
	
	public function to_json()
	{
		if(isset($this->_rev) === false){
		
			unset($this->_rev);
		}
	
		return json_encode(get_object_vars($this));
	}
}