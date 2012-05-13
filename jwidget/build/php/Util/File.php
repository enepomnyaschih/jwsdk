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
	public static function read( // String or false
		$path,         // String
		$tip = 'file') // String
	{
		$result = @file_get_contents($path);
		if ($result === false)
			throw new JWSDK_Exception_CanNotReadFile($path, $tip);
		
		return $result;
	}
	
	public static function mtime( // Integer
		$path,         // String
		$tip = 'file') // String
	{
		if (!file_exists($path))
			throw new JWSDK_Exception_CanNotReadFile($path, $tip);
		
		return filemtime($path);
	}
	
	public static function fopen_recursive( // File or false
		$path,         // String
		$mode,         // String
		$chmod = 0755) // Integer
	{
		self::mkdir_recursive($path, $chmod);
		
		$file = @fopen($path, $mode);
		if ($file === false)
			throw new JWSDK_Exception_CanNotWriteFile($path);
		
		return $file;
	}
	
	public static function mkdir_recursive(
		$path,         // String
		$chmod = 0755) // Integer
	{
		$i = strrpos($path, '/');
		if ($i !== false)
		{
			$directory = substr($path, 0, $i);
			if (!is_dir($directory) && !mkdir($directory, $chmod, 1))
				throw new JWSDK_Exception_CanNotMakeDirectory($directory);
		}
	}
	
	public static function write(
		$path,     // String
		$contents) // String
	{
		$file = self::fopen_recursive($path, 'w');
		fwrite($file, $contents);
		fclose($file);
	}
	
	public static function compress(
		$source, // String
		$target) // String
	{
		$yuiOutput = array();
		$yuiStatus = 0;
		
		$command = "java -jar yuicompressor.jar $source -o $target --charset utf-8 --line-break 8000 2>> yui.log";
		exec($command, $yuiOutput, $yuiStatus);
		
		if ($yuiStatus != 0)
			throw new JWSDK_Exception_CompressorError($source, $target);
	}
}
