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
	private $variables;       // JWSDK_Variables
	
	private $resourceManager; // JWSDK_Resource_Manager
	private $packageManager;  // JWSDK_Package_Manager
	private $templateManager; // JWSDK_Template_Manager
	private $serviceManager;  // JWSDK_Service_Manager
	private $pageManager;     // JWSDK_Page_Manager
	
	public function __construct(
		$modeName) // String
	{
		$this->globalConfig = new JWSDK_GlobalConfig();
		$this->mode = JWSDK_Mode::getMode($modeName);
		
		$this->variables = new JWSDK_Variables();
		$this->variables->applyConfig($this->globalConfig->getModeConfigPath('common'));
		$this->variables->applyConfig($this->globalConfig->getModeConfigPath($this->mode->getConfigId()));
		
		$this->resourceManager = new JWSDK_Resource_Manager($this->globalConfig);
		$this->packageManager = new JWSDK_Package_Manager($this->globalConfig, $this->resourceManager);
		$this->templateManager = new JWSDK_Template_Manager($this->globalConfig);
		$this->serviceManager = new JWSDK_Service_Manager($this->globalConfig);
		$this->pageManager = new JWSDK_Page_Manager($this->globalConfig, $this->mode, $this->variables,
			$this->packageManager, $this->templateManager, $this->serviceManager, $this->resourceManager);
	}
	
	public function buildPages()
	{
		$this->pageManager->buildPages();
	}
}
