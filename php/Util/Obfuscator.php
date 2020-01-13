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
