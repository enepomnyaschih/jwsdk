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
		$this->length = strlen($this->contents);
	}

	public function obfuscate() // String
	{
		$expectOperator = false;

		while ($this->current < $this->length)
		{
			$char = substr($this->contents, $this->current, 1);
			if ($char === '"' || $char === "'" || (!$expectOperator && $char === '/'))
			{
				$this->replace();
				$next = JWSDK_Util_String::findUnescaped($this->contents, $char, $this->current + 1);
				$next = ($next !== false) ? ($next + 1) : $this->length;
				$this->result .= substr($this->contents, $this->current, $next - $this->current);
				$this->current = $next;
				$this->begin = $next;
				$expectOperator = true;
			}
			else
			{
				if (preg_match('~[+\-\*\/%=><\?\:&\|\~\^\{\(,;\.]~', $char)) {
					$expectOperator = false;
				} else if (!preg_match('~\s~', $char)) {
					$expectOperator = true;
				}
				$this->current++;
			}
		}

		$this->replace();

		return $this->result;
	}

	private function replace()
	{
		//$this->result .= "\n=== REPLACE ===\n";
		$fragment = substr($this->contents, $this->begin, $this->current - $this->begin);
		$fragment = preg_replace_callback(self::INVOKE_REG, array($this, 'replaceInvoke'), $fragment);
		$fragment = preg_replace_callback(self::DEFINE_REG, array($this, 'replaceDefine'), $fragment);
		$this->result .= $fragment;
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
