<div class="media justify-content-around p-4 @if($order=='1') text-right @else text-left @endif">
    <span class="order-{{$order}} icon-circle p-4 mx-3 align-self-center" href="#">
        <img src='{{"svg/{$icon}.svg"}}' width="50">
    </span>
    <div class="font-weight-light">
        <span class="" style="font-size: 1.25rem;">{!! $title !!}</span><br> {!! $description !!}
    </div>
</div>