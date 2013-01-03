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

class JWSDK_File_Manager
{
	private $globalConfig;        // JWSDK_GlobalConfig
	private $attachers = array(); // Map from type:String to JWSDK_File_Attacher
	private $files = array();     // Map from name:String to JWSDK_File
	
	public function __construct(
		$globalConfig) // JWSDK_GlobalConfig
	{
		$this->globalConfig = $globalConfig;
		
		$this->registerAttacher(new JWSDK_File_Attacher_Css());
		$this->registerAttacher(new JWSDK_File_Attacher_Js());
	}
	
	public function getAttacher( // JWSDK_Resource_Attacher
		$type) // String
	{
		return JWSDK_Util_Array::get($this->attachers, $type);
	}
	
	public function getAttachers() // Map from type:String to JWSDK_File_Attacher
	{
		return $this->attachers;
	}
	
	public function getFile( // JWSDK_File
		$name,     // String
		$attacher) // String
	{
		$file = JWSDK_Util_Array::get($this->files, $name);
		if ($file)
		{
			if ($file->getAttacher() != $attacher)
				throw new JWSDK_Exception_InsufficientFileType($name);
			
			return $file;
		}
		
		$path = $this->getFilePath($name);
		$mtime = JWSDK_Util_File::mtime($path);
		$file = new JWSDK_File($name, $attacher, $mtime);
		$this->files[$name] = $file;
		
		return $file;
	}
	
	public function getFileContents( // String
		$file) // JWSDK_File
	{
		$contents = $file->getContents();
		if ($contents)
			return $contents;
		
		$contents = JWSDK_Util_File::read($this->getFilePath($file->getName()));
		$file->setContents($contents);
		return $contents;
	}
	
	public function getFileUrl( // String
		$file) // JWSDK_File
	{
		return $this->globalConfig->getUrlPrefix() . $file->getName() . "?timestamp=" . $file->getMtime();
	}
	
	public function getFilePath( // String
		$name) // String
	{
		return $this->globalConfig->getPublicPath() . "/$name";
	}
	
	private function registerAttacher(
		$attacher) // JWSDK_Resource_Attacher
	{
		$this->attachers[$attacher->getType()] = $attacher;
	}
}
