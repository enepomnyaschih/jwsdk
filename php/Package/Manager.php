<?php

/*
MIT License

Copyright (c) 2020 Egor Nepomnyaschih

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
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
