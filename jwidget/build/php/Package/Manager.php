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
	private $globalConfig;
	private $packageMap = array();
	
	public function __construct($globalConfig)
	{
		$this->globalConfig = $globalConfig;
	}
	
	public function addPackage($package)
	{
		$this->packageMap[$package->getName()] = $package;
	}
	
	public function readPackages()
	{
		JWSDK_Log::logTo('build.log', 'Reading JS lists...');
		$this->readDir('');
	}
	
	public function compressPackages()
	{
		JWSDK_Log::logTo('build.log', 'Compressing JS lists...');
	}
	
	private function readDir($path)
	{
		$fullPath = $this->globalConfig->getPackageConfigsPath() . $path;
		
		if (is_file($fullPath))
		{
			$this->readFile($path);
			return;
		}
		
		$dir = @opendir($fullPath);
		if ($dir === false)
			throw new Exception("Can't open jslists folder (path: $dir)");
		
		while (false !== ($child = readdir($dir)))
		{
			if ($child !== '.' && $child !== '..')
				$this->readDir("$path/$child");
		}
		closedir($dir);
	}
	
	private function readFile($fullPath)
	{
		if (!preg_match('/\.jslist$/', $fullPath))
			return;
		
		// Delete initial slash and extension from path
		$name = substr($fullPath, 1, strrpos($fullPath, '.') - 1);
		
		$path      = $this->globalConfig->getPackagePath     ($name);
		$buildPath = $this->globalConfig->getPackageBuildPath($name);
		$mergePath = $this->globalConfig->getPackageMergePath($name);
		
		$contents = @file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open jslist file (name: $name, path: $path)");
		
		$contents = JWSDK_Util_String::removeComments($contents);
		
		$scripts = explode("\n", JWSDK_Util_String::smoothCrlf($contents));
		$scripts = self::removeEmptyStrings($scripts);
		
		$package = new JWSDK_Package($name);
		for ($i = 0; $i < count($scripts); ++$i)
			$package->addResource(JWSDK_Resource::fromString($scripts[$i]));
		
		$this->addPackage($package);
	}
}
