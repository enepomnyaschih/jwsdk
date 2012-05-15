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

class JWSDK_Package
{
	private $compressedResources; // Array of JWSDK_Resource
	
	public function getName() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function getSourceResources() // Array of JWSDK_Resource
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function getCompressedResources() // Array of JWSDK_Resource
	{
		return $this->compressedResources;
	}
	
	public function setCompressedResources(
		$value) // Array of JWSDK_Resource
	{
		$this->compressedResources = $value;
	}
	
	public function getRequires() // Array of String, package names
	{
		return array();
	}
}
