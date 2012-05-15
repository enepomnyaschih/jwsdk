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
	private $name;               // String
	private $base;               // String
	private $template;           // String
	private $title = '';         // String
	private $packages = array(); // Array of String
	private $services = array(); // Map from String to Boolean
	private $custom = array();   // Map from String to String
	private $rootPackage;        // JWSDK_Package_Config, auto-generated
	
	public function __construct(
		$name, // String
		$json) // Object
	{
		$this->name     = $name;
		$this->base     = JWSDK_Util_Array::get($json, 'base');
		$this->template = JWSDK_Util_Array::get($json, 'template');
		$this->title    = JWSDK_Util_Array::get($json, 'title');
		
		if (isset($json['packages']))
			$this->packages = array_merge($this->packages, $json['packages']);
		
		if (isset($json['services']))
			$this->services = array_merge($this->services, $json['services']);
		
		if (isset($json['custom']))
			$this->custom = array_merge($this->custom, $json['custom']);
	}
	
	public function getName() // String
	{
		return $this->name;
	}
	
	public function getBase() // String
	{
		return $this->base;
	}
	
	public function getTemplate() // String
	{
		return $this->template;
	}
	
	public function getTitle() // String
	{
		return $this->title;
	}
	
	public function getPackages() // Array of String
	{
		return $this->packages;
	}
	
	public function getServices() // Map from String to Boolean
	{
		return $this->services;
	}
	
	public function getCustom() // Map from String to String
	{
		return $this->custom;
	}
	
	public function setRootPackage(
		$package) // JWSDK_Package_Config
	{
		$this->rootPackage = $package;
	}
	
	public function getRootPackage() // JWSDK_Package_Config
	{
		return $this->rootPackage;
	}
	
	public function applyBase(
		$base) // JWSDK_Page
	{
		if (!$this->getTemplate())
			$this->template = $base->getTemplate();
		
		if (!$this->getTitle())
			$this->title = $base->getTitle();
		
		$this->packages = array_merge($base->getPackages(), $this->getPackages());
		$this->services = array_merge($base->getServices(), $this->getServices());
		$this->custom   = array_merge($base->getCustom(),   $this->getCustom());
	}
	
	public function getVariables() // Map from String to Map from String to *
	{
		return array(
			'services' => $this->getServices(),
			'custom'   => $this->getCustom()
		);
	}
}
