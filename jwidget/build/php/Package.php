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

class JWSDK_Package
{
	private $name;
	
	private $sourceFiles;
	private $compressedFiles;
	
	public function __construct(
		$name) // String
	{
		$this->name = $name;
	}
	
	public function getName() // String
	{
		return $name;
	}
	
	public function getSourceFiles() // Array of JWSDK_File
	{
		if (!$this->sourceFiles)
			$this->sourceFiles = $this->initSourceFiles();
		
		return $this->sourceFiles;
	}
	
	public function getCompressedFiles() // Array of JWSDK_File
	{
		if (!$this->compressedFiles)
			$this->compressedFiles = $this->initCompressedFiles();
		
		return $this->compressedFiles;
	}
	
	public function getRequires() // Array of String, package names
	{
		return array();
	}
	
	protected function initSourceFiles() // Array of JWSDK_File
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
	
	protected function initCompressedFiles() // Array of JWSDK_File
	{
		throw new JWSDK_Exception_MethodNotImplemented();
	}
}
