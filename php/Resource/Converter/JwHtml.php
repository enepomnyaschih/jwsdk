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
