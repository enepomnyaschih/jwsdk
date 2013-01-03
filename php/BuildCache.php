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

class JWSDK_BuildCache
{
	public $input;  // JWSDK_BuildCache_Input
	public $output; // JWSDK_BuildCache_Output
	
	public function __construct(
		$path) // String
	{
		$this->input  = new JWSDK_BuildCache_Input($path);
		$this->output = new JWSDK_BuildCache_Output($path, $this->input);
	}
}
