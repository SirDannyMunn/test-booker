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

    /**
     * @param Closure $callback
     * @param bool $useExistingProxy
     * @param bool $hunting
     * @param null $existingSessionID
     * @throws Throwable
     * @throws WebDriverCurlException
     */
    public function browse(Closure $callback, $useExistingProxy=false, $hunting=false, $existingSessionID=null)
    {
        $this->proxy = (new ProxyManager)->getProxy($useExistingProxy, $hunting);
//        $this->proxy = Proxy::find(60);

        $this->existingSessionID = $existingSessionID;

        $this->prepare();

        try {
            $callback($this->browser, $this->proxy);
        } catch (Exception $e) {

            if (static::$stage != 'Accessing site')
                $this->makeLog($e);
            if ($e instanceof WebDriverCurlException)
                $this->proxy->failed();
            $this->closeBrowser();

            throw $e;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return RemoteWebDriver
     */
    protected function driver()
    {
        $timeout = 6 * 10000; // 1 minute

        $url = $this->proxy->proxy;

        $capabilities = DesiredCapabilities::chrome()
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

        if ($this->existingSessionID) {
            return RemoteWebDriver::createBySessionId($this->existingSessionID, "http://127.0.0.1:9515");
        }

        // TODO - Fix this
        return RemoteWebDriver::create('http://127.0.0.1:9515', $capabilities, $timeout, $timeout);
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
            $this->browser = $this->browser($this->createWebDriver());
        }
    }

    private function makeLog($e)
    {
        $time = now()->format('y-m-d h.i.s');
        $stage = (string) static::$stage;
        $logContext = ['proxy' => $this->proxy->proxy, 'time' => $time, 'error' => $e->getMessage()];
        Log::alert("dusk failed at: {$stage}", $logContext);
        $this->browser->screenshot("{$time} {$stage}");
        $this->browser->storeConsoleLog("{$time} {$stage}");
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
    protected function browser($driver)
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