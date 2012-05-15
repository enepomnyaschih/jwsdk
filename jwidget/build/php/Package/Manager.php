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
	private $packages = array(); // Map from name:String to JWSDK_Package
	
	// temporary variables for "readPackageWithDependencies" method
	private $_readerPackages;    // Array of JWSDK_Package
	private $_readerPackageMap;  // Map from name:String to JWSDK_Package
	
	public function __construct(
		$globalConfig,    // JWSDK_GlobalConfig
		$resourceManager) // JWSDK_Resource_Manager
	{
		$this->globalConfig = $globalConfig;
		$this->resourceManager = $resourceManager;
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
	
	public function compressPackage( // JWSDK_Resource, compressed file
		$package) // JWSDK_Package
	{
		$compressedResource = $package->getCompressedResource();
		if ($compressedResource)
			return $compressedResource;
		
		try
		{
			$name = $package->getName();
			
			JWSDK_Log::logTo('build.log', "Compressing package $name");
			
			$mergePath = $this->getPackageMergePath($name);
			$buildPath = $this->getPackageBuildPath($name);
			
			JWSDK_Util_File::write($mergePath, $this->getPackageMergedContents($package));
			JWSDK_Util_File::mkdir($buildPath);
			JWSDK_Util_File::compress($mergePath, $buildPath);
			
			$compressedResource = new JWSDK_Resource($this->getPackageBuildUrl($name), 'js');
			$package->setCompressedResource($compressedResource);
			
			return $compressedResource;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PackageCompressError($name, $e);
		}
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
		if (isset($this->_readerPackages[$name]))
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
		if (preg_match('/\.js$/', $name))
			return new JWSDK_Package_Simple($name);
		
		if (preg_match('/\|auto$/', $name))
			return new JWSDK_Package_Auto(substr($name, 0, strrpos($name, '|')));
		
		try
		{
			$json = JWSDK_Util_File::readJson($this->getPackagePath($name), 'package config');
			return $this->getPackageByJson($name, $json);
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PackageReadError($name, $e);
		}
	}
	
	private function getPackageByJson( // JWSDK_Package_Config
		$name, // String
		$json) // Object
	{
		$package = new JWSDK_Package_Config($name);
		
		try
		{
			$resources = JWSDK_Util_Array::get($json, 'resources', array());
			foreach ($resources as $resourceDefinition)
			{
				$resource = $this->resourceManager->getResourceByDefinition($resourceDefinition);
				$resource = $this->resourceManager->convertResource($resource);
				
				$package->addResource($resource);
			}
			
			$requires = JWSDK_Util_Array::get($json, 'requires', array());
			foreach ($requires as $require)
			{
				if (!is_string($require))
					throw new JWSDK_Exception_InvalidFileFormat($name, 'package config', $e);
				
				$package->addRequire($require);
			}
			
			return $package;
		}
		catch (JWSDK_Exception $e)
		{
			throw $e;
		}
		catch (Exception $e)
		{
			throw new JWSDK_Exception_InvalidFileFormat($name, 'package config', $e);
		}
	}
	
	private function getPackageMergedContents( // String
		$package) // JWSDK_Package
	{
		$buf = array();
		foreach ($package->getSourceResources() as $resource)
			$buf[] = $this->resourceManager->getResourceContents($resource);
		
		return implode("\n", $buf);
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
		$name) // String
	{
		return $this->globalConfig->getMergePath() . "/$name.js";
	}
	
	private function getPackageBuildPath( // String
		$name) // String
	{
		return $this->getBuildPath() . "/$name.min.js";
	}
	
	private function getPackageBuildUrl( // String
		$name) // String
	{
		return $this->globalConfig->getBuildUrl() . "/$name.min.js";
	}
}
