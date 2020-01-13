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

class JWSDK_Resource_Converter_JwHtml extends JWSDK_Resource_Converter_Internal
{
	public function getType() // String
	{
		return 'jw.html';
	}
	
	public function convertResource( // String, output contents
		$name,     // String
		$contents, // String
		$params)   // Array of String
	{
		if (!isset($params['class']) || !is_string($params['class']))
			throw new JWSDK_Exception_InvalidResourceParameter("'class' (first)", 'String');
		
		if (isset($params['template']) && !is_string($params['template']))
			throw new JWSDK_Exception_InvalidResourceParameter("'template' (second)", 'String');
		
		$className    = $params['class'];
		$templateName = JWSDK_Util_Array::get($params, 'template', 'main');
		$contents     = JWSDK_Resource_Converter_Util::smoothHtml($contents);
		
		return "(window.viewha || JW.UI).template($className, { $templateName: '$contents' });\n";
	}
	
	public function getParamsByArray( // Array
		$params) // Array
	{
		if (count($params) < 1)
			throw new JWSDK_Exception_InvalidResourceParameter("'class' (first)", 'String');
		
		$result = array(
			'class' => $params[0]
		);
		
		if (count($params) >= 2)
			$result['template'] = $params[1];
		
		return $result;
	}
}
