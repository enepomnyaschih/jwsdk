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
	private $globalConfig;   // JWSDK_GlobalConfig
	private $mode;           // JWSDK_Mode
	private $variables;      // JWSDK_Variables
	
	private $packageManager; // JWSDK_Package_Manager
	
	//private $jslists;      // Map from String(name) to String(scripts to include)
	//private $jspaths;      // Map from String(jslistName) to Array of String(jsPath)
	//private $includes;     // Map from String(name) to Dictionary
	//private $services;     // Map from String(name) to String(html)
	//private $templates;    // Map from String(name) to String(html)
	
	public function build($modeName)
	{
		$this->globalConfig = new JWSDK_GlobalConfig();
		$this->mode = JWSDK_Mode::getMode($modeName);
		
		$this->variables = new JWSDK_Variables();
		$this->variables->applyConfig($this->globalConfig->getModeConfigPath('common'));
		$this->variables->applyConfig($this->globalConfig->getModeConfigPath($this->mode->getConfigId()));
		
		$this->packageManager = new JWSDK_Package_Manager($this->globalConfig);
		$this->packageManager->readPackages();
		
		$this->jspaths   = array();
		$this->includes  = array();
		$this->services  = array();
		$this->templates = array();
		
		$this->link();
	}
	
	private function compress()
	{
		if ($this->mode->isCompress())
			JWSDK_Log::logTo('build.log', 'Compressing JS lists...');
		else
			JWSDK_Log::logTo('build.log', 'Reading JS lists...');
		
		$this->compressDir('');
	}

	private function compressDir($path)
	{
		$fullPath = $this->globalConfig->getPackageConfigsPath() . $path;
		
		if (is_file($fullPath))
		{
			$this->compressFile($path);
			return;
		}
		
		$dir = @opendir($fullPath);
		if ($dir === false)
			throw new Exception("Can't open jslists folder (path: $dir)");
		
		while (false !== ($child = readdir($dir)))
		{
			if ($child !== '.' && $child !== '..')
				$this->compressDir("$path/$child");
		}
		closedir($dir);
	}
	
	private function compressFile($path)
	{
		if (!preg_match('/\.jslist$/', $path))
			return;
		
		$compress = $this->mode->isCompress();
		
		// Delete extension from path
		$name = substr($path, 1, strrpos($path, '.') - 1);
		
		if ($this->mode->isCompress())
			JWSDK_Log::logTo('build.log', "Compressing $name");
		
		$packagePath = $this->globalConfig->getPackagePath($name);
		$buildPath   = $this->globalConfig->getPackageBuildPath($name);
		$mergePath   = $this->globalConfig->getPackageMergePath($name);
		
		$contents = @file_get_contents($packagePath);
		if ($contents === false)
			throw new Exception("Can't open jslist file (name: $name, path: $packagePath)");
		
		$contents = JWSDK_Util_String::removeComments($contents);
		
		$scripts = explode("\n", str_replace("\r", "\n", $contents));
		$scripts = self::removeEmptyStrings($scripts);
		
		for ($i = 0; $i < count($scripts); ++$i)
			$scripts[$i] = JWSDK_Converter::convert($scripts[$i], $name, $this->globalConfig);
		
		$this->jspaths[$name] = $scripts;
		
		if ($compress)
		{
			$output = JWSDK_Util_File::fopen_recursive($mergePath, 'w');
			if ($output === false)
				throw new Exception("Can't create temporary merged js file (name: $name, path: $mergePath)");
		}
		
		$includeBuf = array();
		for ($i = 0; $i < count($scripts); ++$i)
		{
			$script = $scripts[$i];
			$includeBuf[] = $this->includeJs($script);
			
			if (!$compress)
				continue;
			
			$scriptPath = $this->globalConfig->getResourceSourcePath($script);
			$scriptContent = @file_get_contents($scriptPath);
			if ($scriptContent === false)
				throw new Exception("Can't open js file (path: $scriptPath)");
			
			fwrite($output, $scriptContent . "\n");
		}
		
		if ($compress)
		{
			fclose($output);
			
			$buildDir = substr($buildPath, 0, strrpos($buildPath, '/'));
			if (!is_dir($buildDir))
				@mkdir($buildDir, 0755, 1);
			
			$yuiOutput = array();
			$yuiStatus = 0;
			
			$command = "java -jar yuicompressor.jar $mergePath -o $buildPath --charset utf-8 --line-break 8000 2>> yui.log";
			exec($command, $yuiOutput, $yuiStatus);
			
			if ($yuiStatus != 0)
				throw new Exception("Error while running YUI Compressor (name: $name, input: $mergePath, output: $buildPath). See signin/build/yui.log for details");
		}
		
		if ($this->mode->isLinkMin())
			$this->packages[$name] = $this->includeJs($this->globalConfig->getPackageBuildUrl($name));
		else
			$this->packages[$name] = implode("\n", $includeBuf);
	}
	
	private function link()
	{
		if (!$this->mode->isLink())
			return;
		
		JWSDK_Log::logTo('build.log', 'Linking pages...');
		
		$this->linkDir('');
	}

	private function linkDir($path)
	{
		$fullPath = $this->globalConfig->getPageConfigsPath() . $path;
		
		if (is_file($fullPath))
		{
			$this->linkFile($path);
			return;
		}
		
		$dir = @opendir($fullPath);
		if ($dir === false)
			throw new Exception("Can't open page configs folder (path: $dir)");
		
		while (false !== ($child = readdir($dir)))
		{
			if ($child !== '.' && $child !== '..')
				$this->linkDir("$path/$child");
		}
		closedir($dir);
	}
	
	private function linkFile($path)
	{
		if (!preg_match('/\.json$/', $path))
			return;
		
		// Delete extension from path
		$name = substr($path, 1, strrpos($path, '.') - 1);
		
		$pageConfig = $this->readPageConfig($this->globalConfig->getPageConfigName($name));
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
		fclose($output);
	}
	
	private function readPageConfig($name)
	{
		if (isset($this->includes[$name]))
			return $this->includes[$name];
		
		$path = $this->globalConfig->getPageConfigPath($name);
		$contents = @file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open page config file (name: $name, path: $path)");
		
		$data = json_decode($contents, true);
		if (isset($data['base']))
		{
			$baseName = $data['base'];
			$base = $this->readPageConfig($baseName);
			
			foreach ($base as $key => $value)
			{
				if (is_array($value))
				{
					$data[$key] = isset($data[$key]) ? array_merge($value, $data[$key]) : $value;
					continue;
				}
				
				if (isset($data[$key]))
					continue;
				
				$data[$key] = $value;
			}
		}
		
		$this->includes[$name] = $data;
		
		return $data;
	}
	
	private function readPageTemplate($name)
	{
		if (isset($this->templates[$name]))
			return $this->templates[$name];
		
		$path = $this->globalConfig->getTemplatePath($name);
		$contents = @file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open template file (name: $name, path: $path)");
		
		$this->templates[$name] = $contents;
		return $contents;
	}
	
	function includeSource($path, $template)
	{
		$path = $this->globalConfig->getResourceInclusionUrl($path);
		$path = htmlspecialchars($path);
		return '        ' . str_replace('%path%', $path, $template);
	}
	
	function includeCss($path)
	{
		return $this->includeSource($path, '<link rel="stylesheet" type="text/css" href="%path%" />');
	}
	
	function includeJs($path)
	{
		return $this->includeSource($path, '<script type="text/javascript" charset="utf-8" src="%path%"></script>');
	}
	
	function includePackage($path, &$jspaths)
	{
		if (preg_match('/\|auto$/', $path))
			$path = substr($path, 0, strrpos($path, '.')) . ($this->mode->isLinkMin() ?  '.min.js' : '.js');
		
		if (preg_match('/\.js$/', $path))
		{
			$jspaths[] = $path;
			return $this->includeJs($path);
		}
		
		if (!isset($this->jspaths[$path]))
			throw new Exception("Can't find JS list (jslist: $path)");
		
		foreach ($this->jspaths[$path] as $jspath)
			$jspaths[] = $jspath;
		
		return $this->packages[$path];
	}
	
	private function formatSources($pageConfig, $path)
	{
		$buf = array();
		if (isset($pageConfig['css']))
		{
			foreach ($pageConfig['css'] as $value)
				$buf[] = $this->includeCss($value);
		}
		
		$jspaths = array();
		if (isset($pageConfig['js']))
		{
			foreach ($pageConfig['js'] as $value)
				$buf[] = $this->includePackage($value, $jspaths);
		}
		
		$jspathsUnique = array_unique($jspaths);
		if (count($jspaths) != count($jspathsUnique))
			throw new Exception("Duplicated JS file detected while linking $path");
		
		return implode("\n", $buf);
	}
	
	private function formatServices($services)
	{
		$buf = array();
		for ($i = 0; $i < count($services); $i++)
			$buf[] = $this->readService($services[$i]);
		
		return implode("\n", $buf);
	}
	
	private function readService($name)
	{
		if (isset($this->services[$name]))
			return $this->services[$name];
		
		$path = $this->globalConfig->getServicePath($name);
		$contents = @file_get_contents($path);
		if ($contents === false)
			throw new Exception("Can't open service file (name: $name, path: $path)");
		
		$this->services[$name] = $contents;
		return $contents;
	}
	
	private static function removeEmptyStrings($source)
	{
		$result = array();
		foreach ($source as $value)
		{
			$row = trim($value);
			if (empty($row))
				continue;
			
			$result[] = $row;
		}
		
		return $result;
	}
}
