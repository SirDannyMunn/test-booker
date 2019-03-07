@php
    $currentPlan = $tier==auth()->user()->tier;
@endphp

<div class="card text-center">
    <div class="card-body" style="line-height: 2rem;">
        <h4 class="card-title pt-3">{{ ucfirst($plan)}}</h4>
        <h2 class="card-title text-{{$colour}} pt-4">{{$price}}</h2>
        <div class="text-muted mt-4">per booking</div>
        <ul class="list-unstyled ">
            <li>Access to the web app</li>
            <li>See live cancellations</li>
            @isset($features)
                @foreach($features as $additionalFeature)
                    <li class="text-{{$colour}}">{{$additionalFeature}}</li>
                @endforeach
            @endisset
        </ul>
        <button onclick="window.location.href='payment?plan={{ $tier }}'" class="btn btn-{{$colour}}" @if($currentPlan) disabled="true" @endif>
            @if($currentPlan) Current Plan @else Change @endif
        </button>
    </div>
</div>