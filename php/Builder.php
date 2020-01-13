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
