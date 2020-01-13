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

class JWSDK_Package_Auto extends JWSDK_Package
{
	private $fileName;       // String
	private $fileManager;    // JWSDK_File_Manager
	
	private $sourceFile;     // JWSDK_File
	private $compressedFile; // JWSDK_File
	
	public function __construct(
		$name,        // String
		$fileManager) // JWSDK_File_Manager
	{
		parent::__construct($name . '|auto');
		
		$this->fileName = $name;
		
		$this->fileManager = $fileManager;
		
		$type = substr($name, strrpos($name, '.') + 1);
		$this->sourceFile = $this->fileManager->getFile($name, $type);
		
		$compressedName = substr($name, 0, strrpos($name, '.')) . '.min.' . $type;
		$this->compressedFile = $this->fileManager->getFile($compressedName, $type);
	}
	
	public function getFileName()
	{
		return $this->fileName;
	}
	
	protected function initSourceFiles() // Array of JWSDK_File
	{
		return array($this->sourceFile);
	}
	
	protected function initCompressedFiles() // Array of JWSDK_File
	{
		return array($this->compressedFile);
	}
}
