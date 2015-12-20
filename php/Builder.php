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

class JWSDK_Builder
{
	const VERSION = '0.7.2';

	private $globalConfig;    // JWSDK_GlobalConfig
	private $mode;            // JWSDK_Mode
	private $buildCache;      // JWSDK_BuildCache

	private $fileManager;     // JWSDK_File_Manager
	private $resourceManager; // JWSDK_Resource_Manager
	private $packageManager;  // JWSDK_Package_Manager
	private $templateManager; // JWSDK_Template_Manager
	private $pageManager;     // JWSDK_Page_Manager

	public function __construct(
		$runPath,    // String
		$mode,       // JWSDK_Mode
		$configPath) // String
	{
		$runDir = JWSDK_Util_File::getDirectory($runPath);

		$slashIndex = strrpos($configPath, '/');
		if ($slashIndex === false)
		{
			$configDir  = '.';
			$configName = $configPath;
		}
		else
		{
			$configDir  = substr($configPath, 0, $slashIndex);
			$configName = substr($configPath, $slashIndex + 1);
		}

		$this->globalConfig = new JWSDK_GlobalConfig($runDir, $configDir, $configName);
		$this->mode = $mode;
		$this->buildCache = new JWSDK_BuildCache($this->globalConfig->getTempPath() . '/buildcache.json');

		$this->fileManager = new JWSDK_File_Manager($this->globalConfig);
		$this->resourceManager = new JWSDK_Resource_Manager($this->globalConfig, $this->fileManager);
		$this->packageManager = new JWSDK_Package_Manager($this->globalConfig, $this->mode, $this->buildCache, $this->resourceManager, $this->fileManager);
		$this->templateManager = new JWSDK_Template_Manager($this->globalConfig, $this->mode);
		$this->pageManager = new JWSDK_Page_Manager($this->globalConfig, $this->mode,
			$this->packageManager, $this->templateManager, $this->fileManager);
	}

	public function buildPages()
	{
		$this->pageManager->buildPages();
		if ($this->globalConfig->isObfuscate()) {
			$tempPath = $this->globalConfig->getTempPath();
			$jsMembers = $this->fileManager->getJsMembers();
			JWSDK_Util_File::write("$tempPath/obfuscation-map.json", json_encode($jsMembers));
			if ($this->globalConfig->isListObfuscatedMembers()) {
				$runDir = $this->globalConfig->getRunDir();
				$jsMemberArray = array_keys($jsMembers);
				sort($jsMemberArray);
				JWSDK_Util_File::write('members.txt', implode("\n", $jsMemberArray) . "\n");
			}
		}
	}

	public function saveCache()
	{
		echo "Saving build cache...\n";
		$this->buildCache->output->save();
	}
}
