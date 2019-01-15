<?php 

namespace App\Modules;

use App\Browser\Browser;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;

/**
 * Trait InteractsWithDVSA
 * @package App\Modules
 */
trait InteractsWithDVSA
{
    /** @var \App\Proxy */
    private $proxy;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    protected function checkPage($stage)
    {
        Browser::$stage = $stage;

        $captcha = $this->window->captcha();
        $body = $this->window->html('body')[0];

        if (!$captcha && $body != '') return true;

        $this->proxy->failed($body);
        $this->window->quit();
        $this->failed();

        abort(500, $captcha ? 'Captcha found' : 'Blank Response');
    }
 
    public function getToCalendar()
    {
        $this->deleteIncapsulaCookies();
        $this->login();
        $this->checkPage("At Dashboard");
        $this->changeTestDate();
        $this->checkPage("At Calendar");
    }

    public function calendarToMonth($month)
    {
        $calMonthDiff = (int) $month - (int) Carbon::parse(
                $this->window->text('.BookingCalendar-currentMonth')
            )->format('m');
        $direction = $calMonthDiff > 0 ? "next" : "prev";
        for ($i=0; $i < abs($calMonthDiff); $i++) {
            $this->window->click(".BookingCalendar-nav--{$direction}")->pause(100, 250);
        }
    }

    public function makeReservation($slot)
    {
//        $this->window->mouseover("[data-date='{$date->format('Y-m-d')}']")->clickAndHold()->pause(rand(59, 212))->releaseMouse()
//        $this->window->mouseover("[data-datetime-label='{$date->format('l j F Y g:ia')}']")->clickAndHold()->pause(rand(59, 212))->releaseMouse()
        $date = Carbon::parse($slot['date']);

        $this->changeCalendarLocation($slot['location']);

        $this->calendarToMonth($date->format('m'));

        $this->window->click("[data-date='{$date->format('Y-m-d')}']");

        rescue(
            function() use ($date) {
            $this->window->scroll(0, 500)->pause(rand(250, 750))
                ->mouseover("[data-datetime-label='{$date->format('l j F Y g:ia')}']")
                ->clickAndHold()->pause(rand(59, 212))->releaseMouse();
        }, function() {
            $message = "Couldn't find slot date" ;
            Browser::$stage = $message; abort(599, $message);
        });

        $this->window
            ->click("#slot-chosen-submit")->pause(rand(250, 750))
            ->click("#slot-warning-continue")->pause(rand(250, 750))
            ->click("#i-am-candidate")
            ->screenshot("Reservation");
    }

    protected function login()
    {
        $url = 'https://www.gov.uk/change-driving-test';
        $this->window->visit($url);
        $this->checkPage("Accessing site");
        $this->window->click('#get-started > a');
        $this->checkPage("Logging in");
        $this->window->type('#driving-licence-number', decrypt($this->user->dl_number))->type('#application-reference-number', decrypt($this->user->ref_number))->click('#booking-login');
        $this->window->pause(rand(250, 1000));
        $this->proxy->update(['last_used' => now()->toDateTimeString()]);
    }

    protected function changeTestDate()
    {
        $this->window->click('#date-time-change')
        ->click('#test-choice-earliest')
        ->pause(rand(250, 1000))
        // TODO - Get url from here
        ->click('#driving-licence-submit')
        ->pause(rand(250, 1000));
    }

    public function scrapeUserLocations($locations)
    {
        $slots = collect();
        foreach ($locations as $location) { /* @var $location \App\Location */
            $this->changeCalendarLocation($location->name);

            $location->update(['last_checked' => now()->timestamp]);
            $slots[$location->name] = collect([
                'slots' => $this->scrapeSlots($location->name),
                'location' => $location
            ]);
        }
        return $slots;
    }

    public function changeCalendarLocation($location)
    {
        $this->window->pause(rand(250, 1000))->click('#change-test-centre');
        $this->checkPage("#change-test-centre");
        $this->window
            ->pause(rand(1000, 2000))
            ->type('#test-centres-input', $location)
            ->pause(rand(1000, 2000))
            ->click('#test-centres-submit')
            ->pause(rand(1000, 2000));
        $this->checkPage("#test-centres-submit");
        $this->window
            ->clickLink(ucfirst($location));
        $this->checkPage("Changed calendar location");
    }

    /**
     * Scrapes slots from calendar view
     * @param $location
     * @return array
     */
    public function scrapeSlots($location)
    {
        $slots = [];
        $slots[$location] = [];
        foreach (array_slice($this->window->elements('.SlotPicker-slot-label'), 0, 20) as $element) {
            /** @var $element RemoteWebElement */
            $string = $element->findElement(WebDriverBy::className('SlotPicker-slot'))->getAttribute('data-datetime-label');
            
            $slot = Carbon::parse($string)->toDateTimeString();

            array_push($slots[$location], $slot);
        }

        return array_values($slots);
    }
    
    protected function deleteIncapsulaCookies()
    {
        $incapsula_cookies = array_where(array_pluck((array)$this->window->getCookies(), 'name'), function ($cookie) {
            return str_contains($cookie, 'incap');
        });

        foreach ($incapsula_cookies as $cookie) {
            $this->window->deleteCookie($cookie);
        }
    }
}