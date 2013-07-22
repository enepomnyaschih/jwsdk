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

class JWSDK_Resource_Converter_Internal extends JWSDK_Resource_Converter
{
	public function convert(
		$resource,   // JWSDK_Resource
		$sourceName, // String
		$sourcePath, // String
		$buildName,  // String
		$buildPath)  // String
	{
		$sourceContents = JWSDK_Util_File::read($sourcePath, 'resource file');
		$buildContents = $this->convertResource($resource->getName(), $sourceContents, $resource->getParams());
		JWSDK_Util_File::write($buildPath, $buildContents);
	}
	
	public function convertResource( // String, output contents
		$name,     // String
		$contents, // String
		$params)   // Object
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
}
