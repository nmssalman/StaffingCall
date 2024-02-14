@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">New Staffing Request</h3>
                
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
                    <div class="col-xl-12">
                        <form class="js-staffing-new-request-validation-bootstrap" action="{!! url(Config('constants.urlVar.saveStaffingRequest')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="groupName">Business group</label>
                                <div class="col-lg-8">
                                    <h4 > @php echo $groups->groupName." ( ".$groups->groupCode." )" @endphp </h4>
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('businessUnitID') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="businessUnitID">Business unit <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="businessUnitID" name="businessUnitID" style="width: 100%;" data-placeholder="Choose Business Units..">
                                        <option value=""></option>
                                        <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                         @foreach($units as $unit)
                                        <option @if(old('businessUnitID') == $unit->id) selected @endif value="{!! $unit->id !!}">{!! $unit->unitName !!}</option>
                                        @endforeach
                                    </select>
                                    

                                     @if ($errors->has('businessUnitID'))
                                    <div id="businessUnitID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitID') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            
                            
                                
                        <div class="form-group row{!! $errors->has('numberOfOffers') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="numberOfOffers">Number of staff needed? <span class="text-danger">*</span></label>
                            <div class="col-md-4">

                                <input min="1" type="number" value="{!! old('numberOfOffers')?old('numberOfOffers'):1 !!}" class="form-control" id="numberOfOffers" name="numberOfOffers" placeholder="Enter required number of staff/offers">
                                    
                                 @if ($errors->has('numberOfOffers'))
                                <div id="numberOfOffers-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('numberOfOffers') !!}</div>
                                @endif
                            </div>
                        </div>     
                            
                            
                            
                                 <div class="form-group row">
                                     
                                    <label class="col-lg-4 col-form-label" for="businessUnitID">
                                     Reason for requesting this request
                                     <span class="text-danger">*</span></label>
                                    <div class="col-md-4">

                                        <select required="" onchange="reasonRequestCheck(this.value, this)" class="js-select2 form-control" id="requestReasonID" name="requestReasonID" style="width: 100%;" data-placeholder="Vacancy Reasons..">
                                                        <option value=""></option>
                                                        <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                         @php $i = 1; @endphp
                                                         
                                                            @foreach($requestReasons as $requestReason)
                                                        <option defaultOf ="{!! $requestReason->defaultOf !!}" @if($i == 1 || old('requestReasonID') == $requestReason->id) selected @endif value="{!! $requestReason->id !!}">
                                                                 {!! $requestReason->reasonName !!}</option>
                                                           @php $i++; @endphp
                                                            @endforeach
                                        </select>


                                         @if ($errors->has('requestReasonID'))
                                        <div id="requestReasonID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('requestReasonID') !!}</div>
                                        @endif
                                    </div>    
                                    
                                </div>
                    
                    <div id="quesStepOne">
                                
                        <div class="form-group row{!! $errors->has('lastMinuteStaffID') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="staff">Who is the staff with last minute vacancy? <span class="text-danger">*</span></label>
                            <div class="col-md-4">

                                <select class="js-select2 form-control" id="staff" name="lastMinuteStaffID" style="width: 100%;" data-placeholder="Choose staff..">
                                                <option value=""></option>
                                                <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                 @foreach($staffs as $staff)
                                                <option @if(old('lastMinuteStaffID') == $staff->id) selected @endif value="{!! $staff->id !!}">{!! $staff->firstName." ".$staff->lastName !!}</option>
                                                @endforeach
                                </select>


                                 @if ($errors->has('lastMinuteStaffID'))
                                <div id="lastMinuteStaffID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('lastMinuteStaffID') !!}</div>
                                @endif
                            </div>
                        </div>       
                                
                        <div class="form-group row{!! $errors->has('timeOfCallMade') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="timeOfCallMade">What time was the call made by the staff? <span class="text-danger">*</span></label>
                            <div class="col-md-4">

                                <div class="input-group date" id="timeOfCallMadePicker" required>
                                    <input type="text" value="{!! old('timeOfCallMade') !!}" class="form-control" id="timeOfCallMade" name="timeOfCallMade" placeholder="Enter time when call was made">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('timeOfCallMade'))
                                <div id="timeOfCallMade-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('timeOfCallMade') !!}</div>
                                @endif
                            </div>
                        </div>
                                
                             
                        <!-- Vacancy Reason List-->                      
                        
                        <div class="form-group row{!! $errors->has('vacancyReasonID') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="vacancyReasonID">
                                What was the reason given for the last minute vacancy? 
                                <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="vacancyReasonID" name="vacancyReasonID" style="width: 100%;" data-placeholder="Choose Vacancy Reason..">
                                        <option value=""></option>
                                         @foreach($vacancyReasons as $vacancyReason)
                                        <option @if(old('vacancyReasonID') == $vacancyReason->id) selected @endif value="{!! $vacancyReason->id !!}">{!! $vacancyReason->reasonName !!}</option>
                                        @endforeach
                                    </select>
                                    

                                     @if ($errors->has('vacancyReasonID'))
                                    <div id="vacancyReasonID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('vacancyReasonID') !!}</div>
                                    @endif
                                </div>
                            </div>                   
                          
                        <!-- Vacancy Reason List-->  
                    </div>
                    
                            
                                
                        <div class="form-group row{!! $errors->has('requiredStaffCategoryID') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="requiredStaffCategoryID">What type of staffing is required?<span class="text-danger">*</span></label>
                            <div class="col-md-4">
                                <?php $multiSkills = old('requiredStaffCategoryID')?old('requiredStaffCategoryID'):array(); ?>
                                <select class="js-select2 form-control" id="requiredStaffCategoryID" name="requiredStaffCategoryID[]" style="width: 100%;" data-placeholder="Choose staffing category.." multiple="">
                                                <option value=""></option>
                                                <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                    @foreach($staffingCategory as $category)
                                    <option @if(in_array($category->id,$multiSkills)) selected="selected" @endif value="{!! $category->id !!}">
                                        {!! $category->skillName !!}
                                    </option>
                                    @endforeach
                                </select>


                                 @if ($errors->has('requiredStaffCategoryID'))
                                <div id="requiredStaffCategoryID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('requiredStaffCategoryID') !!}</div>
                                @endif
                            </div>
                        </div> 
                            
<!--                             <div class="form-group row{!! $errors->has('requiredExperiencedLevel') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="requiredExperiencedLevel">Required Experienced Level 
                                    <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="requiredExperiencedLevel" name="requiredExperiencedLevel" data-placeholder="Required Experienced Level">
                                        <option value="1">Junior</option>
                                        <option value="2">Intermediate</option>
                                        <option value="3">Experienced</option>
                                        
                                    </select>
                                    

                                     @if ($errors->has('requiredExperiencedLevel'))
                                    <div id="requiredExperiencedLevel-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('requiredExperiencedLevel') !!}</div>
                                    @endif
                                </div>
                            </div>-->
                                
                             
                                
                        <div class="form-group row{!! $errors->has('staffingDate') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="staffingDate">Date staff needed <span class="text-danger">*</span></label>
                            <div class="col-md-3">

                                <div class="input-group date" id="staffingStartDatePicker" required>
                                    <input type="text" class="form-control" id="staffingStartDate" name="staffingStartDate" placeholder="Date staff needed">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('staffingStartDate'))
                                <div id="staffingStartDate-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('staffingStartDate') !!}</div>
                                @endif
                            </div>
                            
                            
                        </div>
                                
                                
                                
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label" for="staffingShiftType">Shift staff needed <span class="text-danger">*</span></label>
                            
                            <div class="col-md-3">
                                <label class="css-control css-control-warning css-radio" id="dayShiftClick">
                                    <input checked="checked" value="0" type="radio" class="css-control-input" id="dayShift" name="shiftType">
                                    <span class="css-control-indicator"></span> 
                                    Preset Shifts
                                    
                                </label>   
                                
                            </div>
                            
                            <div class="col-md-3">
                                <label class="css-control css-control-danger css-radio" id="customShiftClick">
                                    <input value="1" type="radio" class="css-control-input" id="customShift" name="shiftType">
                                    <span class="css-control-indicator"></span> 
                                    Custom Shift
                                    
                                </label>    
                                
                            </div>
                            
                        </div>
                                
                                <div id="dayShiftStyle">
                        <div class="form-group row">
                            <label class="col-lg-4 col-form-label" for="shiftTiming"></label>
                            <div class="col-md-5">
                            
                                <div class="custom-controls-stacked" id="dayShiftData">

                                </div>
                            </div>

                        </div> 
                    </div>
                                
                               
                                
                    <div id="customShiftStyle"  style="display: none;">    
                         <div class="form-group row{!! $errors->has('customShiftStartTime') ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="customShiftStartTime"></label>

                            <div class="col-md-3">
                                Start Time
                                <div class="input-group date" id="datetimepicker2" required>
                                    <input type="text" value="{!! old('customShiftStartTime') !!}" class="form-control" id="customShiftStartTime" name="customShiftStartTime" placeholder="Start Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('customShiftStartTime'))
                                <div id="customShiftStartTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('customShiftStartTime') !!}</div>
                                @endif
                            </div>

                            <div class="col-md-3">
                                    End Time
                                <div class="input-group date" id="datetimepicker3" required>
                                    <input type="text" value="{!! old('customShiftEndTime') !!}" class="form-control" id="timeOfCall" name="customShiftEndTime" placeholder="End Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('customShiftEndTime'))
                                <div id="customShiftEndTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('customShiftEndTime') !!}</div>
                                @endif
                            </div>


                        </div> 
                     </div> 


<!--                     <div class="form-group row{!! $errors->has('offerAlgorithmID') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="offerAlgorithmID">
                                Offer Algorithm
                                <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="offerAlgorithmID" name="offerAlgorithmID" style="width: 100%;" data-placeholder="Choose Offer Algorithm..">
                                        <option value=""></option>
                                         @foreach($algorithms as $algorithm)
                                        <option @if(old('offerAlgorithmID') == $algorithm->id) selected @endif value="{!! $algorithm->id !!}">{!! $algorithm->name." (".ucfirst($algorithm->type).")" !!}</option>
                                        @endforeach
                                    </select>
                                    

                                     @if ($errors->has('offerAlgorithmID'))
                                    <div id="offerAlgorithmID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('offerAlgorithmID') !!}</div>
                                    @endif
                                </div>
                    </div>    -->


                  <div class="form-group row{!! $errors->has('staffingCloseTime') ? ' is-invalid' : '' !!}">
                        <label class="col-lg-4 col-form-label" for="staffingCloseTime">
                            How long into the requested shift, would you like to have the offer remain open? 
                            <span class="text-danger">*</span></label>
                        <div class="col-md-4">

                            <select class="js-select2 form-control" id="staffingCloseTime" name="staffingCloseTime" data-placeholder="How long staffing request should be open?">
                                <option value="30">30 Minutes</option>
                                <option value="60">1 Hour</option>
                                <option value="90">1 Hour 30 Minutes</option>
                                <option value="120">2 Hours</option>
                                <option value="150">2 Hours 30 Minutes</option>
                                <option value="180">3 Hours</option>

                            </select>


                             @if ($errors->has('staffingCloseTime'))
                            <div id="staffingCloseTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('staffingCloseTime') !!}</div>
                            @endif
                        </div>
                </div>      

                                
                     
                    <div class="form-group row{!! $errors->has('notes') ? ' is-invalid' : '' !!}">
                        <label class="col-lg-4 col-form-label" for="notes">Notes </label>
                        <div class="col-lg-8">
                            <textarea class="form-control" id="notes" name="notes" placeholder="Custom notes for candidates.."></textarea>
                         @if ($errors->has('notes'))
                        <div id="notes-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('notes') !!}</div>
                        @endif
                        </div>
                    </div>            
                                
                                
                       
                          
                          <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    
                                    <button type="submit" class="btn btn-alt-primary">Submit</button>
                                </div>
                            </div>  
                            
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>

<!--Background Loader-->  
    <div id="loadingDiv" style="display: none;">
        <div style="margin-left: 45%;
    margin-top: 25%;">
            <h3 style="color: #fff;">Please wait
            <img src="{!! url('/assets/images/loading.gif') !!}"  />
            </h3>
        </div>
    </div>
    <style type="text/css">
        #loadingDiv{
  position:fixed;
  top:0px;
  right:0px;
  width:100%;
  height:100%;
  background-color:#666;
  background-image:url('ajax-loader.gif');
  background-repeat:no-repeat;
  background-position:center;
  z-index:10000000;
  opacity: 0.4;
  filter: alpha(opacity=40); /* For IE8 and earlier */
}
        </style>
    <!--Background Loader-->


@if(Session::has('success'))
<script type="text/javascript" >
    
    var cronRequestUrl = "{!! url(Config('constants.urlVar.CronHandler')) !!}";
    setTimeout("performCronRequest()", 1000);
    function performCronRequest(){
        $.ajax({
            url:cronRequestUrl,
            type:'GET',
            success:function(result){


            },error:function(er){

            }

        });
    }
    </script>
@endif
<script type="text/javascript" >
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var requestUrl = "{!! url(Config('constants.urlVar.ajaxChangeRequestForAddNewRequestForm')) !!}";


function performAction(){
    $('#loadingDiv').css('display', 'block');
    return true;
}

</script>
@endsection