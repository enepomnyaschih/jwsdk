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

class JWSDK_Mode
{
	private static $modes = array(); // Map from id:String to JWSDK_Mode
	
	public function getId() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function getConfigId() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function isCompress() // Boolean
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public function getDescription() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	public static function registerMode(
		$mode) // JWSDK_Mode
	{
		self::$modes[$mode->getId()] = $mode;
	}
	
	public static function getMode( // JWSDK_Mode
		$id) // String
	{
		if (!isset(self::$modes[$id]))
			return null;
		
		return self::$modes[$id];
	}
	
	public static function getModesDescription() // String
	{
		$buf = array();
		
		foreach (self::$modes as $id => $mode)
		{
			$buf[] = "    $id";
			$buf[] = JWSDK_Util_String::tabulize($mode->getDescription(), 2);
		}
		
		return implode("\n", $buf);
	}
}
