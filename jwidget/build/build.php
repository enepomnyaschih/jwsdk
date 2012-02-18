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

$MODES = array(
    'debug' => array(
        'config'   => 'debug',
        'compress' => false,
        'link'     => true,
        'linkMin'  => false,
        'descr'    =>
            "        Link html pages in debug mode\n" .
            "        (no compression, external services are filtered)."
    ),
    
    'release' => array(
        'config'   => 'release',
        'compress' => true,
        'link'     => true,
        'linkMin'  => true,
        'descr'    =>
            "        Compress and link html pages in release mode."
    ),
    
    'compress' => array(
        'config'   => 'release',
        'compress' => true,
        'link'     => false,
        'descr'    =>
            "        Just compress source files."
    ),
    
    'link' => array(
        'config'   => 'release',
        'compress' => false,
        'link'     => true,
        'linkMin'  => true,
        'descr'    =>
            "        Link html pages in release mode using existing compressed files.\n" .
            "        Use only if you are sure that source files were not changed."
    )
);

if ((count($argv) < 2) || !isset($MODES[$argv[1]]))
{
    echo "USAGE php build.php <mode>\n\n" .
         "Supported modes:\n";
    
    foreach ($MODES as $key => $value)
        echo "    $key\n" . $value['descr'] . "\n";
    
    exit(1);
}

function fopen_recursive($path, $mode, $chmod = 0755)
{
    $i = strrpos($path, '/');
    if ($i !== false)
    {
        $directory = substr($path, 0, $i);
        if (!is_dir($directory) && !mkdir($directory, $chmod, 1))
            return false;
    }
    
    return @fopen($path, $mode);
}

function removeEmptyStrings($source)
{
    $result = array();
    foreach ($source as $value)
    {
        $row = trim($value);
        if (empty($row))
            continue;
        
        $result[] = $row;
    }
    
    return $result;
}

class Logger
{
    private $f;
    
    public function __construct()
    {
        $this->f = fopen('build.log', 'a');
        if ($this->f === false)
            throw new Exception("Can't open log file");
    }
    
    public function __destruct()
    {
        @fclose($this->f);
    }
    
    public function log($msg)
    {
        $msg = $msg . "\n";
        echo $msg;
        fwrite($this->f, $msg);
    }
}

$logger = new Logger();

function logLine($msg)
{
    global $logger;
    $logger->log($msg);
}

class Variables
{
    private $vars;
    
    public function __construct($base = null, $vars = null)
    {
        $this->vars = array(
            'services' => array(),
            'custom'   => array()
        );
        
        if ($base !== null)
            $this->apply($base->getVars());
        
        $this->apply($vars);
    }
    
    public function getVars()
    {
        return $this->vars;
    }
    
    public function getServices()
    {
        $result = array();
        $services = $this->vars['services'];
        foreach ($services as $key => $value)
        {
            if ($value)
                $result[] = $key;
        }
        
        return $result;
    }
    
    public function getCustom()
    {
        return $this->vars['custom'];
    }
    
    public function apply($vars)
    {
        if ($vars === null)
            return;
        
        $this->applyVar($vars, 'services');
        $this->applyVar($vars, 'custom');
    }
    
    private function applyVar($vars, $name)
    {
        if (!isset($vars[$name]))
            return;
        
        $target = &$this->vars[$name];
        $source = $vars[$name];
        
        foreach ($source as $key => $value)
            $target[$key] = $value;
    }
}

class Builder
{
    private $config;    // Dictionary
    private $mode;      // Dictionary
    private $variables; // Variables
    
    private $jslists;   // Map from String(name) to String(scripts to include)
    private $jspaths;   // Map from String(jslistName) to Array of String(jsPath)
    private $includes;  // Map from String(name) to Dictionary
    private $services;  // Map from String(name) to String(html)
    private $templates; // Map from String(name) to String(html)
    
    public function build()
    {
        $this->variables = new Variables();
        
        $this->jslists   = array();
        $this->jspaths   = array();
        $this->includes  = array();
        $this->services  = array();
        $this->templates = array();
        
        $this->readConfig();
        $this->readMode();
        
        $this->compress();
        $this->link();
    }
    
    private function readConfig()
    {
        $contents = @file_get_contents('config.json', 'r');
        if ($contents === false)
            throw new Exception("Can't open main config (path: config.json)");
        
        $this->config = json_decode($contents, true);
    }
    
    private function readMode()
    {
        global $argv;
        global $MODES;
        
        $modeName = $argv[1];
        $this->mode = $MODES[$modeName];
        
        $this->readModeConfig('common');
        $this->readModeConfig($this->mode['config']);
    }
    
    private function readModeConfig($name)
    {
        $path     = $this->config['modesPath'] . "/$name.json";
        $contents = @file_get_contents($path);
        if ($contents === false)
            throw new Exception("Can't open mode config (name: $name, path: $path)");
        
        $config = json_decode($contents, true);
        
        $this->variables->apply($config);
    }
    
    private function compress()
    {
        if ($this->mode['compress'])
            logLine('Compressing JS lists...');
        else
            logLine('Reading JS lists...');
        
        $this->compressDir('');
    }

    private function compressDir($path)
    {
        $fullPath = $this->config['jslistsPath'] . $path;
        
        if (is_file($fullPath))
        {
            $this->compressFile($path);
            return;
        }
        
        $dir = @opendir($fullPath);
        if ($dir === false)
            throw new Exception("Can't open jslists folder (path: $dir)");
        
        while (false !== ($child = readdir($dir)))
        {
            if ($child !== '.' && $child !== '..')
                $this->compressDir("$path/$child");
        }
        closedir($dir);
    }
    
    private function compressFile($path)
    {
        if (!preg_match('/\.jslist$/', $path))
            return;
        
        $compress = $this->mode['compress'];
        
        // Delete extension from path
        $path = substr($path, 1, strrpos($path, '.') - 1);
        
        if ($this->mode['compress'])
            logLine("Compressing $path");
        
        $jsListPath = $this->config['jslistsPath'] . "/$path.jslist";
        $outputPath = $this->config['publicPath'] . '/' . $this->config['buildUrl'] . "/$path.min.js";
        $mergePath  = $this->config['tempPath'] . "/$path.js";
        
        $contents = @file_get_contents($jsListPath);
        if ($contents === false)
            throw new Exception("Can't open jslist file (name: $path, path: $jsListPath)");
        
        $scripts = explode("\n", str_replace("\r", "\n", $contents));
        $scripts = removeEmptyStrings($scripts);
        
        $this->jspaths[$path] = $scripts;
        
        if ($compress)
        {
            $output = fopen_recursive($mergePath, 'w');
            if ($output === false)
                throw new Exception("Can't create temporary merged js file (name: $path, path: $mergePath)");
        }
        
        $includeBuf = array();
        for ($i = 0; $i < count($scripts); ++$i)
        {
            $script = $scripts[$i];
            $includeBuf[] = $this->includeJs($script);
            
            if (!$compress)
                continue;
            
            $script = $this->config['publicPath'] . "/$script";
            $scriptContent = @file_get_contents($script);
            if ($scriptContent === false)
                throw new Exception("Can't open js file (path: $script)");
            
            fwrite($output, $scriptContent . "\n");
        }
        
        if ($compress)
        {
            fclose($output);
            
            $outputDir = substr($outputPath, 0, strrpos($outputPath, '/'));
            if (!is_dir($outputDir))
                @mkdir($outputDir, 0755, 1);
            
            $yuiOutput = array();
            $yuiStatus = 0;
            
            $command = "java -jar yuicompressor.jar $mergePath -o $outputPath --charset utf-8 --line-break 8000 2>> yui.log";
            exec($command, $yuiOutput, $yuiStatus);
            
            if ($yuiStatus != 0)
                throw new Exception("Error while running YUI Compressor (name: $path, input: $mergePath, output: $outputPath). See signin/build/yui.log for details");
        }
        
        if ($this->mode['linkMin'])
            $this->jslists[$path] = $this->includeJs($this->config['buildUrl'] . "/$path.min.js");
        else
            $this->jslists[$path] = implode("\n", $includeBuf);
    }
    
    private function link()
    {
        if (!$this->mode['link'])
            return;
        
        logLine('Linking pages...');
        
        $this->linkDir('');
    }

    private function linkDir($path)
    {
        $fullPath = $this->config['configPath'] . '/' . $this->config['pagesFolder'] . "$path";
        
        if (is_file($fullPath))
        {
            $this->linkFile($path);
            return;
        }
        
        $dir = @opendir($fullPath);
        if ($dir === false)
            throw new Exception("Can't open page configs folder (path: $dir)");
        
        while (false !== ($child = readdir($dir)))
        {
            if ($child !== '.' && $child !== '..')
                $this->linkDir("$path/$child");
        }
        closedir($dir);
    }
    
    private function linkFile($path)
    {
        if (!preg_match('/\.json$/', $path))
            return;
        
        // Delete extension from path
        $path = substr($path, 1, strrpos($path, '.') - 1);
        
        $pageConfig = $this->readPageConfig($this->config['pagesFolder'] . "/$path");
        $outputPath = $this->config['publicPath'] . '/' . $this->config['pagesUrl'] . "/$path.html";
        
        if (!isset($pageConfig['template']))
            throw new Exception("Page template is undefined (page: $path)");
        
        $templateName = $pageConfig['template'];
        $template = $this->readPageTemplate($templateName);
        
        $variables = new Variables($this->variables, $pageConfig);
        
        $replaces = $variables->getCustom();
        $replaces['sources']  = $this->formatSources ($pageConfig, $path);
        $replaces['services'] = $this->formatServices($variables->getServices());
        
        $replaces['title'] =
            isset($pageConfig['title']) ? $pageConfig['title'] :
            (isset($replaces['title']) ? $replaces['title'] : '');
        
        $replaceKeys   = array_keys  ($replaces);
        $replaceValues = array_values($replaces);
        
        for ($i = 0; $i < count($replaceKeys); $i++)
            $replaceKeys[$i] = '${' . $replaceKeys[$i] . '}';
        
        $html = str_replace($replaceKeys, $replaceValues, $template);
        
        $output = fopen_recursive($outputPath, 'w');
        if ($output === false)
            throw new Exception("Can't create linked page file (name: $path, path: $outputPath)");
        
        fwrite($output, $html);
        fclose($output);
    }
    
    private function readPageConfig($name)
    {
        if (isset($this->includes[$name]))
            return $this->includes[$name];
        
        $path = $this->config['configPath'] . "/$name.json";
        $contents = @file_get_contents($path);
        if ($contents === false)
            throw new Exception("Can't open page config file (name: $name, path: $path)");
        
        $data = json_decode($contents, true);
        if (isset($data['base']))
        {
            $baseName = $data['base'];
            $base = $this->readPageConfig($baseName);
            
            foreach ($base as $key => $value)
            {
                if (is_array($value))
                {
                    $data[$key] = isset($data[$key]) ? array_merge($value, $data[$key]) : $value;
                    continue;
                }
                
                if (isset($data[$key]))
                    continue;
                
                $data[$key] = $value;
            }
        }
        
        $this->includes[$name] = $data;
        
        return $data;
    }
    
    private function readPageTemplate($name)
    {
        if (isset($this->templates[$name]))
            return $this->templates[$name];
        
        $path = $this->config['templatesPath'] . "/$name.html";
        $contents = @file_get_contents($path);
        if ($contents === false)
            throw new Exception("Can't open template file (name: $name, path: $path)");
        
        $this->templates[$name] = $contents;
        return $contents;
    }
    
    function includeSource($path, $template)
    {
        $jsPath = $this->config['publicPath'] . "/$path";
        if (!file_exists($jsPath))
            throw new Exception("Can't find js file (path: $path)");
        
        $path = $this->config['urlPrefix'] . "$path?timestamp=" . filemtime($jsPath);
        $path = htmlspecialchars($path);
        return '        ' . str_replace('%path%', $path, $template);
    }
    
    function includeCss($path)
    {
        return $this->includeSource($path, '<link rel="stylesheet" type="text/css" href="%path%" />');
    }
    
    function includeJs($path)
    {
        return $this->includeSource($path, '<script type="text/javascript" charset="utf-8" src="%path%"></script>');
    }
    
    function includeJsList($path, &$jspaths)
    {
        if (preg_match('/\|auto$/', $path))
            $path = substr($path, 0, strrpos($path, '.')) . ($this->mode['linkMin'] ?  '.min.js' : '.js');
        
        if (preg_match('/\.js$/', $path))
        {
            $jspaths[] = $path;
            return $this->includeJs($path);
        }
        
        foreach ($this->jspaths[$path] as $jspath)
            $jspaths[] = $jspath;
        
        return $this->jslists[$path];
    }
    
    private function formatSources($pageConfig, $path)
    {
        $buf = array();
        if (isset($pageConfig['css']))
        {
            foreach ($pageConfig['css'] as $value)
                $buf[] = $this->includeCss($value);
        }
        
        $jspaths = array();
        if (isset($pageConfig['js']))
        {
            foreach ($pageConfig['js'] as $value)
                $buf[] = $this->includeJsList($value, $jspaths);
        }
        
        $jspathsUnique = array_unique($jspaths);
        if (count($jspaths) != count($jspathsUnique))
            throw new Exception("Duplicated JS file detected while linking $path");
        
        return implode("\n", $buf);
    }
    
    private function formatServices($services)
    {
        $buf = array();
        for ($i = 0; $i < count($services); $i++)
            $buf[] = $this->readService($services[$i]);
        
        return implode("\n", $buf);
    }
    
    private function readService($name)
    {
        if (isset($this->services[$name]))
            return $this->services[$name];
        
        $path = $this->config['servicesPath'] . "/$name.html";
        $contents = @file_get_contents($path);
        if ($contents === false)
            throw new Exception("Can't open service file (name: $name, path: $path)");
        
        $this->services[$name] = $contents;
        return $contents;
    }
}

$date = date('Y-m-d H:i:s');
logLine("\n\n[$date]");
logLine('Building frontend...');

try
{
    $builder = new Builder();
    $builder->build();
}
catch (Exception $e)
{
    logLine("ERROR! " . $e->getMessage());
    exit(1);
}

logLine('Done');

exit(0);

?>
