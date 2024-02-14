@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->

        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add New User</h3>
                
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
                        <form class="js-user-create-validation-bootstrap" action="{!! url(Config('constants.urlVar.saveNewUser')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                           
                            <div class="form-group row{!! $errors->has('role') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="role">User Level <span class="text-danger">*</span></label>
                                <div class="col-6">
                                    <label class="css-control css-control-warning css-radio" onclick="checkUserType('0')">
                                        <input @if(old('role') == '0') checked="checked" @else checked="checked" @endif value="0" type="radio" class="css-control-input" id="role" name="role">
                                        <span class="css-control-indicator"></span> 
                                        <span class="badge badge-warning">End User</span>
                                    </label>
                                    <label class="css-control css-control-info css-radio" onclick="checkUserType('4')">
                                        <input @if(old('role') == '4') checked="checked" @endif value="4" type="radio" class="css-control-input" id="role" name="role">
                                        <span class="css-control-indicator"></span>
                                        <span class="badge badge-info">Admin </span>
                                    </label>
                                    
                                    @if(Auth::user()->role == 2)
                                    
                                    <label class="css-control css-control-danger css-radio" onclick="checkUserType('3')">
                                        <input @if(old('role') == '3') checked="checked" @endif value="3" type="radio" class="css-control-input" id="role" name="role">
                                        <span class="css-control-indicator"></span> 
                                        <span class="badge badge-danger">Super Admin </span>
                                    </label>
                                    
                                    @endif
                                    
                                     @if ($errors->has('role'))
                                    <div id="type-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('role') !!}</div>
                                    @endif
                                    
                                </div>
                            </div>
                        <?php $multiOldUnits = old('businessUnitID')?old('businessUnitID'):array(); ?>
                        <?php $multiSecondaryOldUnits = old('businessUnitIDs')?old('businessUnitIDs'):array(); ?>
                        <?php $multiSkills = old('skills')?old('skills'):array(); ?>
                                <div id="forEndUser">
                                    <div class="form-group row{!! $errors->has('businessUnitID') ? ' is-invalid' : '' !!}">
                                    <label class="col-lg-4 col-form-label" for="businessUnitID">Primary Business Unit <span class="text-danger">*</span></label>
                                        <div class="col-md-4">

                                            <select class="primaryUnitForEndUser js-select2 form-control" id="businessUnitID" name="businessUnitID[]" style="width: 100%;" data-placeholder="Primary Business Unit" >
                                                            <option value=""></option>
                                                            <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                             @foreach($units as $unit)
                                                            <option @if(in_array($unit->id,$multiOldUnits)) selected="selected" @endif value="{!! $unit->id !!}">{!! $unit->unitName !!}</option>
                                                            @endforeach
                                                        </select>


                                             @if ($errors->has('businessUnitID'))
                                            <div id="businessUnitID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitID') !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    
                                    <div class="form-group row{!! $errors->has('businessUnitIDs') ? ' is-invalid' : '' !!}">
                                    <label class="col-lg-4 col-form-label" for="businessUnitIDs">Secondary Business Unit </label>
                                        <div class="col-md-4">

                                            <select class="js-select2 form-control" id="businessUnitIDs" name="businessUnitIDs[]" style="width: 100%;" data-placeholder="Secondary Business Units" multiple>
                                                            <option value=""></option>
                                                            <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                             @foreach($units as $unit)
                                                            <option @if(in_array($unit->id,$multiSecondaryOldUnits)) selected="selected" @endif value="{!! $unit->id !!}">{!! $unit->unitName !!}</option>
                                                            @endforeach
                                                        </select>


                                             @if ($errors->has('businessUnitID'))
                                            <div id="businessUnitIDs-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitIDs') !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    
                                </div>    
                               
                                <div id="forHigherUser" style="display: none;">
                                    <div class="form-group row{!! $errors->has('businessUnitID') ? ' is-invalid' : '' !!}">
                                    <label class="col-lg-4 col-form-label" for="businessUnitID">Business Unit <span class="text-danger">*</span></label>
                                        <div class="col-md-4">

                                            <select class="js-select2 form-control" id="businessUnitID" name="businessUnitID[]" style="width: 100%;" data-placeholder="Choose Business Unit" >
                                                            <option value=""></option>
                                                            <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                             @foreach($units as $unit)
                                                            <option @if(in_array($unit->id,$multiOldUnits)) selected="selected" @endif value="{!! $unit->id !!}">{!! $unit->unitName !!}</option>
                                                            @endforeach
                                                        </select>


                                             @if ($errors->has('businessUnitID'))
                                            <div id="businessUnitID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitID') !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            
                            
                            <div id="forAdminUser" style="display: none;">
                                <div class="form-group row{!! $errors->has('needApproval') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="needApproval">
                                    Need approval ? <br/>
                                    <span style="font-size: 12px;font-weight: normal;">
                                        (while creating new request)</span>
                                </label>
                                    
                                    <div class="col-md-4">

                                        <select class="js-select2 form-control" id="needApproval" name="needApproval" style="width: 100%;" data-placeholder="Need Approval" >
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                            <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                        </select>

                                    </div>
                                </div>
                            </div>
                            
                            
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
                            
                            <!--<div class="form-group row{!! $errors->has('userName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="userName">Login ID <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('userName') !!}" class="form-control" id="userName" name="userName" placeholder="unique Login ID/username">
                                     @if ($errors->has('userName'))
                                    <div id="userName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('userName') !!}</div>
                                    @endif
                                
                                </div>
                            </div>-->
                            
                            <div class="form-group row{!! $errors->has('email') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="email">Email <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('email') !!}" class="form-control" id="email" name="email" placeholder="Email Address">
                                     @if ($errors->has('email'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('email') !!}</div>
                                    @endif
                                
                                </div>
                            </div>
                            
                            
                             <div class="form-group row{!! $errors->has('skills') ? ' is-invalid' : '' !!}" id="userSkillsForm">
                                <label class="col-lg-4 col-form-label" for="skills">Employee Skills 
                                    <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="skills" name="skills[]" data-placeholder="Employee skills" multiple>
                                        <option value=""></option>
                                        @foreach($staffingCategory as $category)
                                        <option @if(in_array($category->id,$multiSkills)) selected="selected" @endif value="{!! $category->id !!}">
                                            {!! $category->skillName !!}
                                        </option>
                                        @endforeach
                                    </select>
                                    

                                     @if ($errors->has('skills'))
                                    <div id="skills-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('skills') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
<!--                             <div class="form-group row{!! $errors->has('experiencedLevel') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="experiencedLevel">Experienced Level 
                                    <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="experiencedLevel" name="experiencedLevel" data-placeholder="Experienced Level">
                                        <option value="1">Junior</option>
                                        <option value="2">Intermediate</option>
                                        <option value="3">Experienced</option>
                                        
                                    </select>
                                    

                                     @if ($errors->has('experiencedLevel'))
                                    <div id="experiencedLevel-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('experiencedLevel') !!}</div>
                                    @endif
                                </div>
                            </div>-->
                            
                            
                            
                            
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
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password">
                                     @if ($errors->has('password'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password') !!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row{!! $errors->has('password_confirmation') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password">
                                     @if ($errors->has('password_confirmation'))
                                    <div id="email-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('password_confirmation') !!}</div>
                                    @endif
                                </div>
                            </div>-->
                         
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Submit</button>
                                    <button style="cursor: pointer;" onclick="javascript:location.href='{!! url(Config('constants.urlVar.userList')) !!}'" type="button" class="btn btn-alt-info">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>

<script type="text/javascript" >
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var requestUrl = "{!! url(Config('constants.urlVar.ajaxGetSecondaryBusinessUnit')) !!}";
var multiSkills = {!! $multiSkills?json_encode($multiSkills):json_encode($multiSkills) !!}

</script>
@endsection