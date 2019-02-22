@extends('layouts.app') 

@section('nav')@endsection

@section('content')

{{-- Payment navbar --}}
<div>
    <nav class="navbar navbar-light navbar-expand-md navigation-clean">
        <div class="container payments-container">
            <a class="navbar-brand" href="{{ url('/home') }}">
                <img src="{{ url('/icons/back_curved.png') }}" width="34px"> Back
            </a><button class="navbar-toggler" data-toggle="collapse"
                data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="nav navbar-nav ml-auto">
                    <li class="nav-item" role="presentation"><a class="nav-link active" href="#"><img src="{{ url('icons/padlock_3.png') }}" width="35px"> Secure Checkout</a></li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<div id="content">        

    @include('components.plans_modal')

    {{-- Payment page --}}
    <div class="payment-header">
    <div class="container payments-container" >
        <h2 class="pt-6 pb-7 text-center font-weight-bold">Complete Your Purchase</h2>
        <div class="card border-0 text-right payment-items-card mb-5">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-left">Item Name</th>
                                <th>Item Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody >
                            <tr>
                                <td class="text-left">{{ ucfirst($plan['name']) }}</td>
                                <td>£{{ $plan['price'] }}</td>
                                <td><a data-toggle="modal" data-target="#planModal" href="#">Change</a></td>
                        
                            </tr>
                            <tr class="total-cost">
                                <td></td>
                                <td></td>
                                <td>
                                    <div>
                                        <small>Total</small> <br>
                                        <strong>
                                            £{{ $plan['price'] }}
                                        </strong>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="coupon" class="my-5 container text-center payments-container">
    <div class="my-5 p-3 card m-auto"><a href="#" class="text-primary">Have a discount code? Click here to enter it.</a></div>
</div>

{{-- Plan selection --}}
{{-- <div>
    <div class="container payments-container">
        <h5 class="payment-section-label">Choose your plan</h5>
        <div class="row">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5>Basic</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5>Premium</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    
    {{-- Form --}}
    <div>
        <div class="container payments-container">
            <h5 class="payment-section-label">Personal Info</h5>
            <div class="row">
                <div class="col-md-12" id="payment-details">

                    <div class="form-group">
                        <h5 class="p-1 required control-label">Email Address</h5><small>We will send the purchase reciept to this address</small>
                        <input type="email" class="form-control" value="{{ auth()->user()->email }}" required></div>
                        <div class="form-group">
                            <h5 class="p-1 required control-label">Full Name</h5><small>We will use this for the billing information</small> 
                            <input id="cardholder-name" type="text" class="form-control" value="{{ auth()->user()->name }}" required></div>
                <div class="form-group">
                    <h5 class="p-1 required control-label">Payment Details</h5><small>We will use this information to make the payment</small>
                    <stripe-card client-secret="{{ $intent->client_secret }}"></stripe-card>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Banner --}}
<div>
    <div class="container payments-container" id="payments-banner">
        <div class="row py-5">
            <div class="col-md-6"><img src="{{ url('icons/padlock_4.png') }}">
                <h5>Your Information is Safe</h5>
                <small>We will not use your information for anything other than this purchase with will be taken when a booking is made.</small>
            </div>
            <div class="col-md-6"><img src="{{ url('icons/protected.png') }}">
                <h5>Secure Checkout</h5>
                <small>All information is encrypted and transmitted without risk using a <strong>Secure Sockets Layer</strong> protocol.
                    You can trust us!</small>
                </div>
            </div>
        </div>
    </div>
    
    <div id="background"></div>
    
</div>
@endsection