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

class JWSDK_Template_Manager
{
	private $globalConfig;        // JWSDK_GlobalConfig
	private $templates = array(); // Map from name:String to JWSDK_Template
	
	public function __construct(
		$globalConfig) // JWSDK_GlobalConfig
	{
		$this->globalConfig = $globalConfig;
	}
	
	public function readTemplate( // JWSDK_Template
		$name) // String
	{
		$template = $this->getTemplate($name);
		if ($template)
			return $template;
		
		try
		{
			$path = $this->getTemplatePath($name);
			$contents = JWSDK_Util_File::read($path, 'page template');
			$template = new JWSDK_Template($name, $contents);
			$this->registerTemplate($template);
			
			return $template;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_TemplateReadError($name, $e);
		}
	}
	
	private function getTemplatePath( // String
		$name) // String
	{
		return $this->globalConfig->getTemplatesPath() . "/$name.html";
	}
	
	private function registerTemplate(
		$template) // JWSDK_Template
	{
		$this->templates[$template->getName()] = $template;
	}
	
	private function getTemplate( // JWSDK_Template
		$name) // String
	{
		return JWSDK_Util_Array::get($this->templates, $name);
	}
}
