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

class JWSDK_GlobalConfig
{
	private $runDir;    // String
	private $configDir; // String
	private $json;      // Object
	private $mtime;     // Timestamp
	
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
			'urlPrefix'
		);
		
		$defaults = array(
			'templatesPath' => null,
			'pagesUrl' => null,
			'jsonPath' => null,
			'snippetsPath' => null
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
	
	public function getMtime() // Timestamp
	{
		return $this->mtime;
	}
}
