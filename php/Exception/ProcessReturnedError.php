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

class JWSDK_Exception_ProcessReturnedError extends JWSDK_Exception
{
	private $error; // String

	public function __construct($process, $returnValue, $error)
	{
		parent::__construct("Process returned $returnValue code. Command:\n$process\nError:\n$error");
		$this->error = $error;
	}

	public function getError()
	{
		return $this->error;
	}
}
