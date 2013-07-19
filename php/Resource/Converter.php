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

class JWSDK_Resource_Converter
{
	public function getType() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function isConvertion() // Boolean
	{
		return true;
	}
	
	public function getAttacher() // String
	{
		return 'js';
	}
	
	public function convert(
		$resource,   // JWSDK_Resource
		$sourcePath, // String
		$buildPath)  // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function getParamsByArray( // Array
		$params) // Array
	{
		return array();
	}
	
	public function getParamsByJson( // Array
		$json) // Object
	{
		return $json;
	}
}
