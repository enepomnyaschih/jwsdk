<?php

/*
Copyright (C) 2019 by Egor Nepomnyaschih

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
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
