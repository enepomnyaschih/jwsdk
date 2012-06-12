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

class JWSDK_Page_Manager
{
	private $globalConfig;        // JWSDK_GlobalConfig
	private $mode;                // JWSDK_Mode
	private $packageManager;      // JWSDK_Package_Manager
	private $templateManager;     // JWSDK_Template_Manager
	private $resourceManager;     // JWSDK_Resource_Manager
	private $fileManager;         // JWSDK_File_Manager
	private $pages = array();     // Map from name:String to JWSDK_Page
	
	// TODO: Move to JWSDK_Resource after merging CSS and JS together
	private $resourceAttachUrls = array();
	
	public function __construct(
		$globalConfig,    // JWSDK_GlobalConfig
		$mode,            // JWSDK_Mode
		$packageManager,  // JWSDK_Package_Manager
		$templateManager, // JWSDK_Template_Manager
		$fileManager)     // JWSDK_File_Manager
	{
		$this->globalConfig = $globalConfig;
		$this->mode = $mode;
		$this->packageManager = $packageManager;
		$this->templateManager = $templateManager;
		$this->fileManager = $fileManager;
	}
	
	public function buildPages()
	{
		$this->buildDir('');
	}
	
	private function buildDir(
		$path) // String
	{
		$fullPath = $this->globalConfig->getPagesPath() . $path;
		
		if (is_file($fullPath))
		{
			$this->buildFile($path);
			return;
		}
		
		$dir = @opendir($fullPath);
		if ($dir === false)
		{
			JWSDK_Log::logTo('build.log', "Warning: Can't open pages folder (path: $dir)");
			return;
		}
		
		while (false !== ($child = readdir($dir)))
		{
			if ($child !== '.' && $child !== '..')
				$this->buildDir("$path/$child");
		}
		closedir($dir);
	}
	
	private function buildFile(
		$fullPath) // String
	{
		if (!preg_match('/\.json$/', $fullPath))
			return;
		
		// Delete initial slash and extension from path
		$name = substr($fullPath, 1, strrpos($fullPath, '.') - 1);
		
		$this->buildPage($name);
	}
	
	private function buildPage( // JWSDK_Page
		$name) // String
	{
		JWSDK_Log::logTo('build.log', "Building page $name");
		
		try
		{
			$page = $this->readPage($name);
			
			$templateName = $page->getTemplate();
			if (!$templateName)
			{
				$this->buildSources($page);
				return;
			}
			
			$template = $this->templateManager->readTemplate($templateName);
			$contents = $this->applyTemplate($template, $page);
			
			JWSDK_Util_File::write($this->getPageBuildPath($name), $contents);
			
			return $page;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PageBuildError($name, $e);
		}
	}
	
	private function readPage( // JWSDK_Page
		$name) // String
	{
		$page = $this->getPage($name);
		if ($page)
			return $page;
		
		try
		{
			$path = $this->getPageConfigPath($name);
			$data = JWSDK_Util_File::readJson($path, 'page config');
			$page = new JWSDK_Page($name, $data);
			$this->addPage($page);
			
			return $page;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_PageReadError($name, $e);
		}
	}
	
	private function applyTemplate( // String
		$template, // JWSDK_Template
		$page)     // JWSDK_Page
	{
		$replaces = $page->getParams();
		$replaces['sources'] = $this->buildSources($page);
		
		$replaceKeys   = array_keys  ($replaces);
		$replaceValues = array_values($replaces);
		
		for ($i = 0; $i < count($replaceKeys); $i++)
			$replaceKeys[$i] = '${' . $replaceKeys[$i] . '}';
		
		return str_replace($replaceKeys, $replaceValues, $template->getContents());
	}
	
	private function buildSources( // String, attachment HTML fragment
		$page) // JWSDK_Page
	{
		$rootPackageName = $page->getPackage();
		if (!$rootPackageName)
			return '';
		
		$name = $page->getName();
		
		$attaches = array(); // Map from attacherType:String to Array of String
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
			$attaches[$type] = array();
		
		$packages = array_merge(
			array($this->packageManager->getLibraryPackage()),
			$this->packageManager->readPackagesWithDependencies(array($rootPackageName))
		);
		
		foreach ($packages as $package)
		{
			$files = $this->mode->isCompress() ?
				$package->getCompressedFiles() :
				$package->getSourceFiles();
			
			foreach ($files as $file)
			{
				$attacherId = $file->getAttacher();
				
				$url = $this->fileManager->getFileUrl($file);
				$attachStr = $this->fileManager->getAttacher($attacherId)->format($url);
				
				array_push($attaches[$attacherId], JWSDK_Util_String::tabulize($attachStr, 2, "\t"));
			}
		}
		
		$buf = array();
		foreach ($attaches as $lines)
		{
			foreach ($lines as $line)
				$buf[] = $line;
		}
		
		return implode("\n", $buf);
	}
	
	private function getPageConfigPath( // String
		$name) // String
	{
		return $this->globalConfig->getPagesPath() . "/$name.json";
	}
	
	private function getPagesBuildPath() // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->globalConfig->getPagesUrl();
	}
	
	private function getPageBuildPath( // String
		$name) // String
	{
		return $this->getPagesBuildPath() . "/$name.html";
	}
	
	private function addPage(
		$page) // JWSDK_Page
	{
		$this->pages[$page->getName()] = $page;
	}
	
	private function getPage( // JWSDK_Page
		$name) // String
	{
		return JWSDK_Util_Array::get($this->pages, $name);
	}
}
