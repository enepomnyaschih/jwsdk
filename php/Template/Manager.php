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
		if (!preg_match("~\.[^/]+$~i", $name)) {
			$name = "$name.html";
		}
		return $this->globalConfig->getTemplatesPath() . "/$name";
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
