<?php

/*
	jWidget SDK source file.
	
	Copyright (C) 2012 Egor Nepomnyaschih
	
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

class JWSDK_Variables
{
	private $vars; // Map from String to Map from String to *
	
	public function __construct(
		$base = null, // JWSDK_Variables
		$vars = null) // Map from String to Map from String to *
	{
		$this->vars = array(
			'services' => array(),
			'custom'   => array()
		);
		
		if ($base !== null)
			$this->apply($base->getVars());
		
		$this->apply($vars);
	}
	
	public function getVars() // Map from String to Map from String to *
	{
		return $this->vars;
	}
	
	public function getServices() // Array of String
	{
		$result = array();
		$services = $this->vars['services'];
		foreach ($services as $key => $value)
		{
			if ($value)
				$result[] = $key;
		}
		
		return $result;
	}
	
	public function getCustom() // Map from String to String
	{
		return $this->vars['custom'];
	}
	
	public function apply(
		$vars) // Map from String to Map from String to *
	{
		if ($vars === null)
			return;
		
		$this->applyVar($vars, 'services');
		$this->applyVar($vars, 'custom');
	}
	
	public function applyConfig(
		$path) // String
	{
		$contents = JWSDK_Util_File::file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open mode config (path: $path)");
		
		$config = json_decode($contents, true);
		$this->apply($config);
	}
	
	private function applyVar(
		$vars, // Map from String to Map from String to *
		$name) // String
	{
		if (!isset($vars[$name]))
			return;
		
		$target = &$this->vars[$name];
		$source = $vars[$name];
		
		foreach ($source as $key => $value)
			$target[$key] = $value;
	}
}
