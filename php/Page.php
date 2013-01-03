<?php

/*
	jWidget SDK source file.
	
	Copyright (C) 2013 Egor Nepomnyaschih
	
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.
	
	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
