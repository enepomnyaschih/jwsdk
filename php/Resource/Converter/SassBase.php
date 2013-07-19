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

class JWSDK_Resource_Converter_SassBase extends JWSDK_Resource_Converter
{
	public function getAttacher() // String
	{
		return 'css';
	}
	
	public function convert(
		$resource,   // JWSDK_Resource
		$sourcePath, // String
		$buildPath)  // String
	{
		$sassOutput = array();
		$sassStatus = 0;
		
		JWSDK_Util_File::mkdir($buildPath);
		$command = "sass --unix-newlines -f $sourcePath $buildPath 2>> sass.log";
		exec($command, $sassOutput, $sassStatus);
		
		if ($sassStatus != 0)
			throw new JWSDK_Exception_SassError($sourcePath, $buildPath);
	}
}
