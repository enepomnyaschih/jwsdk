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
	private $name;     // String
	private $type;     // String
	private $attacher; // String
	private $params;   // Array of String
	
	public function __construct(
		$name,             // String
		$type = 'js',      // String
		$attacher = 'js',  // String
		$params = array()) // Array of String
	{
		$this->name = $name;
		$this->type = $type;
		$this->attacher = $attacher;
		$this->params = $params;
	}
	
	public function getName() // String
	{
		return $this->name;
	}
	
	public function getType() // String
	{
		return $this->type;
	}
	
	public function getAttacher() // String
	{
		return $this->attacher;
	}
	
	public function getParams() // Array of String
	{
		return $this->params;
	}
}
