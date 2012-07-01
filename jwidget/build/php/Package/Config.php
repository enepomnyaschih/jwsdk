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

class JWSDK_Package_Config extends JWSDK_Package
{
	private $globalConfig;         // JWSDK_GlobalConfig
	private $mode;                 // JWSDK_Mode
	private $buildCache;           // JWSDK_BuildCache
	private $packageManager;       // JWSDK_Package_Manager
	private $resourceManager;      // JWSDK_Resource_Manager
	private $fileManager;          // JWSDK_File_Manager
	
	private $resources = array();  // Array of JWSDK_Resource
	private $requires = array();   // Array of String
	private $loaders = array();    // Array of String
	private $timestamps = array(); // Array of String
	
	public function __construct(
		$name,            // String
		$globalConfig,    // JWSDK_GlobalConfig
		$mode,            // JWSDK_Mode
		$buildCache,      // JWSDK_BuildCache
		$packageManager,  // JWSDK_Package_Manager
		$resourceManager, // JWSDK_Resource_Manager
		$fileManager)     // JWSDK_File_Manager
	{
		parent::__construct($name);
		
		$this->globalConfig = $globalConfig;
		$this->mode = $mode;
		$this->buildCache = $buildCache;
		$this->packageManager = $packageManager;
		$this->resourceManager = $resourceManager;
		$this->fileManager = $fileManager;
		
		try
		{
			$json = JWSDK_Util_File::readJson($this->getConfigPath(), 'package config');
			
			$resources = JWSDK_Util_Array::get($json, 'resources', array());
			foreach ($resources as $resourceDefinition)
			{
				$resource = $this->resourceManager->getResourceByDefinition($resourceDefinition);
				
				$this->resources[] = $resource;
			}
			
			$requires = JWSDK_Util_Array::get($json, 'requires', array());
			foreach ($requires as $require)
			{
				if (!is_string($require))
					throw new JWSDK_Exception_InvalidFileFormat($name, 'package config');
				
				$this->requires[] = $require;
			}
			
			if (isset($json['loaders']))
			{
				if (!$this->globalConfig->isDynamicLoader())
					throw new JWSDK_Exception_DynamicLoaderDisabled('loaders');
				
				$loaders = $json['loaders'];
				if (is_string($loaders))
					$this->loaders = array($loaders);
				else if (is_array($loaders))
					$this->loaders = $loaders;
			}
			
			$timestamps = JWSDK_Util_Array::get($json, 'timestamps', array());
			foreach ($timestamps as $timestamp)
			{
				if (!$this->globalConfig->isDynamicLoader())
					throw new JWSDK_Exception_DynamicLoaderDisabled('timestamps');
				
				if (!is_string($timestamp))
					throw new JWSDK_Exception_InvalidFileFormat($name, 'package config');
				
				$this->timestamps[] = $timestamp;
			}
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
	
	public function getRequires() // Array of String
	{
		return $this->requires;
	}
	
	protected function initSourceFiles() // Array of JWSDK_File
	{
		$result = array();
		
		if ($this->globalConfig->isDynamicLoader())
			$result[] = $this->createHeader();
		
		foreach ($this->resources as $resource)
			$result[] = $this->resourceManager->convertResource($resource);
		
		return $result;
	}
	
	protected function initCompressedFiles() // Array of JWSDK_File
	{
		$name = $this->getName();
		
		try
		{
			// Header file should be rebuilt before "isModified" call
			$this->getSourceFiles();
			
			if ($this->isModified())
				return $this->initCompressedFilesModified();
			else
				return $this->initCompressedFilesUnmodified();
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PackageCompressError($name, $e);
		}
	}
	
	private function createHeader() // JWSDK_File
	{
		$name = $this->getHeaderName();
		$path = $this->getHeaderPath();
		
		$json = $this->getHeaderJson();
		$jsonStr = json_encode($json);
		
		$oldContents = @file_get_contents($path);
		$newContents = "JWSDK.packageHeader($jsonStr);";
		
		if ($oldContents == $newContents)
			return $this->fileManager->getFile($name, 'js');
		
		JWSDK_Util_File::write($path, $newContents);
		
		return $this->fileManager->getFile($name, 'js');
	}
	
	private function getHeaderJson() // Object
	{
		$loadersJson = array();
		$loaderPackages = $this->packageManager->readPackagesWithDependencies($this->loaders);
		foreach ($loaderPackages as $loaderPackage)
			$loadersJson[] = $this->getHeaderLoaderJson($loaderPackage);
		
		$timestampsJson = array();
		foreach ($this->timestamps as $timestamp)
			$timestampsJson[$timestamp] = JWSDK_Util_File::mtime($this->fileManager->getFilePath($timestamp));
		
		return array(
			'name'       => $this->getName(),
			'requires'   => $this->getRequires(),
			'loaders'    => $loadersJson,
			'timestamps' => $timestampsJson
		);
	}
	
	private function getHeaderLoaderJson( // Object
		$package) // JWSDK_Package
	{
		$result = array(
			'name'     => $package->getName(),
			'requires' => $package->getRequires()
		);
		
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
			$result[$type] = array();
		
		$files = $this->mode->isCompress() ?
			$package->getCompressedFiles() :
			$package->getSourceFiles();
		
		foreach ($files as $file)
		{
			$attacherId = $file->getAttacher();
			$url = $this->fileManager->getFileUrl($file);
			array_push($result[$attacherId], $url);
		}
		
		return $result;
	}
	
	private function isModified() // Boolean
	{
		$oldMtime = $this->buildCache->input->getPackageGlobalConfigMtime($this->getName());
		$newMtime = $this->globalConfig->getMtime();
		if ($oldMtime != $newMtime)
		{
			//echo "-- Global config is modified ($oldMtime:$newMtime)\n";
			return true;
		}
		
		$oldMtime = $this->buildCache->input->getPackageConfigMtime($this->getName());
		$newMtime = JWSDK_Util_File::mtime($this->getConfigPath());
		if ($oldMtime != $newMtime)
		{
			//echo "-- Package config is modified ($oldMtime:$newMtime)\n";
			return true;
		}
		
		if ($this->globalConfig->isDynamicLoader())
		{
			$oldMtime = $this->buildCache->input->getPackageHeaderMtime($this->getName());
			$newMtime = JWSDK_Util_File::mtime($this->getHeaderPath());
			if ($oldMtime != $newMtime)
			{
				//echo "-- Package header is modified ($oldMtime:$newMtime)\n";
				return true;
			}
		}
		
		foreach ($this->resources as $resource)
		{
			$name = $resource->getName();
			$oldMtime = $this->buildCache->input->getPackageResourceMtime($this->getName(), $name);
			$newMtime = $resource->getSourceFile()->getMtime();
			if ($oldMtime != $newMtime)
			{
				//echo "-- Resource $name is modified ($oldMtime:$newMtime)\n";
				return true;
			}
		}
		
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
		{
			if (!$this->hasFilesOfAttacher($type))
				continue;
			
			$name = $this->getBuildName($type);
			$path = $this->fileManager->getFilePath($name);
			if (!file_exists($path))
			{
				//echo "-- Compressed $type file does not exist\n";
				return true;
			}
			
			$oldMtime = $this->buildCache->input->getPackageCompressionMtime($this->getName(), $type);
			$newMtime = filemtime($path);
			if ($oldMtime != $newMtime)
			{
				//echo "-- Compressed $type file is modified ($oldMtime:$newMtime)\n";
				return true;
			}
		}
		
		return false;
	}
	
	private function initCompressedFilesModified() // Array of JWSDK_File
	{
		$name = $this->getName();
		
		$sourceFiles = $this->getSourceFiles();
		
		JWSDK_Log::logTo('build.log', "Compressing package $name");
		
		$this->buildCache->output->setPackageGlobalConfigMtime(
			$this->getName(), $this->globalConfig->getMtime());
		
		$this->buildCache->output->setPackageConfigMtime(
			$this->getName(), JWSDK_Util_File::mtime($this->getConfigPath()));
		
		if ($this->globalConfig->isDynamicLoader())
		{
			$this->buildCache->output->setPackageHeaderMtime(
				$this->getName(), JWSDK_Util_File::mtime($this->getHeaderPath()));
		}
		
		foreach ($this->resources as $resource)
		{
			$this->buildCache->output->setPackageResourceMtime(
				$this->getName(), $resource->getName(), $resource->getSourceFile()->getMtime());
		}
		
		$result = array();
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
		{
			if (!$this->hasFilesOfAttacher($type))
				continue;
			
			$buildName = $this->getBuildName($type);
			
			$contents = array();
			foreach ($sourceFiles as $file)
			{
				if ($file->getAttacher() == $type)
				{
					$fileContents = $this->fileManager->getFileContents($file);
					$contents[] = $attacher->beforeCompress($fileContents, $file->getName(), $buildName);
				}
			}
			
			$contents = implode("\n", $contents);
			
			$mergePath = $this->getMergePath($type);
			$buildPath = $this->getBuildPath($type);
			
			JWSDK_Util_File::write($mergePath, $contents);
			JWSDK_Util_File::mkdir($buildPath);
			JWSDK_Util_File::compress($mergePath, $buildPath);
			
			$compressedFile = $this->fileManager->getFile($buildName, $type);
			$this->buildCache->output->setPackageCompressionMtime($this->getName(), $type, $compressedFile->getMtime());
			
			$result[] = $compressedFile;
		}
		
		return $result;
	}
	
	private function initCompressedFilesUnmodified() // Array of JWSDK_File
	{
		$name = $this->getName();
		
		JWSDK_Log::logTo('build.log', "Package $name is not modified, skipping...");
		
		$result = array();
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
		{
			if (!$this->hasFilesOfAttacher($type))
				continue;
			
			$result[] = $this->fileManager->getFile($this->getBuildName($type), $type);
		}
		
		return $result;
	}
	
	private function hasFilesOfAttacher( // Boolean
		$type) // String
	{
		foreach ($this->getSourceFiles() as $file)
		{
			if ($file->getAttacher() == $type)
				return true;
		}
		
		return false;
	}
	
	private function getConfigPath() // String
	{
		return $this->globalConfig->getPackagesPath() . '/' . $this->getName() . '.json';
	}
	
	private function getHeaderName() // String
	{
		return $this->globalConfig->getBuildUrl() . '/packages/' . $this->getName() . '.header.' . $this->mode->getId() . '.js';
	}
	
	private function getHeaderPath() // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->getHeaderName();
	}
	
	private function getMergePath( // String
		$type) // String
	{
		return $this->globalConfig->getTempPath() . '/merge/' . $this->getName() . ".$type";
	}
	
	private function getBuildName( // String
		$type) // String
	{
		return $this->globalConfig->getBuildUrl() . '/packages/' . $this->getName() . ".min.$type";
	}
	
	private function getBuildPath( // String
		$type) // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->getBuildName($type);
	}
}
