<?php

namespace App\Browser;

use App\Jobs\ScrapeDVSA;
use App\Modules\InteractsWithDVSA;
use App\Modules\ProxyManager;
use App\Proxy;
use App\User;
use Closure;
use Exception;
use Facebook\WebDriver\Exception\WebDriverCurlException;
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
    public static $stage = 'Start';
    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    protected $browser;
    /**
     * @var Proxy
     */
    protected $proxy;
    private $existingSessionID;
    private $useExistingProxy;

    /**
     * @param Closure $callback
     * @param $useExistingProxy
     * @param null $existingSessionID
     * @throws Throwable
     */
    public function browse(Closure $callback, $useExistingProxy=false, $existingSessionID=null)
    {
        $this->useExistingProxy = $useExistingProxy;
        $this->existingSessionID = $existingSessionID;

        $this->prepare();

        $this->browser->storeConsoleLog(storage_path('logs'));

        try {
            $callback($this->browser, $this->proxy);
        } catch (Exception $e) {
            if ($e instanceof WebDriverCurlException) {
                $this->proxy->failed();
            }

            $time = now()->format('y-m-d h.i.s');
            $stage = static::$stage;
            $logContext = ['proxy' => $this->proxy->proxy, 'time' => $time, 'error' => $e->getMessage()];
            Log::alert("dusk failed at: {$stage}", $logContext);
            $this->browser->screenshot("{$time} {$stage}");

            $this->closeBrowser();
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
        if (!!$this->browser) {
            $this->browser->quit();
            $this->browser = null;
        }
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
     * @throws Exception
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
     * @throws Exception
     */
    public function prepare()
    {
        $chrome_log_path = storage_path('logs/chromedriver.log');

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
        $this->proxy = (new ProxyManager)->getProxy($this->useExistingProxy);

        $url = $this->proxy->proxy;

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
    
        $timeout = 6 * 10000; // 1 minute
    
        if ($this->existingSessionID) {
            return RemoteWebDriver::createBySessionId($this->existingSessionID, "http://127.0.0.1:9515");
        }
    
        return RemoteWebDriver::create('http://127.0.0.1:9515', $capabilities, $timeout, $timeout);
    }

    // /**
    //  * @throws Exception
    //  */
    // function __destruct()
    // {
    //     if ($this->browser) {
    //         $this->closeBrowser();
    //     }
    // }
}