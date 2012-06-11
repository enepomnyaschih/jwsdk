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
	public function setPackageResourceMtime(
		$packageName,  // String
		$resourceName, // String
		$value)        // Timestamp
	{
		return JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'resources', $resourceName, 'mtime'), $value);
	}
	
	public function setPackageCompressionMtime(
		$packageName,  // String
		$attacherType, // String
		$value)        // Timestamp
	{
		return JWSDK_Util_Json::set($this->json, array('packages', $packageName, 'compressions', $attacherType, 'mtime'), $value);
	}
}
