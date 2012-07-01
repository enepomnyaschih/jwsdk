<?php

/*
	jWidget SDK source file.
	
	Copyright (C) 2012 Egor Nepomnyaschih
	
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
	
	public function getPackageHeaderMtime( // Timestamp
		$packageName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'headerMtime'));
	}
	
	public function getPackageResourceMtime( // Timestamp
		$packageName,  // String
		$resourceName) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'resources', $resourceName, 'mtime'));
	}
	
	public function getPackageCompressionMtime( // Timestamp
		$packageName,  // String
		$attacherType) // String
	{
		return JWSDK_Util_Json::get($this->json, array('packages', $packageName, 'compressions', $attacherType, 'mtime'));
	}
}
