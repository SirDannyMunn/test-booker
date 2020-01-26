<?php

namespace Tests;

use App\Location;
use Illuminate\Support\Arr;

class DummyData
{
    public $times = ['8:40', '9:20', '10:40', '11:20', '12:40', '13:20', '14:40', '15:20', '16:40', '17:20'];

    private $location;

    public function __construct()
    {
        $this->location = array_random(['Skipton', 'Blackburn']);
    }

    public function makeNewDummySlots($amount, $dataPath)
    {
        $slots = collect(array_fill(0, $amount, null))->map(function($item) {

            $time = explode(':', Arr::random($this->times));

            return $random_date_time = today()->addDays(rand(0, 60))
                          ->startOfWeek()
                          ->addDays(rand(0, 4))
                          ->setTime($time[0], $time[1])
                          ->toDateTimeString();
        });

        $locationsSlots = collect([[
            'slots' => [$slots],
            'location' => Location::firstOrCreate(['name' => $this->location])
        ]]);

        $this->storeSlots($locationsSlots, $dataPath);

        return $locationsSlots;
    }

    /**
     * @return mixed
     */
    public function getDummySlots()
    {
        $dataPath = base_path("database/data/{$this->location}");
        $slots = collect();
        $defaultAmount = rand(3, 5);

        if (file_exists($dataPath)) {
            $slots = $slots->merge(json_decode(file_get_contents($dataPath), true));
        }

        $slots = $slots->merge($this->makeNewDummySlots($defaultAmount, $dataPath));

//        $testsLaterThanToday = count($slots->filter(function($item) {
//            return Carbon::parse($item['date'])->greaterThan(today()->format('d/m/y H:m:s'));
//        }));
//
//        if ($testsLaterThanToday < $this->defaultAmount) {
//            $slots = $slots->merge($this->makeNewDummySlots($this->defaultAmount - $testsLaterThanToday + rand(1,3), $dataPath));
//        }

        return $slots;
    }

    public function storeSlots($slots, $path)
    {
        $file = fopen("{$path}.json", 'w');
        fwrite($file, json_encode($slots));
        fclose($file);

        return $slots;
    }
}