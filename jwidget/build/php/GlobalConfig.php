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

class JWSDK_GlobalConfig
{
	private $json;  // Object
	private $mtime; // Timestamp
	
	public function __construct(
		$path = 'config.json') // String
	{
		$this->json = JWSDK_Util_File::readJson($path, 'global config');
		$this->mtime = JWSDK_Util_File::mtime($path);
		
		$stringFields = array(
			'packagesPath',
			'pagesPath',
			'templatesPath',
			'publicPath',
			'buildUrl',
			'pagesUrl',
			'tempPath',
			'urlPrefix'
		);
		
		foreach ($stringFields as $field)
		{
			if (!isset($this->json[$field]) || !is_string($this->json[$field]))
				throw new JWSDK_Exception_InvalidFileFormat('config.json', 'global config');
		}
		
		if (!isset($this->json['dynamicLoader']) || !is_bool($this->json['dynamicLoader']))
			throw new JWSDK_Exception_InvalidFileFormat('config.json', 'global config');
	}
	
	public function getPackagesPath() // String
	{
		return $this->json['packagesPath'];
	}
	
	public function getPagesPath() // String
	{
		return $this->json['pagesPath'];
	}
	
	public function getTemplatesPath() // String
	{
		return $this->json['templatesPath'];
	}
	
	public function getPublicPath() // String
	{
		return $this->json['publicPath'];
	}
	
	public function getBuildUrl() // String
	{
		return $this->json['buildUrl'];
	}
	
	public function getPagesUrl() // String
	{
		return $this->json['pagesUrl'];
	}
	
	public function getTempPath() // String
	{
		return $this->json['tempPath'];
	}
	
	public function getUrlPrefix() // String
	{
		return $this->json['urlPrefix'];
	}
	
	public function isDynamicLoader() // Boolean
	{
		return $this->json['dynamicLoader'];
	}
	
	public function getMtime() // Timestamp
	{
		return $this->mtime;
	}
}
