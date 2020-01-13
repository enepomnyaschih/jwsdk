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
			echo "Warning: Can't open pages folder (path: $dir)\n";
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
		echo "Building page $name\n";

		try
		{
			$page = $this->readPage($name);
			$sources = $this->buildSources($page);

			$this->processJson($page, $sources);

			if ($this->globalConfig->isTemplateProcessorEnabled() ||
			    $this->globalConfig->isSnippetsProcessorEnabled())
			{
				$snippets = $this->buildSnippets($sources);

				$this->processSnippets($page, $snippets);
				$this->processTemplate($page, $snippets);
			}

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

	private function buildSources( // Map from attacherId:String to Array of url:String
		$page) // JWSDK_Page
	{
		$result = array();
		foreach ($this->fileManager->getAttachers() as $type => $attacher)
			$result[$type] = array();

		$rootPackageName = $page->getPackage();
		if (!$rootPackageName)
			return $result;

		$packages = $this->packageManager->readPackagesWithDependencies(array($rootPackageName));
		if ($this->globalConfig->isDynamicLoader())
			array_unshift($packages, $this->packageManager->getLibraryPackage());

		foreach ($packages as $package)
		{
			$files = $this->mode->isCompress() ?
				$package->getCompressedFiles() :
				$package->getSourceFiles();

			foreach ($files as $file)
				array_push($result[$file->getAttacher()], $this->fileManager->getFileUrl($file));
		}

		return $result;
	}

	private function buildSnippets( // Map from attacherId:String to html:String
		$sources) // Map from attacherId:String to Array of url:String
	{
		$result = array();
		foreach ($sources as $attacherId => $urls)
		{
			$lines = array();
			foreach ($urls as $url)
			{
				$attachStr = $this->fileManager->getAttacher($attacherId)->format($url);
				array_push($lines, JWSDK_Util_String::tabulize($attachStr, 2, "\t"));
			}
			$result[$attacherId] = implode("\n", $lines);
		}
		return $result;
	}

	private function processJson(
		$page,    // JWSDK_Page
		$sources) // Map from attacherId:String to Array of url:String
	{
		if (!$this->globalConfig->isJsonProcessorEnabled())
			return;

		JWSDK_Util_File::write($this->getJsonBuildPath($page), json_encode($sources));
	}

	private function processSnippets(
		$page,     // JWSDK_Page
		$snippets) // Map from attacherId:String to html:String
	{
		if (!$this->globalConfig->isSnippetsProcessorEnabled())
			return;

		foreach ($snippets as $attacherId => $html)
			JWSDK_Util_File::write($this->getSnippetsBuildPath($page, $attacherId), $html);
	}

	private function processTemplate(
		$page,     // JWSDK_Page
		$snippets) // Map from attacherId:String to html:String
	{
		if (!$this->globalConfig->isTemplateProcessorEnabled())
			return;

		$templateName = $page->getTemplate();
		if (!$templateName)
			return;

		$template = $this->templateManager->readTemplate($templateName);

		$replaces = $page->getParams();
		$snippetsArray = array();
		foreach ($snippets as $attacherId => $html)
		{
			$replaces[$attacherId] = $html;
			$snippetsArray[] = $html;
		}
		$replaces['sources'] = implode("\n", $snippetsArray);

		$replaceKeys   = array_keys  ($replaces);
		$replaceValues = array_values($replaces);

		for ($i = 0; $i < count($replaceKeys); $i++)
			$replaceKeys[$i] = '${' . $replaceKeys[$i] . '}';

		$contents = str_replace($replaceKeys, $replaceValues, $template->getContents());
		JWSDK_Util_File::write($this->getPageBuildPath($page), $contents);
	}

	private function getPageConfigPath( // String
		$name) // String
	{
		return $this->globalConfig->getPagesPath() . "/$name.json";
	}

	private function getJsonBuildPath( // String
		$page) // JWSDK_Page
	{
		return $this->globalConfig->getJsonPath() . '/' . $page->getName() . '.json';
	}

	private function getSnippetsBuildPath( // String
		$page,       // JWSDK_Page
		$attacherId) // String
	{
		return $this->globalConfig->getSnippetsPath() . '/' . $page->getName() . ".$attacherId.html";
	}

	private function getPagesBuildPath() // String
	{
		return $this->globalConfig->getPublicPath() . '/' . $this->globalConfig->getPagesUrl();
	}

	private function getPageBuildPath( // String
		$page) // JWSDK_Page
	{
		return $this->getPagesBuildPath() . '/' . $page->getOutputName();
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
