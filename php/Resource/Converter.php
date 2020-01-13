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

class JWSDK_Resource_Converter
{
	public function getType() // String
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}

	public function isConvertion() // Boolean
	{
		return true;
	}

	public function getAttacher() // String
	{
		return 'js';
	}

	// TypeScript files have special traits:
	// * they are compiled all together
	// * they need recompilation on dependency modification
	public function isTypeScript() // Boolean
	{
		return false;
	}

	public function addTypeScriptDependencies(
		&$typeScripts,    // Array<JWSDK_Resource>
		$resource,        // JWSDK_Resource
		$resourceManager, // JWSDK_Resource_Manager
		$globalConfig)    // JWSDK_GlobalConfig
	{
	}

	public function expand( // Array<JWSDK_Resource>
		$resource,        // JWSDK_Resource
		$resourceManager, // JWSDK_Resource_Manager
		$globalConfig)    // JWSDK_Global_Config
	{
		return array($resource);
	}

	public function convert(
		$resource,     // JWSDK_Resource
		$globalConfig) // JWSDK_GlobalConfig
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}

	public function getParamsByArray( // Array
		$params) // Array
	{
		return array();
	}

	public function getParamsByJson( // Array
		$json) // Object
	{
		return $json;
	}

	public function getResourceBuildName( // String
		$resource,     // String
		$globalConfig, // JWSDK_GlobalConfig
		$suffix = '')  // String
	{
		$name = $resource->getName();
		$attacher = $this->getAttacher();
		if (!empty($suffix)) {
			$suffix = $suffix . '.';
		}
		return $globalConfig->getBuildUrl() . "/resources/$name.$suffix$attacher";
	}

	public function getResourceSourcePath( // String
		$resource,     // String
		$globalConfig) // JWSDK_GlobalConfig
	{
		$name = $resource->getName();
		return $globalConfig->getPublicPath() . "/$name";
	}

	public function getResourceBuildPath( // String
		$resource,     // String
		$globalConfig, // JWSDK_GlobalConfig
		$suffix = '')  // String
	{
		return $globalConfig->getPublicPath() . "/" . $this->getResourceBuildName($resource, $globalConfig, $suffix);
	}
}
