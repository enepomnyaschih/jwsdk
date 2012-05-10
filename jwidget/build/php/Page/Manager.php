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
	private $globalConfig;
	private $pageMap = array();
	
	public function __construct($globalConfig)
	{
		$this->globalConfig = $globalConfig;
	}
	
	public function addPage($page)
	{
		$this->pageMap[$page->getName()] = $page;
	}
	
	public function getPage($name)
	{
		return JWSDK_Util_Array::get($this->pageMap, $name);
	}
	
	public function readPages()
	{
		JWSDK_Log::logTo('build.log', "Reading page configs...");
		$this->readDir('');
	}
	
	private function readDir($path)
	{
		$fullPath = $this->globalConfig->getPageConfigsPath() . $path;
		
		if (is_file($fullPath))
		{
			$this->readFile($path);
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
				$this->readDir("$path/$child");
		}
		closedir($dir);
	}
	
	private function readFile($fullPath)
	{
		if (!preg_match('/\.json$/', $fullPath))
			return;
		
		// Delete initial slash and extension from path
		$name = substr($fullPath, 1, strrpos($fullPath, '.') - 1);
		
		$this->readPage($name, true);
	}
	
	private function readPage($name, $hasOutput)
	{
		$page = $this->getPage($name);
		if (isset($page))
		{
			if ($hasOutput)
				$page->setHasOutput(true);
			
			return $page;
		}
		
		$path = $this->globalConfig->getPageConfigPath($name);
		$contents = @file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open page config file (name: $name, path: $path)");
		
		$data = json_decode($contents, true);
		$page = new JWSDK_Page($name, $data);
		
		if (isset($data['base']))
		{
			$baseName = $data['base'];
			$base = $this->readPage($baseName, false);
			$page->applyBase($base);
		}
		
		$this->addPage($page);
		return $page;
	}
	/*
		$pageConfig = $this->getPageConfig($this->globalConfig->getPageConfigName($name));
		$buildPath  = $this->globalConfig->getPageBuildPath($name);
		
		if (!isset($pageConfig['template']))
			throw new Exception("Page template is undefined (page: $name)");
		
		$templateName = $pageConfig['template'];
		$template = $this->readPageTemplate($templateName);
		
		$variables = new JWSDK_Variables($this->variables, $pageConfig);
		
		$replaces = $variables->getCustom();
		$replaces['sources']  = $this->formatSources ($pageConfig, $name);
		$replaces['services'] = $this->formatServices($variables->getServices());
		
		$replaces['title'] =
			isset($pageConfig['title']) ? $pageConfig['title'] :
			(isset($replaces['title']) ? $replaces['title'] : '');
		
		$replaceKeys   = array_keys  ($replaces);
		$replaceValues = array_values($replaces);
		
		for ($i = 0; $i < count($replaceKeys); $i++)
			$replaceKeys[$i] = '${' . $replaceKeys[$i] . '}';
		
		$html = str_replace($replaceKeys, $replaceValues, $template);
		
		$output = JWSDK_Util_File::fopen_recursive($buildPath, 'w');
		if ($output === false)
			throw new Exception("Can't create linked page file (name: $name, path: $buildPath)");
		
		fwrite($output, $html);
		fclose($output);*/
}
