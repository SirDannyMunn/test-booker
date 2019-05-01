<?php

namespace App;

use App\Notifications\ReservationMade;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getTestDateObjectAttribute()
    {
        return Carbon::parse($this->test_date);
    }

    public function location()
    {
        return $this->hasOne('App\Location');
    }

    public function slots()
    {
        return $this->hasManyThrough('App\Slot', 'App\UserSlot', 'slot_id', 'id');
    }

    public function userSlots()
    {
        return $this->hasMany('App\UserSlot', 'user_id');
    }

    public function locations()
    {
        return $this->belongsToMany('App\Location', 'user_location');
    }

    public function proxy()
    {
        return $this->proxies()->where('active', 1)->get()->last();
    }

    public function proxies()
    {
        return $this->hasMany('App\Proxy');
    }

    public function canChangePlan()
    {
        return $this->booked==0;
    }

    public function getAlternativeUsers(Collection $userSlots)
    {
        $alternativeUserSlots = $userSlots->sortByDesc('points')->sortBy('tries');

        return $eligibleUsers = User::whereIn('id', $alternativeUserSlots->pluck('user_id'))->get();
    }

    /**
     * Returns user records based on most area coverage
     * @param $users Collection
     * @param $locations Collection
     * @return \Illuminate\Support\Collection
     */
    public function getBest($users, $locations) {
        $best_users = collect();
        while (filled($locations)) {
            $location_points = collect();
            foreach ($users as $user) {
                $location_points[$user->id] = collect(['points' => 0, 'user' => $user]);
                foreach ($user->locations as $location) {
                    // Check each user against remaining locations
                    if ($locations->has($location->name)) {
                        // If user has one of locations, give point
                        $location_points[$user->id]['points']+=1;
                    }
                }
            }
            
            // Sort to get most points
            $sorted = $location_points->sortBy('points');
            $best_user = $sorted->keys()->last();
            $best_users->push($sorted->last()['user']);
            // Remove user and all user's locations from lists
            $best_user = $users->find($best_user);
            $users->forget($users->search($best_user));
            $locations->forget($best_user->locations->pluck('name')->toArray());
        }

        return $best_users;
    }
}