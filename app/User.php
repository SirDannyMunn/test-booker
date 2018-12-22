<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use Notifiable;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function location()
    {
        return $this->hasOne('App\Location');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
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

    /**
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
