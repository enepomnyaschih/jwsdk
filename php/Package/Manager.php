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

class JWSDK_Package_Manager
{
	private $globalConfig;       // JWSDK_GlobalConfig
	private $mode;               // JWSDK_Mode
	private $buildCache;         // JWSDK_BuildCache
	private $resourceManager;    // JWSDK_Resource_Manager
	private $fileManager;        // JWSDK_File_Manager
	private $packages = array(); // Map from name:String to JWSDK_Package
	
	private $dependencyReaders = array(); // Array of JWSDK_Package_DependencyReader
	
	public function __construct(
		$globalConfig,    // JWSDK_GlobalConfig
		$mode,            // JWSDK_Mode
		$buildCache,      // JWSDK_BuildCache
		$resourceManager, // JWSDK_Resource_Manager
		$fileManager)     // JWSDK_File_Manager
	{
		$this->globalConfig = $globalConfig;
		$this->mode = $mode;
		$this->buildCache = $buildCache;
		$this->resourceManager = $resourceManager;
		$this->fileManager = $fileManager;
	}
	
	public function readPackagesWithDependencies( // Array of JWSDK_Package
		$packageNames) // Array of String
	{
		$dependencyReader = new JWSDK_Package_DependencyReader();
		$this->dependencyReaders[] = $dependencyReader;
		
		foreach ($packageNames as $name)
			$this->readPackageWithDependenciesRecursion($name);
		
		$result = $dependencyReader->packages;
		array_pop($this->dependencyReaders);
		
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
	
	public function getLibraryPackage() // JWSDK_Package
	{
		return $this->readPackage('jwsdk.js|auto');
	}
	
	private function readPackageWithDependenciesRecursion(
		$name) // String
	{
		$dependencyReader = $this->dependencyReaders[count($this->dependencyReaders) - 1];
		if (isset($dependencyReader->map[$name]))
			return;
		
		$package = $this->readPackage($name);
		$dependencyReader->map[$name] = $package;
		
		foreach ($package->getRequires() as $require)
			$this->readPackageWithDependenciesRecursion($require);
		
		$dependencyReader->packages[] = $package;
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
		
		return new JWSDK_Package_Config($name, $this->globalConfig, $this->mode, $this->buildCache, $this, $this->resourceManager, $this->fileManager);
	}
}
