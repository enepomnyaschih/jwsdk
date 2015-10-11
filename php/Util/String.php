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
	const COMMENTS_CSS   = 0x03;
	const CONSIDER_REGEX = 0x04;
	const COMMENTS_JS    = 0x07;

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
		$flags = self::COMMENTS_CSS) // Integer
	{
		$text = self::smoothCrlf($text);

		$last  = 0;
		$index = 0;
		$buf   = '';
		$len   = strlen($text);
		$expectOperator = false;

		while ($index < $len)
		{
			$char = substr($text, $index, 1);
			$char2 = substr($text, $index, 2);
			if (($flags & self::COMMENTS_LINE) && ($char2 == '//'))
			{
				$buf .= substr($text, $last, $index - $last);
				$index = strpos($text, "\n", $index);
				if ($index === false)
					break;

				$last = $index;
			}
			else if (($flags & self::COMMENTS_BLOCK) && ($char2 == '/*'))
			{
				$buf .= substr($text, $last, $index - $last) . ' ';
				$index = strpos($text, '*/', $index);
				if ($index === false)
					break;

				$index += 2;
				$last = $index;
			}
			else if ($char === '"' || $char === "'" ||
					(($flags & self::CONSIDER_REGEX) && !$expectOperator && $char === '/'))
			{
				$next = self::findUnescaped($text, $char, $index + 1);
				$index = ($next !== false) ? ($next + 1) : $len;
				$expectOperator = true;
			}
			else
			{
				if (preg_match('~[+\-\*\/%=><\?\:&\|\~\^\{\(,;\.]~', $char)) {
					$expectOperator = false;
				} else if (!preg_match('~\s~', $char)) {
					$expectOperator = true;
				}
				$index++;
			}
		}

		if ($index !== false)
			$buf .= substr($text, $last);

		return $buf;
	}

	public static function findUnescaped($haystack, $needle, $index)
	{
		$len = strlen($haystack);
		while ($index < $len) {
			$char = substr($haystack, $index, 1);
			if ($char === $needle) {
				return $index;
			}
			if ($char === '\\') {
				$index += 2;
			} else {
				$index++;
			}
		}
		return false;
	}
}
