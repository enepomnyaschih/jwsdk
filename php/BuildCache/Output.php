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

class JWSDK_BuildCache_Output
{
	private $path; // String
	private $json; // Object

	public function __construct(
		$path,  // String
		$input) // JWSDK_BuildCache_Input
	{
		$this->path = $path;
		$this->json = JWSDK_Util_Json::cloneRecursive($input->getJson());
	}

	public function getJson()
	{
		return $this->json;
	}

	public function save()
	{
		JWSDK_Util_File::write($this->path, json_encode($this->json));
	}

	/**
	 * Compression info.
	 */
	public function setPackageGlobalConfigMtime(
		$packageName, // String
		$value)       // Timestamp
	{
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'globalConfigMtime'), $value);
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'sdkVersion'), JWSDK_Builder::VERSION);
	}

	public function setPackageConfigMtime(
		$packageName, // String
		$value)       // Timestamp
	{
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'configMtime'), $value);
	}

	public function setPackageDtsOutputMtime(
		$packageName, // String
		$value)       // Timestamp
	{
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'dtsOutputMtime'), $value);
	}

	public function setPackageResourceMtime(
		$packageName,  // String
		$resourceName, // String
		$value)        // Timestamp
	{
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'resources', $resourceName, 'mtime'), $value);
	}

	public function setPackageResourceTargetMtime(
		$packageName,  // String
		$resourceName, // String
		$value)        // Timestamp
	{
		JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'resources', $resourceName, 'targetMtime'), $value);
	}
}
