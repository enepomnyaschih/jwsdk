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

class JWSDK_Resource
{
	private $name;
	private $type;
	private $params;
	
	public function __construct($name, $type, $params = array())
	{
		$this->name   = $name;
		$this->type   = $type;
		$this->params = $params;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getParams()
	{
		return $this->params;
	}
	
	public static function fromString($str)
	{
		$tokens = explode(":", $str);
		$name = trim($tokens[0]);
		$type = JWSDK_Converter::getResourceType($name);
		if (!$type)
			throw new Exception("Unknown resource type (name: $name)");
		
		if (count($tokens) == 1)
		{
			$params = array();
		}
		else
		{
			$params = explode(",", $tokens[1]);
			for ($i = 0; $i < count($params); $i++)
				$params[$i] = trim($params[$i]);
		}
		
		return new JWSDK_Resource($name, $type, $params);
	}
}
