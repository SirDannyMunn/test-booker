@extends('layouts.app')

@push('scripts-top')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="container">
    <div class="py-5"></div>
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

                    <a href="javascript:void(0);" class="btn btn-primary">More</a>
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

                    <a href="javascript:void(0);" class="btn btn-primary">More</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection