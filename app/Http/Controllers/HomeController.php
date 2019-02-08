<?php

namespace App\Http\Controllers;

use App\Slot;
use Illuminate\Http\Request;
use App\Location;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $availableUserSlots = $user->userSlots->where('slot.taken', false)->pluck('slot');

        return view('home', [
            'user' => $user,
            'availableUserSlots' => $availableUserSlots,
            'locations' => Slot::whereIn('location', auth()->user()->locations->pluck('name'))->get()->groupBy('location')
        ]);
    }
}
