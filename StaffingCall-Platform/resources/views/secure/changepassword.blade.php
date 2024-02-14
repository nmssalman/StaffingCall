@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
        <h2 class="content-heading">Change Password</h2>
        
        @if(Session::has('error'))
                <div class="alert alert-danger alert-dismissable">

                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <p> {!! Session::get('error') !!}</p>

                 </div>
            @endif  
            
             @if(Session::has('success'))
                <div class="alert alert-success alert-dismissable">

                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <p> {!! Session::get('success') !!}</p>

                 </div>
            @endif  
        
        <div class="block">
            <div class="block-header block-header-default">
                <!--<h3 class="block-title">Change your password here</h3>-->
                <div class="block-options">
                    
                </div>
            </div>
            
             
         
            
            
            <div class="block-content">
                <div class="row justify-content-center py-20">
                    <div class="col-xl-6">
                        <form class="change-password-validation-bootstrap" action="{!! url(Config('constants.urlVar.changePassword')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            <div class="form-group row{!! $errors->has('old_password') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="old_password">Current Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Please enter current password..">
                                     @if ($errors->has('old_password'))
                                    <div id="old_password-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password') !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{!! $errors->has('password') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="password">New Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Choose a safe one..">
                                     @if ($errors->has('password'))
                                    <div id="password-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password') !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{!! $errors->has('password_confirmation') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="..and confirm it!">
                                     @if ($errors->has('password_confirmation'))
                                    <div id="password_confirmation-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password_confirmation') !!}</div>
                                    @endif
                                </div>
                            </div>
                         
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <button type="submit" class="btn btn-alt-primary">Change Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>
@endsection