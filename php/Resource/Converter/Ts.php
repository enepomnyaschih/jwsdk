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