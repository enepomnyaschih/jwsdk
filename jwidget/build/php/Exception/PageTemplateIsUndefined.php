<?php

class JWSDK_Exception_PageTemplateIsUndefined extends JWSDK_Exception
{
	private $name;
	
	public function __construct($name)
	{
		parent::__construct("Template is undefined for page '$name'");
		$this->name = $name;
	}
	
	public function getName()
	{
		return $this->name;
	}
}
