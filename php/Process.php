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

class JWSDK_Process
{
	private $name;           // String
	private $command;        // String
	private $input;          // String
	private $output;         // String
	private $inputEmbedded;  // Boolean
	private $outputEmbedded; // Boolean

	public function __construct(
		$name,                   // String
		$command,                // String
		$input = null,           // String
		$output = null,          // String
		$inputEmbedded = false,  // Boolean
		$outputEmbedded = false) // Boolean
	{
		$this->name           = $name;
		$this->command        = $command;
		$this->input          = $input;
		$this->output         = $output;
		$this->inputEmbedded  = $inputEmbedded  || ($this->input  == null);
		$this->outputEmbedded = $outputEmbedded || ($this->output == null);
	}

	public function getName()
	{
		return $this->name;
	}

	public function execute()
	{
		$descriptors = array(2 => array('pipe', 'w'));
		if (!$this->inputEmbedded)
			$descriptors[0] = array('file', $this->input, 'r');
		if (!$this->outputEmbedded)
			$descriptors[1] = array('file', $this->output, 'w');

		$process = proc_open($this->command, $descriptors, $pipes, getcwd());
		if (!is_resource($process))
			throw new JWSDK_Exception_ProcessNotStarted($this);

		$error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$code = proc_close($process);
		if ($code != 0)
			throw new JWSDK_Exception_ProcessError($this->name, $this->input, $code, $error);
	}

	public function __toString()
	{
		return $this->command .
			(!$this->inputEmbedded  ? (' < ' . JWSDK_Process::escapePath($this->input )) : '') .
			(!$this->outputEmbedded ? (' > ' . JWSDK_Process::escapePath($this->output)) : '');
	}

	public static function escapePath($path)
	{
		return JWSDK_Util_Os::escapePathUnix($path);
	}
}
