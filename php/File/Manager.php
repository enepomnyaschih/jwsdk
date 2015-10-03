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
		'native', 'short', 'synchronized', 'transient', 'volatile'
	);

	private $globalConfig;        // JWSDK_GlobalConfig
	private $attachers = array(); // Map from type:String to JWSDK_File_Attacher
	private $files = array();     // Map from name:String to JWSDK_File
	private $jsSymbols = array(); // Map from String to String, for obfuscation
	private $symbolIndex = 0;     // Integer

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

	public function getJsSymbol( // String
		$symbol,    // String
		$namespace) // String
	{
		if ($this->globalConfig->isNotObfuscateSymbol($symbol) ||
			$this->globalConfig->isNotObfuscateNamespace($namespace)) {
			return $symbol;
		}
		$format = $this->globalConfig->getObfuscateDebugFormat();
		if (isset($format)) {
			return str_replace('%v', $symbol, $format);
		}
		if (!isset($this->jsSymbols[$symbol])) {
			$this->jsSymbols[$symbol] = $this->newJsSymbol();
			echo "Map $symbol to " . $this->jsSymbols[$symbol] . "\n";
		}
		return $this->jsSymbols[$symbol];
	}

	private function newJsSymbol() // String
	{
		do {
			$this->symbolIndex++;
			$symbol = '';
			$index = $this->symbolIndex;
			do {
				$index -= 1;
				$symbol = self::$ALPHABET[$index % strlen(self::$ALPHABET)] . $symbol;
				$index = (int)($index / strlen(self::$ALPHABET));
			} while ($index != 0);
		} while ($this->globalConfig->isNotObfuscateSymbol($symbol) || in_array($symbol, self::$RESERVED_WORDS, true));
		return $symbol;
	}

	private function registerAttacher(
		$attacher) // JWSDK_Resource_Attacher
	{
		$this->attachers[$attacher->getType()] = $attacher;
	}
}
