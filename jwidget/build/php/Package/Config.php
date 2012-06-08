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
	private $globalConfig;        // JWSDK_GlobalConfig
	private $resourceManager;     // JWSDK_Resource_Manager
	private $fileManager;         // JWSDK_File_Manager
	
	private $resources = array(); // Array of JWSDK_Resource
	private $requires = array();  // Array of String
	
	public function __construct(
		$name,            // String
		$json,            // Object
		$globalConfig,    // JWSDK_GlobalConfig
		$resourceManager, // JWSDK_Resource_Manager
		$fileManager)     // JWSDK_File_Manager
	{
		parent::__construct($name);
		
		$this->globalConfig = $globalConfig;
		$this->resourceManager = $resourceManager;
		$this->fileManager = $fileManager;
		
		try
		{
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
		foreach ($this->resources as $resource)
			$result[] = $this->resourceManager->convertResource($resource);
		
		return $result;
	}
	
	protected function initCompressedFiles() // Array of JWSDK_File
	{
		$name = $this->getName();
		
		try
		{
			JWSDK_Log::logTo('build.log', "Compressing package $name");
			
			$result = array();
			foreach ($this->fileManager->getAttachers() as $type => $attacher)
			{
				$contents = array();
				foreach ($this->getSourceFiles() as $file)
				{
					if ($file->getAttacher() == $type)
						$contents[] = $this->fileManager->getFileContents($file);
				}
				
				if (count($contents) == 0)
					continue;
				
				$contents = implode("\n", $contents);
				
				$mergePath = $this->getMergePath($type);
				$buildPath = $this->getBuildPath($type);
				
				JWSDK_Util_File::write($mergePath, $contents);
				JWSDK_Util_File::mkdir($buildPath);
				JWSDK_Util_File::compress($mergePath, $buildPath);
				
				$result[] = $this->fileManager->getFile($this->getBuildName($type), $type);
			}
			
			return $result;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PackageCompressError($name, $e);
		}
	}
	
	private function getMergePath( // String
		$type) // String
	{
		return $this->globalConfig->getMergePath() . '/' . $this->getName() . ".$type";
	}
	
	private function getBuildName( // String
		$type) // String
	{
		return $this->globalConfig->getBuildUrl() . '/' . $this->getName() . ".min.$type";
	}
	
	private function getBuildPath( // String
		$type) // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->getBuildName($type);
	}
}
