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

class JWSDK_File_Attacher_Js extends JWSDK_Resource_Attacher
{
	public function getType() // String
	{
		return 'js';
	}

	public function format( // String
		$url) // String
	{
		return '<script type="text/javascript" charset="utf-8" src="' . htmlspecialchars($url) . '"></script>';
	}

	public function beforeCompress( // String
		$contents,     // String
		$sourceName,   // String
		$targetName,   // String
		$globalConfig, // JWSDK_GlobalConfig
		$fileManager)  // JWSDK_File_Manager
	{
		$result = array();
		$index = 0;
		while (true) {
			if (!preg_match('~^\s*//\s*<jwdebug>\s*$~m', $contents, $matches, PREG_OFFSET_CAPTURE, $index)) {
				break;
			}
			$begin = $matches[0][1];
			if (!preg_match('~^\s*//\s*</jwdebug>\s*$~m', $contents, $matches, PREG_OFFSET_CAPTURE, $begin)) {
				break;
			}
			$result[] = substr($contents, $index, $begin - $index);
			$index = $matches[0][1] + strlen($matches[0][0]);
		}
		$result[] = substr($contents, $index);
		$contents = implode('', $result);

		if (!$globalConfig->isObfuscate()) {
			return $contents;
		}
		$obfuscator = new JWSDK_Util_Obfuscator($contents, $fileManager);
		return $obfuscator->obfuscate();
	}
}
