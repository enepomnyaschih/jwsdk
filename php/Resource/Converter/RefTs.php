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

class JWSDK_Resource_Converter_RefTs extends JWSDK_Resource_Converter_Ts
{
	public function getType() // String
	{
		return 'ref.ts';
	}

	public function expand( // Array<JWSDK_Resource>
		$resource,        // JWSDK_Resource
		$resourceManager, // JWSDK_Resource_Manager
		$globalConfig)    // JWSDK_Global_Config
	{
		$sourcePath = $this->getResourceSourcePath($resource, $globalConfig);
		$sourceContents = JWSDK_Util_File::read($sourcePath, 'resource file');
		$sourceContents = JWSDK_Util_String::smoothCrlf($sourceContents);
		$sourceLines = explode("\n", $sourceContents);
		$result = array($resource);
		foreach ($sourceLines as $line)
		{
			if (!preg_match('~^\s*///\s*<reference path="([^"]*)"\s*/>\s*$~', $line, $matches))
				continue;
			if (preg_match('~\.d\.ts$~', $matches[1]))
				continue;
			$definition = JWSDK_Util_File::normalizePath(
				JWSDK_Util_File::getDirectory($resource->getName()) . '/' . $matches[1]);
			$result[] = $resourceManager->getResourceByDefinition($definition);
		}
		return $result;
	}
}
