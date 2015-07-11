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

class JWSDK_Resource_Converter_Styl extends JWSDK_Resource_Converter_CssBase
{
	public function getType() // String
	{
		return 'styl';
	}

	protected function getProcess( // JWSDK_Process
		$source,       // String
		$target,       // String
		$globalConfig) // JWSDK_GlobalConfig
	{
		$publicPathOs = JWSDK_Process::escapePath($globalConfig->getPublicPath());
		return new JWSDK_Process('Stylus compilation', "stylus -I $publicPathOs", $source, $target);
	}
}
