<?php

class JWSDK_Exception_CanNotOpenGlobalConfig extends JWSDK_Exception
{
	private $path;
	
	public function __construct($path)
	{
		parent::__construct("Can't open main config '$path'");
	}
}
