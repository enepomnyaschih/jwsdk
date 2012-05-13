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

class JWSDK_Resource_Manager
{
	private $globalConfig;         // JWSDK_GlobalConfig
	private $converters = array(); // Map from type:String to JWSDK_Resource_Converter
	
	public function __construct(
		$globalConfig) // JWSDK_GlobalConfig
	{
		$this->globalConfig = $globalConfig;
		
		$this->registerConverter(new JWSDK_Resource_Converter_JwHtml());
		$this->registerConverter(new JWSDK_Resource_Converter_Txt());
		$this->registerConverter(new JWSDK_Resource_Converter_Html());
		$this->registerConverter(new JWSDK_Resource_Converter_Json());
		$this->registerConverter(new JWSDK_Resource_Converter_Js());
	}
	
	public function getResourceByDefinition( // JWSDK_Resource
		$str) // String
	{
		$tokens = explode(":", $str);
		$name = trim($tokens[0]);
		$converter = $this->getConverterByResourceName($name);
		if (!$converter)
			throw new Exception("Unknown resource type (name: $name)");
		
		if (count($tokens) == 1)
		{
			$params = array();
		}
		else
		{
			$params = explode(",", $tokens[1]);
			for ($i = 0; $i < count($params); $i++)
				$params[$i] = trim($params[$i]);
		}
		
		return new JWSDK_Resource($name, $converter->getType(), $params);
	}
	
	public function convertResource( // JWSDK_Resource
		$resource) // JWSDK_Resource
	{
		$name = $resource->getName();
		
		$converter = $this->getConverter($resource->getType());
		if (!$converter->isConvertion())
			return $resource;
		
		JWSDK_Log::logTo('build.log', "Converting resource $name");
		
		$sourceContents = $this->getResourceContents($resource);
		$buildContents = $converter->convertResource($name, $sourceContents, $params);
		
		$buildName = $this->globalConfig->getResourceBuildName($name);
		$buildPath = $this->globalConfig->getResourceBuildPath($name);
		
		$buildFile = JWSDK_Util_File::fopen_recursive($buildPath, 'w');
		if ($buildFile === false)
			throw new Exception("Can't create resource target file (name: $name, target: $buildName)");
		
		fwrite($buildFile, $buildContents);
		fclose($buildFile);
		
		return new JWSDK_Resource($buildName, 'js');
	}
	
	public function getResourceContents( // String
		$resource) // JWSDK_Resource
	{
		$name = $resource->getName();
		$sourcePath = $this->globalConfig->getResourceSourcePath($name);
		$contents = JWSDK_Util_File::file_get_contents($sourcePath);
		if ($contents === false)
			throw new Exception("Can't open resource file (name: $name)");
		
		return $contents;
	}
	
	private function registerConverter(
		$converter) // JWSDK_Resource_Converter
	{
		$this->converters[$converter->getType()] = $converter;
	}
	
	private function getConverter( // JWSDK_Resource_Converter
		$type) // String
	{
		return JWSDK_Util_Array::get($this->converters, $type);
	}
	
	private function getConverterByResourceName( // JWSDK_Resource_Converter
		$name) // String
	{
		foreach ($this->converters as $type => $converter)
		{
			if (preg_match("/\.$type$/i", $name))
				return $converter;
		}
		
		return null;
	}
}
