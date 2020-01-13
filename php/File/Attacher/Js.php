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
		$result[] = ';';
		$contents = implode('', $result);

		if (!$globalConfig->isObfuscate()) {
			return $contents;
		}
		$obfuscator = new JWSDK_Util_Obfuscator($contents, $fileManager);
		return $obfuscator->obfuscate();
	}
}
