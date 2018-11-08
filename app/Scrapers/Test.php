<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 07/11/2018
 * Time: 23:40
 */

namespace App\Scrapers;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class Test
{
    public function scrape()
    {
        $host = 'http://localhost:4444/wd/hub';
        $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
    }
}

(new Test)->scrape();