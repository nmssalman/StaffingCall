@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
        <h2 class="content-heading">Update Group Information</h2>
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Update Group</h3>
                <div class="block-options">
                    <button type="button" class="btn-block-option">
                        <i class="si si-wrench"></i>
                    </button>
                </div>
            </div>
            
             @if(Session::has('error'))
                <div class="alert alert-danger alert-dismissable">

                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <p> {!! Session::get('error') !!}</p>

                 </div>
            @endif  
         
            
            
            <div class="block-content">
                <div class="row justify-content-center py-20">
                    <div class="col-xl-10">
                      @if(Auth::user()->role == '1')
                         <form class="js-group-validation-bootstrap" action="{!! url(Config('constants.urlVar.updateGroup')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            <fieldset><legend>Group Information</legend>
                            <div class="form-group row{!! $errors->has('groupCode') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="groupCode">Group Code <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $group->groupCode !!}" class="form-control" id="groupCode" name="groupCode" placeholder="Enter Group Code">
                                 @if ($errors->has('groupCode'))
                                <div id="groupCode-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('groupCode') !!}</div>
                                @endif
                                </div>
                            </div>
                                
                            <div class="form-group row{!! $errors->has('groupName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="groupName">Group Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $group->groupName !!}" class="form-control" id="groupName" name="groupName" placeholder="Enter Group Name..">
                                 @if ($errors->has('groupName'))
                                <div id="name-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('groupName') !!}</div>
                                @endif
                                </div>
                            </div>
                                
                                
                           <div class="form-group row{!! $errors->has('logo') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="logo">Logo </label>
                                <div class="col-lg-8">
                                    <div style="position: absolute;margin-left: 25%;">
                                        <a id="removeLogo" href="javascript:void(0);" onclick=""><i class="fa fa-trash"></i> Remove</a>
                                    </div>
                                     <img class="img_style" width='150' height='150' src="@if($group->logo){!! url('public/'.$group->logo) !!}@else {!! url('/assets/img/group-logo.png') !!} @endif" id="logo_preview"/>
                                    <!--<input type="file" name="logo" id="logo" value="">-->
                                     <a id="cropPopup" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" >
                                         Choose logo
                                     </a>
                                
                                </div>
                            </div>     
                            
                            
                            
                            <div class="form-group row{!! $errors->has('maximumUnits') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="maximumUnits">Maximum Number of Business Units </label>
                                <div class="col-lg-8">
                                    <input min="0" type="number" value="{!! $group->maximumUnits?$group->maximumUnits:'' !!}" class="form-control" id="maximumUnits" name="maximumUnits" placeholder="Unlimited">
                                 @if ($errors->has('maximumUnits'))
                                <div id="maximumUnits-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('maximumUnits') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            <div class="form-group row{!! $errors->has('maximumEmployee') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="maximumEmployee">Maximum Number of Employees (per Business Unit) </label>
                                <div class="col-lg-8">
                                    <input min="0" type="number" value="{!! $group->maximumEmployee?$group->maximumEmployee:'' !!}" class="form-control" id="maximumEmployee" name="maximumEmployee" placeholder="Unlimited">
                                 @if ($errors->has('maximumEmployee'))
                                <div id="maximumEmployee-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('maximumEmployee') !!}</div>
                                @endif
                                </div>
                            </div>
                                
                            <div class="form-group row{!! $errors->has('whiteLabelOption') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="whiteLabelOption">Enable White Labeling ? 
                                   </label>
                                <div class="col-lg-1">
                                    <label class="custom-control custom-checkbox">
                                    <input @if($group->whiteLabelOption) {!! "checked" !!} @endif type="checkbox" class="custom-control-input" id="whiteLabelOption" name="whiteLabelOption" value="1">
                                        <span class="custom-control-indicator"></span>
                                    </label> 
                                     @if ($errors->has('whiteLabelOption'))
                                    <div id="whiteLabelOption-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('whiteLabelOption') !!}</div>
                                    @endif
                                </div>
                            </div>     
                            
                            
                            </fieldset>
                            
                            
                            <fieldset><legend>Group Manager Information</legend>  
                                
                                <div class="form-group row{!! $errors->has('firstName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="firstName">First Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $manager->firstName !!}" class="form-control" id="firstName" name="firstName" placeholder="First name">
                                     @if ($errors->has('firstName'))
                                    <div id="firstName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('firstName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('lastName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="lastName">Last Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $manager->lastName !!}" class="form-control" id="lastName" name="lastName" placeholder="Last name">
                                     @if ($errors->has('lastName'))
                                    <div id="lastName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('lastName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('userName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="userName">Login ID <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $manager->userName !!}" class="form-control" id="userName" name="userName" placeholder="unique Login ID/username">
                                     @if ($errors->has('userName'))
                                    <div id="userName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('userName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('email') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="email">Email <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $manager->email !!}" class="form-control" id="email" name="email" placeholder="Email address">
                                     @if ($errors->has('email'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('email') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row{!! $errors->has('phone') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    <input value="{!! str_replace(array('+1','+91'), '', $manager->phone) !!}" type="text" class="form-control" id="phone" name="phone"  placeholder="Phone Number">
                                     @if ($errors->has('phone'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('phone') !!}</div>
                                    @endif
                                </div>
                            </div>
                             
                                
                                
                                
                                
                            </fieldset>
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    
                                    <input type="hidden" name="id" value="{!! $group->id !!}" />
                                    <input type="hidden" name="managerID" value="{!! $manager->id !!}" />
                                    
                                    <input type="hidden" name="croppedImage" id="croppedImage" value="" />
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Update</button>
                                </div>
                            </div>
                        </form>
                      @else
                        <form class="js-update-group-validation-bootstrap" action="{!! url(Config('constants.urlVar.updateGroup')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            
                            <div class="form-group row{!! $errors->has('groupName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="groupName">Group Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $group->groupName !!}" class="form-control" id="groupName" name="groupName" placeholder="Enter Group Name..">
                                 @if ($errors->has('groupName'))
                                <div id="name-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('groupName') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            <div class="form-group row{!! $errors->has('logo') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="logo">Group Logo </label>
                                <div class="col-lg-8">
                                     <img class="img_style" width='150' height='150' src="@if($group->logo){!! url('public/'.$group->logo) !!}@else {!! url('/assets/img/group-logo.png') !!} @endif" id="logo_preview"/>
                                     @if($group->whiteLabelOption)
                                        <!--<input type="file" name="logo" id="logo" value="">-->
                                     <a id="cropPopup" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" >
                                         Choose logo
                                     </a>
                                     @endif
                                </div>
                            </div>
                            
                                                        
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    
                                    <input type="hidden" name="id" value="{!! $group->id !!}" />
                                    <input type="hidden" name="croppedImage" id="croppedImage" value="" />
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Update</button>
                                </div>
                            </div>
                        </form>
                      @endif
                        
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
                        <h3 style="color:#fff;">Update Group Logo</h3>

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
                        <div style="float: right;margin-right: 66%;margin-top: -3%;">
                            <span title="Rotate anti-clock" style="cursor: pointer;" class="rotate-cw fa fa-undo"></span>
                            &nbsp;&nbsp;
                            <span title="Rotate clock-wise" style="cursor: pointer;" class="rotate-ccw fa fa-repeat"></span>
                        </div>
                        <!--<button class="rotate-ccw">Rotate counterclockwise</button>-->
                        <!--<button class="rotate-cw">Rotate clockwise</button>-->

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



<script type="text/javascript">
    var defaultGroupIcon = "@if($group->logo){!! url('public/'.$group->logo) !!}@else {!! url('/assets/img/group-logo.png') !!} @endif";   
    var defaultGroupIconAfterRemove = "{!! url('/assets/img/group-logo.png') !!}";   
    var removeGroupIconUrl = '{!! url(Config::get('constants.urlVar.removeGroupLogo').$group->id) !!}';
</script>

        <style>
            
       .rotate{
    -moz-transition: all 2s linear;
    -webkit-transition: all 2s linear;
    transition: all 2s linear;
}

.rotate.down{
    -ms-transform: rotate(180deg);
    -moz-transform: rotate(180deg);
    -webkit-transform: rotate(180deg);
    transform: rotate(180deg);
}
            
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