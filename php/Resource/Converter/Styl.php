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

class JWSDK_Resource_Converter_Styl extends JWSDK_Resource_Converter
{
	public function getType() // String
	{
		return 'styl';
	}
	
	public function getAttacher() // String
	{
		return 'css';
	}
	
	public function convert(
		$resource,   // JWSDK_Resource
		$sourceName, // String
		$sourcePath, // String
		$buildName,  // String
		$buildPath)  // String
	{
		$tempPath = preg_replace('~\.css$~', '.temp.css', $buildPath);
		
		$lessOutput = array();
		$lessStatus = 0;
		
		JWSDK_Util_File::mkdir($buildPath);
		$command = "stylus < $sourcePath > $tempPath 2>> stylus.log";
		exec($command, $lessOutput, $lessStatus);
		
		if ($lessStatus != 0)
			throw new JWSDK_Exception_StylusError($sourcePath, $tempPath);
		
		$sourceContents = JWSDK_Util_File::read($tempPath, 'temp file');
		$buildContents = JWSDK_Util_Css::updateRelativePaths($sourceContents, $sourceName, $buildName);
		JWSDK_Util_File::write($buildPath, $buildContents);
	}
}
