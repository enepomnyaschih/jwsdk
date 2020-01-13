<?php

/*
MIT License

Copyright (c) 2020 Egor Nepomnyaschih

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
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
