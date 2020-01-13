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

class JWSDK_BuildCache_Input
{
	private $path; // String
	private $json; // Object

	public function __construct(
		$path) // String
	{
		$this->path = $path;

		$contents = @file_get_contents($path);
		if ($contents === false)
			$this->json = array();
		else
			$this->json = json_decode($contents, true);
	}

	public function getJson()
	{
		return $this->json;
	}

	/**
	 * Compression info.
	 */
	public function getPackageGlobalConfigMtime( // Timestamp
		$packageName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'globalConfigMtime'));
	}

	public function getPackageConfigMtime( // Timestamp
		$packageName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'configMtime'));
	}

	public function getPackageDtsOutputMtime( // Timestamp
		$packageName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'dtsOutputMtime'));
	}

	public function getPackageSdkVersion( // String
		$packageName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'sdkVersion'));
	}

	public function getPackageResourceMtime( // Timestamp
		$packageName,  // String
		$resourceName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'resources', $resourceName, 'mtime'));
	}

	public function getPackageResourceTargetMtime( // Timestamp
		$packageName,  // String
		$resourceName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'resources', $resourceName, 'targetMtime'));
	}
}
