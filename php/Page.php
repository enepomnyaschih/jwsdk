<?php

/*
MIT License

Copyright (c) 2020 Egor Nepomnyaschih

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

class JWSDK_Page
{
	private $name;       // String
	private $template;   // String
	private $package;    // String
	private $outputName; // String
	private $params;     // Map from String to String
	
	public function __construct(
		$name, // String
		$json) // Object
	{
		$this->name = $name;
		
		if (isset($json['template']) && is_string($json['template']))
			$this->template = $json['template'];
		
		if (isset($json['package']) && is_string($json['package']))
			$this->package = $json['package'];
		
		if (isset($json['outputName']) && is_string($json['outputName']))
			$this->outputName = $json['outputName'];
		else
			$this->outputName = "$name.html";
		
		$this->params = $this->filterParams($json);
	}
	
	public function getName() // String
	{
		return $this->name;
	}
	
	public function getTemplate() // String
	{
		return $this->template;
	}
	
	public function getPackage() // String
	{
		return $this->package;
	}
	
	public function getOutputName() // String
	{
		return $this->outputName;
	}
	
	public function getParams() // Map from String to String
	{
		return $this->params;
	}
	
	private function filterParams( // Map from String to String
		$json) // Map from String to *
	{
		$result = array();
		foreach ($json as $key => $value)
		{
			if (is_string($value))
				$result[$key] = $value;
		}
		
		return $result;
	}
}
