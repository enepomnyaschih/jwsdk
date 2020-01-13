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

class JWSDK_GlobalConfig
{
	private $runDir;    // String
	private $configDir; // String
	private $json;      // Object
	private $mtime;     // Timestamp

	private $notObfuscateNamespaces;
	private $notObfuscateMembers;

	public function __construct(
		$runDir,    // String
		$configDir, // String
		$name)      // String
	{
		$path = "$configDir/$name";

		$this->runDir = $runDir;
		$this->configDir = $configDir;
		$this->json = JWSDK_Util_File::readJson($path, 'global config');
		$this->mtime = JWSDK_Util_File::mtime($path);

		$stringFields = array(
			'packagesPath',
			'pagesPath',
			'templatesPath',
			'publicPath',
			'buildUrl',
			'pagesUrl',
			'jsonPath',
			'snippetsPath',
			'tempPath',
			'tsTarget',
			'urlPrefix',
			'obfuscateDebugFormat',
			'javaCmd'
		);

		$defaults = array(
			'templatesPath' => null,
			'pagesUrl' => null,
			'jsonPath' => null,
			'snippetsPath' => null,
			'tsTarget' => 'ES5',
			'obfuscateDebugFormat' => null,
			'javaCmd' => 'java'
		);

		foreach ($stringFields as $field)
		{
			if (!isset($this->json[$field]))
			{
				if (!array_key_exists($field, $defaults))
				{
					throw new JWSDK_Exception_InvalidFileFormat(
						'config.json', 'global config', "$field is required");
				}
				$this->json[$field] = $defaults[$field];
			}
			else if (!is_string($this->json[$field]))
			{
				throw new JWSDK_Exception_InvalidFileFormat(
					'config.json', 'global config', "$field must be string");
			}
		}

		$tsTargets = JWSDK_Resource_Converter_Ts::$availableTargets;
		if (!in_array($this->json['tsTarget'], $tsTargets))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'tsTarget must be one of ' . implode(', ', $tsTargets));
		}

		if (!isset($this->json['dynamicLoader']))
			$this->json['dynamicLoader'] = false;

		if (!is_bool($this->json['dynamicLoader']))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'dynamicLoader must be boolean');
		}

		if (!isset($this->json['conversionLog']))
			$this->json['conversionLog'] = false;

		if (!is_bool($this->json['conversionLog']))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'conversionLog must be boolean');
		}

		if (!isset($this->json['obfuscate']))
			$this->json['obfuscate'] = false;

		if (!is_bool($this->json['obfuscate']))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'obfuscate must be boolean');
		}

		if (!isset($this->json['listObfuscatedMembers']))
			$this->json['listObfuscatedMembers'] = false;

		if (!is_bool($this->json['listObfuscatedMembers']))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'listObfuscatedMembers must be boolean');
		}

		if (!isset($this->json['embedDataUri']))
			$this->json['embedDataUri'] = false;

		if (!is_bool($this->json['embedDataUri']))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'embedDataUri must be boolean');
		}

		if (!isset($this->json['dataUriMaxHits']))
			$this->json['dataUriMaxHits'] = 1;

		if (!is_int($this->json['dataUriMaxHits']) || ($this->json['dataUriMaxHits'] <= 0))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'dataUriMaxHits must be positive integer');
		}

		if (!isset($this->json['dataUriMaxSize']))
			$this->json['dataUriMaxSize'] = 30720;

		if (!is_int($this->json['dataUriMaxSize']) || ($this->json['dataUriMaxSize'] <= 0))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'dataUriMaxSize must be positive integer');
		}

		if (!isset($this->json['mimeTypes']))
			$this->json['mimeTypes'] = array();

		$mimeTypes = $this->json['mimeTypes'];
		if (!is_array($mimeTypes))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'mimeTypes must be dictionary of strings');
		}

		foreach ($mimeTypes as $key => $value)
		{
			if (!is_string($value))
			{
				throw new JWSDK_Exception_InvalidFileFormat(
					'config.json', 'global config', 'mimeTypes must be dictionary of strings');
			}
		}

		if (!isset($this->json['notObfuscateMembers']))
			$this->json['notObfuscateMembers'] = array();

		$notObfuscateMembers = &$this->json['notObfuscateMembers'];
		if (!is_array($notObfuscateMembers))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'notObfuscateMembers must be array of strings');
		}

		foreach ($notObfuscateMembers as $value)
		{
			if (!is_string($value))
			{
				throw new JWSDK_Exception_InvalidFileFormat(
					'config.json', 'global config', 'notObfuscateMembers must be array of strings');
			}
		}

		$this->notObfuscateMembers = $this->mergePatterns($notObfuscateMembers);

		if (!isset($this->json['notObfuscateNamespaces']))
			$this->json['notObfuscateNamespaces'] = array();

		$notObfuscateNamespaces = $this->json['notObfuscateNamespaces'];
		if (!is_array($notObfuscateNamespaces))
		{
			throw new JWSDK_Exception_InvalidFileFormat(
				'config.json', 'global config', 'notObfuscateNamespaces must be array of strings');
		}

		foreach ($notObfuscateNamespaces as $value)
		{
			if (!is_string($value))
			{
				throw new JWSDK_Exception_InvalidFileFormat(
					'config.json', 'global config', 'notObfuscateNamespaces must be array of strings');
			}
		}

		$this->notObfuscateNamespaces = $this->mergePatterns($notObfuscateNamespaces);
	}

	public function isTemplateProcessorEnabled() // Boolean
	{
		return isset($this->json['pagesUrl']) && isset($this->json['templatesPath']);
	}

	public function isJsonProcessorEnabled() // Boolean
	{
		return isset($this->json['jsonPath']);
	}

	public function isSnippetsProcessorEnabled() // Boolean
	{
		return isset($this->json['snippetsPath']);
	}

	public function getRunDir() // String
	{
		return $this->runDir;
	}

	public function getPackagesPath() // String
	{
		return $this->configDir . '/' . $this->json['packagesPath'];
	}

	public function getPagesPath() // String
	{
		return $this->configDir . '/' . $this->json['pagesPath'];
	}

	// requires template processor on!
	public function getTemplatesPath() // String
	{
		return $this->configDir . '/' . $this->json['templatesPath'];
	}

	public function getPublicPath() // String
	{
		return $this->configDir . '/' . $this->json['publicPath'];
	}

	public function getBuildUrl() // String
	{
		return $this->json['buildUrl'];
	}

	// requires template processor on!
	public function getPagesUrl() // String
	{
		return $this->json['pagesUrl'];
	}

	// requires json processor on!
	public function getJsonPath() // String
	{
		return $this->configDir . '/' . $this->json['jsonPath'];
	}

	// requires snippets processor on!
	public function getSnippetsPath() // String
	{
		return $this->configDir . '/' . $this->json['snippetsPath'];
	}

	public function getTempPath() // String
	{
		return $this->configDir . '/' . $this->json['tempPath'];
	}

	public function getTsTarget() // String
	{
		return $this->json['tsTarget'];
	}

	public function getUrlPrefix() // String
	{
		return $this->json['urlPrefix'];
	}

	public function isDynamicLoader() // Boolean
	{
		return $this->json['dynamicLoader'];
	}

	public function isConversionLog() // Boolean
	{
		return $this->json['conversionLog'];
	}

	public function isObfuscate() // Boolean
	{
		return $this->json['obfuscate'];
	}

	public function isListObfuscatedMembers() // Boolean
	{
		return $this->json['listObfuscatedMembers'];
	}

	public function getObfuscateDebugFormat() // String
	{
		return $this->json['obfuscateDebugFormat'];
	}

	public function isEmbedDataUri() // Boolean
	{
		return $this->json['embedDataUri'];
	}

	public function getDataUriMaxHits() // Integer
	{
		return $this->json['dataUriMaxHits'];
	}

	public function getDataUriMaxSize() // Integer
	{
		return $this->json['dataUriMaxSize'];
	}

	public function getMimeType( // String
		$extension) // String
	{
		$mimeTypes = $this->json['mimeTypes'];
		return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : "image/$extension";
	}

	public function isNotObfuscateMember( // Boolean
		$member) // String
	{
		return preg_match($this->notObfuscateMembers, $member);
	}

	public function isNotObfuscateNamespace( // Boolean
		$namespace) // String
	{
		return preg_match($this->notObfuscateNamespaces, $namespace);
	}

	public function getJavaCmd() // String
	{
		return $this->json['javaCmd'];
	}

	public function getMtime() // Timestamp
	{
		return $this->mtime;
	}

	private function mergePatterns( // String
		$patterns) // Array<String>
	{
		$result = '~^(?:';
		$first = true;
		foreach ($patterns as $pattern) {
			if ($first) {
				$first = false;
			} else {
				$result .= '|';
			}
			$result .= '(?:' . $pattern . ')';
		}
		$result .= ')$~';
		return $result;
	}
}
