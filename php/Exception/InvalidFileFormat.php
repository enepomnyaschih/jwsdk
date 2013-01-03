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

// Runtime
class JWSDK_Exception_InvalidFileFormat extends JWSDK_Exception
{
	private $path;
	private $format;
	
	public function __construct($path, $format, $cause = null)
	{
		if (!$cause)
			parent::__construct("Can't parse $format '$path'");
		else if (is_string($cause))
			parent::__construct("Can't parse $format '$path':\n" . $cause);
		else
			parent::__construct("Can't parse $format '$path':\n" . $cause->getMessage(), $cause);
		
		$this->path = $path;
		$this->format = $format;
	}
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function getFormat()
	{
		return $this->format;
	}
}
