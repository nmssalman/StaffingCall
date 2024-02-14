<!doctype html>
<!--[if lte IE 9]>     <html lang="{!! app()->getLocale() !!}" class="no-focus lt-ie10 lt-ie10-msg"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="{!! app()->getLocale() !!}" class="no-focus"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

        <title>StaffingCall</title>
        
        <meta name="description" content="A fully featured admin dashboard for Staffing Call.">
        <meta name="author" content="Tribyss Apps Solutions">
        <meta name="robots" content="noindex, nofollow">

        <!-- Open Graph Meta -->
        <meta property="og:title" content="Staffing Call - A fully featured admin dashboard for Staffing Call.">
        <meta property="og:site_name" content="Staffing Call">
        <meta property="og:description" content="Staffing Call - A fully featured admin dashboard for Staffing Call.">
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
	
        <!-- Only Grid Page Css-->
        <link rel="stylesheet" href="{!! asset('/assets/js/plugins/datatables/dataTables.bootstrap4.min.css') !!}">
        <!-- Only Grid Page Css-->
        
        <!-- Only Form Page CSS-->        
        <link rel="stylesheet" href="{!! asset('/assets/js/plugins/select2/select2.min.css') !!}">
        <link rel="stylesheet" href="{!! asset('/assets/js/plugins/select2/select2-bootstrap.min.css') !!}">
        <!-- Only Form Page CSS--> 
        
        
        
        
        <!-- Codebase framework -->
        <link rel="stylesheet" id="css-main" href="{!! asset('/assets/css/codebase.min.css') !!}">

        <!-- You can include a specific file from css/themes/ folder to alter the default color theme of the template. eg: -->
        <!-- <link rel="stylesheet" id="css-theme" href="{!! asset('/assets/css/themes/flat.min.css') !!}"> -->
        <!-- END Stylesheets -->
        
       
</head>


    <body>
        <!-- Page Container -->
        
<!--        <div id="page-container" class="sidebar-o side-scroll page-header-modern main-content-boxed">-->
        <div id="page-container" class="sidebar-o side-scroll page-header-fixed main-content-boxed side-trans-enabled sidebar-inverse">
           
             <!-- Side Overlay-->           
<!--            include('admin.includes.right-side-overlay')-->
            <!-- END Side Overlay -->
            
            <!-- Sidebar -->
            @include('includes.leftmenu')
            <!-- END Sidebar -->
            
            <!-- Header -->
             @include('includes.header')
            <!-- END Header -->
            
            
            <!-- Main Container -->
            <main id="main-container">
                <!-- Page Content -->
<!--                <div class="content">-->
                     @yield('content')
<!--                </div>-->
                <!-- END Page Content -->                
            </main>
            
            <!-- END Main Container -->
            <!-- Footer -->
            
            @include('includes.footer')
            
            <!-- END Footer -->
           
        
        </div>
        <!-- END Page Container -->

        <!-- Codebase Core JS -->
        
        @include('includes.footer-script')        
        

</body>

</html>         