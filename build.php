<?php

/*
    jWidget project builder.
    
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

include_once 'php/Util/Array.php';
include_once 'php/Util/Css.php';
include_once 'php/Util/File.php';
include_once 'php/Util/Json.php';
include_once 'php/Util/String.php';
include_once 'php/Util/Url.php';

include_once 'php/Mode.php';
include_once 'php/Mode/Debug.php';
include_once 'php/Mode/Release.php';

if ((count($argv) < 2) || !JWSDK_Mode::getMode($argv[1]))
{
	echo "USAGE php build.php <mode> [<path_to_config.json>]\n\n" .
	     "Supported modes:\n" .
	     JWSDK_Mode::getModesDescription();
	
	exit(1);
}

include_once 'php/Log.php';

include_once 'php/BuildCache.php';
include_once 'php/BuildCache/Input.php';
include_once 'php/BuildCache/Output.php';
include_once 'php/Builder.php';
include_once 'php/Exception.php';
include_once 'php/Exception/CanNotMakeDirectory.php';
include_once 'php/Exception/CanNotReadFile.php';
include_once 'php/Exception/CanNotWriteFile.php';
include_once 'php/Exception/CompressorError.php';
include_once 'php/Exception/DuplicatedResourceError.php';
include_once 'php/Exception/DynamicLoaderDisabled.php';
include_once 'php/Exception/InsufficientFileType.php';
include_once 'php/Exception/InvalidFileFormat.php';
include_once 'php/Exception/InvalidResourceFormat.php';
include_once 'php/Exception/InvalidResourceParameter.php';
include_once 'php/Exception/InvalidResourceType.php';
include_once 'php/Exception/LessError.php';
include_once 'php/Exception/MethodNotImplemented.php';
include_once 'php/Exception/PackageCompressError.php';
include_once 'php/Exception/PageBuildError.php';
include_once 'php/Exception/PageReadError.php';
include_once 'php/Exception/ResourceConvertionError.php';
include_once 'php/Exception/ResourceReadError.php';
include_once 'php/Exception/SassError.php';
include_once 'php/Exception/StylusError.php';
include_once 'php/Exception/TemplateCircleDependency.php';
include_once 'php/Exception/TemplateReadError.php';
include_once 'php/File.php';
include_once 'php/File/Attacher.php';
include_once 'php/File/Attacher/Css.php';
include_once 'php/File/Attacher/Js.php';
include_once 'php/File/Manager.php';
include_once 'php/GlobalConfig.php';
include_once 'php/Package.php';
include_once 'php/Package/Auto.php';
include_once 'php/Package/Config.php';
include_once 'php/Package/DependencyReader.php';
include_once 'php/Package/Manager.php';
include_once 'php/Package/Simple.php';
include_once 'php/Page.php';
include_once 'php/Page/Manager.php';
include_once 'php/Resource.php';
include_once 'php/Resource/Converter.php';
include_once 'php/Resource/Converter/Internal.php';
include_once 'php/Resource/Converter/Css.php';
include_once 'php/Resource/Converter/JwHtml.php';
include_once 'php/Resource/Converter/SchemaJson.php';
include_once 'php/Resource/Converter/Txt.php';
include_once 'php/Resource/Converter/Html.php';
include_once 'php/Resource/Converter/Json.php';
include_once 'php/Resource/Converter/Js.php';
include_once 'php/Resource/Converter/Less.php';
include_once 'php/Resource/Converter/SassBase.php';
include_once 'php/Resource/Converter/Sass.php';
include_once 'php/Resource/Converter/Scss.php';
include_once 'php/Resource/Converter/Styl.php';
include_once 'php/Resource/Converter/Util.php';
include_once 'php/Resource/Manager.php';
include_once 'php/Template.php';
include_once 'php/Template/Manager.php';

$date = date('Y-m-d H:i:s');
JWSDK_Log::logTo('build.log', "\n\n[$date]");
JWSDK_Log::logTo('build.log', 'Building frontend with jWidget SDK 0.4...');

try
{
    $builder = new JWSDK_Builder($argv[0], $argv[1], (count($argv) < 3) ? null : $argv[2]);
    $builder->buildPages();
    $builder->saveCache();
}
catch (JWSDK_Exception $e)
{
    JWSDK_Log::logTo('build.log', "\nERROR\n" . $e->getMessage());
    exit(1);
}
catch (Exception $e)
{
    JWSDK_Log::logTo('build.log', "\nUNEXPECTED ERROR\nPlease report to https://github.com/enepomnyaschih/jwsdk/issues/new\n\n" . $e);
    exit(1);
}

JWSDK_Log::logTo('build.log', 'Done');

exit(0);
