<?php
/**
 Copyright 2014 Myers Enterprises II

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

namespace com_brucemyers\test\CleanupWorklistBot;

use com_brucemyers\CleanupWorklistBot\Categories;
use com_brucemyers\CleanupWorklistBot\CleanupWorklistBot;
use com_brucemyers\CleanupWorklistBot\ProjectPages;
use com_brucemyers\CleanupWorklistBot\ReportGenerator;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\FileCache;
use com_brucemyers\MediaWiki\FileResultWriter;
use com_brucemyers\test\CleanupWorklistBot\CreateTables;
use UnitTestCase;
use PDO;

class TestReports extends UnitTestCase
{

    public function testGenerate()
    {
    	$enwiki_host = Config::get(CleanupWorklistBot::ENWIKI_HOST);
    	$tools_host = Config::get(CleanupWorklistBot::TOOLS_HOST);
    	$user = Config::get(CleanupWorklistBot::LABSDB_USERNAME);
    	$pass = Config::get(CleanupWorklistBot::LABSDB_PASSWORD);

    	$dbh_enwiki = new PDO("mysql:host=$enwiki_host;dbname=enwiki_p", $user, $pass);
    	$dbh_tools = new PDO("mysql:host=$tools_host;dbname=s51454__CleanupWorklistBot", $user, $pass);
    	$dbh_enwiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$dbh_tools->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    	new CreateTables($dbh_enwiki, $dbh_tools);

    	$categories = new Categories($dbh_enwiki, $dbh_tools);
    	$categories->load(false);

    	$asof_date = getdate();
    	$outputdir = Config::get(CleanupWorklistBot::HTMLDIR);
    	$urlpath = Config::get(CleanupWorklistBot::URLPATH);

    	$project_pages = new ProjectPages($dbh_enwiki, $dbh_tools);

    	$wikiDir = Config::get(CleanupWorklistBot::OUTPUTDIR);
    	$wikiDir = str_replace(FileCache::CACHEBASEDIR, Config::get(Config::BASEDIR), $wikiDir);
    	$wikiDir = preg_replace('!(/|\\\\)$!', '', $wikiDir); // Drop trailing slash
    	$wikiDir .= DIRECTORY_SEPARATOR;
    	$resultwriter = new FileResultWriter($wikiDir);

    	$repgen = new ReportGenerator($dbh_tools, $outputdir, $urlpath, $asof_date, $resultwriter);

    	$category = 'Good_article_nominees';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);

    	$category = 'Featured_articles';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);

        $category = 'India';
    	$page_count = $project_pages->load($category);

    	$repgen->generateReports($category, false, $page_count);

    	$category = 'Michigan';
    	$page_count = $project_pages->load($category);

    	$csvpath = $outputdir . 'csv' . DIRECTORY_SEPARATOR . $category . '.csv';
    	$hndl = fopen($csvpath, 'wb');
    	fwrite($hndl, '"Article","Importance","Class","Count","Oldest month","Categories"' . "\n");
    	fwrite($hndl, '"Detroit, Michigan","NA","Unassessed","2","March 2013","Articles needing cleanup (May 2013, March 2013)"' . "\n");
    	fwrite($hndl, '""K" Brighton, Michigan","NA","Unassessed","2","March 2013","Articles needing cleanup (May 2013, March 2013)"' . "\n");
    	fclose($hndl);

    	$repgen->generateReports($category, true, $page_count, false, 1);
    	$repgen->generateReports($category, true, $page_count, true);
    }
}