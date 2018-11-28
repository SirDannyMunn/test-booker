<?php

namespace App\Browser;

use Closure;
use Exception;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
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
        $user_agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
            'Mozilla/5.0 (Windows NT 5.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.2; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
        ];

        $userAgent = $user_agents[rand(0, count($user_agents))];
        $options = $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--ignore-certificate-errors',
            "--user-agent={$userAgent}"
        ]);

        $capabilities = DesiredCapabilities::chrome();

//        $url = 'accb2a6ad600495b917187f2873558ec@proxy.crawlera.com:8010';
        $url = '127.0.0.1:8123';
        $proxy = ['proxyType' => 'manual', 'httpProxy' => $url];
        $capabilities->setCapability(WebDriverCapabilityType::PROXY, $proxy);
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $driver = RemoteWebDriver::create(
            'http://127.0.0.1:9515', $capabilities
        );

        return $driver;
    }

    public function connectProxy($details)
    {
        //        $options->setExperimentalOption("prefs", ["chrome.proxy" => [
//            "value" => [
//                "mode" => "fixed_servers",
//                "rules" => [
//                    "singleProxy" => [
//                        "scheme" => "http",
//                        "host" => "172.111.186.2",
//                        "port" => 443
//                    ],
//                    "bypassList" => ["foobar.com"]
//                ]
//            ],
//            "scope" => "regular"
//        ]]);

//        $pluginForProxyLogin = $this->connectProxy(['172.111.186.2', '443', 'purevpn0s7415434', 'p2yfiq7n']);
//        $options->addExtensions([$pluginForProxyLogin]);

//        $zip = new \ZipArchive();
//        $plugin_path = base_path('App/Browser/Proxy');
//        $pluginForProxyLogin = "{$plugin_path}/a".uniqid().".zip";
//        $res = $zip->open($pluginForProxyLogin, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
//        $zip->addFile("{$plugin_path}/manifest.json", 'manifest.json');
//        $background = file_get_contents("{$plugin_path}/background.js");
//        $background = str_replace(['%proxy_host', '%proxy_port', '%username', '%password'], $details, $background);
//        $zip->addFromString('background.js', $background);
//        $zip->close();
//
//        putenv("webdriver.chrome.driver=".base_path('chromedriver-linux'));

//        $options = new ChromeOptions();
//        $options->addExtensions([$pluginForProxyLogin]);
//        $caps = DesiredCapabilities::chrome();
//        $caps->setCapability(ChromeOptions::CAPABILITY, $options);
//        $driver = ChromeDriver::start($caps);
//        $driver->get('https://old-linux.com/ip/');
//        header('Content-Type: image/png');
//        echo $driver->takeScreenshot();
//        unlink($pluginForProxyLogin);

//        return $pluginForProxyLogin;
    }
}
