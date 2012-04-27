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

class JWSDK_Converter_Txt extends JWSDK_Converter
{
	public $type = 'txt';
	
	public function convertResource($source, $contents, $params, $jslist)
	{
		if (count($params) < 1)
			throw new Exception("JS txt resource requires variable name in first parameter (source: $source, jslist: $jslist)");
		
		$varName  = JWSDK_Converter_Util::defineJsVar($params[0]);
		$contents = JWSDK_Converter_Util::smoothText($contents);
		
		return "$varName = '$contents';\n";
	}
}

JWSDK_Converter::register(new JWSDK_Converter_Txt());
