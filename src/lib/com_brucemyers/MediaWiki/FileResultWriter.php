<?php
/**
 Copyright 2013 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

namespace com_brucemyers\MediaWiki;

class FileResultWriter implements ResultWriter
{
    protected $filepath;

    /**
     * Constructor
     *
     * @param $filepath string Full path to output directory including trailing /
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    public function writeResults($resultpage, $output, $comment)
    {
        $resultpage = str_replace(array(':','/'), '.', $resultpage);
        $resultpage = str_replace('User.', '', $resultpage);
        $filepath = $this->filepath . $resultpage . '.txt';
        $output .= "$comment\n";
        file_put_contents($filepath, $output);
    }
}