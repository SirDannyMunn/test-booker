<div class="card text-center">
    <div class="card-body" style="line-height: 2rem;">
        <h4 class="card-title pt-3">{{ ucfirst($tier)}}</h4>
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
        <a href="{{ url("payment?plan=$tier") }}" class="btn btn-{{$colour}}">
            Get Started
        </a>
    </div>
</div>