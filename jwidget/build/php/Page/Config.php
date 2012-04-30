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

class JWSDK_Page_Config
{
	private $name;
	private $base;
	private $template;
	private $title;
	private $css = array();
	private $js = array();
	private $services = array();
	private $custom = array();
	
	public function __construct($name, $json)
	{
		$this->name     = name;
		$this->base     = JWSDK_Util_Array::get($json, 'base');
		$this->template = JWSDK_Util_Array::get($json, 'template');
		$this->title    = JWSDK_Util_Array::get($json, 'title');
		
		if (isset($json['css']))
		{
			$csss = $json['css'];
			for ($i = 0; $i < count($csss); $i++)
				$this->css[] = $csss[$i];
		}
	}
}
