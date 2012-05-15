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

class JWSDK_Package_Auto extends JWSDK_Package
{
	private $name;           // String
	private $type;           // String
	private $sourceResource; // JWSDK_Resource
	
	public function __construct(
		$name) // String
	{
		$this->name = $name;
		$this->type = $this->getResourceType();
		$this->sourceResource = new JWSDK_Resource($name, $this->type, $this->type);
		$this->setCompressedResources(array(new JWSDK_Resource($this->getCompressedName(), $this->type, $this->type)));
	}
	
	public function getName() // String
	{
		return $this->name . '|auto';
	}
	
	public function getSourceResources() // Array of JWSDK_Resource
	{
		return array($this->sourceResource);
	}
	
	private function getResourceType() // String
	{
		return substr($this->name, strrpos($this->name, '.') + 1);
	}
	
	private function getCompressedName() // String
	{
		return substr($this->name, 0, strrpos($this->name, '.')) . '.min.' . $this->type;
	}
}
