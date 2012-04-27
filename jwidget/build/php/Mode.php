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

class JWSDK_Mode
{
	private static $modes = array();
	
	public function getId()
	{
		throw new Exception('Method not implemented');
	}
	
	public function getConfigId()
	{
		throw new Exception('Method not implemented');
	}
	
	public function isCompress()
	{
		throw new Exception('Method not implemented');
	}
	
	public function isLink()
	{
		throw new Exception('Method not implemented');
	}
	
	public function isLinkMin()
	{
		throw new Exception('Method not implemented');
	}
	
	public function getDescription()
	{
		throw new Exception('Method not implemented');
	}
	
	public static function registerMode($mode)
	{
		self::$modes[$mode->getId()] = $mode;
	}
	
	public static function getMode($id)
	{
		if (!isset(self::$modes[$id]))
			return null;
		
		return self::$modes[$id];
	}
	
	public static function getModesDescription()
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
