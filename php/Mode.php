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
