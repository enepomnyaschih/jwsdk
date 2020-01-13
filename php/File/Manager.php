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

class JWSDK_File_Manager
{
	private static $ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	private static $RESERVED_WORDS = array(
		// ES6
		'break', 'case', 'class', 'catch', 'const', 'continue', 'debugger', 'default', 'delete',
		'do', 'else', 'export', 'extends', 'finally', 'for', 'function', 'if', 'import', 'in',
		'instanceof', 'let', 'new', 'return', 'super', 'switch', 'this', 'throw', 'try', 'typeof',
		'var', 'void', 'while', 'with', 'yield', 'null', 'true', 'false',
		// Future
		'enum', 'await', 'implements', 'package', 'protected', 'static', 'interface', 'private', 'public',
		// Unused
		'abstract', 'boolean', 'byte', 'char', 'double', 'final', 'float', 'goto', 'int', 'long',
		'native', 'short', 'synchronized', 'transient', 'volatile', 'prototype'
	);

	private $globalConfig;        // JWSDK_GlobalConfig
	private $attachers = array(); // Map from type:String to JWSDK_File_Attacher
	private $files = array();     // Map from name:String to JWSDK_File
	private $jsMembers = array(); // Map from String to String, for obfuscation
	private $memberIndex = 0;     // Integer

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
		$name,            // String
		$attacher = null) // String
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
	
	public function getFileSize( // Integer
		$file) // JWSDK_File
	{
		$size = $file->getSize();
		if ($size !== null)
			return $size;
		
		$size = filesize($this->getFilePath($file->getName()));
		$file->setSize($size);
		return $size;
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
	
	public function getFileDependencies( // Array or JWSDK_File
		$file) // JWSDK_File
	{
		$dependencies = $file->getDependencies();
		if ($dependencies)
			return $dependencies;
		
		$attacher = $this->getAttacher($file->getAttacher());
		$dependencies = $attacher->getFileDependencies($file, $this);
		$file->setDependencies($dependencies);
		return $dependencies;
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

	public function getJsMember( // String
		$member,    // String
		$namespace) // String
	{
		if ($this->globalConfig->isNotObfuscateMember($member) ||
			$this->globalConfig->isNotObfuscateNamespace($namespace) ||
			in_array($member, self::$RESERVED_WORDS, true)) {
			return $member;
		}
		$format = $this->globalConfig->getObfuscateDebugFormat();
		if (isset($format)) {
			return str_replace('%v', $member, $format);
		}
		if (!isset($this->jsMembers[$member])) {
			$this->jsMembers[$member] = $this->newJsMember();
		}
		return $this->jsMembers[$member];
	}

	public function getJsMembers() // Map<String>
	{
		return $this->jsMembers;
	}

	private function newJsMember() // String
	{
		do {
			$this->memberIndex++;
			$member = '';
			$index = $this->memberIndex;
			do {
				$index -= 1;
				$member = self::$ALPHABET[$index % strlen(self::$ALPHABET)] . $member;
				$index = (int)($index / strlen(self::$ALPHABET));
			} while ($index != 0);
		} while ($this->globalConfig->isNotObfuscateMember($member) || in_array($member, self::$RESERVED_WORDS, true));
		return $member;
	}

	private function registerAttacher(
		$attacher) // JWSDK_Resource_Attacher
	{
		$this->attachers[$attacher->getType()] = $attacher;
	}
}
