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
	private $mode;                // JWSDK_Mode
	private $templates = array(); // Map from name:String to JWSDK_Template
	
	// temporary variable for "readTemplate" method
	private $_readerTemplates;    // Map from name:String to true
	
	public function __construct(
		$globalConfig, // JWSDK_GlobalConfig
		$mode)         // JWSDK_Mode
	{
		$this->globalConfig = $globalConfig;
		$this->mode = $mode;
		$this->_readerTemplates = array();
	}
	
	public function readTemplate( // JWSDK_Template
		$name) // String
	{
		$template = $this->getTemplate($name);
		if ($template)
			return $template;
		
		try
		{
			if (isset($this->_readerTemplates[$name]))
				throw new JWSDK_Exception_TemplateCircleDependency($name);
			
			$this->_readerTemplates[$name] = true;
			$path = $this->getTemplatePath($name);
			$contents = JWSDK_Util_File::read($path, 'page template');
			$contents = $this->unpackTemplate($contents);
			$template = new JWSDK_Template($name, $contents);
			$this->registerTemplate($template);
			unset($this->_readerTemplates[$name]);
			
			return $template;
		}
		catch (JWSDK_Exception $e)
		{
			throw new JWSDK_Exception_TemplateReadError($name, $e);
		}
	}
	
	private function unpackTemplate( // String
		$contents) // String
	{
		$buf = array();
		
		$index = 0;
		while (true)
		{
			$pos = strpos($contents, '${', $index);
			if ($pos === false)
				break;
			
			$pos2 = strpos($contents, '}', $pos + 2);
			if ($pos2 === false)
				break;
			
			$buf[] = substr($contents, $index, $pos - $index);
			
			$operator = $contents[$pos + 2];
			$argument = substr($contents, $pos + 3, $pos2 - $pos - 3);
			
			if ($operator == '+')
			{
				$template = $this->readTemplate($argument);
				$buf[] = $template->getContents();
			}
			else if ($operator == '?')
			{
				if (($argument != 'end') &&
				    ($argument != $this->mode->getId()))
				{
					$endPos = strpos($contents, '${?end}', $pos2);
					if ($endPos !== false)
						$pos2 = $endPos + 6;
				}
			}
			else
			{
				$buf[] = substr($contents, $pos, $pos2 - $pos + 1);
			}
			
			$index = $pos2 + 1;
		}
		
		$buf[] = substr($contents, $index);
		
		return implode('', $buf);
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
