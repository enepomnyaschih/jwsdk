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

class JWSDK_Resource
{
	private $name;       // String
	private $type;       // String
	private $params;     // Array of String
	private $sourceFile; // JWSDK_File
	private $outputFile; // JWSDK_File
	
	public function __construct(
		$name,       // String
		$type,       // String
		$params,     // Array of String
		$sourceFile) // JWSDK_File
	{
		$this->name = $name;
		$this->type = $type;
		$this->params = $params;
		$this->sourceFile = $sourceFile;
	}
	
	public function getName() // String
	{
		return $this->name;
	}
	
	public function getType() // String
	{
		return $this->type;
	}
	
	public function getParams() // Array of String
	{
		return $this->params;
	}
	
	public function getSourceFile() // JWSDK_File
	{
		return $this->sourceFile;
	}
	
	public function getOutputFile() // JWSDK_File
	{
		return $this->outputFile;
	}
	
	public function setOutputFile(
		$file) // JWSDK_File
	{
		$this->outputFile = $file;
	}
}
