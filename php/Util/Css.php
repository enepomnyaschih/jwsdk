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

$__cssImagePrefix;

function __cssImageReplacer($match)
{
	global $__cssImagePrefix;
	$path = trim($match[1]);
	
	if ((($path[0] == '"') && ($path[strlen($path) - 1] == '"')) ||
		(($path[0] == "'") && ($path[strlen($path) - 1] == "'")))
		$path = substr($path, 1, strlen($path) - 2);
	
	if (($path[0] == '/') || preg_match('~^\w+://~', $path))
		return "url(\"$path\")";
	
	return 'url("' . JWSDK_Util_Url::normalizeRelative($__cssImagePrefix . $path) . '")';
}

class JWSDK_Util_Css
{
	public static function updateRelativePaths( // String
		$contents,   // String
		$sourceName, // String
		$targetName) // String
	{
		global $__cssImagePrefix;
		
		$sourceFolders = explode('/', $sourceName);
		$targetFolders = explode('/', $targetName);
		
		$diffIndex = 0;
		while (($diffIndex < count($sourceFolders) - 1) &&
		       ($diffIndex < count($targetFolders) - 1) &&
		       ($sourceFolders[$diffIndex] == $targetFolders[$diffIndex]))
		{
			$diffIndex++;
		}
		
		$__cssImagePrefix = array();
		for ($i = $diffIndex; $i < count($targetFolders) - 1; $i++)
			$__cssImagePrefix[] = '..';
		
		for ($i = $diffIndex; $i < count($sourceFolders) - 1; $i++)
			$__cssImagePrefix[] = $sourceFolders[$i];
		
		if (count($__cssImagePrefix) == 0)
		{
			$__cssImagePrefix = '';
		}
		else
		{
			$__cssImagePrefix[] = '';
			$__cssImagePrefix = implode('/', $__cssImagePrefix);
		}
		
		$contents = preg_replace_callback('~url\s*\(([^\)]*)\)~', '__cssImageReplacer', $contents);
		
		return $contents;
	}
}
