<!doctype html>
<!--[if lte IE 9]>     <html lang="{!! app()->getLocale() !!}" class="no-focus lt-ie10 lt-ie10-msg"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="{!! app()->getLocale() !!}" class="no-focus"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

        <title>{!! $page->title !!}</title>

        <meta name="description" content="Staffing-Call {!! $page->title !!}">
        <meta name="author" content="Gaurav">
        <meta name="robots" content="noindex, nofollow">

        <!-- Open Graph Meta -->
        <meta property="og:title" content="Staffing-Call {!! $page->title !!}">
        <meta property="og:site_name" content="Staffing-Call">
        <meta property="og:description" content="Staffing-Call {!! $page->title !!}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="">
        <meta property="og:image" content="">

        <!-- Icons -->
        <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
        <link rel="shortcut icon" href="{!! asset('/assets/img/favicons/favicon.png') !!}">
        <link rel="icon" type="image/png" sizes="192x192" href="{!! asset('/assets/img/favicons/favicon-192x192.png') !!}">
        <link rel="apple-touch-icon" sizes="180x180" href="{!! asset('/assets/img/favicons/apple-touch-icon-180x180.png') !!}">
        <!-- END Icons -->

        <!-- Stylesheets -->
        <!-- Codebase framework -->
        <link rel="stylesheet" id="css-main" href="{!! asset('/assets/css/codebase.min.css') !!}">

        <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
        <!-- <link rel="stylesheet" id="css-theme" href="{!! asset('/assets/css/themes/flat.min.css') !!}"> -->
        <!-- END Stylesheets -->
    </head>
    <body>
        
        
        
        
        <!-- Page Container -->
        
        <div id="page-container" class="main-content-boxed">
            <!-- Main Container -->
            <main id="main-container">
                <!-- Page Content -->
                <div class="bg-gd-dusk">
                    <div class="hero-static content content-full bg-white invisible" data-toggle="appear">
                        <!-- Header -->
<!--                        <div class="py-5 px-5 text-center">
                            
                            <h1 class="h2 font-w700 mt-50 mb-10">
                                <a class="link-effect font-w700" href="{!! url('/') !!}">
                                
                                
                                <span class="font-size-xl text-primary-dark">
                                    <img width="120" src="{!! url('/assets/img/logo_light.png') !!}" />
                                </span>
                                
                            </a></h1>
                            <h1 class="h4 font-w400 text-muted mb-0">{!! $page->title !!}</h1>
                        </div>-->
                        <!-- END Header -->

                       
                        
                        <div class="block block-bordered block-rounded mb-5" style="margin-top: 20px;">
<!--                                    <div class="block-header" role="tab" id="faq1_h1">
                                        <a class="font-w600 text-body-color-dark" data-toggle="collapse" data-parent="#faq1" href="#faq1_q1" aria-expanded="true" aria-controls="faq1_q1">
                                            {!! $page->title !!}</a>
                                    </div>-->
                                    <div id="faq1_q1" class="collapse show" role="tabpanel" aria-labelledby="faq1_h1">
                                        <div class="block-content border-t1">
                                            {!! $page->content !!}
                                        </div>
                                    </div>
                                </div>
                <!-- END Sign In Form -->
            </div>
        </div>
        <!-- END Page Content -->
    </main>
    <!-- END Main Container -->
</div>
        <!-- END Page Container -->

        <!-- Codebase Core JS -->
        <script src="{!! asset('/assets/js/core/jquery.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/popper.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/bootstrap.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/jquery.slimscroll.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/jquery.scrollLock.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/jquery.appear.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/jquery.countTo.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/core/js.cookie.min.js') !!}"></script>
        <script src="{!! asset('/assets/js/codebase.js') !!}"></script>

      
       
    </body>
</html>