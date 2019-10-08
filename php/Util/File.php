<?php

/*
Copyright (C) 2019 by Egor Nepomnyaschih

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
*/

class JWSDK_Util_File
{
	public static function read( // String
		$path,         // String
		$tip = 'file') // String
	{
		$result = @file_get_contents($path);
		if ($result === false)
			throw new JWSDK_Exception_CanNotReadFile($path, $tip);

		return preg_replace('/^\xEF\xBB\xBF/', '', $result);
	}

	public static function readJson( // Object
		$path,         // String
		$tip = 'file') // String
	{
		$contents = self::read($path, $tip);
		$contents = JWSDK_Util_String::removeComments($contents);
		$result = json_decode($contents, true);
		if (!$result)
			throw new JWSDK_Exception_InvalidFileFormat($path, $tip, "Can't parse JSON");

		return $result;
	}

	public static function mtime( // Integer
		$path,         // String
		$tip = 'file') // String
	{
		if (!file_exists($path))
			throw new JWSDK_Exception_CanNotReadFile($path, $tip);

		return filemtime($path);
	}

	public static function mkdir(
		$path,         // String
		$chmod = 0755) // Integer
	{
		$i = strrpos($path, '/');
		if ($i !== false)
		{
			$directory = substr($path, 0, $i);
			if (!is_dir($directory) && !mkdir($directory, $chmod, 1))
				throw new JWSDK_Exception_CanNotMakeDirectory($directory);
		}
	}

	public static function write(
		$path,     // String
		$contents) // String
	{
		self::mkdir($path);

		$file = @fopen($path, 'w');
		if ($file === false)
			throw new JWSDK_Exception_CanNotWriteFile($path);

		fwrite($file, $contents);
		fclose($file);
	}

	public static function compress(
		$globalConfig, // JWSDK_GlobalConfig
		$source,       // String
		$target)       // String
	{
		$jarPath = JWSDK_Util_File::normalizePath($globalConfig->getRunDir() . '/yuicompressor.jar');

		$javaCmdOs = JWSDK_Process::escapePath($globalConfig->getJavaCmd());
		$jarPathOs = JWSDK_Process::escapePath($jarPath);
		$sourceOs  = JWSDK_Process::escapePath($source);
		$targetOs  = JWSDK_Process::escapePath($target);

		$command = "$javaCmdOs -jar $jarPath $sourceOs -o $targetOs --charset utf-8 --line-break 8000";
		$process = new JWSDK_Process('Compression', $command, $source, $target, true, true);
		$process->execute();
	}

	public static function getDirectory( // String
		$filePath) // String
	{
		$slashIndex = strrpos($filePath, '/');
		if ($slashIndex === false)
			$slashIndex = strrpos($filePath, '\\');

		return ($slashIndex === false) ? '.' : substr($filePath, 0, $slashIndex);
	}

	public static function getExtension( // String
		$filePath) // String
	{
		$index = strrpos($filePath, '.');
		return ($index === false) ? null : substr($filePath, $index + 1);
	}

	// from http://php.net/manual/en/function.realpath.php#112367
	public static function normalizePath($path)
	{
		$parts = array();// Array to build a new path from the good parts
		$path = str_replace('\\', '/', $path);// Replace backslashes with forwardslashes
		$path = preg_replace('/\/+/', '/', $path);// Combine multiple slashes into a single slash
		$segments = explode('/', $path);// Collect path segments
		$test = '';// Initialize testing variable
		foreach($segments as $segment)
		{
			if($segment != '.')
			{
				$test = array_pop($parts);
				if(is_null($test))
					$parts[] = $segment;
				else if($segment == '..')
				{
					if($test == '..')
						$parts[] = $test;

					if($test == '..' || $test == '')
						$parts[] = $segment;
				}
				else
				{
					$parts[] = $test;
					$parts[] = $segment;
				}
			}
		}
		return implode('/', $parts);
	}
}
