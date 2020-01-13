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

class JWSDK_Resource_Manager
{
	private $globalConfig;         // JWSDK_GlobalConfig
	private $fileManager;          // JWSDK_FileManager

	private $attachers = array();  // Map from type:String to JWSDK_Resource_Attacher
	private $converters = array(); // Map from type:String to JWSDK_Resource_Converter
	private $resources = array();  // Map from name:String to JWSDK_Resource

	public function __construct(
		$globalConfig, // JWSDK_GlobalConfig
		$fileManager)  // JWSDK_FileManager
	{
		$this->globalConfig = $globalConfig;
		$this->fileManager = $fileManager;

		$this->registerConverter(new JWSDK_Resource_Converter_Css());
		$this->registerConverter(new JWSDK_Resource_Converter_JwHtml());
		$this->registerConverter(new JWSDK_Resource_Converter_RefTs());
		$this->registerConverter(new JWSDK_Resource_Converter_SchemaJson());
		$this->registerConverter(new JWSDK_Resource_Converter_Txt());
		$this->registerConverter(new JWSDK_Resource_Converter_Html());
		$this->registerConverter(new JWSDK_Resource_Converter_Json());
		$this->registerConverter(new JWSDK_Resource_Converter_Js());
		$this->registerConverter(new JWSDK_Resource_Converter_Less());
		$this->registerConverter(new JWSDK_Resource_Converter_Sass());
		$this->registerConverter(new JWSDK_Resource_Converter_Scss());
		$this->registerConverter(new JWSDK_Resource_Converter_Styl());
		$this->registerConverter(new JWSDK_Resource_Converter_Ts());
		$this->registerConverter(new JWSDK_Resource_Converter_Jsx());
	}

	public function getResourceByDefinition( // JWSDK_Resource
		$definition) // *
	{
		try
		{
			if (is_string($definition))
				return $this->getResourceByString($definition);

			if (is_array($definition))
				return $this->getResourceByJson($definition);

			throw new JWSDK_Exception_InvalidResourceFormat();
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_ResourceReadError(json_encode($definition), $e);
		}
	}

	public function isTypeScriptResource( // Boolean
		$resource) // JWSDK_Resource
	{
		$converter = $this->getConverter($resource->getType());
		return $converter->isTypeScript();
	}

	public function addTypeScriptDependencies(
		&$typeScripts, // Array<JWSDK_Resource>
		$resource)     // JWSDK_Resource
	{
		$converter = $this->getConverter($resource->getType());
		return $converter->addTypeScriptDependencies($typeScripts, $resource, $this, $this->globalConfig);
	}

	public function expandResource( // Array<JWSDK_Resource>
		$resource) // JWSDK_Resource
	{
		$converter = $this->getConverter($resource->getType());
		return $converter->expand($resource, $this, $this->globalConfig);
	}

	public function convertResource( // JWSDK_File
		$resource) // JWSDK_Resource
	{
		$file = $resource->getOutputFile();
		if ($file)
			return $file;

		$file = $this->getFileByResource($resource);
		$resource->setOutputFile($file);

		return $file;
	}

	private function getResourceByString( // JWSDK_Resource
		$str) // String
	{
		$tokens = explode(":", $str);
		$name = trim($tokens[0]);

		$resource = $this->getResource($name);
		if ($resource)
			throw new JWSDK_Exception_DuplicatedResourceError($name);

		$converter = $this->getConverterByResourceName($name);
		if (!$converter)
			throw new JWSDK_Exception_InvalidResourceType();

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

		return $this->createResource($name, $converter->getType(), $converter->getAttacher(), $converter->getParamsByArray($params));
	}

	private function getResourceByJson( // JWSDK_Resource
		$json) // Object
	{
		if (!isset($json['path']) || !is_string($json['path']))
			throw new JWSDK_Exception_InvalidResourceFormat();

		$name = $json['path'];

		$resource = $this->getResource($name);
		if ($resource)
			throw new JWSDK_Exception_DuplicatedResourceError($name);

		if (isset($json['type']) && is_string($json['type']))
			$converter = $this->getConverter($json['type']);
		else
			$converter = $this->getConverterByResourceName($name);

		if (!$converter)
			throw new JWSDK_Exception_InvalidResourceType();

		return $this->createResource($name, $converter->getType(), $converter->getAttacher(), $converter->getParamsByJson($json));
	}

	private function getFileByResource( // JWSDK_File
		$resource) // JWSDK_Resource
	{
		$name = $resource->getName();
		$type = $resource->getType();

		try
		{
			$converter = $this->getConverter($type);
			if (!$converter->isConvertion())
				return $this->fileManager->getFile($resource->getName(), $converter->getAttacher());

			// TODO: check that resource mtime has been changed

			if ($this->globalConfig->isConversionLog())
				echo "Converting resource $name\n";

			$attacher = $converter->getAttacher();
			$converter->convert($resource, $this->globalConfig);
			$buildName = $converter->getResourceBuildName($resource, $this->globalConfig);
			return $this->fileManager->getFile($buildName, $converter->getAttacher());
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_ResourceConvertionError($name, $type, $e);
		}
	}

	private function registerConverter(
		$converter) // JWSDK_Resource_Converter
	{
		$this->converters[$converter->getType()] = $converter;
	}

	public function getConverter( // JWSDK_Resource_Converter
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

	private function createResource( // JWSDK_Resource
		$name,     // String
		$type,     // String
		$attacher, // String
		$params)   // Array of String
	{
		$resource = new JWSDK_Resource($name, $type, $params, $this->fileManager->getFile($name, $attacher));
		//$this->registerResource($resource); // can cause back compatibility issues
		return $resource;
	}

	private function registerResource(
		$resource) // JWSDK_Resource
	{
		$this->resources[$resource->getName()] = $resource;
	}

	private function getResource( // JWSDK_Resource
		$name) // String
	{
		return JWSDK_Util_Array::get($this->resources, $name);
	}
}
