@extends('layouts.app')

@section('content')
<div class="container">
    <div class="py-5"></div>

    <slot-modal></slot-modal>

    <div class="row">
    </div>

    {{-- <nav>
        <ul class="nav nav-tabs device-small" role="tablist">
            <li role="presentation" class="nav-item active"><a href="#slots" aria-controls="home" role="tab" data-toggle="tab">Slots</a></li>
            <li role="presentation" class="nav-item"><a href="#details" aria-controls="profile" role="tab" data-toggle="tab">Details</a></li>
            <li role="presentation" class="nav-item"><a href="#locations" aria-controls="messages" role="tab" data-toggle="tab">Locations</a></li>
        </ul>
    </nav> --}}

    <nav>
        <div class="nav nav-tabs device-small" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" data-toggle="tab" href="#slots" role="tab" aria-controls="nav-home" aria-selected="true">Home</a>
            <a class="nav-item nav-link" data-toggle="tab" href="#details" role="tab" aria-controls="nav-profile" aria-selected="false">Profile</a>
            <a class="nav-item nav-link" data-toggle="tab" href="#locations" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</a>
        </div>
    </nav>

    {{-- <div class=""> --}}

        {{-- Slots --}}
        <div class="tab-content row">
            {{-- <div class="tab-pane active col col-xs-12 col-sm-4" id="home">...home...</div>
            <div class="tab-pane col-xs-12 col-sm-4" id="profile">...profile...</div>
            <div class="tab-pane col-xs-12 col-sm-4" id="messages">...messages...</div> --}}

            <div role="tabpanel" id="slots" class="tab-pane active col my-1">
                <div class="card">
                    <div class="card card-body">
                        <h3 class="card-title">Test Dates</h3>
                        <hr>
                        @foreach ($locations as $name => $slots)
                    @php
                        $locationUserSlots = $availableUserSlots->where('location', $name);
                        $remainingLocationSlots = $slots->whereNotIn('datetime', $locationUserSlots->pluck('datetime'));
                    @endphp

                    <div>
                        <h5>{{ $name }}</h5>
                        
                        <hr style="margin-top: 0;">
                        
                        <h5>Top Picks For You 
                            <a href="#"  data-toggle="popover" data-html="true"
                            data-content='
                                <div class="popover" role="tooltip" style="width:750px;"><div class="arrow"></div><h3 class="popover-header" style="padding: 1rem 2rem;">How does this work?</h3><div class="popover-body" style="padding: 1rem 2rem;"> 
                                    Your place is ranked using the following conditions: <br><br>
                                    <ul>
                                        <li>Date you signed up </li>
                                        <li>Your main location</li>
                                        <li>Your account plan</li>
                                    </ul>    
                                    
                                    <small><strong>Clicking "Book" will put you in front for that slot.</strong></small> 
                                </div></div>
                            '>
                                <img style="cursor:pointer;" src="{{ url('icons/question.svg') }}">
                            </a></h5>
                        @forelse ($availableUserSlots->where('location', $name) as $availableUserSlot)

                            @php
                                $numberFormatter = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
                                $place = $numberFormatter->format($availableUserSlot->currentUserPlace()); 
                                $promotable = $availableUserSlot->promotable();  
                            @endphp

                            @if($promotable)
                                <test-slot 
                                    slot-id="{{ $availableUserSlot->id }}" 
                                    place="{{ $place }}"
                                    datetime="{{ $availableUserSlot->datetime_object->format('D, j F Y g:ia') }}">
                                </test-slot>
                            @endif
                        @empty
                            Suitable slots will show here when they arise
                        @endforelse
                        
                        <hr>    
                                
                        @foreach($remainingLocationSlots as $slot)        
                            <test-slot 
                                slot-id="{{ $slot->id }}" 
                                can-promote="1"
                                datetime="{{ $slot->datetime_object->format('D, j F Y g:ia') }}">
                            </test-slot>
                        @endforeach
                    </div>
                    @endforeach
                    </div>
                </div>
            </div>
            
            {{-- Details --}}
            <div role="tabpanel" id="details" class="tab-pane col my-1">
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
                        
                        @if( ! $user->details_valid) 
                        <div class="alert alert-danger"> We were unable to find a test with these details. Please check them and try again. </div> 
                        @endif
                        
                        <h5>Liscence Number: </h5>
                        <p> @if ( !$user->details_valid) ❌ @endif  {{ decrypt($user->dl_number) }}</p>
                        
                        <h5>Test Reference: </h5>
                        <p> @if ( !$user->details_valid) ❌ @endif {{ decrypt($user->ref_number) }}</p>
                        
                        <hr style="margin-top: 0;">
                            
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
            <div role="tabpanel" id="locations" class="tab-pane col my-1">
                <div class="card">
                    <div class="card card-body">
                        <h3 class="card-title">Your Locations</h3>
                        <hr>

                        @foreach($user->locations as $location)
                            @php 
                                $favourite = $location->name==$user->location;
                            @endphp
                            <p>
                                <span @if($favourite)data-toggle="tooltip" data-title="Your main location"@endif>
                                        @if($favourite)<img src="{{ url('icons/star.png') }}" width="24px">@endif
                                    {{ $location->name }}
                                </span>
                            </p>
                        @endforeach

                        <a href="javascript:void(0);" class="btn btn-primary">Change</a>
                    </div>
                </div>
            </div>
        </div>
    {{-- </div> --}}

</div>
@endsection