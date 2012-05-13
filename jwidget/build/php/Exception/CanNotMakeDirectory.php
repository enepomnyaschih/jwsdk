<?php

// Runtime
class JWSDK_Exception_CanNotMakeDirectory extends JWSDK_Exception
{
	private $path;
	
	public function __construct($path)
	{
		parent::__construct("Can't make directory '$path'");
		$this->path = $path;
	}
	
	public function getPath()
	{
		return $this->path;
	}
}
