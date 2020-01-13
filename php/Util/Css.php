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
	if ($__cssUrlGlobalConfig->isEmbedDataUri() && ($__cssUrlHitCount[$name] <= $__cssUrlGlobalConfig->getDataUriMaxHits()))
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
		
		$contents = JWSDK_Util_String::removeComments($contents, JWSDK_Util_String::COMMENTS_BLOCK);
		$contents = preg_replace_callback(self::URL_REG, '__cssRelativeUrlReplacer', $contents);
		return preg_replace_callback(self::URL_REG, '__cssCompressUrlReplacer', $contents);
	}
}
