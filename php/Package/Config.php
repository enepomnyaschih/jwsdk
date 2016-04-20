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

define('VERBOSE_VERSIONING', false);

class JWSDK_Package_Config extends JWSDK_Package
{
	private $globalConfig;           // JWSDK_GlobalConfig
	private $mode;                   // JWSDK_Mode
	private $buildCache;             // JWSDK_BuildCache
	private $packageManager;         // JWSDK_Package_Manager
	private $resourceManager;        // JWSDK_Resource_Manager
	private $fileManager;            // JWSDK_File_Manager

	private $resources = array();    // Array of JWSDK_Resource
	private $resourceDefs = array(); // Array of Object
	private $requires = array();     // Array of String
	private $loaders = array();      // Array of String
	private $timestamps = array();   // Array of String

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

			$requires = JWSDK_Util_Array::get($json, 'requires', array());
			foreach ($requires as $require)
			{
				if (!is_string($require))
					throw new JWSDK_Exception_InvalidFileFormat($name, 'package config');

				$this->requires[] = $require;
			}

			$this->resourceDefs = JWSDK_Util_Array::get($json, 'resources', array());

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
		$this->buildCache->output->setPackageGlobalConfigMtime(
			$this->getName(), $this->globalConfig->getMtime());

		$this->buildCache->output->setPackageConfigMtime(
			$this->getName(), JWSDK_Util_File::mtime($this->getConfigPath()));

		$resources = array();
		foreach ($this->resourceDefs as $resourceDefinition)
			$resources[] = $this->resourceManager->getResourceByDefinition($resourceDefinition);

		$typeScriptDependencies = array(); // to check for modifications
		foreach ($resources as $resource)
		{
			$this->resourceManager->addTypeScriptDependencies($typeScriptDependencies, $resource);
			JWSDK_Util_Array::addAll($this->resources, $this->resourceManager->expandResource($resource));
		}
		$typeScriptDependencies = array_values($typeScriptDependencies);

		$typeScripts = array(); // to compile
		foreach ($this->resources as $resource)
		{
			if ($this->resourceManager->isTypeScriptResource($resource))
				$typeScripts[] = $resource;
		}

		if (!empty($typeScripts))
		{
			if ($this->isPackageModified() || $this->isResourceSourceModified($typeScriptDependencies) ||
				$this->isResourceOutputModified($typeScripts) || $this->isDtsOutputModified())
			{
				echo "Compiling TypeScript for package " . $this->getName() . "\n";
				JWSDK_Util_Ts::build($this, $typeScripts, $this->globalConfig, $this->buildCache);
			}
			$dtsName = $this->getDtsOutputName();
			$dtsResource = $this->resourceManager->getResourceByDefinition($dtsName);
			$this->buildCache->output->setPackageDtsOutputMtime(
				$this->getName(), $dtsResource->getSourceFile()->getMtime());
		}

		$result = array();

		if ($this->globalConfig->isDynamicLoader())
			$result[] = $this->createHeader();

		foreach ($typeScriptDependencies as $resource)
		{
			$this->buildCache->output->setPackageResourceMtime(
				$this->getName(), $resource->getName(), $resource->getSourceFile()->getMtime());
		}

		foreach ($this->resources as $resource)
		{
			$name = $resource->getName();
			$this->buildCache->output->setPackageResourceMtime(
				$this->getName(), $name, $resource->getSourceFile()->getMtime());

			$output = $this->resourceManager->convertResource($resource);
			if (!$output)
				continue;

			$result[] = $output;
			$this->buildCache->output->setPackageResourceTargetMtime(
				$this->getName(), $name, $output->getMtime());
		}

		$this->buildCache->output->save();

		return $result;
	}

	protected function initCompressedFiles() // Array of JWSDK_File
	{
		$name = $this->getName();

		try
		{
			$result = ($this->globalConfig->isObfuscate() || $this->isCompressedModified()) ?
				$this->initCompressedFilesModified() : $this->initCompressedFilesUnmodified();

			foreach ($this->fileManager->getAttachers() as $type => $attacher)
			{
				if (!$this->hasFilesOfAttacher($type))
					continue;

				JWSDK_Util_File::copy($this->getMergePath($type), $this->getDistPath($type));
				JWSDK_Util_File::copy($this->getBuildPath($type), $this->getBuildDistPath($type));
			}

			JWSDK_Util_File::copy($this->getDtsOutputPath(), $this->getDtsDistPath());

			return $result;
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

	private function isPackageModified() // Boolean
	{
		$oldMtime = $this->buildCache->input->getPackageGlobalConfigMtime($this->getName());
		$newMtime = $this->globalConfig->getMtime();
		if ($oldMtime != $newMtime)
		{
			if (VERBOSE_VERSIONING)
				echo "-- Global config is modified ($oldMtime:$newMtime)\n";
			return true;
		}

		$oldMtime = $this->buildCache->input->getPackageConfigMtime($this->getName());
		$newMtime = JWSDK_Util_File::mtime($this->getConfigPath());
		if ($oldMtime != $newMtime)
		{
			if (VERBOSE_VERSIONING)
				echo "-- Package config is modified ($oldMtime:$newMtime)\n";
			return true;
		}

		return false;
	}

	private function isDtsOutputModified() // Boolean
	{
		$dtsPath = $this->getDtsOutputPath();
		$exists = file_exists($dtsPath);
		$oldMtime = $this->buildCache->input->getPackageDtsOutputMtime($this->getName());
		$newMtime = $exists ? JWSDK_Util_File::mtime($dtsPath) : null;
		if (!$exists || $oldMtime != $newMtime)
		{
			if (VERBOSE_VERSIONING)
				echo "-- d.ts output is modified ($oldMtime:$newMtime)\n";
			return true;
		}

		return false;
	}

	private function isResourceSourceModified($resources) // Boolean
	{
		foreach ($resources as $resource)
		{
			$name = $resource->getName();
			$oldMtime = $this->buildCache->input->getPackageResourceMtime($this->getName(), $name);
			$newMtime = $resource->getSourceFile()->getMtime();
			if ($oldMtime != $newMtime)
			{
				if (VERBOSE_VERSIONING)
					echo "-- Resource $name is modified ($oldMtime:$newMtime)\n";
				return true;
			}
		}

		return false;
	}

	private function isResourceOutputModified($resources) // Boolean
	{
		foreach ($resources as $resource)
		{
			$name = $resource->getName();
			$converter = $this->resourceManager->getConverter($resource->getType());
			$buildPath = $converter->getResourceBuildPath($resource, $this->globalConfig);
			$exists = file_exists($buildPath);
			$oldMtime = $this->buildCache->input->getPackageResourceTargetMtime($this->getName(), $name);
			$newMtime = $exists ? JWSDK_Util_File::mtime($buildPath) : null;
			if (!$exists || $oldMtime != $newMtime)
			{
				if (VERBOSE_VERSIONING)
					echo "-- Resource $name output is modified ($oldMtime:$newMtime)\n";
				return true;
			}
		}

		return false;
	}

	private function isCompressedModified() // Boolean
	{
		/*
		- Header file should be rebuilt before modification detection
		- All CSS-base resources must be converted to CSS files before modification detection
		*/
		$sourceFiles = $this->getSourceFiles();

		if ($this->buildCache->input->getPackageSdkVersion($this->getName()) !== JWSDK_Builder::VERSION) {
			if (VERBOSE_VERSIONING)
				echo "-- SDK version has been changed after compression\n";
			return true;
		}

		foreach ($this->fileManager->getAttachers() as $type => $attacher)
		{
			if (!$this->hasFilesOfAttacher($type))
				continue;

			$name = $this->getBuildName($type);
			$path = $this->fileManager->getFilePath($name);
			if (!file_exists($path))
			{
				if (VERBOSE_VERSIONING)
					echo "-- Compressed $type file does not exist\n";
				return true;
			}

			$compressionMtime = filemtime($path);
			if ($this->globalConfig->getMtime() > $compressionMtime)
			{
				if (VERBOSE_VERSIONING)
					echo "-- Global config has been changed after compression\n";
				return true;
			}

			if (JWSDK_Util_File::mtime($this->getConfigPath()) > $compressionMtime)
			{
				if (VERBOSE_VERSIONING)
					echo "-- Package config has been changed after compression\n";
				return true;
			}

			if ($this->globalConfig->isDynamicLoader())
			{
				if (JWSDK_Util_File::mtime($this->getHeaderPath()) > $compressionMtime)
				{
					if (VERBOSE_VERSIONING)
						echo "-- Package header is modified after compression\n";
					return true;
				}
			}

			foreach ($this->resources as $resource)
			{
				if ($resource->getSourceFile()->getAttacher() != $type)
					continue;

				if ($resource->getSourceFile()->getMtime() > $compressionMtime)
				{
					if (VERBOSE_VERSIONING)
						echo "-- Resource " . $resource->getName() . " is modified after compression\n";
					return true;
				}
			}

			foreach ($sourceFiles as $file)
			{
				if ($file->getAttacher() != $type)
					continue;

				$dependencies = $this->fileManager->getFileDependencies($file);
				foreach ($dependencies as $dependency) // JWSDK_File
				{
					if ($dependency->getMtime() > $compressionMtime)
					{
						if (VERBOSE_VERSIONING)
							echo "-- Dependency " . $dependency->getName() . " of " . $file->getName() . " is modified after compression\n";
						return true;
					}
				}
			}
		}

		return false;
	}

	private function initCompressedFilesModified() // Array of JWSDK_File
	{
		$name = $this->getName();

		$sourceFiles = $this->getSourceFiles();

		echo "Compressing package $name\n";

		$result = array();
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
		{
			if (!$this->hasFilesOfAttacher($type))
				continue;

			$buildName = $this->getBuildName($type);

			$contents = array();
			foreach ($sourceFiles as $file)
			{
				try
				{
					if ($file->getAttacher() == $type)
					{
						$fileContents = $this->fileManager->getFileContents($file);
						$contents[] = $attacher->beforeCompress($fileContents, $file->getName(), $buildName,
							$this->globalConfig, $this->fileManager);
					}
				}
				catch (JWSDK_Exception $e)
				{
					throw new JWSDK_Exception_FileProcessError($file->getName(), $e);
				}
			}

			$contents = implode("\n", $contents);

			$mergePath = $this->getMergePath($type);
			$buildPath = $this->getBuildPath($type);

			JWSDK_Util_File::write($mergePath, $contents);
			JWSDK_Util_File::mkdir($buildPath);
			JWSDK_Util_File::compress($this->globalConfig, $mergePath, $buildPath);

			$result[] = $this->fileManager->getFile($buildName, $type);
		}

		$this->buildCache->output->save();

		return $result;
	}

	private function initCompressedFilesUnmodified() // Array of JWSDK_File
	{
		$name = $this->getName();

		echo "Package $name is not modified, skipping...\n";

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

	private function getDistPath( // String
		$type) // String
	{
		return $this->globalConfig->getTempPath() . '/dist/' . $this->getName() . ".$type";
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

	private function getBuildDistPath( // String
		$type) // String
	{
		return $this->globalConfig->getTempPath() . '/dist/' . $this->getName() . ".min.$type";
	}

	private function getDtsOutputName() // String
	{
		return $this->globalConfig->getBuildUrl() . '/d.ts/' . $this->getName() . '.d.ts';
	}

	private function getDtsOutputPath() // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->getDtsOutputName();
	}

	private function getDtsDistPath() // String
	{
		return $this->globalConfig->getTempPath() . '/dist/' . $this->getName() . ".d.ts";
	}
}
