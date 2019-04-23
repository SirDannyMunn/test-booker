<?php

namespace Tests;

use App\Location;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class DummyData
{
    public $times = ['8:40', '9:20', '10:40', '11:20', '12:40', '13:20', '14:40', '15:20', '16:40', '17:20'];

    private $defaultAmount = 20;

    public function makeNewDummySlots($amount, $dataPath)
    {
        return $slots = collect(array_fill(0, $amount, null))->map(function($item) {

            $time = explode(':', Arr::random($this->times));

            return today()->addDays(rand(0, 60))
                          ->startOfWeek()
                          ->addDays(rand(0, 4))
                          ->setTime($time[0], $time[1]);
        });
    }

    /**
     * @param $location
     * @return mixed
     */
    public function getDummySlots($location)
    {
        $dataPath = base_path("database/data/{$location}");
        $dummySlots = collect();

        if (!file_exists($dataPath)) {
            $dummySlots = $dummySlots->merge($this->makeNewDummySlots(rand($this->defaultAmount, $this->defaultAmount + 5), $dataPath));
        }

        $dummySlots = $dummySlots->merge(json_decode(file_get_contents($dataPath), true));
        
        $testsLaterThanToday = count($dummySlots->filter(function($item) {
            return Carbon::parse($item['date'])->greaterThan(today()->format('d/m/y H:m:s'));
        }));

        if ($testsLaterThanToday < $this->defaultAmount) {
            $dummySlots = $dummySlots->merge($this->makeNewDummySlots($this->defaultAmount - $testsLaterThanToday + rand(1,3), $dataPath));
        }

        $locationsSlots = collect([['slots' => [$dummySlots->pluck('date')], 'location' => Location::firstOrCreate(['name' => $location])]]);

        $this->storeSlots($locationsSlots, $dataPath);

        return $locationsSlots;
    }

    public function storeSlots($slots, $path)
    {
        $file = fopen("{$path}.json", 'w');
        fwrite($file, json_encode($slots));
        fclose($file);

        return $slots;
    }
}