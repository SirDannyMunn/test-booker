<!-- Modal -->
<div class="modal fadeIn delay-2s slower" id="planModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
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
            <div class="modal-body plan-modal-body">

                <div class="row">
                    @foreach(config('settings.plans') as $tier => $plan)
                        <div class="col-md-4">
                            @include('components.price_card', 
                                ['tier'=>$tier, 'plan'=>$plan['name'], 'colour'=>$plan['colour'], 'price'=>"£{$plan['price']}", "features"=>$plan['features']]
                            )
                        </div>
                    @endforeach
                    {{-- <div class="col-md-4">
                        @include('components.price_card', [ 'tier'=>'free','colour'=>'secondary','price'=>'£0', ])
                    </div>
                    <div class="col-md-4">
                        @include('components.price_card', [ 'tier'=>'basic','colour'=>'success','price'=>'£9.99', 'features'=>[ '3 additional test
                        centres', 'searches every 9 minutes' ], ])
                    </div>
                    <div class="col-md-4">
                        @include('components.price_card', [ 'tier'=>'premium','colour'=>'success','price'=>'£19.99', 'features'=> [ '5 additional
                        test centres', 'Searches every 5 minutes', 'Get first priority' ], ])
                    </div> --}}
                </div>

            </div>
        </div>
    </div>
</div>