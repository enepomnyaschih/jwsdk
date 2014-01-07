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

$__cssUrlPrefix;
$__cssUrlTargetName;
$__cssUrlGlobalConfig;
$__cssUrlFileManager;
$__cssUrlHitCount;

function __cssRelativeUrlBuilder($url)
{
	global $__cssUrlPrefix;
	global $__cssUrlTargetName;
	global $__cssUrlHitCount;
	
	if (!JWSDK_Util_Url::isDomesticUrl($url))
		return $url;
	
	if (JWSDK_Util_Url::isRelativeUrl($url))
	{
		$url = JWSDK_Util_Url::normalizeRelative($__cssUrlPrefix . $url);
		$name = JWSDK_Util_Url::normalizeRelative($__cssUrlTargetName . '/' . $url);
	}
	else
	{
		$name = substr($url, 1);
	}
	$name = JWSDK_Util_Url::extractName($name);
	
	$__cssUrlHitCount[$name] = (isset($__cssUrlHitCount[$name]) ? $__cssUrlHitCount[$name] : 0) + 1;
	
	return $url;
}

function __cssRelativeUrlReplacer($match)
{
	return 'url("' . __cssRelativeUrlBuilder($match[2]) . '")';
}

function __cssCompressUrlBuilder($url)
{
	global $__cssUrlPrefix;
	global $__cssUrlTargetName;
	global $__cssUrlGlobalConfig;
	global $__cssUrlFileManager;
	global $__cssUrlHitCount;
	
	if (!JWSDK_Util_Url::isDomesticUrl($url))
		return $url;
	
	$name = JWSDK_Util_Url::isRelativeUrl($url) ?
		JWSDK_Util_Url::normalizeRelative($__cssUrlTargetName . '/' . $url) : substr($url, 1);
	$name = JWSDK_Util_Url::extractName($name);
	$file = $__cssUrlFileManager->getFile($name);
	if ($__cssUrlGlobalConfig->isEmbedDataUri() && ($__cssUrlHitCount[$name] == 1))
	{
		$size = $__cssUrlFileManager->getFileSize($file);
		if ($size <= $__cssUrlGlobalConfig->getDataUriMaxSize())
		{
			$mime = $__cssUrlGlobalConfig->getMimeType($file->getExtension());
			$contents = $__cssUrlFileManager->getFileContents($file);
			$base64 = base64_encode($contents);
			return "data:$mime;base64,$base64";
		}
	}
	$mtime = $file->getMtime();
	return JWSDK_Util_Url::addQueryParam($url, '_dc', $mtime);
}

function __cssCompressUrlReplacer($match)
{
	return 'url("' . __cssCompressUrlBuilder($match[2]) . '")';
}

class JWSDK_Util_Css
{
	const URL_REG = '~url\s*\(\s*([\'"]?)([^\)]*)\1\s*\)~'; // match[2] will be the URL
	
	/*
	Returns relative URL prefix considering that CSS file is moved from sourceName to targetName.
	*/
	public static function getRelativeUrlPrefix( // String
		$sourceName, // String
		$targetName) // String
	{
		$sourceFolders = explode('/', $sourceName);
		$targetFolders = explode('/', $targetName);
		
		$diffIndex = 0;
		while (($diffIndex < count($sourceFolders) - 1) &&
		       ($diffIndex < count($targetFolders) - 1) &&
		       ($sourceFolders[$diffIndex] == $targetFolders[$diffIndex]))
		{
			$diffIndex++;
		}
		
		$dirs = array();
		for ($i = $diffIndex; $i < count($targetFolders) - 1; $i++)
			$dirs[] = '..';
		
		for ($i = $diffIndex; $i < count($sourceFolders) - 1; $i++)
			$dirs[] = $sourceFolders[$i];
		
		$dirs[] = '';
		return implode('/', $dirs);
	}
	
	/*
	- Replaces all relative URLs considering that CSS file is moved from sourceName to targetName
	- Counts hit count for every domestic URL
	*/
	public static function updateRelativeUrls( // String
		$contents,   // String
		$sourceName, // String
		$targetName) // String
	{
		global $__cssUrlPrefix;
		global $__cssUrlTargetName;
		global $__cssUrlHitCount;
		
		$__cssUrlPrefix = self::getRelativeUrlPrefix($sourceName, $targetName);
		$__cssUrlTargetName = JWSDK_Util_File::getDirectory($targetName);
		$__cssUrlHitCount = array();
		
		return preg_replace_callback(self::URL_REG, '__cssRelativeUrlReplacer', $contents);
	}
	
	/*
	- Modifies relative URLs considering that CSS file is moved from $sourceName to $targetName
	- Appends timestamps to URLs if available
	- Converts smaller targets to data URIs if this option is enabled
	*/
	public static function compressUrls( // String
		$contents,     // String
		$sourceName,   // String
		$targetName,   // String
		$globalConfig, // JWSDK_GlobalConfig
		$fileManager)  // JWSDK_File_Manager
	{
		global $__cssUrlPrefix;
		global $__cssUrlTargetName;
		global $__cssUrlGlobalConfig;
		global $__cssUrlFileManager;
		global $__cssUrlHitCount;
		
		$__cssUrlPrefix = self::getRelativeUrlPrefix($sourceName, $targetName);
		$__cssUrlTargetName = JWSDK_Util_File::getDirectory($targetName);
		$__cssUrlGlobalConfig = $globalConfig;
		$__cssUrlFileManager = $fileManager;
		$__cssUrlHitCount = array();
		
		$contents = JWSDK_Util_String::removeComments($contents);
		$contents = preg_replace_callback(self::URL_REG, '__cssRelativeUrlReplacer', $contents);
		return preg_replace_callback(self::URL_REG, '__cssCompressUrlReplacer', $contents);
	}
}
