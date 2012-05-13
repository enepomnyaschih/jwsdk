<?php

class JWSDK_Exception extends Exception
{
	private $cause;
	
	public function __construct($message = '', $cause = null)
	{
		parent::__construct($message, 0);
		$this->cause = $cause;
	}
	
	public function getCause()
	{
		return $this->cause;
	}
	
	public function __toString()
	{
		$cause = $this->getCause();
		return "JWSDK_Exception:\n" . parent::__toString() .
			(isset($cause) ? ("\nCaused By:\n" . $cause->__toString()) : '');
	}
}
