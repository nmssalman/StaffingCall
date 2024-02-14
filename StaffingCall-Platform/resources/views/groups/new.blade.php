@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add New Group</h3>
                
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
                        <form class="js-group-validation-bootstrap" action="{!! url(Config('constants.urlVar.saveNewGroup')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            <fieldset><legend>Group Information</legend>
                            <div class="form-group row{!! $errors->has('groupCode') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="groupCode">Group Code <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('groupCode') !!}" class="form-control" id="groupCode" name="groupCode" placeholder="Enter Group Code">
                                 @if ($errors->has('groupCode'))
                                <div id="groupCode-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('groupCode') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row{!! $errors->has('groupName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="groupName">Group Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('groupName') !!}" class="form-control" id="groupName" name="groupName" placeholder="Enter Group Name">
                                 @if ($errors->has('groupName'))
                                <div id="groupName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('groupName') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            
                            <div class="form-group row{!! $errors->has('maximumUnits') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="maximumUnits">Maximum Number of Business Units </label>
                                <div class="col-lg-8">
                                    <input min="0" type="number" value="{!! old('maximumUnits') !!}" class="form-control" id="maximumUnits" name="maximumUnits" placeholder="Maximum Number of Business Units">
                                 @if ($errors->has('maximumUnits'))
                                <div id="maximumUnits-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('maximumUnits') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            <div class="form-group row{!! $errors->has('maximumEmployee') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="maximumEmployee">Maximum Number of Employees (per Business Unit)</label>
                                <div class="col-lg-8">
                                    <input min="0" type="number" value="{!! old('maximumEmployee') !!}" class="form-control" id="maximumEmployee" name="maximumEmployee" placeholder="Maximum Number of Employees (per Business Unit)">
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
                                    <input type="checkbox" class="custom-control-input" id="whiteLabelOption" name="whiteLabelOption" value="1">
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
                                    <input type="text" value="{!! old('firstName') !!}" class="form-control" id="firstName" name="firstName" placeholder="First name">
                                     @if ($errors->has('firstName'))
                                    <div id="firstName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('firstName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('lastName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="lastName">Last Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('lastName') !!}" class="form-control" id="lastName" name="lastName" placeholder="Last name">
                                     @if ($errors->has('lastName'))
                                    <div id="lastName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('lastName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('userName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="userName">Login ID <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('userName') !!}" class="form-control" id="userName" name="userName" placeholder="unique Login ID/username">
                                     @if ($errors->has('userName'))
                                    <div id="userName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('userName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('email') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="email">Email <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('email') !!}" class="form-control" id="email" name="email" placeholder="Email address">
                                     @if ($errors->has('email'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('email') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row{!! $errors->has('phone') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="phone">Phone Number <span class="text-danger">*</span></label>
                                <div class="col-lg-6">
                                    <input value="{!! old('phone') !!}" type="text" class="form-control" id="phone" name="phone"  placeholder="Phone Number">
                                     @if ($errors->has('phone'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('phone') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            
<!--                            <div class="form-group row{!! $errors->has('password') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
                                     @if ($errors->has('password'))
                                    <div id="password-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password') !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{!! $errors->has('password_confirmation') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                                     @if ($errors->has('password_confirmation'))
                                    <div id="cpassword-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password_confirmation') !!}</div>
                                    @endif
                                </div>
                            </div>-->
                         
                            
                            </fieldset>
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Submit</button>
                                    <button style="cursor: pointer;" type="button" onclick="javascript:location.href='{!! url(Config('constants.urlVar.groupList'))!!}'" class="btn btn-alt-danger">Cancel</button>
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