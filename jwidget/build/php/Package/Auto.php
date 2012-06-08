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

class JWSDK_Package_Auto extends JWSDK_Package
{
	private $fileManager;    // JWSDK_File_Manager
	
	private $sourceFile;     // JWSDK_File
	private $compressedFile; // JWSDK_File
	
	public function __construct(
		$name,        // String
		$fileManager) // JWSDK_File_Manager
	{
		parent::__construct($name . '|auto');
		
		$this->fileManager = $fileManager;
		
		$type = $this->getResourceType();
		$this->sourceFile = $this->fileManager->getFile($name, $type);
		$this->compressedFile = $this->fileManager->getFile($this->getCompressedName($type), $type);
	}
	
	protected function initSourceFiles() // Array of JWSDK_File
	{
		return array($this->sourceFile);
	}
	
	protected function initCompressedFiles() // Array of JWSDK_File
	{
		return array($this->compressedFile);
	}
	
	private function getResourceType() // String
	{
		return substr($this->name, strrpos($this->name, '.') + 1);
	}
	
	private function getCompressedName( // String
		$type) // String
	{
		return substr($this->name, 0, strrpos($this->name, '.')) . '.min.' . $type;
	}
}
