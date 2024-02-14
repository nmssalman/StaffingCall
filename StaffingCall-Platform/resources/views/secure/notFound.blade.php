
<!DOCTYPE html>
<html lang="{!! app()->getLocale() !!}">
<head>
<meta charset="utf-8">
<title>404 Page Not Found | StaffingCall</title>
<link href="{!! asset('/assets/css/bootstrap.min.css') !!}" rel="stylesheet" type="text/css">
<link href="{!! asset('/assets/css/main.css') !!}" rel="stylesheet" type="text/css">
<link href="{!! asset('/assets/css/codebase.min.css') !!}" rel="stylesheet" type="text/css">

</head>
<!--<body style="background-image:url({{ asset('/assets/images/app-background.png') }}); background-repeat:no-repeat; background-size:cover;">-->
   
<body style="background-color:#2d2d2d; background-repeat:no-repeat; background-size:cover;">
   
         <div class="wrapper full-page-wrapper page-auth page-login text-center">
            <div class="inner-page">
                <div class="logo">
                    <a href="{!! url('/') !!}"><img width="200" src="{!! asset('/assets/img/logo.png') !!}" alt="" /></a>
                   
                </div>
                
                
                <div class="login-box center-block">
    
				 @if(Session::has('msg'))
                            <div class="alert alert-danger alert-dismissable">

                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <p> {!! Session::get('msg') !!}</p>

                            </div>
                            @endif   
         
		<h1 style="color:#FFF;text-align: center;">Link Expired</h1>
		<p style="color:#FFF;text-align: center;">
                    The page you are looking for is no longer available</p>	
        
        		</div>
                
            </div>
       </div>
         
         
	
     
	 
	<script src="{!! asset('/assets/js/jquery/jquery-2.1.0.min.js') !!}"></script>
	<script src="{!! asset('/assets/js/bootstrap/bootstrap.js') !!}"></script>
</body>
</html>