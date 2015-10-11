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

class JWSDK_Util_Obfuscator
{
	const MEMBER_REG = '~^[A-Za-z_$][A-Za-z0-9_$]*$~';
	const INVOKE_REG = '~[A-Za-z_$\)\]][A-Za-z0-9_$.]*~';
	const DEFINE_REG = '~([{,])\s*([A-Za-z_$][A-Za-z0-9_$]*)\s*:~';

	private $contents;
	private $fileManager;
	private $length;
	private $begin = 0;
	private $current = 0;
	private $result = '';

	public function __construct( // String
		$contents, // String
		$fileManager) // JWSDK_File_Manager
	{
		$this->contents = JWSDK_Util_String::removeComments($contents, JWSDK_Util_String::COMMENTS_JS);
		$this->fileManager = $fileManager;
		$this->length = strlen($contents);
	}

	public function obfuscate() // String
	{
		$isString1 = false;
		$isString2 = false;
		$isRegex   = false;
		$isEscape  = false;
		for ($this->current = 0; $this->current < $this->length; $this->current++) {
			if ($isEscape) {
				$isEscape = false;
				continue;
			}
			$char = substr($this->contents, $this->current, 1);
			if ($char === '\\') {
				if ($isString1 || $isString2 || $isRegex) {
					$isEscape = true;
				}
			}
			if ($isString1) {
				if ($char === "'") {
					$this->dump();
					$isString1 = false;
				}
				continue;
			}
			if ($isString2) {
				if ($char === '"') {
					$this->dump();
					$isString2 = false;
				}
				continue;
			}
			if ($isRegex) {
				if ($char === '/') {
					$this->dump();
					$isRegex = false;
				}
				continue;
			}
			$isWrap = false;
			if ($char === "'") {
				$this->replace();
				$isString1 = true;
			}
			if ($char === '"') {
				$this->replace();
				$isString2 = true;
			}
			if ($char === '/' && $this->current > 0 && $this->contents[$this->current - 1] == '(') {
				$this->replace();
				$isRegex = true;
			}
		}
		$this->replace();
		return $this->result;
	}

	private function dump()
	{
		//$this->result .= "\n=== DUMP ===\n";
		$this->result .= substr($this->contents, $this->begin, $this->current + 1 - $this->begin);
		$this->begin = $this->current + 1;
	}

	private function replace()
	{
		//$this->result .= "\n=== REPLACE ===\n";
		$fragment = substr($this->contents, $this->begin, $this->current - $this->begin);
		$fragment = preg_replace_callback(self::INVOKE_REG, array($this, 'replaceInvoke'), $fragment);
		$fragment = preg_replace_callback(self::DEFINE_REG, array($this, 'replaceDefine'), $fragment);
		$this->result .= $fragment;
		$this->begin = $this->current;
	}

	private function replaceInvoke($match)
	{
		$match = $match[0];
		$tokens = explode('.', $match);
		for ($i = 1; $i < count($tokens); $i++) {
			if (preg_match(self::MEMBER_REG, $tokens[$i])) {
				$tokens[$i] = $this->fileManager->getJsMember($tokens[$i], $tokens[0]);
			}
		}
		return implode('.', $tokens);
	}

	private function replaceDefine($match)
	{
		return $match[1] . "\n" . $this->fileManager->getJsMember($match[2], '') . ':';
	}
}
