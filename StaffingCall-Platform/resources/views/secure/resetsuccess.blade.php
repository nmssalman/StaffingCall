
<!DOCTYPE html>
<html lang="{!! app()->getLocale() !!}">
<head>
<meta charset="utf-8">
 @php $sessionStatus = false; @endphp
 
@if(Session::has('msg'))
     @php $sessionStatus = true; @endphp
 @endif


<title><?= $sessionStatus?'StaffingCall : Reset Password':'StaffingCall : 404 Not Found'; ?></title>
<link href="{!! asset('/assets/css/bootstrap.min.css'); !!}" rel="stylesheet" type="text/css">
<link href="{!! asset('/assets/css/main.css') !!}" rel="stylesheet" type="text/css">
<link href="{!! asset('/assets/css/codebase.min.css') !!}" rel="stylesheet" type="text/css">

<link rel="shortcut icon" href="{!! asset('/assets/img/favicons/favicon.png') !!}">
        <link rel="icon" type="image/png" sizes="192x192" href="{!! asset('/assets/img/favicons/favicon-192x192.png') !!}">
        <link rel="apple-touch-icon" sizes="180x180" href="{!! asset('/assets/img/favicons/apple-touch-icon-180x180.png') !!}">
       

</head>
<body style="background-color:#2d2d2d; background-repeat:no-repeat; background-size:cover;">
   
	<div class="wrapper full-page-wrapper page-auth page-login text-center">
            <div class="inner-page">
                <div class="logo">
                    <a href="{!! url('/') !!}"><img width="200" src="{!! asset('/assets/img/logo.png') !!}" alt="" /></a>
                   
                </div>
                
                
                <div class="login-box center-block" style="border: none;">
    
                    @if($sessionStatus)
                        
                            @if(Session::has('msg'))
                            <div class="alert alert-success alert-dismissable">

                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <p> {!! Session::get('msg') !!}</p>

                            </div>
                            @endif  
                        <a  href="{!! url(Config('constants.urlVar.login')) !!}" type="button" class="btn btn-block btn-hero btn-noborder btn-rounded btn-alt-primary">
                            <i class="si si-login mr-10"></i> Back to Login
                        </a>
                    @else
                    <h1 style="color:#FFF;">OOPS! 404 Not Found</h1>
                    <p style="color:#FFF;">Please check your url either it has expired or moved.</p>
                    @endif
         
         		</div>
                
            </div>
       </div>
            
            
            
      
<!--	<footer style="color:#FFF;font-weight:bold;" class="footer">
            
            Staffing Call V1.0 &copy; 2017-18
        </footer>      -->
	<script src="{!! asset('/assets/js/jquery/jquery-2.1.0.min.js') !!}"></script>
	<script src="{!! asset('/assets/js/bootstrap/bootstrap.js') !!}"></script>
</body>
</html>