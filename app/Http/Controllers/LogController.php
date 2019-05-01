<?php

namespace App\Http\Controllers;

use App\Slot;
use App\User;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index()
    {
//        $slots = Slot::with('userSlots.user.locations')->get();
        $users = User::with(['locations', 'slots.userSlots'])->get();

        return view('log', ['users' => $users]);
    }
}
