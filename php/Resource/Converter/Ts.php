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

class JWSDK_Resource_Converter_Ts extends JWSDK_Resource_Converter
{
	public static $availableTargets = array('ES3', 'ES5');

	public function getType() // String
	{
		return 'ts';
	}

	public function getAttacher() // String
	{
		return 'js';
	}

	public function isTypeScript() // Boolean
	{
		return true;
	}

	public function addTypeScriptDependencies(
		&$typeScripts,    // Array<JWSDK_Resource>
		$resource,        // JWSDK_Resource
		$resourceManager, // JWSDK_Resource_Manager
		$globalConfig)    // JWSDK_GlobalConfig
	{
		$name = $resource->getName();
		if (isset($typeScripts[$name]))
			return;

		$sourcePath = $this->getResourceSourcePath($resource, $globalConfig);
		$sourceContents = JWSDK_Util_File::read($sourcePath, 'resource file');
		$sourceContents = JWSDK_Util_String::smoothCrlf($sourceContents);
		$sourceLines = explode("\n", $sourceContents);
		$typeScripts[$name] = $resource;
		foreach ($sourceLines as $line)
		{
			if (!preg_match('~^\s*///\s*<reference path="([^"]*)"\s*/>\s*$~', $line, $matches))
				continue;
			$definition = JWSDK_Util_File::normalizePath(
				JWSDK_Util_File::getDirectory($name) . '/' . $matches[1]);
			$subresource = $resourceManager->getResourceByDefinition($definition);
			$resourceManager->addTypeScriptDependencies($typeScripts, $subresource);
		}
	}

	public function convert(
		$resource,     // JWSDK_Resource
		$globalConfig) // JWSDK_GlobalConfig
	{
		// do nothing - everything is implemented in JWSDK_Package_Config
	}

	public function getResourceBuildName( // String
		$resource,     // String
		$globalConfig, // JWSDK_GlobalConfig
		$suffix = '')  // String
	{
		$name = preg_replace('/(\.d)?\.ts/', '', $resource->getName());
		$attacher = $this->getAttacher();
		if (!empty($suffix)) {
			$suffix = $suffix . '.';
		}
		return $globalConfig->getBuildUrl() . "/ts/$name.$suffix$attacher";
	}
}
