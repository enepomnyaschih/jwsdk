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
	private $json; // Object
	
	public function __construct(
		$path = 'config.json') // String
	{
		$contents = @file_get_contents($path, 'r');
		if ($contents === false)
			throw new Exception("Can't open main config (path: $path)");
		
		$this->json = json_decode($contents, true);
	}
	
	// ##### Common #####
	
	public function getConfigPath() // String
	{
		return $this->json['configPath'];
	}
	
	public function getPublicPath() // String
	{
		return $this->json['publicPath'];
	}
	
	// ##### Modes #####
	
	public function getModeConfigPath( // String
		$name) // String
	{
		return $this->json['modesPath'] . "/$name.json";
	}
	
	// ##### Packages #####
	
	public function getBuildUrl() // String
	{
		return $this->json['buildUrl'];
	}
	
	public function getBuildPath() // String
	{
		return $this->getPublicPath() . '/' . $this->getBuildUrl();
	}
	
	public function getPackageConfigsPath() // String
	{
		return $this->json['jslistsPath'];
	}
	
	public function getPackagePath( // String
		$name) // String
	{
		return $this->json['jslistsPath'] . "/$name.jslist";
	}
	
	public function getPackageMergePath( // String
		$name) // String
	{
		return $this->json['tempPath'] . "/$name.js";
	}
	
	public function getPackageBuildPath( // String
		$name) // String
	{
		return $this->getBuildPath() . "/$name.min.js";
	}
	
	public function getPackageBuildUrl( // String
		$name) // String
	{
		return $this->getBuildUrl() . "/$name.min.js";
	}
	
	// ##### Pages #####
	
	public function getPagesBuildPath() // String
	{
		return $this->getPublicPath() . '/' . $this->json['pagesUrl'];
	}
	
	public function getPageBuildPath( // String
		$name) // String
	{
		return $this->getPagesBuildPath() . "/$name.html";
	}
	
	public function getPageConfigsPath() // String
	{
		return $this->getConfigPath() . '/' . $this->json['pagesFolder'];
	}
	
	public function getPageConfigName( // String
		$pageName) // String
	{
		return $this->json['pagesFolder'] . "/$pageName";
	}
	
	public function getPageConfigPath( // String
		$name) // String
	{
		return $this->getConfigPath() . "/$name.json";
	}
	
	// ##### Services #####
	
	public function getServicePath( // String
		$name) // String
	{
		return $this->json['servicesPath'] . "/$name.html";
	}
	
	// ##### Templates #####
	
	public function getTemplatePath( // String
		$name) // String
	{
		return $this->json['templatesPath'] . "/$name.html";
	}
	
	// ##### Resources #####
	
	public function getResourceSourcePath( // String
		$name) // String
	{
		return $this->getPublicPath() . "/$name";
	}
	
	public function getResourceBuildUrl( // String
		$name) // String
	{
		return $this->getBuildUrl() . "/$name.js";
	}
	
	public function getResourceBuildPath( // String
		$name) // String
	{
		return $this->getPublicPath() . "/" . $this->getResourceBuildUrl($name);
	}
	
	public function getResourceInclusionUrl( // String
		$name) // String
	{
		$sourcePath = $this->getResourceSourcePath($name);
		if (!file_exists($sourcePath))
			throw new Exception("Can't find resource (name: $name)");
		
		return $this->json['urlPrefix'] . "$name?timestamp=" . filemtime($sourcePath);
	}
}
