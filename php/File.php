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

class JWSDK_File
{
	private $name;         // String
	private $attacher;     // String
	private $mtime;        // Integer
	private $size;         // Integer
	private $contents;     // String
	private $dependencies; // Array of JWSDK_File
	
	public function __construct(
		$name,     // String
		$attacher, // String
		$mtime)    // Integer
	{
		$this->name = $name;
		$this->attacher = $attacher;
		$this->mtime = $mtime;
	}
	
	public function getName() // String
	{
		return $this->name;
	}
	
	public function getAttacher() // String
	{
		return $this->attacher;
	}
	
	public function getMtime() // Integer
	{
		return $this->mtime;
	}
	
	public function getSize() // Integer
	{
		return $this->size;
	}
	
	public function setSize(
		$size) // Integer
	{
		$this->size = $size;
	}
	
	public function getContents() // String
	{
		return $this->contents;
	}
	
	public function setContents(
		$contents) // String
	{
		$this->contents = $contents;
	}
	
	public function getDependencies() // Array of JWSDK_File
	{
		return $this->dependencies;
	}
	
	public function setDependencies(
		$dependencies) // Array of JWSDK_File
	{
		$this->dependencies = $dependencies;
	}
	
	public function getDirectory() // String
	{
		return JWSDK_Util_File::getDirectory($this->name);
	}
	
	public function getExtension() // String
	{
		return JWSDK_Util_File::getExtension($this->name);
	}
}
