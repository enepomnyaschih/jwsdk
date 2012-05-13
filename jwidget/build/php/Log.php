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

class JWSDK_Log
{
	private $path; // String
	private $file; // File
	
	private static $logs = array(); // Map from path:String to JWSDK_Log
	
	public function __construct(
		$path) // String
	{
		$this->path = $path;
		$this->file = fopen($path, 'a');
		if ($this->file === false)
			throw new Exception("Can't open log file '$path'");
	}
	
	public function __destruct()
	{
		@fclose($this->file);
	}
	
	public function log(
		$msg) // String
	{
		$msg = $msg . "\n";
		echo $msg;
		fwrite($this->file, $msg);
	}
	
	public static function getLog( // JWSDK_Log
		$path) // String
	{
		if (!isset(self::$logs[$path]))
			self::$logs[$path] = new JWSDK_Log($path);
		
		return self::$logs[$path];
	}
	
	public static function logTo(
		$path, // String
		$msg)  // String
	{
		self::getLog($path)->log($msg);
	}
}
