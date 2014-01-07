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
