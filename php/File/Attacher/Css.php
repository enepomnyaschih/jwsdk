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
		$contents,     // String
		$sourceName,   // String
		$targetName,   // String
		$globalConfig, // JWSDK_GlobalConfig
		$fileManager)  // JWSDK_File_Manager
	{
		return JWSDK_Util_Css::compressUrls($contents, $sourceName, $targetName, $globalConfig, $fileManager);
	}
	
	public function getFileDependencies( // Array of JWSDK_File
		$file,        // JWSDK_File
		$fileManager) // JWSDK_File_Manager
	{
		$matches = array();
		$contents = $fileManager->getFileContents($file);
		$contents = JWSDK_Util_String::removeComments($contents, JWSDK_Util_String::COMMENTS_BLOCK);
		$matchCount = preg_match_all(JWSDK_Util_Css::URL_REG, $contents,
			$matches, PREG_SET_ORDER);
		if (!$matchCount)
			return array();
		
		$urls = array();
		foreach ($matches as $match)
		{
			$url = $match[2];
			if (JWSDK_Util_Url::isDomesticUrl($url))
				$urls[JWSDK_Util_Url::extractName($url)] = null;
		}
		$urls = array_keys($urls);
		
		$relativeUrlPrefix = $file->getDirectory();
		$relativeUrlPrefix = ($relativeUrlPrefix == '.') ? '' : "$relativeUrlPrefix/";
		$dependencies = array();
		foreach ($urls as $url)
		{
			$name = JWSDK_Util_Url::isRelativeUrl($url) ? ($relativeUrlPrefix . $url) : substr($url, 1);
			$dependencies[] = $fileManager->getFile($name);
		}
		return $dependencies;
	}
}
