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
