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
