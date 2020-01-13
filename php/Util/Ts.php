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
			$sourcePaths[] = JWSDK_Process::escapePath($publicPath . '/' . $resource->getName());

		$sourcePath = implode(' ', $sourcePaths);
		$buildUrl = $globalConfig->getBuildUrl();
		$packageName = $package->getName();
		$target = $globalConfig->getTsTarget();

		// build JavaScript
		$buildDir = "$publicPath/$buildUrl/ts";
		$buildDirOs  = JWSDK_Process::escapePath($buildDir);
		$dummyPathOs = JWSDK_Process::escapePath($dummyPath);
		$process = new JWSDK_Process('TypeScript compilation', "tsc -t $target --outDir $buildDirOs $sourcePath $dummyPathOs");
		$process->execute();
		unlink($dummyPath);

		// build d.ts
		$buildPath = "$publicPath/$buildUrl/d.ts/$packageName.js";
		$buildPathOs = JWSDK_Process::escapePath($buildPath);
		$process = new JWSDK_Process('TypeScript compilation', "tsc -d -t $target --out $buildPathOs $sourcePath");
		$process->execute();

		return "$buildUrl/d.ts/$packageName.d.ts";
	}
}
