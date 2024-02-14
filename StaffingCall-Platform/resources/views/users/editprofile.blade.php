@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->

        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">My Account</h3>
                
            </div>
            
             @if(Session::has('success'))
                <div class="alert alert-success alert-dismissable">

                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <p> {!! Session::get('success') !!}</p>

                 </div>
            @endif  
            
             @if(Session::has('error'))
                <div class="alert alert-danger alert-dismissable">

                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <p> {!! Session::get('error') !!}</p>

                 </div>
            @endif  
         
            
            
            <div class="block-content">
                <div class="row justify-content-center py-20">
                    <div class="col-xl-10">
                        <form class="js-user-create-validation-bootstrap" action="{!! url(Config('constants.urlVar.updateProfile')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                            
                            
                            
                            <div class="form-group row{!! $errors->has('profilePic') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="logo"></label>
                                <div class="col-lg-8" style="position: relative;">
                                    <a  title="Choose photo" style="color: #1b1212de;outline: none;" id="cropPopup" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" >
                                     <img class="img_style" width='150' height='150' src="@if($userInfo->profilePic){!! url('public/'.$userInfo->profilePic) !!}@else {!! url('/assets/img/img_preview.png') !!} @endif" id="logo_preview"/>
                                     <div style="position: absolute;top: 70px;left: 75px;">
                                        <i class="fa fa-camera fa-2x"></i>
                                     </div>
                                    </a> 
                                    <!--<input type="file" name="profilePic" id="logo" value="">-->
                                
                                </div>
                            </div>
                            
                            @if(Auth::user()->role == 4 || Auth::user()->role == 0)
                            
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="firstName">First Name <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    {!! $userInfo->firstName !!}
                                    
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="lastName">Last Name 
                                    </label>
                                <div class="col-lg-6">
                                    {!! $userInfo->lastName !!}
                                    
                                </div>
                            </div>
                            
                            @else
                            <div class="form-group row{!! $errors->has('firstName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="firstName">First Name <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    <input value="{!! $userInfo->firstName !!}" type="text" class="form-control" id="firstName" name="firstName"  placeholder="First Name">
                                     @if ($errors->has('firstName'))
                                    <div id="firstName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('firstName') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row{!! $errors->has('lastName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="lastName">Last Name 
                                    </label>
                                <div class="col-lg-6">
                                    <input value="{!! $userInfo->lastName !!}" type="text" class="form-control" id="firstName" name="lastName"  placeholder="Last Name">
                                     @if ($errors->has('lastName'))
                                    <div id="lastName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('lastName') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            @endif
                            
                            <div class="form-group row{!! $errors->has('email') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="email">Email <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    <input type="text" value="{!! $userInfo->email !!}" class="form-control" id="email" name="email" placeholder="Email Address">
                                     @if ($errors->has('email'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('email') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('phone') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    <input value="{!! str_replace(array('+1','+91'), '', $userInfo->phone) !!}" type="text" class="form-control" id="phone" name="phone"  placeholder="Phone Number">
                                     @if ($errors->has('phone'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('phone') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    
                                    <input type="hidden" name="croppedImage" id="croppedImage" value="" />
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Save</button>
                                    <button style="cursor: pointer;" onclick="javascript:location.href='{!! url(Config('constants.urlVar.myProfile')) !!}'" type="button" class="btn btn-alt-info">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>



<!--Cropping Popup-->

<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content" style="width:760px;margin-top: 5%;">

            <div class="block block-themed block-transparent mb-0" style="max-height: 570px;overflow: auto;">
                <div class="block-header bg-primary-dark">

                    <div class="block-options">
                        <h3 style="color:#fff;">Choose your profile picture</h3>

                    </div>
                </div>
                <div class="block-content">
                    
                    <div class="image-editor">
                        <input type="file" class="cropit-image-input">
                        <div class="cropit-preview"></div>
                        <div class="image-size-label">
                          Resize image
                        </div>
                        <input type="range" class="cropit-image-zoom-input">
                        <button class="rotate-ccw">Rotate counterclockwise</button>
                        <button class="rotate-cw">Rotate clockwise</button>

                        <button style="cursor: pointer;" id="getCroppedImg" disabled="" class="export btn btn-alt-success">Save</button>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Cancel</button>
             </div>

        </div>
    </div>
</div>
<!--Cropping Popup-->

<script type="text/javascript" >
    
 var defaultGroupIcon = "@if($userInfo->profilePic){!! url('public/'.$userInfo->profilePic) !!}@else {!! url('/assets/img/img_preview.png') !!} @endif";   
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var requestUrl = "{!! url(Config('constants.urlVar.ajaxGetSecondaryBusinessUnit')) !!}";

</script>


        <style>
      .cropit-preview {
        background-color: #f8f8f8;
        background-size: cover;
        border: 5px solid #ccc;
        border-radius: 3px;
        margin-top: 7px;
        width: 250px;
        height: 250px;
      }

      .cropit-preview-image-container {
        cursor: move;
      }

      .cropit-preview-background {
        opacity: .2;
        cursor: auto;
      }

      .image-size-label {
        margin-top: 10px;
      }

      input, .export {
        /* Use relative position to prevent from being covered by image background */
        position: relative;
        z-index: 10;
        display: block;
      }

      button {
        margin-top: 10px;
      }
      
      
      
    </style>
@endsection