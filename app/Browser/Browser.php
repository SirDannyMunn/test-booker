<?php

namespace App\Browser;

use App\Jobs\ScrapeDVSA;
use App\Modules\ProxyManager;
use App\User;
use Closure;
use Exception;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Illuminate\Support\Facades\Log;
use Throwable;
use Tpccdaniel\DuskSecure\Browser as DuskBrowser;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tpccdaniel\DuskSecure\BrowserInstance;

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
            $time = now()->format('y-m-d h.i.s');
            $stage = ScrapeDVSA::$stage;
            Log::alert("dusk failed at: {$stage}. Time: {$time}. Error: {$e->getMessage()}");
            $this->browser->screenshot("{$stage} {$time}");
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
        $chrome_log_path = storage_path('logs/chromedriver.log');
        exec('rm -r '.$chrome_log_path);
        exec('rm -r '.storage_path('logs/laravel-'.now()->format('Y-m-d').'.log'));

        static::startChromeDriver([
            '--verbose',
            "--log-path={$chrome_log_path}"
        ]);

        if (!$this->browser) {
            $this->browser = $this->newBrowser($this->createWebDriver());
        }
    }
    
    public function getConfig()
    {
        $user = User::find(User::all()->count());

        $proxy = (new ProxyManager)->getProxy($user);
        $url = "{$proxy['ip']}:{$proxy['port']}";

        return DesiredCapabilities::chrome()
                        ->setCapability(WebDriverCapabilityType::ACCEPT_SSL_CERTS, true)
                        ->setCapability(ChromeOptions::CAPABILITY, (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--ignore-certificate-errors',
//            "--user-agent={$proxy['randomUserAgent']}"
        ]))
                        ->setCapability(WebDriverCapabilityType::PROXY, [
            'proxyType' => 'manual', 'httpProxy' => $url, 'sslProxy' => $url, 'ftpProxy' => $url
        ]);
    }

    /**
     * @return RemoteWebDriver
     */
    protected function driver()
    {
        $capabilities = $this->getConfig();

        $driver = RemoteWebDriver::create(
            'http://127.0.0.1:9515', $capabilities,
            6 * 10000, // 1 minute
            6 * 10000
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
}