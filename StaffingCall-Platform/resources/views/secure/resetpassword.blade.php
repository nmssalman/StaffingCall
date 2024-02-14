<!doctype html>
<!--[if lte IE 9]>     <html lang="{!! app()->getLocale() !!}" class="no-focus lt-ie10 lt-ie10-msg"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="{!! app()->getLocale() !!}" class="no-focus"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

        <title>StaffingCall | Reset Pasword</title>

        <meta name="description" content="Staffing-Call Login">
        <meta name="author" content="Gaurav">
        <meta name="robots" content="noindex, nofollow">

        <!-- Open Graph Meta -->
        <meta property="og:title" content="Staffing-Call Reset Password">
        <meta property="og:site_name" content="Staffing-Call">
        <meta property="og:description" content="Staffing-Call Login">
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
        
        <!-- Only Form Page CSS-->        
        
        <!-- Only Form Page CSS--> 

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
                        <div class="py-30 px-5 text-center">
                            
                            <h1 class="h2 font-w700 mt-50 mb-10"><a class="link-effect font-w700" href="{!! url('/') !!}">
                                
                                <span class="font-size-xl text-primary-dark">
                                    <img width="200" src="{!! url('/assets/img/logo.png') !!}" />
                                </span>
                                
                                </a></h1>
                            <h2 class="h4 font-w400 text-muted mb-0">Reset your password</h2>
                        </div>
                        <!-- END Header -->

                        <!-- Sign In Form -->
                        <div class="row justify-content-center px-5">
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                
                                 <form  class="js-validation-resetpass" method="POST" action="{!! url(Config('constants.urlVar.resetpassword').$token) !!}" role="form" data-parsley-validate novalidate>
                     <input type="hidden" name="token" value="{!! $token !!}" />      
                      <input type="hidden" name="_token" value="{!! csrf_token() !!}" />      
                       
                     
					
                    
                            @if(Session::has('success'))
                                <div class="alert alert-success alert-dismissable">
							
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <p> {!! Session::get('success') !!}</p>
                            
                            </div>
                            
                            @endif               
                             
                            @if(Session::has('msg'))
                                <div class="alert alert-danger alert-dismissable">
							
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <p> {!! Session::get('msg') !!}</p>
                            
                            </div>
                            
                            @endif          
                                         
                 
                 	@if (count($errors) > 0)
						<div class="alert alert-danger alert-dismissable">
							
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							
								@foreach ($errors->all() as $error)
									<p>{!! $error !!}</p>
								@endforeach
							
						</div>
						@endif
                                    
                
                <?php //echo count($groups); echo '<pre>';print_r($groups); ?> 
                                                
                @if(count($groups) > 0)
                
                <div class="form-group row{!! $errors->has('businessGroupID') ? ' is-invalid' : '' !!}">
                    
                    <div class="col-12">
                        <label for="businessGroup">Select your Business Group</label>
                        <select class="js-select2 form-control" id="businessGroupID" name="businessGroupID" style="width: 100%;" data-placeholder="Choose Your Group..">
                            <!--<option value=""></option>-->
                            <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                             @foreach($groups as $group)  
                            <option @if(old('businessGroupID') == $group->id) selected @endif value="{!! $group->id !!}">{!! $group->groupName." (".$group->groupCode.")" !!}</option>
                            @endforeach
                        </select>


                         @if ($errors->has('businessGroupID'))
                        <div id="businessGroupID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessGroupID') !!}</div>
                        @endif
                    </div>
                </div>
                
                  
                @endif
                                                
                <div class="form-group row">
                    <div class="col-12">
                        <div class="form-material floating{!! $errors->has('password') ? ' has-error' : '' !!}">
                            <input style="color: #fff;" type="password" class="form-control" id="password" name="password">
                            <label for="password">New Password</label>
                            
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{!! $errors->first('password') !!}</strong>
                                    </span>
                                @endif
                        </div>
                    </div>
                </div>                                  
                                                
                <div class="form-group row">
                    <div class="col-12">
                        <div class="form-material floating{!! $errors->has('password_confirmation') ? ' has-error' : '' !!}">
                            <input style="color: #fff;" type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            <label for="password_confirmation">Confirm Password</label>
                            
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{!! $errors->first('password_confirmation') !!}</strong>
                                    </span>
                                @endif
                        </div>
                    </div>
                </div>                                
                                                
                                                
                                 
              
                <div class="form-group row gutters-tiny">
                    <div class="col-12 mb-10">
                        <button style="cursor: pointer;" type="submit" class="btn btn-block btn-hero btn-noborder btn-rounded btn-alt-primary">
                            <i class="si si-login mr-10"></i> Reset
                        </button>
                    </div>
                    <div class="col-sm-6 mb-5">
                        &nbsp;
                    </div>
                    <div class="col-sm-6 mb-5">
                        <a class="btn btn-block btn-noborder btn-rounded btn-alt-secondary" href="{!! url(Config('constants.urlVar.login')) !!}">
                            <i class="fa fa-warning text-muted mr-5"></i> Login
                        </a>
                    </div>
                            </div>
                        </form>
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
        
        
        <!-- Page JS Plugins -->
        <script src="{!! asset('/assets/js/plugins/jquery-validation/jquery.validate.min.js') !!}"></script>
        
        <!-- Page JS Code -->
        <script src="{!! asset('/assets/js/pages/op_auth_resetpassword.js') !!}"></script>
        
        
        
        <script type="text/javascript">
            var csrfToken = $('[name="_token"]').val();

            setInterval(refreshToken, 60*1000); // 1 min 

            function refreshToken(){
                $.get('refresh-csrf').done(function(data){
                    csrfToken = data; // the new token
                });
            }

            setInterval(refreshToken, 60*1000); // 1 min 

        </script>
        
        
    </body>
</html>
