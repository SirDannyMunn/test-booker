<?php

namespace App\Browser\Pages;

use Tpccdaniel\DuskSecure\Page as BasePage;

abstract class Page extends BasePage
{
    /**
     * Get the global element shortcuts for the site.
     *
     * @return array
     */
    public static function siteElements()
    {
        return [
            '@element' => '#selector',
        ];
    }

    public function checkPage($stage)
    {
        static::$stage = $stage;

        $captcha = $this->window->captcha();
        $body = $this->window->html('body');

        if (!$captcha && $body != '') return;

        $this->proxy->failed();

        abort(500, $captcha ? 'Captcha found' : 'Blank Response');
    }

    public function deleteIncapsulaCookies()
    {
        $incapsula_cookies = array_where(array_pluck((array)$this->window->getCookies(), 'name'), function ($cookie) {
            return str_contains($cookie, 'incap');
        });

        foreach ($incapsula_cookies as $cookie) {
            $this->window->deleteCookie($cookie);
        }
    }

    public function login()
    {
        $url = 'https://www.gov.uk/change-driving-test';
        $this->window->visit($url);
        $this->checkPage("Accessing site");
        $this->window->click('#get-started > a');
        $this->checkPage("Logging in");
        $this->window->type('#driving-licence-number', decrypt($this->user->dl_number))->type('#application-reference-number', decrypt($this->user->ref_number))->click('#booking-login');
        $this->window->pause(rand(250, 1000));
    }

    public function changeTestDate()
    {
        $this->window->click('#date-time-change')->click('#test-choice-earliest')->pause(rand(250, 1000))->click('#driving-licence-submit')->pause(rand(250, 1000));
    }

    /** * @param $to_notify Collection */
    public function sendNotifications($to_notify)
    {
        $users = User::all();
        foreach ($to_notify->collapse()->groupBy('user.id') as $item) { /* @var $user User*/ /* @var $item Collection * collection of users and slots, ranked and sorted to acquire best match */ $item = $item->sortByDesc('date')->sortByDesc('user.points')[0];
            $user = $users->find($item['user']['id']);
            $user->notify(new ReservationMade($user, $item));
        }
    }

    public function loopLocations()
    {
        $slotManager = new SlotManager;
        foreach ($this->user->locations as $location) { /* @var $location Location */ $this->window->pause(rand(250, 1000))->click('#change-test-centre');
            $this->checkPage("#change-test-centre");
            $this->window->pause(rand(1000, 2000))->type('#test-centres-input', $location->name)->pause(rand(1000, 2000))->click('#test-centres-submit')->pause(rand(1000, 2000));
            $this->checkPage("#test-centres-submit");
            $this->window->clickLink(ucfirst($location->name));
            $this->checkPage("Changed calendar location");
            $slots = $this->getSlots($location->name);
            $location->update(['last_checked' => now()->timestamp]);
            $this->toNotify->push($slotManager->getMatches($slots, $location));
        }
    }

    /** * @param $location * @return array */
    public function getSlots($location)
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
}
