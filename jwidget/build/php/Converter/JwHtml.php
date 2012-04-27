<?php

/*
	jWidget SDK source file.
	
	Copyright (C) 2012 Egor Nepomnyaschih
	
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

class JWSDK_Converter_JwHtml extends JWSDK_Converter
{
	public $type = 'jw.html';
	
	public function convertResource($source, $contents, $params, $jslist)
	{
		if (count($params) < 1)
			throw new Exception("JS jw.html resource requires class name in first parameter (source: $source, jslist: $jslist)");
		
		$className = $params[0];
		
		if (count($params) < 2)
			$templateName = 'main';
		else
			$templateName = $params[1];
		
		$contents = JWSDK_Converter_Util::smoothHtml($contents);
		
		return "JW.UI.template($className, { $templateName: '$contents' });\n";
	}
}

JWSDK_Converter::register(new JWSDK_Converter_JwHtml());
