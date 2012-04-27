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
	private $json;
	
	public function __construct($path = 'config.json')
	{
		$contents = @file_get_contents($path, 'r');
		if ($contents === false)
			throw new Exception("Can't open main config (path: $path)");
		
		$this->json = json_decode($contents, true);
	}
	
	// ##### Common #####
	
	public function getConfigPath()
	{
		return $this->json['configPath'];
	}
	
	public function getPublicPath()
	{
		return $this->json['publicPath'];
	}
	
	// ##### Modes #####
	
	public function getModeConfigPath($name)
	{
		return $this->json['modesPath'] . "/$name.json";
	}
	
	// ##### JS lists #####
	
	public function getBuildUrl()
	{
		return $this->json['buildUrl'];
	}
	
	public function getBuildPath()
	{
		return $this->getPublicPath() . '/' . $this->getBuildUrl();
	}
	
	public function getJsListConfigsPath()
	{
		return $this->json['jslistsPath'];
	}
	
	public function getJsListPath($name)
	{
		return $this->json['jslistsPath'] . "/$name.jslist";
	}
	
	public function getJsListMergePath($name)
	{
		return $this->json['tempPath'] . "/$name.js";
	}
	
	public function getJsListBuildPath($name)
	{
		return $this->getBuildPath() . "/$name.min.js";
	}
	
	public function getJsListBuildUrl($name)
	{
		return $this->getBuildUrl() . "/$name.min.js";
	}
	
	// ##### Pages #####
	
	public function getPagesBuildPath()
	{
		return $this->getPublicPath() . '/' . $this->json['pagesUrl'];
	}
	
	public function getPageBuildPath($name)
	{
		return $this->getPagesBuildPath() . "/$name.html";
	}
	
	public function getPageConfigsPath()
	{
		return $this->getConfigPath() . '/' . $this->json['pagesFolder'];
	}
	
	public function getPageConfigName($pageName)
	{
		return $this->json['pagesFolder'] . "/$pageName";
	}
	
	public function getPageConfigPath($name)
	{
		return $this->getConfigPath() . "/$name.json";
	}
	
	// ##### Services #####
	
	public function getServicePath($name)
	{
		return $this->json['servicesPath'] . "/$name.html";
	}
	
	// ##### Templates #####
	
	public function getTemplatePath($name)
	{
		return $this->json['templatesPath'] . "/$name.html";
	}
	
	// ##### Resources #####
	
	public function getResourceSourcePath($name)
	{
		return $this->getPublicPath() . "/$name";
	}
	
	public function getResourceBuildUrl($name)
	{
		return $this->getBuildUrl() . "/$name.js";
	}
	
	public function getResourceBuildPath($name)
	{
		return $this->getPublicPath() . "/" . $this->getResourceBuildUrl($name);
	}
	
	public function getResourceInclusionUrl($name)
	{
		$sourcePath = $this->getResourceSourcePath($name);
		if (!file_exists($sourcePath))
			throw new Exception("Can't find resource (name: $name)");
		
		return $this->json['urlPrefix'] . "$name?timestamp=" . filemtime($sourcePath);
	}
}
