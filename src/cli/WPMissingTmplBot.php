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

use com_brucemyers\WPMissingTmplBot\WPMissingTmplBot;
use com_brucemyers\MediaWiki\WikiResultWriter;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Timer;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;

$clidir = dirname(__FILE__);
$GLOBALS['botname'] = 'WPMissingTmplBot';

require $clidir . DIRECTORY_SEPARATOR . 'bootstrap.php';

    $activerules = array(
        'WikiProject Protected areas' => array(
                        'WPCategory' => 'Articles of WikiProject Protected areas',
                        'altWPCategory' => '',
                        'articleCategoryPart' => array('Protected area','Special Areas of Conservation','Special Protection Area‎',
        					'Game p?reserve','Marine park‎','Marine p?reserve','Natural monument‎','Nature park','Nature p?reserve',
                        	'Conservation area','Special Protection Area','Wildlife sanctuar','Faunal p?reserve',
                        	'Biosphere p?reserve','Bird sanctuar','Wildlife refuge','Wilderness area'),
                        'resultPage' => 'Wikiproject:Protected areas/Missing template',
                        'excludeWords' => array('National Park Service')
        ),
        'WikiProject Romania' => array(
                        'WPCategory' => 'Unknown-importance Romania articles',
                        'altWPCategory' => array('High-importance Romania articles‎', 'Low-importance Romania articles‎',
        					'Mid-importance Romania articles‎', 'NA-importance Romania articles‎', 'Top-importance Romania articles‎'),
                        'articleCategoryPart' => array('romania'),
                        'resultPage' => 'Wikiproject:Romania/Missing template',
                        'excludeWords' => array()
        ),
    	'WikiProject Michigan' => array(
                        'WPCategory' => 'WikiProject Michigan articles',
                        'altWPCategory' => 'Michigan road transport articles',
                        'articleCategoryPart' => array('Michigan','Detroit'),
                        'resultPage' => 'Wikiproject:Michigan/Missing template',
                        'excludeWords' => array('alumni','people','faculty','actors','actresses','mayors','members','coaches','players','seasons',
                            'artists','musicians','presidents','prisoners','personalities','writers','wikipedian','directors','legislators',
                            'politicians','athletes','article','from Michigan','swimmers','deaths','suicides','Michigan City, Indiana',
                            'executives','owners','personnel','scouts','broadcasters','draft picks','managers','judges','bishops',
                        	'sportspeople','republicans','democrats','lawyers','burials','fellows')
        ),
    );

try {
    $ruletype = 'custom'; // 'active', 'custom', 'all'

    $timer = new Timer();
    $timer->start();
    Logger::log("Started");

    $url = Config::get(MediaWiki::WIKIURLKEY);
    $wiki = new MediaWiki($url);
    $username = Config::get(MediaWiki::WIKIUSERNAMEKEY);
    $password = Config::get(MediaWiki::WIKIPASSWORDKEY);
    $wiki->login($username, $password);

    if ($ruletype == 'active') $rules = $activerules;
    elseif ($ruletype== 'custom') $rules = array('WikiProject Romania' => $activerules['WikiProject Romania']);
    //elseif ($ruletype== 'custom') $rules = array('WikiProject Protected areas' => $activerules['WikiProject Protected areas']);
    //    elseif ($ruletype== 'custom') $rules = array('WikiProject Michigan' => $activerules['WikiProject Michigan']);
    else {
        $data = $wiki->getpage('User:AlexNewArtBot/Master');
        $rules = $data; // TODO: Parse WPMissingTmplBot page for rules
    }


    //$bot = new WPMissingTmplBot($wiki, $rules, new WikiResultWriter($wiki));
    $bot = new WPMissingTmplBot($wiki, $rules, new FileResultWriter('/home/bruce/temp/tedderbot/'));

    $ts = $timer->stop();

    Logger::log(sprintf("Elapsed Time: %d days %02d:%02d:%02d\n", $ts['days'], $ts['hours'], $ts['minutes'], $ts['seconds']));
} catch (Exception $ex) {
    Logger::log($ex->getMessage() . "\n" . $ex->getTraceAsString());
}
