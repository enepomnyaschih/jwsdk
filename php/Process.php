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
	private $command; // String
	private $input;   // String
	private $output;  // String

	public function __construct(
		$command,       // String
		$input = null,  // String
		$output = null) // String
	{
		$this->command = $command;
		$this->input   = $input;
		$this->output  = $output;
	}

	public function execute()
	{
		$descriptors = array(2 => array('pipe', 'w'));
		if ($this->input != null)
			$descriptors[0] = array(0 => array('file', $this->input, 'r'));
		if ($this->output != null)
			$descriptors[1] = array(1 => array('file', $this->output, 'w'));

		$process = proc_open($this->command, $descriptors, $pipes, getcwd());
		if (!is_resource($process))
			throw new JWSDK_Exception_ProcessNotStarted($this);

		$error = stream_get_contents($pipes[2]);
		fclose($pipes[2]);

		$returnValue = proc_close($process);
		if ($returnValue != 0)
			throw new JWSDK_Exception_ProcessReturnedError($this, $returnValue, $error);
	}

	public function __toString()
	{
		return $this->command .
			(($this->input  != null) ? (' < ' . JWSDK_Util_Os::escapePath($this->input )) : '') .
			(($this->output != null) ? (' > ' . JWSDK_Util_Os::escapePath($this->output)) : '');
	}
}
