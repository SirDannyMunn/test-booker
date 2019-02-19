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
                        
                        <div class="card card-light p-2">
                            @foreach($availableUserSlots->where('location', $name) as $availableUserSlot)
                                @include('components.slot', ['slot' => $availableUserSlot])       
                            @endforeach
                        </div>
                            
                        @foreach($remainingLocationSlots as $slot)        
                            @include('components.slot', ['slot' => $slot])       
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
                    <p>{{ ucfirst($user->tier) }} <button data-toggle="modal" data-target="#planModal" class="btn btn-sm float-right btn-primary"><strong><img src="{{ url('icons/badge.png') }}" width="14px"> Upgrade</strong></button></p>
                
                    <!-- Modal -->
                    <div class="modal fadeIn delay-2s slower" id="planModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header" style="border-bottom:unset;">
                            <h2 class="col-12 modal-title text-center" id="exampleModalLongTitle">
                                <div style="width: 45px; height:50px" class="float-left"></div>
                                Pick a Plan!
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>        
                            </h2>

                            </div>    
                            <div class="modal-body">
                            
                                <div class="row">
                                    <div class="col-md-4">
                                        @include('components.price_card', [
                                            'tier'=>'free','colour'=>'secondary','price'=>'£0', 
                                        ])
                                    </div>
                                    <div class="col-md-4">
                                        @include('components.price_card', [
                                            'tier'=>'basic','colour'=>'success','price'=>'£9.99', 
                                            'features'=>[
                                                '3 additional test centres',
                                                'searches every 9 minutes'
                                            ],
                                        ])
                                    </div>
                                    <div class="col-md-4">
                                            @include('components.price_card', [
                                                'tier'=>'premium','colour'=>'success','price'=>'£19.99', 
                                                'features'=> [
                                                    '5 additional test centres',
                                                    'Searches every 5 minutes',
                                                    'Get first priority'
                                                ],
                                            ])
                                    </div>
                                </div>

                            </div>
                        </div>
                        </div>
                    </div>

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