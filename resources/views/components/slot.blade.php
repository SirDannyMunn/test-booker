<p class="
{{-- @if($slot->in_use) text-info @else text-success @endif --}}
"
    style="line-height: 2;">
    {{ $slot->datetime_object->format('l, j F Y g:ia') }}
    <span><i class=""></i></span>
    <a href="javascript:void(0);" class="btn btn-outline-primary float-right">More</a>
</p>