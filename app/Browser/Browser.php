<?php

namespace App\Browser;

use Closure;
use Exception;
use Facebook\WebDriver\Firefox\FirefoxPreferences;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverPlatform;
use Symfony\Component\Process\Process;
use Throwable;
use Tpccdaniel\DuskSecure\Browser as DuskBrowser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tpccdaniel\DuskSecure\BrowserInstance;
use Tpccdaniel\DuskSecure\Concerns\ProvidesBrowser;
use Tpccdaniel\DuskSecure\Selenium\StartsSelenium;
use Tpccdaniel\DuskSecure\Selenium\StartsXvfb;

//use Tpccdaniel\DuskSecure\Selenium\StartsSelenium;
//use Tpccdaniel\DuskSecure\Selenium\StartsXvfb;

/**
 * Reporting browser for console commands
 */
class Browser extends BrowserInstance
{
    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    protected $browser;

    /**
     * @param Closure $callback
     * @throws Throwable
     */
    public function browse(Closure $callback)
    {
        $this->prepare();

        try {
            $callback($this->browser);
        } catch (Exception $e) {
//            $filename = now()->format('y-m-d h.m i') .' '. preg_replace('/[^A-Za-z0-9 _ .-]/', ' ', $e->getMessage());
//            $time = now()->format('y-m-d h.i.s');
//            Log::alert("{$time} - dusk failed: {$e->getMessage()}");
//            $this->browser->screenshot(now()->format('y-m-d h.m i'));
            throw $e;
        } catch (Throwable $e) {
            throw $e;
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
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public function prepare()
    {
//        static::$seleniumProcess = static::buildSeleniumProcess();
//        static::$seleniumProcess->start();
//        ProvidesBrowser::afterClass(function () {
//            static::stopSelenium();
//        });
//        sleep(4);


        if (!$this->browser) {
            $this->browser = $this->newBrowser($this->createWebDriver());
        }
    }

    /**
     * @return RemoteWebDriver
     */
    protected function driver()
    {

//        exec('rm -r '.base_path('chromedriver.log'));

        static::startChromeDriver([
//            '--verbose',
            '--log-path=chromedriver.log'
        ]);

//        $url = '127.0.0.1:8123';
//        $url = '0.0.0.0:8123';
        $url = 'localhost:3128';
        $proxy = ['proxyType' => 'manual', 'httpProxy' => $url, 'sslProxy' => $url, 'ftpProxy' => $url];

        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--disable-accelerated-jpeg-decoding',
//            '--ignore-certificate-errors',
//            '--allow-insecure-localhost',
//            '--disable-background-networking',
//            '--disable-client-side-phishing-detection',
//            '--disable-default-apps',
//            '--aggressive-cache-discard',
//            '--ignore-urlfetcher-cert-requests',
//            '--disable-hang-monitor',
//            '--disable-popup-blocking',
//            '--disable-prompt-on-repost',
//            '--disable-sync',
//            '--disable-web-resources',
//            '--enable-automation',
//            '--incognito',
//            '--enable-logging',
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(WebDriverCapabilityType::PROXY, $proxy);
        $capabilities->setCapability(WebDriverCapabilityType::ACCEPT_SSL_CERTS, true);
        $capabilities->setCapability("acceptInsecureCerts", true);
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

//        $capabilities = DesiredCapabilities::firefox();
//        $capabilities = new DesiredCapabilities([
//            "alwaysMatch" => [
//                WebDriverCapabilityType::BROWSER_NAME => WebDriverBrowserType::FIREFOX,
//                WebDriverCapabilityType::PLATFORM => WebDriverPlatform::ANY,
//                'acceptInsecureCerts', "true"
//            ]
//        ]);
//        $options = new FirefoxProfile();
//        $options->setPreference(FirefoxPreferences::READER_PARSE_ON_LOAD_ENABLED, false);
//        $options->setPreference('portPreference', '4444');
//        $capabilities->setCapability(FirefoxDriver::PROFILE, $options);
//        $capabilities->setCapability(
//            FirefoxDriver::PROFILE,
//            $options
//        );

        $driver = RemoteWebDriver::create(
            'http://127.0.0.1:9515', $capabilities,
            1000 * 1000,
            1000 * 1000
        );

        return $driver;
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

    protected static function buildSeleniumProcess() : Process
    {
        if (static::$seleniumPath) {
            $seleniumPath = realpath(static::$seleniumPath);
        } else {
            $seleniumPath = realpath(base_path('selenium-server-standalone-3.141.59.jar'));
        }

        if ($seleniumPath === false) {
            throw new \RuntimeException(
                "Invalid path to selenium server [{$seleniumPath}]."
            );
        }
        if (static::$geckoDriverPath) {
            $geckoDriverPath = realpath(static::$geckoDriverPath);
        } else {
            $geckoDriverPath = realpath(base_path('geckodriver'));
        }
        if ($geckoDriverPath === false) {
            throw new \RuntimeException(
                "Invalid path to geckodriver [{$geckoDriverPath}]."
            );
        }

        $processBuilder = (new Process([
            'java',
            "-webdriver.gecko.driver=$geckoDriverPath",
            '-jar',
            '-enablePassThrough false',
            $seleniumPath
        ]));

//        if (env('HEADLESS_MODE')) {
//            $processBuilder->setEnv('DISPLAY', ':10');
//        }

        $processBuilder->setEnv(['DISPLAY' => ':10']);

        return $processBuilder;
    }
}