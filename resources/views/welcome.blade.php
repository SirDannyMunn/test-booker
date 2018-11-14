@extends('layouts.app')

@section('content')

    <!--hero header-->
    <section class="bg-success" id="home">
        <div class="container-fluid">
            <div class="row vh-md-100">
                <div class="col-md-5 ml-auto my-auto text-center text-md-left">
                    <span class="text- text-muted small-xl">A better way to book your driving test.</span>
                    <h1 class="display-4 mt-2 mb-5">We find you the driving test date best suited to you.</h1>
                    <a href="#" class="btn btn-secondary d-inline-flex flex-row align-items-center">
                        Find Out More!

                        <i data-feather="chevrons-right"></i>
                    </a>
                </div>
                <div class="col-md-6 my-auto pt-5 pt-md-0">
                    <img src="img/mockup.png" class="img-fluid d-block mx-auto" alt="Mockup">
                </div>
            </div>
        </div>
    </section>

    <!--about section-->
    {{--<section class="pb-5" id="about">--}}
        {{--<div class="container">--}}
            {{--<div class="row">--}}
                {{--<div class="col-md-6 mr-auto text-center text-md-left">--}}
                    {{--<p class="lead">--}}
                        {{--"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum in nisi commodo, tempus--}}
                        {{--odio a, vestibulum nibh.--}}
                        {{--Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum in nisi commodo, tempus odio--}}
                        {{--a, vestibulum nibh."--}}
                    {{--</p>--}}
                    {{--<span class="text-muted">— Tech Crunch</span>--}}
                {{--</div>--}}
                {{--<div class="col-md-5 pt-5 pt-md-0">--}}
                    {{--<img src="img/brands.png" class="img-fluid img-faded d-block mx-auto" alt="Brands" title=""--}}
                         {{--style="">--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</section>--}}

    <!-- features section -->
    <section class="pb-5 pt-5" id="features">
        <div class="container">
            <div class="row">
                <div class="col-sm-10 col-md-12 mx-auto text-center">
                    <h1>Our Service</h1>
                    <div class="divider"></div>
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            @include('components.media_item',
                            ['order'=>'1','title'=>'Notifications','description'=>'We send you a text message automatically when a slot opens','icon'=>'smartphone'])
                            @include('components.media_item',
                            ['order'=>'1','title'=>'Completely Automated','description'=>'We look through all UK test centre databases daily <small>(so you don\'t have to!)</small>','icon'=>'meditation'])
                            @include('components.media_item',
                            ['order'=>'1','title'=>'Preferred Date ','description'=>'We keep checking the specific date that works for you','icon'=>'calendar'])
                        </div>
                        <div class="col-md-6 col-sm-6">
                            @include('components.media_item',
                            ['order'=>'0','title'=>'Fast Track','description'=>'We check your test centres every 5 minutes to find cancellations','icon'=>'fast'])
                            @include('components.media_item',
                            ['order'=>'0','title'=>'Free Until You Book','description'=>'We won\'t charge you a penny until you use one of our appointments','icon'=>'hand'])
                            @include('components.media_item',
                            ['order'=>'0','title'=>'Unlimited Tries','description'=>'As many checks as you need until you find the right date for you','icon'=>'infinite-symbol'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <!--pricing section-->
    <section class="pt-5" id="pricing">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto text-center">
                    <h2>Choose your pricing plan</h2>
                    <div class="divider"></div>
                    <p class="text-muted lead">No hidden fees. guaranteed no charge until you make a booking.</p>
                </div>
            </div>
            <!--pricing tables-->
            <div class="row pt-5 pricing-table">
                <div class="col-sm-12">
                    <div class="card-deck pricing-table">
                        @include('components.price_card', ['tier'=>'Free','colour'=>'secondary','price'=>'£0'])
                        @include('components.price_card',
                        ['tier'=>'Pretty Eager','colour'=>'success','price'=>'£9.99', 'features'=>[
                            '3 additional test centres',
                            'searches every 9 minutes'
                        ]])
                        @include('components.price_card',
                        ['tier'=>'Super Eager!','colour'=>'success','price'=>'£19.99', 'features'=>[
                            '5 additional test centres',
                            'Searches every 5 minutes',
                            'Get first priority',
                        ]])
                    </div>
                </div>
            </div>

            <!--faq-->
            <div class="row mt-5">
                <div class="col-md-10 mx-auto">
                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <h6>Can I try Musli for free?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h6>Do you have hidden fees?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h6>What are the payment methods you accept?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h6>How often do you release updates?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h6>What is your refund policy?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                        <div class="col-md-6 mb-5">
                            <h6>How can I contact you?</h6>
                            <p class="text-muted">Nam liber tempor cum soluta nobis eleifend option congue nihil imper
                                per tem por legere me doming.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--CTA section-->
    {{--<section class="py-4 my-4 border-top border-bottom">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 mx-auto">
                    <div class="card p-4">
                        <div class="card-body d-md-flex flex-row align-items-center text-center text-md-left">
                            <div class="mb-4 mb-md-0">
                                <h4>Try free for 7 days</h4>
                                <span class="text-muted">Sign up now and setup your own channel.</span>
                            </div>
                            <button class="btn btn-secondary ml-md-auto d-inline-flex flex-row align-items-center">
                                Get Started
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" class="feather feather-chevrons-right ml-2">
                                    <polyline points="13 17 18 12 13 7"></polyline>
                                    <polyline points="6 17 11 12 6 7"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>--}}

    <!--blog section-->
    <section class="py-5" id="blog">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto text-center">
                    <h2>From Musli's blog</h2>
                    <p class="text-muted lead">What's new at Musli.</p>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="card">
                        <a href="#">
                            <img class="card-img-top img-raised" src="img/blog-1.jpg" alt="Blog 1">
                        </a>
                        <div class="card-body px-0">
                            <h5 class="card-title mb-2"><a href="#">Basic of mobile apps</a></h5>
                            <p class="text-muted small-xl mb-2">January 27, 2018</p>
                            <p class="card-text">Nam liber tempor cum soluta nobis eleifend option congue nihil imper,
                                consectetur adipiscing elit. <a href="#">Learn more</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <a href="#">
                            <img class="card-img-top img-raised" src="img/blog-2.jpg" alt="Blog 2">
                        </a>
                        <div class="card-body px-0">
                            <h5 class="card-title mb-2"><a href="#">How to create gradient</a></h5>
                            <p class="text-muted small-xl mb-2">December 16, 2017</p>
                            <p class="card-text">Nam liber tempor cum soluta nobis eleifend option congue nihil imper,
                                consectetur adipiscing elit. <a href="#">Learn more</a></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <a href="#">
                            <img class="card-img-top img-raised" src="img/blog-3.jpg" alt="Blog 3">
                        </a>
                        <div class="card-body px-0">
                            <h5 class="card-title mb-2"><a href="#">How to create T-shirt design</a></h5>
                            <p class="text-muted small-xl mb-2">December 2nd, 2017</p>
                            <p class="card-text">Nam liber tempor cum soluta nobis eleifend option congue nihil imper,
                                consectetur adipiscing elit. <a href="#">Learn more</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-6 mx-auto text-center">
                    <a href="#" class="btn btn-outline-success">Follow us on Medium</a>
                </div>
            </div>
        </div>
    </section>

    <!--contact us-->
    <section class="jumbotron py-5" id="contact" style="background-image: url(img/mockup.png)">
        <div class="container">
            <div class="row">
                <div class="col-md-6 my-md-auto text-center text-md-left pb-5 pb-md-0">
                    <h1 class="display-4 text-white">Want to partner with us?</h1>
                    <p class="lead text-light">Magnis modipsae que voloratati andigen daepeditem quiate conecus aut
                        labore. Laceaque quiae sitiorem rest non restibusaes maio es dem tumquam explabo.</p>
                </div>
                <div class="col-md-5 ml-auto">
                    <div class="card">
                        <div class="card-body p-4">
                            <h5 class="text-center">Fill the form below to contact</h5>
                            <form class="signup-form">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Full name">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Website">
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control" placeholder="Email address">
                                </div>
                                <div class="form-group">
                                    <textarea class="form-control" placeholder="What are you looking for?"></textarea>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-secondary btn-block">Send your message</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!--footer-->
    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-sm-5 mr-auto">
                    <h6>About Musli</h6>
                    <p class="text-muted">Magnis modipsae que voloratati andigen daepeditem quiate conecus aut labore.
                        Laceaque quiae sitiorem rest non restibusaes maio es dem tumquam explabo.</p>
                    <ul class="list-inline social social-rounded social-sm">
                        <li class="list-inline-item">
                            <a href=""><i class="fa fa-facebook"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href=""><i class="fa fa-twitter"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href=""><i class="fa fa-google-plus"></i></a>
                        </li>
                        <li class="list-inline-item">
                            <a href=""><i class="fa fa-dribbble"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-2">
                    <h6>Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Refund policy</a></li>
                    </ul>
                </div>
                <div class="col-sm-2">
                    <h6>Partner</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Refer a friend</a></li>
                        <li><a href="#">Affiliates</a></li>
                    </ul>
                </div>
                <div class="col-sm-2">
                    <h6>Help</h6>
                    <ul class="list-unstyled">
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Log in</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12 text-muted text-center small-xl">
                    © 2018 Driving Test Cancellations

                    <div>Icons made by <a href="https://www.freepik.com" title="Freepik">Freepik</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a></div>
                    <div>Icons made by <a href="https://www.flaticon.com/authors/smashicons" title="Smashicons">Smashicons</a> from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com</a> is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank">CC 3.0 BY</a></div>
                </div>
            </div>
        </div>
    </footer>

    <!--scroll to top-->
    <div class="scroll-top">
        <i class="fa fa-angle-up" aria-hidden="true"></i>
    </div>

    {{--<div id="saka-gui-root"--}}
         {{--style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 2147483647; opacity: 1; pointer-events: none;">--}}
        {{--<div></div>--}}
    {{--</div>--}}

@stop