<?php

// Runtime
class JWSDK_Exception_CompressorError extends JWSDK_Exception
{
	private $source;
	private $target;
	
	public function __construct($source, $target)
	{
		parent::__construct("Error occured while running YUI Compressor (input: $source, output: $target). See yui.log for details");
		$this->source = $source;
		$this->target = $target;
	}
	
	public function getSource()
	{
		return $this->source;
	}
	
	public function getTarget()
	{
		return $this->target;
	}
}
