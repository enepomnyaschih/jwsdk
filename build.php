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

date_default_timezone_set('UTC');

$jwsdkDir = __DIR__ . '/';

include_once $jwsdkDir . 'php/Util/Array.php';
include_once $jwsdkDir . 'php/Util/Css.php';
include_once $jwsdkDir . 'php/Util/File.php';
include_once $jwsdkDir . 'php/Util/Json.php';
include_once $jwsdkDir . 'php/Util/Obfuscator.php';
include_once $jwsdkDir . 'php/Util/Os.php';
include_once $jwsdkDir . 'php/Util/String.php';
include_once $jwsdkDir . 'php/Util/Ts.php';
include_once $jwsdkDir . 'php/Util/Url.php';

include_once $jwsdkDir . 'php/Mode.php';
include_once $jwsdkDir . 'php/Mode/Debug.php';
include_once $jwsdkDir . 'php/Mode/Release.php';

function printUsage()
{
	fwrite(STDERR,
		"\nUSAGE jwsdk [<mode>] [<path>]\n\n" .
		"Supported modes:\n" .
		JWSDK_Mode::getModesDescription() . "\n\n" .
		"Path refers to global config JSON file or a folder containing config.json file.\n" .
		"Path defaults to jwsdk-config/config.json (if exists) or config.json.\n");
}

if (count($argv) > 2 && !JWSDK_Mode::getMode($argv[1])) {
	printUsage();
	exit(1);
}

include_once $jwsdkDir . 'php/BuildCache.php';
include_once $jwsdkDir . 'php/BuildCache/Input.php';
include_once $jwsdkDir . 'php/BuildCache/Output.php';
include_once $jwsdkDir . 'php/Builder.php';
include_once $jwsdkDir . 'php/Exception.php';
include_once $jwsdkDir . 'php/Exception/CanNotMakeDirectory.php';
include_once $jwsdkDir . 'php/Exception/CanNotReadFile.php';
include_once $jwsdkDir . 'php/Exception/CanNotWriteFile.php';
include_once $jwsdkDir . 'php/Exception/DuplicatedResourceError.php';
include_once $jwsdkDir . 'php/Exception/DynamicLoaderDisabled.php';
include_once $jwsdkDir . 'php/Exception/FileProcessError.php';
include_once $jwsdkDir . 'php/Exception/InsufficientFileType.php';
include_once $jwsdkDir . 'php/Exception/InvalidFileFormat.php';
include_once $jwsdkDir . 'php/Exception/InvalidResourceFormat.php';
include_once $jwsdkDir . 'php/Exception/InvalidResourceParameter.php';
include_once $jwsdkDir . 'php/Exception/InvalidResourceType.php';
include_once $jwsdkDir . 'php/Exception/MethodNotImplemented.php';
include_once $jwsdkDir . 'php/Exception/PackageCompressError.php';
include_once $jwsdkDir . 'php/Exception/PageBuildError.php';
include_once $jwsdkDir . 'php/Exception/PageReadError.php';
include_once $jwsdkDir . 'php/Exception/ProcessNotStarted.php';
include_once $jwsdkDir . 'php/Exception/ProcessError.php';
include_once $jwsdkDir . 'php/Exception/ResourceConvertionError.php';
include_once $jwsdkDir . 'php/Exception/ResourceReadError.php';
include_once $jwsdkDir . 'php/Exception/TemplateCircleDependency.php';
include_once $jwsdkDir . 'php/Exception/TemplateReadError.php';
include_once $jwsdkDir . 'php/File.php';
include_once $jwsdkDir . 'php/File/Attacher.php';
include_once $jwsdkDir . 'php/File/Attacher/Css.php';
include_once $jwsdkDir . 'php/File/Attacher/Js.php';
include_once $jwsdkDir . 'php/File/Manager.php';
include_once $jwsdkDir . 'php/GlobalConfig.php';
include_once $jwsdkDir . 'php/Package.php';
include_once $jwsdkDir . 'php/Package/Auto.php';
include_once $jwsdkDir . 'php/Package/Config.php';
include_once $jwsdkDir . 'php/Package/DependencyReader.php';
include_once $jwsdkDir . 'php/Package/Manager.php';
include_once $jwsdkDir . 'php/Package/Simple.php';
include_once $jwsdkDir . 'php/Page.php';
include_once $jwsdkDir . 'php/Page/Manager.php';
include_once $jwsdkDir . 'php/Process.php';
include_once $jwsdkDir . 'php/Resource.php';
include_once $jwsdkDir . 'php/Resource/Converter.php';
include_once $jwsdkDir . 'php/Resource/Converter/Internal.php';
include_once $jwsdkDir . 'php/Resource/Converter/CssBase.php';
include_once $jwsdkDir . 'php/Resource/Converter/Css.php';
include_once $jwsdkDir . 'php/Resource/Converter/JwHtml.php';
include_once $jwsdkDir . 'php/Resource/Converter/SchemaJson.php';
include_once $jwsdkDir . 'php/Resource/Converter/Txt.php';
include_once $jwsdkDir . 'php/Resource/Converter/Html.php';
include_once $jwsdkDir . 'php/Resource/Converter/Json.php';
include_once $jwsdkDir . 'php/Resource/Converter/Js.php';
include_once $jwsdkDir . 'php/Resource/Converter/Jsx.php';
include_once $jwsdkDir . 'php/Resource/Converter/Less.php';
include_once $jwsdkDir . 'php/Resource/Converter/SassBase.php';
include_once $jwsdkDir . 'php/Resource/Converter/Sass.php';
include_once $jwsdkDir . 'php/Resource/Converter/Scss.php';
include_once $jwsdkDir . 'php/Resource/Converter/Styl.php';
include_once $jwsdkDir . 'php/Resource/Converter/Ts.php';
include_once $jwsdkDir . 'php/Resource/Converter/RefTs.php';
include_once $jwsdkDir . 'php/Resource/Converter/Util.php';
include_once $jwsdkDir . 'php/Resource/Manager.php';
include_once $jwsdkDir . 'php/Template.php';
include_once $jwsdkDir . 'php/Template/Manager.php';

try
{
	$mode = JWSDK_Mode::getMode((count($argv) > 1) ? $argv[1] : 'debug');
	$config = (count($argv) > 2) ? $argv[2] : null;
	if (!$mode) {
		$mode = JWSDK_Mode::getMode('debug');
		$config = (count($argv) > 1) ? $argv[1] : null;
	}
	if (!is_string($config)) {
		$config = file_exists('jwsdk-config/config.json') ? 'jwsdk-config' : '.';
	}
	if (!preg_match('/\.json$/', $config)) {
		$config .= '/config.json';
	}
	if (!file_exists($config)) {
		printUsage();
		exit(1);
	}
	$modeName = strtoupper($mode->getId());
	echo "Building frontend in $modeName mode with jWidget SDK " . JWSDK_Builder::VERSION . "...\n";
	$builder = new JWSDK_Builder($argv[0], $mode, $config);
	$builder->buildPages();
	$builder->saveCache();
}
catch (JWSDK_Exception $e)
{
	$message = $e->getMessage();
	fwrite(STDERR, "ERROR\n$message\n");
	exit(1);
}
catch (Exception $e)
{
	fwrite(STDERR, "UNEXPECTED ERROR\nPlease report to https://github.com/enepomnyaschih/jwsdk/issues/new\n\n$e\n");
	exit(2);
}

echo "Done\n";

exit(0);
