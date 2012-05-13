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
	private $globalConfig;    // JWSDK_GlobalConfig
	private $mode;            // JWSDK_Mode
	private $variables;       // JWSDK_Variables
	private $packageManager;  // JWSDK_Package_Manager
	private $templateManager; // JWSDK_Template_Manager
	private $serviceManager;  // JWSDK_Service_Manager
	private $resourceManager; // JWSDK_Resource_Manager
	private $pages = array(); // Map from name:String to JWSDK_Page
	
	// TODO: Move to JWSDK_Resource after merging CSS and JS together
	private $inclusions = array();
	
	public function __construct(
		$globalConfig,    // JWSDK_GlobalConfig
		$mode,            // JWSDK_Mode
		$variables,       // JWSDK_Variables
		$packageManager,  // JWSDK_Package_Manager
		$templateManager, // JWSDK_Template_Manager
		$serviceManager,  // JWSDK_Service_Manager
		$resourceManager) // JWSDK_Resource_Manager
	{
		$this->globalConfig = $globalConfig;
		$this->mode = $mode;
		$this->variables = $variables;
		$this->packageManager = $packageManager;
		$this->templateManager = $templateManager;
		$this->serviceManager = $serviceManager;
		$this->resourceManager = $resourceManager;
	}
	
	public function buildPages()
	{
		$this->buildDir('');
	}
	
	private function buildDir(
		$path) // String
	{
		$fullPath = $this->getPageConfigsPath() . $path;
		
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
			$page = $this->readPage("pages/$name");
			
			$templateName = $page->getTemplate();
			if (!$templateName)
				throw new JWSDK_Exception_PageTemplateIsUndefined();
			
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
			$contents = JWSDK_Util_File::file_get_contents($path);
			$data = json_decode($contents, true);
			$page = new JWSDK_Page($name, $data);
			
			if (isset($data['base']))
			{
				$baseName = $data['base'];
				$base = $this->readPage($baseName);
				$page->applyBase($base);
			}
			
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
		$variables = new JWSDK_Variables($this->variables, $page->getVariables());
		
		$replaces = $variables->getCustom();
		$replaces['sources']  = $this->buildSources($page);
		$replaces['services'] = $this->buildServices($variables->getServices());
		$replaces['title']    = $page->getTitle();
		
		$replaceKeys   = array_keys  ($replaces);
		$replaceValues = array_values($replaces);
		
		for ($i = 0; $i < count($replaceKeys); $i++)
			$replaceKeys[$i] = '${' . $replaceKeys[$i] . '}';
		
		return str_replace($replaceKeys, $replaceValues, $template->getContents());
	}
	
	private function buildSources( // String, inclusion HTML fragment
		$page) // JWSDK_Page
	{
		$name = $page->getName();
		
		$buf = array();
		foreach ($page->getCss() as $value)
			$buf[] = $this->buildSourceCss($value);
		
		$jspaths = array();
		foreach ($page->getJs() as $value)
			$buf[] = $this->buildSourcePackage($value, $jspaths);
		
		$jspathsUnique = array_unique($jspaths);
		if (count($jspaths) != count($jspathsUnique))
			throw new Exception("Duplicated resource detected while building page $name");
		
		return implode("\n", $buf);
	}
	
	private function buildSource( // String, inclusion HTML fragment
		$path,     // String
		$template) // String
	{
		if (isset($this->inclusions[$path]))
			return $this->inclusions[$path];
		
		$inclusion = $this->resourceManager->getResourceInclusionUrl($path);
		$inclusion = htmlspecialchars($inclusion);
		$inclusion = JWSDK_Util_String::tabulize(str_replace('%path%', $inclusion, $template), 2);
		$this->inclusions[$path] = $inclusion;
		return $inclusion;
	}
	
	private function buildSourceCss( // String, inclusion HTML fragment
		$path) // String
	{
		return $this->buildSource($path, '<link rel="stylesheet" type="text/css" href="%path%" />');
	}
	
	private function buildSourceJs( // String, inclusion HTML fragment
		$path) // String
	{
		return $this->buildSource($path, '<script type="text/javascript" charset="utf-8" src="%path%"></script>');
	}
	
	private function buildSourcePackage( // String, inclusion HTML fragment
		$name,     // String
		&$jspaths) // Array of name:String for resource duplications detection
	{
		if (preg_match('/\|auto$/', $name))
			$name = substr($name, 0, strrpos($name, '.')) . ($this->mode->isCompress() ?  '.min.js' : '.js');
		
		if (preg_match('/\.js$/', $name))
		{
			$jspaths[] = $name;
			return $this->buildSourceJs($name);
		}
		
		$package = $this->packageManager->readPackage($name);
		if ($this->mode->isCompress())
		{
			$compressResource = $this->packageManager->compressPackage($package);
			return $this->buildSourceJs($compressResource->getName());
		}
		
		$buf = array();
		foreach ($package->getResources() as $resource)
		{
			$name = $resource->getName();
			$jspaths[] = $name;
			$buf[] = $this->buildSourceJs($name);
		}
		
		return implode("\n", $buf);
	}
	
	private function buildServices($services)
	{
		$buf = array();
		foreach ($services as $name)
		{
			$service = $this->serviceManager->readService($name);
			$buf[] = $service->getContents();
		}
		
		return implode("\n", $buf);
	}
	
	private function getPageConfigsPath() // String
	{
		return $this->globalConfig->getConfigPath() . '/' . $this->globalConfig->getPagesFolder();
	}
	
	private function getPageConfigPath( // String
		$name) // String
	{
		return $this->globalConfig->getConfigPath() . "/$name.json";
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
