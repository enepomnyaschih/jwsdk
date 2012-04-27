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

function removeEmptyStrings($source)
{
	$result = array();
	foreach ($source as $value)
	{
		$row = trim($value);
		if (empty($row))
			continue;
		
		$result[] = $row;
	}
	
	return $result;
}

function defineJsVar($varName)
{
	return (strpos($varName, ".") === false) ? "var $varName" : $varName;
}

function smoothHtml($contents)
{
	$contents = trim($contents);
	$contents = preg_replace('/>\ *\n\s*</', '><', $contents);
	$contents = preg_replace('/\ *\n\s*/', ' ', $contents);
	$contents = str_replace ("'", "\\'", $contents);
	
	return $contents;
}

function smoothText($contents)
{
	return str_replace(array("\n", "\r", "\t", "'"), array("\\n\\\n", "\\r", "\\t", "\\'"), $contents);
}

function smoothCrlf($contents)
{
	$contents = str_replace("\r\n", "\n", $contents);
	$contents = str_replace("\r", "\n", $contents);
	return $contents;
}

function removeComments($text)
{
	$text = smoothCrlf($text);
	
	$last  = 0;
	$index = 0;
	$buf   = array();
	
	while ($index < strlen($text))
	{
		if (substr($text, $index, 2) == '//')
		{
			$buf[] = substr($text, $last, $index - $last);
			$index = strpos($text, "\n", $index);
			if ($index === false)
				break;
			
			$last = $index;
		}
		else if (substr($text, $index, 2) == '/*')
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
