<?php
/*
 * The MIT License
 *
 * Copyright 2015 David Callizaya.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace BowerArtisan;

/**
 * Description of Bower
 *
 * @author David Callizaya <davidcallizaya@gmail.com>
 */
class Bower
{

    protected $components = [];

    public static function main()
    {
        $bower = new Bower();
        $base = '/home/david/php-5.6.5/www/public/bower_components';
        $bower->folder($base, $base);
        $head = [];
        foreach ($bower->components as $name => $component) {
            $bower->addComponentMains($head, $name, '.css');
        }
        $script = [];
        foreach ($bower->components as $name => $component) {
            $bower->addComponentMains($script, $name, '.js');
        }
        print_r($head);
        print_r($script);
    }

    function folder($path, $base)
    {
        foreach (glob($path . '/{,.}[!.,!..]*', GLOB_MARK | GLOB_BRACE)as $filename) {
            if (!is_file($filename) || basename($filename) != '.bower.json') {
                continue;
            }
            $bower = json_decode(file_get_contents($filename));
            $bower->basePath = dirname($filename);
            $name = $bower->name; //better to avoid duplicated packages
            $dirName = basename($bower->basePath); //avoid wrong missing dependencies
            if ($name != $dirName) {
                echo "Warning: Folder($dirName) does not match with its package name ($name)\n";
            }
            if (!isset($this->components[$name])) {
                $this->components[$name] = $bower;
            }
        }
        foreach (glob("$path/*", GLOB_ONLYDIR) as $filename) {
            $this->folder($filename, $base);
        }
    }

    function getMinFile($basePath, $relativePath)
    {
        if (substr($relativePath, 0, 1) == '.') {
            $filename = $basePath . substr($relativePath, 1);
        } else {
            $filename = $basePath . '/' . $relativePath;
        }
        $ext = substr($filename, strrpos($filename, '.'));
        $extlen = strlen($ext);
        if (substr($filename, -4 - $extlen) == ".min$ext") {
            $bestFilename = $filename;
        } elseif (substr($filename, -$extlen) == $ext) {
            $filenameM = substr($filename, 0, -$extlen) . ".min$ext";
            $bestFilename = file_exists($filenameM) ? $filenameM : $filename;
        }
        return basename($basePath) . str_replace('\\', '/', substr($bestFilename, strlen($basePath)));
    }

    function getComponentMains($name, $type)
    {
        $res = [];
        if (!isset($this->components[$name]->main)) {
            $urls = [];
        } elseif (is_string($this->components[$name]->main)) {
            $urls = [$this->components[$name]->main];
        } else {
            $urls = $this->components[$name]->main;
        }
        foreach ($urls as $filename) {
            $ext = substr($filename, strrpos($filename, '.'));
            if ($ext == '.less') {
                $ext = '.css';
                $filename = substr($filename, 0, -5) . $ext;
            }
            $uri = $this->getMinFile($this->components[$name]->basePath, $filename);
            if ($ext === $type) {
                $res[] = $uri;
            }
        }
        return $res;
    }

    function addComponentMains(&$res, $name, $type)
    {
        if (isset($this->components[$name]->dependencies)) {
            foreach ($this->components[$name]->dependencies as $depName => $depVersion) {
                if (!isset($this->components[$depName])) {
                    echo "Warning: Package $depName ($depVersion) not found for $name\n";
                }
                $this->addComponentMains($res, $depName, $type);
            }
        }
        $req = $this->getComponentMains($name, $type);
        foreach ($req as $uri) {
            if (array_search($uri, $res) === false) {
                $res[] = $uri;
            }
        }
        return $res;
    }
}
