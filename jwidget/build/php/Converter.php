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

class JWSDK_Converter
{
	private static $instances = array();
	
	public $type;
	public $converted = true;
	
	public function convertResource($source, $contents, $params, $jslist, $config)
	{
	}
	
	public static function register($instance)
	{
		self::$instances[$instance->type] = $instance;
	}
	
	public static function convert($definition, $jslist, $config)
	{
		$tokens = explode(":", $definition);
		$source = trim($tokens[0]);
		
		$resource = self::getResource($source, $jslist);
		if (!$resource->converted)
			return $source;
		
		JWSDK_Log::logTo('build.log', "Converting JS template $source");
		
		if (count($tokens) == 1)
		{
			$params = array();
		}
		else
		{
			$params = explode(",", $tokens[1]);
			for ($i = 0; $i < count($params); $i++)
				$params[$i] = trim($params[$i]);
		}
		
		$sourcePath = $config['publicPath'] . "/$source";
		$sourceContents = @file_get_contents($sourcePath);
		if ($sourceContents === false)
			throw new Exception("Can't open JS resource file (path: $source, jslist: $jslist)");
		
		$outputContents = $resource->convertResource($source, $sourceContents, $params, $jslist, $config);
		
		$outputUrl = $config['buildUrl'] . "/$source.js";
		$outputPath = $config['publicPath'] . "/$outputUrl";
		
		$outputFile = JWSDK_Util_File::fopen_recursive($outputPath, 'w');
		if ($outputFile === false)
			throw new Exception("Can't create JS resource target file (source: $source, target: $outputUrl)");
		
		fwrite($outputFile, $outputContents);
		fclose($outputFile);
		
		return $outputUrl;
	}
	
	private static function getResource($source, $jslist)
	{
		foreach (self::$instances as $type => $instance)
		{
			if (preg_match("/\.$type$/i", $source))
				return $instance;
		}
		
		throw new Exception("Unknown JS resource type (source: $source, jslist: $jslist)");
	}
}
