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

class JWSDK_Exception_InvalidResourceParameter extends JWSDK_Exception
{
	private $name;
	private $format;
	
	public function __construct($name, $format)
	{
		parent::__construct("Resource requires $format in $name parameter");
		$this->name = $name;
		$this->format = $format;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getFormat()
	{
		return $this->format;
	}
}
