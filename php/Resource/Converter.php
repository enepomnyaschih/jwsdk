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
