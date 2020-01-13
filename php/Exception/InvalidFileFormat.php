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
