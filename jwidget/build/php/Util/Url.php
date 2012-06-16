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
}
