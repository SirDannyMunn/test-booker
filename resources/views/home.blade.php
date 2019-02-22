@extends('layouts.app')

@section('content')
<div class="container">
    <div class="py-5"></div>

    <div class="row">
    </div>

    <div class="row justify-content-center">
        
        {{-- Slots --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card card-body">
                    <h3 class="card-title">Test Dates</h3>
                    <hr>
                    @foreach ($locations as $name => $slots)
                        @php
                            $locationUserSlots = $availableUserSlots->where('location', $name);
                            $remainingLocationSlots = $slots->whereNotIn('datetime', $locationUserSlots->pluck('datetime'));
                        @endphp

                        <h5>{{ $name }}</h5>
                        
                        <hr style="margin-top: 0;">
                        
                        <h5>Top Picks For You</h5>
                        @forelse ($availableUserSlots->where('location', $name) as $availableUserSlot)
 
                            @php
                                $numberFormatter = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
                                $place = $numberFormatter->format($availableUserSlot->currentUserPlace());   
                            @endphp

                            <small>Your are in {{ $place }} Place for this slot</small>

                            <test-slot datetime="{{ $availableUserSlot->datetime_object->format('D, j F Y g:ia') }}"></test-slot>
                        @empty
                            Suitable slots will show here when they arise
                        @endforelse
                        
                        <hr>    
                                
                        @foreach($remainingLocationSlots as $slot)        
                            <test-slot datetime="{{ $slot->datetime_object->format('D, j F Y g:ia') }}"></test-slot>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
        
        {{-- Details --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card card-body">
                    <h3 class="card-title">Your Details</h3>
                    <hr>
                    
                    {{-- Plan --}}
                    <h5>Your Current Plan: </h5>
                    <p>{{ ucfirst($user->tier) }} 
                        <button data-toggle="modal" data-target="#planModal" class="btn btn-sm float-right btn-primary"><strong><img src="{{ url('icons/badge.png') }}" width="14px"> Upgrade</strong></button>
                    </p>
                
                    @include('components.plans_modal')

                    <hr style="margin-top: 0;">

                    <h5>Liscence Number: </h5>
                    <p>{{ decrypt($user->dl_number) }}</p>

                    <h5>Test Reference: </h5>
                    <p>{{ decrypt($user->ref_number) }}</p>
                    
                    <h5>Your Test Date: </h5>
                    <P>{{ $user->test_date_object->format('l, j F Y g:ia') }}</P>
                    
                    <h5>Email: </h5>
                    <p>{{ $user->email }}</p>
                    
                    <h5>Phone Number: </h5>
                    <p>{{ $user->phone_number }}</p>

                    <a href="javascript:void(0);" class="btn btn-primary">Change</a>
                </div>
            </div>
        </div>

        {{-- Locations --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card card-body">
                    <h3 class="card-title">Your Locations</h3>
                    <hr>

                    @foreach($user->locations as $location)
                        <p>{{ $location->name }}</p>
                    @endforeach

                    <a href="javascript:void(0);" class="btn btn-primary">Change</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection