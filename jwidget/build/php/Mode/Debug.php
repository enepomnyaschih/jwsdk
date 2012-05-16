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

class JWSDK_Mode_Debug extends JWSDK_Mode
{
	public function getId() // String
	{
		return 'debug';
	}
	
	public function getConfigId() // String
	{
		return 'debug';
	}
	
	public function isCompress() // Boolean
	{
		return false;
	}
	
	public function getDescription() // String
	{
		return "Link html pages in debug mode (no compression).";
	}
}

JWSDK_Mode::registerMode(new JWSDK_Mode_Debug());
