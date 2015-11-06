<?php

/*
 * The MIT License
 *
 * Copyright 2015 David Callizaya <davidcallizaya@gmail.com>.
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
namespace BowerArtisan\Console\Commands;

use Illuminate\Console\Command;
use BowerArtisan\Bower;

/**
 * Description of BowerCommand
 *
 * @author David Callizaya <davidcallizaya@gmail.com>
 */
class BowerHtmlCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bower:html {filename} {bower_components}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a base html page with bower dependencies.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $filename = $this->argument('filename');
        $bowerComponents = $this->argument('bower_components');
        $bower = new Bower();
        $base = realpath($bowerComponents);
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
}
