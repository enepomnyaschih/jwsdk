<?php

/*
    jWidget project builder.
    
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

include_once 'php/Util/File.php';
include_once 'php/Util/String.php';

include_once 'php/Mode.php';
include_once 'php/Mode/Debug.php';
include_once 'php/Mode/Release.php';
include_once 'php/Mode/Compress.php';
include_once 'php/Mode/Link.php';

if ((count($argv) < 2) || !JWSDK_Mode::getMode($argv[1]))
{
	echo "USAGE php build.php <mode>\n\n" .
	     "Supported modes:\n" .
	     JWSDK_Mode::getModesDescription();
	
	exit(1);
}

include_once 'php/Log.php';

include_once 'php/Builder.php';
include_once 'php/Converter.php';
include_once 'php/Converter/JwHtml.php';
include_once 'php/Converter/Txt.php';
include_once 'php/Converter/Html.php';
include_once 'php/Converter/Json.php';
include_once 'php/Converter/Js.php';
include_once 'php/Converter/Util.php';
include_once 'php/GlobalConfig.php';
include_once 'php/Variables.php';

$date = date('Y-m-d H:i:s');
JWSDK_Log::logTo('build.log', "\n\n[$date]");
JWSDK_Log::logTo('build.log', 'Building frontend...');

try
{
    $builder = new JWSDK_Builder();
    $builder->build();
}
catch (Exception $e)
{
    JWSDK_Log::logTo('build.log', "ERROR! " . $e->getMessage());
    exit(1);
}

JWSDK_Log::logTo('build.log', 'Done');

exit(0);
