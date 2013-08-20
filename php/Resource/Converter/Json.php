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

class JWSDK_Resource_Converter_Json extends JWSDK_Resource_Converter_Internal
{
	public function getType() // String
	{
		return 'json';
	}
	
	public function convertResource( // String, output contents
		$name,     // String
		$contents, // String
		$params)   // Object
	{
		if (!isset($params['var']) || !is_string($params['var']))
			throw new JWSDK_Exception_InvalidResourceParameter("'var' (first)", 'String');
		
		$varName = JWSDK_Resource_Converter_Util::defineJsVar($params['var']);
		
		$contents = JWSDK_Util_String::removeComments($contents);
		
		$json = json_decode($contents, true);
		if (!$json)
			throw new JWSDK_Exception_InvalidFileFormat($name, 'JSON resource', "Can't parse JSON");
		
		$contents = json_encode($json);
		
		return "$varName = $contents;\n";
	}
	
	public function getParamsByArray( // Array
		$params) // Array
	{
		if (count($params) < 1)
			throw new JWSDK_Exception_InvalidResourceParameter("'var' (first)", 'String');
		
		return array(
			'var' => $params[0]
		);
	}
}