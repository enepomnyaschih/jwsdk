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

class JWSDK_Resource_Converter_CssBase extends JWSDK_Resource_Converter
{
	public function getAttacher() // String
	{
		return 'css';
	}

	public function convert(
		$resource,     // JWSDK_Resource
		$globalConfig) // JWSDK_GlobalConfig
	{
		$sourcePath = $this->getResourceSourcePath($resource, $globalConfig);
		$tempPath   = $this->getResourceBuildPath ($resource, $globalConfig, 'temp');
		$buildPath  = $this->getResourceBuildPath ($resource, $globalConfig);

		$output = array();
		$status = 0;

		JWSDK_Util_File::mkdir($buildPath);

		$process = $this->getProcess($sourcePath, $tempPath, $globalConfig);
		$process->execute();

		$sourceContents = JWSDK_Util_File::read($tempPath, 'temp file');
		$buildName = $this->getResourceBuildName($resource, $globalConfig);
		$buildContents = JWSDK_Util_Css::updateRelativeUrls($sourceContents, $resource->getName(), $buildName);
		JWSDK_Util_File::write($buildPath, $buildContents);
	}

	protected function getProcess( // JWSDK_Process
		$source,       // String
		$target,       // String
		$globalConfig) // JWSDK_GlobalConfig
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
}
