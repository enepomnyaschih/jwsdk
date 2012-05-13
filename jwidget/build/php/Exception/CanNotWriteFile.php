<?php

// Runtime
class JWSDK_Exception_CanNotWriteFile extends JWSDK_Exception
{
	private $path;
	
	public function __construct($path)
	{
		parent::__construct("Can't write file '$path'");
		$this->path = $path;
	}
	
	public function getPath()
	{
		return $this->path;
	}
}
