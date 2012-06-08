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

class JWSDK_Package_Manager
{
	private $globalConfig;       // JWSDK_GlobalConfig
	private $resourceManager;    // JWSDK_Resource_Manager
	private $fileManager;        // JWSDK_File_Manager
	private $packages = array(); // Map from name:String to JWSDK_Package
	
	// temporary variables for "readPackageWithDependencies" method
	private $_readerPackages;    // Array of JWSDK_Package
	private $_readerPackageMap;  // Map from name:String to JWSDK_Package
	
	public function __construct(
		$globalConfig,    // JWSDK_GlobalConfig
		$resourceManager, // JWSDK_Resource_Manager
		$fileManager)     // JWSDK_File_Manager
	{
		$this->globalConfig = $globalConfig;
		$this->resourceManager = $resourceManager;
		$this->fileManager = $fileManager;
	}
	
	public function readPackageWithDependencies( // Array of JWSDK_Package
		$name) // String
	{
		$this->_readerPackages = array();
		$this->_readerPackageMap = array();
		
		$this->readPackageWithDependenciesRecursion($name);
		
		$result = $this->_readerPackages;
		
		$this->_readerPackages = null;
		$this->_readerPackageMap = null;
		
		return $result;
	}
	
	public function addPackage(
		$package) // JWSDK_Package
	{
		$this->packages[$package->getName()] = $package;
	}
	
	public function getPackage( // JWSDK_Package
		$name) // String
	{
		return JWSDK_Util_Array::get($this->packages, $name);
	}
	
	private function readPackageWithDependenciesRecursion(
		$name) // String
	{
		if (isset($this->_readerPackageMap[$name]))
			return;
		
		$package = $this->readPackage($name);
		$this->_readerPackageMap[$name] = $package;
		
		foreach ($package->getRequires() as $require)
			$this->readPackageWithDependenciesRecursion($require);
		
		$this->_readerPackages[] = $package;
	}
	
	private function readPackage( // JWSDK_Package
		$name) // String
	{
		$package = $this->getPackage($name);
		if ($package)
			return $package;
		
		$package = $this->buildPackage($name);
		$this->addPackage($package);
		
		return $package;
	}
	
	private function buildPackage( // JWSDK_Package
		$name) // String
	{
		if (preg_match('/\.(js|css)$/', $name))
			return new JWSDK_Package_Simple($name, $this->fileManager);
		
		if (preg_match('/\.(js|css)|auto$/', $name))
			return new JWSDK_Package_Auto(substr($name, 0, strrpos($name, '|')), $this->fileManager);
		
		try
		{
			$json = JWSDK_Util_File::readJson($this->getPackagePath($name), 'package config');
			return new JWSDK_Package_Config($name, $json, $this->resourceManager, $this->fileManager);
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PackageReadError($name, $e);
		}
	}
	
	private function getBuildPath() // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->globalConfig->getBuildUrl();
	}
	
	private function getPackagePath( // String
		$name) // String
	{
		return $this->globalConfig->getPackagesPath() . "/$name.json";
	}
	
	private function getPackageMergePath( // String
		$name, // String
		$type) // String
	{
		return $this->globalConfig->getMergePath() . "/$name.$type";
	}
	
	private function getPackageBuildPath( // String
		$name, // String
		$type) // String
	{
		return $this->getBuildPath() . "/$name.min.$type";
	}
	
	private function getPackageBuildUrl( // String
		$name, // String
		$type) // String
	{
		return $this->globalConfig->getBuildUrl() . "/$name.min.$type";
	}
}
