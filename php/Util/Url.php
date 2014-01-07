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

class JWSDK_Util_Url
{
	public static function normalizeRelative( // String
		$url) // String
	{
		$tokens = explode('/', $url);
		$result = array();
		
		foreach ($tokens as $token)
		{
			if ($token == '.')
				continue;
			
			if (($token == '..') && (count($result) != 0) && ($result[count($result) - 1] != '..'))
				array_pop($result);
			else
				$result[] = $token;
		}
		
		return implode('/', $result);
	}
	
	public static function extractName( // String
		$url) // String
	{
		$length = strlen($url);
		$index1 = strpos($url, '?');
		$index1 = ($index1 === false) ? $length : $index1;
		$index2 = strpos($url, '#');
		$index2 = ($index2 === false) ? $length : $index2;
		return substr($url, 0, min($index1, $index2));
	}
	
	public static function isDataUrl( // Boolean
		$url) // String
	{
		return preg_match('~^data:~', $url);
	}
	
	public static function isDomesticUrl( // Boolean
		$url) // String
	{
		return !self::isDataUrl($url) && !preg_match('~^\w+://~', $url);
	}
	
	public static function isRelativeUrl( // Boolean
		$url) // String
	{
		return self::isDomesticUrl($url) && ($url[0] != '/');
	}
	
	public static function addQueryParam( // String
		$url,   // String
		$name,  // String
		$value) // String
	{
		$separator = (strpos($url, '?') === false) ? '?' : '&';
		$index = strpos($url, '#');
		$index = ($index === false) ? strlen($url) : $index;
		return substr($url, 0, $index) . "$separator$name=$value" . substr($url, $index);
	}
}
