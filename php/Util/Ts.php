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

class JWSDK_Util_Ts
{
	public static function build( // String
		$package,      // JWSDK_Package_Config
		$resources,    // Array<JWSDK_Resource>
		$globalConfig) // JWSDK_GlobalConfig
	{
		$publicPath = $globalConfig->getPublicPath();
		$dummyPath = "$publicPath/__dummy.ts";
		JWSDK_Util_File::write($dummyPath, '');

		$sourcePaths = array();
		foreach ($resources as $resource)
			$sourcePaths[] = JWSDK_Util_Os::escapePath($publicPath . '/' . $resource->getName());

		$sourcePath = implode(' ', $sourcePaths);
		$buildUrl = $globalConfig->getBuildUrl();
		$packageName = $package->getName();
		$target = $globalConfig->getTsTarget();

		// build JavaScript
		$buildDir = "$publicPath/$buildUrl/ts";
		$buildDirOs  = JWSDK_Util_Os::escapePath($buildDir);
		$dummyPathOs = JWSDK_Util_Os::escapePath($dummyPath);
		$output = array();
		$status = 0;
		$command = "tsc -t $target --outDir $buildDirOs $sourcePath $dummyPathOs 2> ts.log";
		exec($command, $output, $status);
		unlink($dummyPath);

		if ($status != 0)
			throw new JWSDK_Exception_TsError($packageName, $buildDir);

		// build d.ts
		$buildPath = "$publicPath/$buildUrl/d.ts/$packageName.js";
		$buildPathOs = JWSDK_Util_Os::escapePath($buildPath);
		$output = array();
		$status = 0;
		$command = "tsc -d -t $target --out $buildPathOs $sourcePath 2> ts.log";
		exec($command, $output, $status);

		if ($status != 0)
			throw new JWSDK_Exception_TsError($packageName, $buildPath);

		return "$buildUrl/d.ts/$packageName.d.ts";
	}
}
