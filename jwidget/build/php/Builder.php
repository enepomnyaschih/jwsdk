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

class JWSDK_Builder
{
	private $globalConfig;    // JWSDK_GlobalConfig
	private $mode;            // JWSDK_Mode
	
	private $fileManager;     // JWSDK_File_Manager
	private $resourceManager; // JWSDK_Resource_Manager
	private $packageManager;  // JWSDK_Package_Manager
	private $templateManager; // JWSDK_Template_Manager
	private $pageManager;     // JWSDK_Page_Manager
	
	public function __construct(
		$modeName) // String
	{
		$this->globalConfig = new JWSDK_GlobalConfig();
		$this->mode = JWSDK_Mode::getMode($modeName);
		
		$this->fileManager = new JWSDK_File_Manager($this->globalConfig);
		$this->resourceManager = new JWSDK_Resource_Manager($this->globalConfig, $this->fileManager);
		$this->packageManager = new JWSDK_Package_Manager($this->globalConfig, $this->resourceManager, $this->fileManager);
		$this->templateManager = new JWSDK_Template_Manager($this->globalConfig, $this->mode);
		$this->pageManager = new JWSDK_Page_Manager($this->globalConfig, $this->mode,
			$this->packageManager, $this->templateManager, $this->fileManager);
	}
	
	public function buildPages()
	{
		$this->pageManager->buildPages();
	}
}
