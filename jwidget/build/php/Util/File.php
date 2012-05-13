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

class JWSDK_Util_File
{
	public static function fopen_recursive( // File
		$path,         // String
		$mode,         // String
		$chmod = 0755) // Integer
	{
		if (!self::mkdir_recursive($path, $chmod))
			return false;
		
		return @fopen($path, $mode);
	}
	
	public static function mkdir_recursive( // Boolean
		$path,         // String
		$chmod = 0755) // Integer
	{
		$i = strrpos($path, '/');
		if ($i !== false)
		{
			$directory = substr($path, 0, $i);
			if (!is_dir($directory) && !mkdir($directory, $chmod, 1))
				return false;
		}
		
		return true;
	}
	
	public static function write( // Boolean
		$path,     // String
		$contents) // String
	{
		$file = self::fopen_recursive($path, 'w');
		if ($file === false)
			return false;
		
		fwrite($file, $contents);
		fclose($file);
		
		return true;
	}
	
	public static function compress( // Boolean
		$source, // String
		$target) // String
	{
		$yuiOutput = array();
		$yuiStatus = 0;
		
		$command = "java -jar yuicompressor.jar $source -o $target --charset utf-8 --line-break 8000 2>> yui.log";
		exec($command, $yuiOutput, $yuiStatus);
		
		return $yuiStatus == 0;
	}
}
