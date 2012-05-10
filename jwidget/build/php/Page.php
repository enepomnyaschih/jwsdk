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

class JWSDK_Page
{
	private $name;
	private $base;
	private $template;
	private $title;
	private $css = array();
	private $js = array();
	private $services = array();
	private $custom = array();
	private $_hasOutput;
	
	public function __construct($name, $json, $hasOutput)
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
		
		$this->_hasOutput = $hasOutput;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getBase()
	{
		return $this->base;
	}
	
	public function getTemplate()
	{
		return $this->template;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function getCss()
	{
		return $this->css;
	}
	
	public function getJs()
	{
		return $this->js;
	}
	
	public function getServices()
	{
		return $this->services;
	}
	
	public function getCustom()
	{
		return $this->custom;
	}
	
	public function hasOutput()
	{
		return $this->_hasOutput;
	}
	
	public function setHasOutput($value)
	{
		$this->_hasOutput = $value;
	}
	
	public function applyBase($base)
	{
		if (!$this->getTemplate())
			$this->template = $base->getTemplate();
		
		if (!$this->getTitle())
			$this->title = $base->getTitle();
		
		$this->css      = array_merge($base->getCss(),      $this->getCss());
		$this->js       = array_merge($base->getJs(),       $this->getJs());
		$this->services = array_merge($base->getServices(), $this->getServices());
		$this->custom   = array_merge($base->getCustom(),   $this->getCustom());
	}
}
