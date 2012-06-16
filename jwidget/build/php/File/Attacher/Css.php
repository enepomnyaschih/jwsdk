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

$__attacherCssNamePrefix;

function __attacherCssReplacer($match)
{
	global $__attacherCssNamePrefix;
	$path = trim($match[1]);
	
	if ((($path[0] == '"') && ($path[strlen($path) - 1] == '"')) ||
		(($path[0] == "'") && ($path[strlen($path) - 1] == "'")))
		$path = substr($path, 1, strlen($path) - 2);
	
	if (($path[0] == '/') || preg_match('~^\w+://~', $path))
		return "url(\"$path\")";
	
	return 'url("' . JWSDK_Util_Url::normalizeRelative($__attacherCssNamePrefix . $path) . '")';
}

class JWSDK_File_Attacher_Css extends JWSDK_Resource_Attacher
{
	public function getType() // String
	{
		return 'css';
	}
	
	public function format( // String
		$url) // String
	{
		return '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($url) . '" />';
	}
	
	public function beforeCompress( // String
		$contents,   // String
		$sourceName, // String
		$targetName) // String
	{
		global $__attacherCssNamePrefix;
		
		$sourceFolders = explode('/', $sourceName);
		$targetFolders = explode('/', $targetName);
		
		$diffIndex = 0;
		while (($diffIndex < count($sourceFolders) - 1) &&
		       ($diffIndex < count($targetFolders) - 1) &&
		       ($sourceFolders[$diffIndex] == $targetFolders[$diffIndex]))
		{
			$diffIndex++;
		}
		
		$__attacherCssNamePrefix = array();
		for ($i = $diffIndex; $i < count($targetFolders) - 1; $i++)
			$__attacherCssNamePrefix[] = '..';
		
		for ($i = $diffIndex; $i < count($sourceFolders) - 1; $i++)
			$__attacherCssNamePrefix[] = $sourceFolders[$i];
		
		if (count($__attacherCssNamePrefix) == 0)
		{
			$__attacherCssNamePrefix = '';
		}
		else
		{
			$__attacherCssNamePrefix[] = '';
			$__attacherCssNamePrefix = implode('/', $__attacherCssNamePrefix);
		}
		
		$contents = preg_replace_callback('~url\s*\(([^\)]*)\)~', '__attacherCssReplacer', $contents);
		
		return $contents;
	}
}
