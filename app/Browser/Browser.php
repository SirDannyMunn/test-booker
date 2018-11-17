<?php

namespace App\Browser;

use Closure;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tpccdaniel\DuskSecure\Browser as DuskBrowser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * Reporting browser for console commands
 */
class Browser
{

    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    private $browser;

    /**
     * @param Closure $callback
     * @throws Throwable
     */
    public function browse(Closure $callback)
    {
        if (!$this->browser) {
            $this->browser = $this->newBrowser($this->createWebDriver());
        }

        try {
            $callback($this->browser);
        } catch (Exception $e) {
//            $filename = now()->format('y-m-d h.m i') .' '. preg_replace('/[^A-Za-z0-9 _ .-]/', ' ', $e->getMessage());
            $time = now()->format('y-m-d h.m i');
            Log::alert("{$time} - dusk failed: {$e->getMessage()}");
            $this->browser->screenshot(now()->format('y-m-d h.m i'));
            throw $e;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    function __destruct()
    {
        if ($this->browser) {
            $this->closeBrowser();
        }
    }

    /**
     * @throws Exception
     */
    protected function closeBrowser()
    {
        if (!$this->browser) {
            throw new Exception("The browser hasn't been initialized yet");
        }
        $this->browser->quit();
        $this->browser = null;
    }

    /**
     * @param $driver
     * @return DuskBrowser
     */
    protected function newBrowser($driver)
    {
        return new DuskBrowser($driver);
    }

    /**
     * Create the remote web driver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function createWebDriver()
    {
        return retry(5, function () {
            return $this->driver();
        }, 50);
    }

    /**
     * @return RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless'
        ]);
        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $driver = RemoteWebDriver::create(
            'http://127.0.0.1:9515', $capabilities
        );

        return $driver;
    }
}
