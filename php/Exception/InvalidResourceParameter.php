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

class JWSDK_Exception_InvalidResourceParameter extends JWSDK_Exception
{
	private $name;
	private $format;
	
	public function __construct($name, $format)
	{
		parent::__construct("Resource requires $format in $name parameter");
		$this->name = $name;
		$this->format = $format;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getFormat()
	{
		return $this->format;
	}
}
