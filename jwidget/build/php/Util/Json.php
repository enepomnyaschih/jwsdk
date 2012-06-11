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

class JWSDK_Util_Json
{
	public static function get( // *
		&$json,          // Object
		$path,           // Array of String
		$default = null) // *
	{
		$pointer = $json;
		foreach ($path as $key)
		{
			if (!isset($pointer[$key]))
				return $default;
			
			$pointer = $pointer[$key];
		}
		
		return $pointer;
	}
	
	public static function set(
		&$json, // Object
		$path,  // Array of String
		$value) // *
	{
		$pointer = &$json;
		for ($i = 0; $i < count($path) - 1; $i++)
		{
			$key = $path[$i];
			if (!isset($pointer[$key]))
				$pointer[$key] = array();
			
			$pointer = &$pointer[$key];
		}
		
		$pointer[$path[count($path) - 1]] = $value;
	}
	
	public static function cloneRecursive( // Object
		$json) // Object
	{
		if (!is_array($json))
			return $json;
		
		$result = array();
		foreach ($json as $key => $value)
			$result[$key] = self::cloneRecursive($json[$key]);
		
		return $result;
	}
}
