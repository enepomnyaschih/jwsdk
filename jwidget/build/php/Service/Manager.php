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

class JWSDK_Service_Manager
{
	private $globalConfig;        // JWSDK_GlobalConfig
	private $services = array(); // Map from name:String to JWSDK_Service
	
	public function __construct(
		$globalConfig) // JWSDK_GlobalConfig
	{
		$this->globalConfig = $globalConfig;
	}
	
	public function readService( // JWSDK_Service
		$name) // String
	{
		$service = $this->getService($name);
		if ($service)
			return $service;
		
		$path = $this->getServicePath($name);
		$contents = JWSDK_Util_File::file_get_contents($path);
		if ($contents === false)
			throw new Exception("Service doesn't exist (name: $name)");
		
		$service = new JWSDK_Service($name, $contents);
		$this->registerService($service);
		
		return $service;
	}
	
	private function getServicePath( // String
		$name) // String
	{
		return $this->globalConfig->getServicesPath() . "/$name.html";
	}
	
	private function registerService(
		$service) // JWSDK_Service
	{
		$this->services[$service->getName()] = $service;
	}
	
	private function getService( // JWSDK_Service
		$name) // String
	{
		return JWSDK_Util_Array::get($this->services, $name);
	}
}
