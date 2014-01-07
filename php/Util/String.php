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

class JWSDK_Util_String
{
	const COMMENTS_LINE  = 0x01;
	const COMMENTS_BLOCK = 0x02;
	const COMMENTS_ALL   = 0x03;
	
	public static function repeat( // String
		$str, // String
		$num) // Integer
	{
		$buf = array();
		for ($i = 0; $i < $num; $i++)
			$buf[] = $str;
		
		return implode('', $buf);
	}
	
	public static function tabulize( // String
		$str,          // String
		$num,          // Integer
		$tab = '    ') // String
	{
		$lines = explode("\n", $str);
		for ($i = 0; $i < count($lines); $i++)
			$lines[$i] = JWSDK_Util_String::repeat($tab, $num) . $lines[$i];
		
		return implode("\n", $lines);
	}
	
	public static function smoothCrlf( // String
		$contents) // String
	{
		$contents = str_replace("\r\n", "\n", $contents);
		$contents = str_replace("\r", "\n", $contents);
		return $contents;
	}
	
	public static function removeComments( // String
		$text,                       // String
		$flags = self::COMMENTS_ALL) // Integer
	{
		$text = self::smoothCrlf($text);
		
		$last  = 0;
		$index = 0;
		$buf   = array();
		
		while ($index < strlen($text))
		{
			if (($flags & self::COMMENTS_LINE) && (substr($text, $index, 2) == '//'))
			{
				$buf[] = substr($text, $last, $index - $last);
				$index = strpos($text, "\n", $index);
				if ($index === false)
					break;
				
				$last = $index;
			}
			else if (($flags & self::COMMENTS_BLOCK) && (substr($text, $index, 2) == '/*'))
			{
				$buf[] = substr($text, $last, $index - $last);
				$index = strpos($text, '*/', $index);
				if ($index === false)
					break;
				
				$index += 2;
				$last = $index;
			}
			else
			{
				$index++;
			}
		}
		
		if ($index !== false)
			$buf[] = substr($text, $last);
		
		return implode('', $buf);
	}
}
