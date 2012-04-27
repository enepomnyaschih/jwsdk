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
	private $vars;
	
	public function __construct($base = null, $vars = null)
	{
		$this->vars = array(
			'services' => array(),
			'custom'   => array()
		);
		
		if ($base !== null)
			$this->apply($base->getVars());
		
		$this->apply($vars);
	}
	
	public function getVars()
	{
		return $this->vars;
	}
	
	public function getServices()
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
	
	public function getCustom()
	{
		return $this->vars['custom'];
	}
	
	public function apply($vars)
	{
		if ($vars === null)
			return;
		
		$this->applyVar($vars, 'services');
		$this->applyVar($vars, 'custom');
	}
	
	private function applyVar($vars, $name)
	{
		if (!isset($vars[$name]))
			return;
		
		$target = &$this->vars[$name];
		$source = $vars[$name];
		
		foreach ($source as $key => $value)
			$target[$key] = $value;
	}
}
