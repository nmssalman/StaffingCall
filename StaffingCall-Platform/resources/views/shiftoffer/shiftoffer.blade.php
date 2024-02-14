@extends('layouts.app')
@section('content')
<div class="content">
   

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

        
<h2 class="content-heading">Business Group - {!! $groupInfo->groupName !!} ({!! $groupInfo->groupCode !!})

    
</h2>
       
<!--        Active Posts-->
<h2 class="content-heading">Shift Offers ({!! count($offers) !!})<small>
        <!--<span class="badge badge-success">Active</span>-->
    </small></h2>






<div id="searchBox">
    <form onsubmit="return searchValidation()" action="{!! url(Config('constants.urlVar.shiftOffer')) !!}" method="get">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
        <div class="form-group row">
            <label class="col-lg-2 col-form-label" for="search">Filter By Date:</label>
            
            <div class="col-lg-3">
               <div class="input-group date" id="searchingStartDatePicker" required>
                   <input type="text" value="{!! $searchStartDateValue !!}" class="form-control" id="searchingStartDate" name="fromDate" placeholder="Start Date">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                 </span>
                </div>
            </div>
            
            <div class="col-lg-3">
               <div class="input-group date" id="searchingEndDatePicker" required>
                <input type="text" value="{!! $searchEndDateValue !!}" class="form-control" id="searchingEndDate" name="toDate" placeholder="End Date">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                 </span>
                </div>
            </div>
            
            <button style="cursor: pointer;" type="submit" class="btn btn-success mb-10">Filter</button>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOffer')) !!}'" style="cursor: pointer;" type="button" class="btn btn-info mb-10">Reset</button>
        </div>
    </form>  
    
    
</div>

        <div class="row">
            
            @if($offers)
            
            @foreach($offers as $offer)
            <div class="col-lg-12">
                <!-- Simple Rating -->
                
                <div class="block" style="border: 1px solid lightgray;">
                    <div class="block-header block-header-default" style="background-color: #343a40;
    text-align: center;
    color: #fff;">
                        <h3 class="block-title" style="font-weight: bold;font-size: 22px;color: #fff;">
                            
                            {!! date("l, F d, Y",strtotime($offer->staffingStartDate)) !!}
                            
                            <small style="color: #fff;"><br />Owner: {!! $offer->staffOwner !!}</small>
                        
                            
                            @php 
                            $startDateOfShift = date("l M d, Y",strtotime($offer->staffingStartDate));
                            @endphp
                           @if($offer->shiftType == 1)
                            @php  
                            $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                            @endphp
                           @else
                            @php  
                            $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                            @endphp 
                           @endif
                           @php
                           $shiftTiming = date("l M d, Y",strtotime($offer->staffingStartDate))." - ".$shiftTimes;
                           @endphp
                            
                            
                        </h3>
                    </div>
                    
                    <div class="block-content block-content-full" style="padding-bottom: 0;">
                        @if($offer->notes)
                        <p><strong>Description -</strong> {!! $offer->notes !!}</p>
                        @endif
                        <p><strong>Request Reason -</strong> {!! $offer->requestReason !!}</p>
                        <p><strong>Type of staff needed -</strong>
                            {!! myHelper::getRequiredSkills($offer->requiredStaffCategoryID) !!}
                        </p>
                       
                        <p><strong>Shift Time -</strong> 
                        
                                @if($offer->shiftType == 1)
                           {!! date("g:i A",strtotime($offer->customShiftStartTime)) !!}
                           -
                           {!! date("g:i A",strtotime($offer->customShiftEndTime)) !!}
                           @else
                           {!! date("g:i A",strtotime($offer->startTime)) !!}
                           -
                           {!! date("g:i A",strtotime($offer->endTime)) !!}
                           @endif
                        
                        </p>
                        
                         @if($offer->confirmOfferID > 0)
                        
                           @if($offer->respondStatus == 4)
                           
                           
                           
                            <p style="color: #008000;"><i class="fa fa-check">
                                </i>
                                @if($offer->responseType == 0)
                                    Full Shift
                                @endif    
                                @if($offer->responseType == 1)
                                    Partial Shift
                                @endif    
                            
                                @if($offer->responseType == 0)
                                {!! $startDateOfShift !!} - 
                                    @if($offer->shiftType == 1)
                                    {!! date("g:i A",strtotime($offer->customShiftStartTime)) !!}
                                    -
                                    {!! date("g:i A",strtotime($offer->customShiftEndTime)) !!}
                                    @else
                                    {!! date("g:i A",strtotime($offer->startTime)) !!}
                                    -
                                    {!! date("g:i A",strtotime($offer->endTime)) !!}
                                    @endif
                                
                                
                                @elseif($offer->responseType == 1)
                                {!! $startDateOfShift !!} - 
                                
                                {!! date("g:i A",strtotime($offer->partialShiftStartTime)) !!}
                                    -
                                {!! date("g:i A",strtotime($offer->partialShiftEndTime)) !!}
                                @endif
                                
                            
                            </p>
                            
                            
                            <div class="form-group row">
                                        <label class="col-lg-12 col-form-label" for="confirmation">
                                            You have been selected to take this shift offer, please confirm :
                                        </label>
                            </div>
                         
                            <p id="successmsg_{!! $offer->postID !!}">
                                
                                <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOfferDetail').$offer->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-info mb-10">
                                           &nbsp;View Detail
                                        </button>
                                
<!--                                <button id="confirmBtn{!! $offer->postID !!}" onclick="confirmOffer('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                    Confirm
                                </button>
                                <button id="declineOfferBtn{!! $offer->postID !!}" onclick="declineOffer('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-danger mb-10">
                                    Decline
                                </button>
                                <img style="visibility: hidden;vertical-align: text-top;" id="progressBarOffer{!! $offer->postID !!}" src="{!! url('/assets/images/loading.gif') !!}"  />-->
                            </p>
                        @elseif($offer->respondStatus == 5)
                        <p style="color: #008000;"><i class="fa fa-check"></i> 
                            Offer Accepted. You are expected to come on Scheduled time.</p>
                        @elseif($offer->respondStatus == 6)
                        <p style="color: #f00;"><i class="fa fa-check"></i> Offer Declined</p>
                        @elseif($offer->respondStatus == 7)
                        <p style="color: #51b6e9;">
                            Would you like to remain AVAILABLE as per your response?</p>
                        
                        <p id="successmsgwaitlist_{!! $offer->postID !!}">
                                
                                <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOfferDetail').$offer->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                           &nbsp;Send Response
                                        </button>
                                
<!--                                <button id="waitlistconfirmBtn{!! $offer->postID !!}" onclick="confirmOfferWaitlist('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                    Yes
                                </button>
                                <button id="waitlistdeclineOfferBtn{!! $offer->postID !!}" onclick="declineOfferWaitlist('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-danger mb-10">
                                    No
                                </button>
                                <img style="visibility: hidden;vertical-align: text-top;" id="waitlistprogressBarOffer{!! $offer->postID !!}" src="{!! url('/assets/images/loading.gif') !!}"  />
                            -->
                        </p>
                            
                        @elseif($offer->respondStatus == 8)
                        <p style="color: #008000;"> You are on waitlist</p>
                            
                        @elseif($offer->respondStatus == 9)
                        <p style="color: #f00;"> Waitlist declined</p>
                        
                        
                        @endif
                        
                        @else
                        @php $displayStyle = 'style="display:block;"'; @endphp
                        
                                @if($offer->respondStatus == 7)
                            <p style="color: #51b6e9;">
                                Would you like to remain AVAILABLE as per your response?</p>

                            <p id="successmsgwaitlist_{!! $offer->postID !!}">

                                <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOfferDetail').$offer->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                           &nbsp;Send Response
                                </button>

<!--                                    <button id="waitlistconfirmBtn{!! $offer->postID !!}" onclick="confirmOfferWaitlist('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                        Yes
                                    </button>
                                    <button id="waitlistdeclineOfferBtn{!! $offer->postID !!}" onclick="declineOfferWaitlist('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-danger mb-10">
                                        No
                                    </button>
                                    <img style="visibility: hidden;vertical-align: text-top;" id="waitlistprogressBarOffer{!! $offer->postID !!}" src="{!! url('/assets/images/loading.gif') !!}"  />-->
                                </p>
                            
                                @elseif($offer->respondStatus == 8)
                                <p style="color: #008000;"> You are on waitlist</p>

                                @elseif($offer->respondStatus == 9)
                                <p style="color: #f00;"> Waitlist declined</p>
                                @else
                                    @if($offer->userResponseID > 0)
                                        @if($offer->responseType == 0)
                                        <p style="color: #008000;"><i class="fa fa-check"></i>Accepted Full Shift
                                       @endif
                                        @if($offer->responseType == 1)
                                        <p style="color: #008000;"><i class="fa fa-check"></i>Accepted Partial Shift
                                       @endif
                                        @if($offer->responseType == 2)
                                        <p style="color: #f00;">Declined.
                                       @endif

<!--                                       <button onclick="modifyShift('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-secondary mb-10">
                                           &nbsp;Edit
                                        </button>-->

                                        <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOfferDetail').$offer->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-secondary mb-10">
                                           &nbsp;Edit Response
                                        </button>

                                        </p>

                                        @php $displayStyle = 'style="display:none;"'; @endphp

                                    @endif
                        
                                
                        
                        
                        
                        <div id="userResponseData_{!! $offer->postID !!}" {!! $displayStyle !!}>    
                        <p>
<!--                            <button id="fullBtn{!! $offer->postID !!}" onclick="userFullShiftResponse('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-success mb-10">
                                Accept Full
                            </button>
                            <button id="partialBtn{!! $offer->postID !!}" onclick="userPartialShiftResponse('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                Accept Partial
                            </button>
                            <button id="declineBtn{!! $offer->postID !!}" onclick="userDeclineResponse('{!! $offer->postID !!}','{!! $shiftTiming !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-danger mb-10">
                                Decline
                            </button>-->

                            <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.shiftOfferDetail').$offer->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-success mb-10">
                                           &nbsp;Send Response
                                        </button>
                            
                            
                        </p>
                        
                        <form name="shif-offer-form-{!! $offer->postID !!}" id="shif-offer-form-{!! $offer->postID !!}"
                              class="js-shift-offer-validation-bootstrap" 
                              action="{!! url(Config('constants.urlVar.acceptShiftOffer')) !!}" 
                              method="post" >    
                        
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                            
                            <div id="overtimeblock_{!! $offer->postID !!}" style="display: none;">

                              <div class="form-group row">
                                <label class="css-control css-control-info css-checkbox"> 
                                    <div class="col-md-12">
                                        
                                   
                                     <div class="form-group row">
                                        <label class="col-lg-12 col-form-label" for="partialTime">
                                            Will this be an overtime shift ?
                                        </label>
                                    </div>
                                        
                                    <label class="css-control css-control-success css-radio" onclick="checkUserType('0')">
                                        <input @if($offer->overTime == 1) checked="checked" @endif type="radio" class="css-control-input" id="overTime_{!! $offer->postID !!}" name="overTime" value="1">
                                        <span class="css-control-indicator"></span> 
                                        <span class="badge badge-success">Yes</span>
                                    </label> 
                                    
                                    <label class="css-control css-control-primary css-radio" onclick="checkUserType('0')">
                                        <input @if($offer->overTime == 0) checked="checked" @elseif(!isset($offer->overTime)) checked="checked" @endif type="radio" class="css-control-input" id="overTime_{!! $offer->postID !!}" name="overTime" value="0">
                                        <span class="css-control-indicator"></span> 
                                        <span class="badge badge-primary">No</span>
                                    </label> 
                                    
                                    </div>

                                </label>
                              </div>

                            </div>

                            <div id="partialShiftBlock_{!! $offer->postID !!}" style="display: none;">

                                <div class="form-group row">
                                    <label class="col-lg-12 col-form-label" for="partialTime">
                                        What time you are available? 
                                    </label>
                                </div>
                                
                                @php
                                    $partialShiftStartTimeOfUser = '';
                                    $partialShiftEndTimeOfUser = '';
                                @endphp
                                
                            @if($offer->partialShiftTimeID > 0)  
                                @php                                
                                    $partialShiftsTimings = myHelper::getPartialShiftsTimeOfUser($offer->partialShiftTimeID, $offer->postID);
                                    $partialShiftStartTimeOfUser = date("g:i A", strtotime($partialShiftsTimings->partialShiftStartTime));
                                    $partialShiftEndTimeOfUser = date("g:i A", strtotime($partialShiftsTimings->partialShiftEndTime));
                                @endphp
                            @endif   
                                
                                <div class="form-group row{!! $errors->has('partialShiftStartTime') ? ' is-invalid' : '' !!}">
                            
                            <div class="col-md-4">
                                Start Time
                                <div class="input-group date" id="partialtimepicker2" required>
                                    <input required type="text" value="{!! $partialShiftStartTimeOfUser !!}" class="form-control" id="partialShiftStartTime_{!! $offer->postID !!}" name="partialShiftStartTime" placeholder="Start Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('partialShiftStartTime'))
                                    <div id="partialShiftStartTime-error" class="invalid-feedback animated fadeInDown">
                                        {!! $errors->first('partialShiftStartTime') !!}
                                    </div>
                                @endif
                                
                            </div>

                            <div class="col-md-4">
                                    End Time
                                <div class="input-group date" id="partialtimepicker3" required>
                                    <input required type="text" value="{!! $partialShiftEndTimeOfUser !!}" class="form-control" id="partialShiftEndTime_{!! $offer->postID !!}" name="partialShiftEndTime" placeholder="End Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('partialShiftEndTime'))
                                <div id="partialShiftEndTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('partialShiftEndTime') !!}</div>
                                @endif
                            </div>


                        </div> 
                                

                              
                            </div>

                            <div id="submitBtn_{!! $offer->postID !!}" style="display: none;">
                                <button id="submission{!! $offer->postID !!}" onclick="submitRequest('{!! $offer->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-secondary mb-10">
                                    Submit <img style="visibility: hidden;" id="progressBar{!! $offer->postID !!}" src="{!! url('/assets/images/loading.gif') !!}"  />
                       </button>
                            <input type="hidden" name="respondType" id="respondType_{!! $offer->postID !!}"  value="0"/>     
                        
                            <input type="hidden" name="requestID" value="{!! $offer->postID !!}"/>    
                            <input type="hidden" name="partialShiftTimeID" value="{!! $offer->partialShiftTimeID?$offer->partialShiftTimeID:0 !!}"/>   
                        </div>
                            
                        </form>
                        
                        </div>
                        
                        @endif
                        @endif
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach
            
            @else
            
            @endif
            
            
        </div>
<!--        Active Posts-->

        
</div>

<!-- container -->
<script type="text/javascript">
    
    function modifyShift(requestID){        
        $('#userResponseData_'+requestID).fadeIn(500);
    }
    
    
    function userFullShiftResponse(requestID){
        
       $('#partialShiftBlock_'+requestID).fadeOut(500);
        
        $('#overtimeblock_'+requestID).fadeIn(500);
        $('#submitBtn_'+requestID).fadeIn(500);
        
         $('#respondType_'+requestID).val('0');//Full Respond
       
    }
    
    function userPartialShiftResponse(requestID){       
       $('#overtimeblock_'+requestID).fadeIn(500); 
       $('#partialShiftBlock_'+requestID).fadeIn(500); 
       $('#submitBtn_'+requestID).fadeIn(500); 
       
       $('#respondType_'+requestID).val('1');//Partial Respond
       
    }
    
    function userDeclineResponse(requestID,shiftTime){       
       $('#overtimeblock_'+requestID).fadeOut(500); 
       $('#partialShiftBlock_'+requestID).fadeOut(500); 
       $('#submitBtn_'+requestID).fadeOut(500); 
       
       //var r = confirm("Are you sure? Cancel this shift- "+shiftTime);
       var r = confirm("Are you sure to decline this offer?");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.declineShiftRequest')) !!}'; 
            
            $('#progressBar'+requestID).css('visibility', "visible");
            $('#submission'+requestID).attr('disabled', true);
            $('#fullBtn'+requestID).attr('disabled', true);
            $('#partialBtn'+requestID).attr('disabled', true);
            $('#declineBtn'+requestID).attr('disabled', true);
            
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'requestID':requestID},
                    success:function(result){
                     location.href = '{!! url(Config('constants.urlVar.shiftOfferDetail')) !!}/'+requestID;
                       //console.log(result);
                    },error:function(er){
                        //console.log(er.responseText);
                    }

            });          
           //location.href = requestUrl;
        } else {
            return false; 
        }   
       
    }
    
    function submitRequest(requestID){
       var r = confirm("Are you sure to send this response?");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.acceptShiftOffer')) !!}';
            
            
            $('#progressBar'+requestID).css('visibility', "visible");
            $('#submission'+requestID).attr('disabled', true);
            $('#fullBtn'+requestID).attr('disabled', true);
            $('#partialBtn'+requestID).attr('disabled', true);
            $('#declineBtn'+requestID).attr('disabled', true);
            
            $.ajax({
                    url:requestUrl,
                    type:'POST',
                    data:$('#shif-offer-form-'+requestID).serialize(),
                    success:function(result){                       
                        location.href = '{!! url(Config('constants.urlVar.shiftOfferDetail')) !!}/'+requestID;
                       //console.log(result);
                    },error:function(er){
                        console.log(er.responseText);
                    }

            });
            
        } else {
            return false; 
        }   
    }
    
    
    
    
    
    function confirmOffer(requestID){
        
       var r = confirm("Would you like to confirm acceptance of this offer?");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.confirmShiftOffer')) !!}';
            
            $('#progressBarOffer'+requestID).css('visibility', "visible");
            $('#declineOfferBtn'+requestID).attr('disabled', true);
            $('#confirmBtn'+requestID).attr('disabled', true);
            
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'requestID':requestID},
                    success:function(result){
                        //console.log(result);
                        $('#progressBarOffer'+requestID).css('visibility', "hidden");
                        $('#declineOfferBtn'+requestID).attr('disabled', false);
                        $('#confirmBtn'+requestID).attr('disabled', false);
                        $('#successmsg_'+requestID).html('<span style="color: #008000;">'+
                                    '<i class="fa fa-check"></i>'+result.msg+'</span>');
                        //location.href = '{!! url(Config('constants.urlVar.shiftOffer')) !!}';
                       //console.log(result);
                    },error:function(er){
                        //console.log(er.responseText);
                    }

            });
            
        } else {
            return false; 
        }   
    }
    
    
    
    function declineOffer(requestID){
        
       //var r = confirm("Would you like to decline this offer?");
       var r = confirm("Are you sure to decline this offer? You will not able to  change your decision in future.");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.declineShiftOffer')) !!}';
            $('#progressBarOffer'+requestID).css('visibility', "visible");
            $('#declineOfferBtn'+requestID).attr('disabled', true);
            $('#confirmBtn'+requestID).attr('disabled', true);
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'requestID':requestID},
                    success:function(result){
                        $('#progressBarOffer'+requestID).css('visibility', "hidden");
                        $('#declineOfferBtn'+requestID).attr('disabled', false);
                        $('#confirmBtn'+requestID).attr('disabled', false);
                        $('#successmsg_'+requestID).html('<span style="color: #f00;">'+
                                    '<i class="fa fa-check"></i>'+result.msg+'</span>');
                        //location.href = '{!! url(Config('constants.urlVar.shiftOffer')) !!}';
                       //console.log(result);
                    },error:function(er){
                        //console.log(er.responseText);
                    }

            });
            
        } else {
            return false; 
        }   
    }
    
    
    /* Waitlist User Confirmation/Decline */
    
    
    
    
    function confirmOfferWaitlist(requestID){
        
       var r = confirm("Would you like to remain AVAILABLE on waitlist?");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.acceptToBeOnWaitlist')) !!}';
            
            var waitListStatus = 1;//Want be on Waitlist.
            
            $('#waitlistprogressBarOffer'+requestID).css('visibility', "visible");
            $('#waitlistdeclineOfferBtn'+requestID).attr('disabled', true);
            $('#waitlistconfirmBtn'+requestID).attr('disabled', true);
            
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'requestID':requestID, 'waitListStatus':waitListStatus},
                    success:function(result){
                        //console.log(result);
                        $('#waitlistprogressBarOffer'+requestID).css('visibility', "hidden");
                        $('#waitlistdeclineOfferBtn'+requestID).attr('disabled', false);
                        $('#waitlistconfirmBtn'+requestID).attr('disabled', false);
                        $('#successmsgwaitlist_'+requestID).html('<span style="color: #008000;">'+
                                    '<i class="fa fa-check"></i>'+result.msg+'</span>');
                        //location.href = '{!! url(Config('constants.urlVar.shiftOffer')) !!}';
                       //console.log(result);
                    },error:function(er){
                        //console.log(er.responseText);
                    }

            });
            
        } else {
            return false; 
        }   
    }
    
    
    
    function declineOfferWaitlist(requestID){
        
       var r = confirm("Are you sure to decline your availability on waitlist?");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.acceptToBeOnWaitlist')) !!}';
            
            var waitListStatus = 2;//User declined to be on Waitlist.
            
            $('#waitlistprogressBarOffer'+requestID).css('visibility', "visible");
            $('#waitlistdeclineOfferBtn'+requestID).attr('disabled', true);
            $('#waitlistconfirmBtn'+requestID).attr('disabled', true);
            
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'requestID':requestID, 'waitListStatus':waitListStatus},
                    success:function(result){
                        //console.log(result);
                        $('#waitlistprogressBarOffer'+requestID).css('visibility', "hidden");
                        $('#waitlistdeclineOfferBtn'+requestID).attr('disabled', false);
                        $('#waitlistconfirmBtn'+requestID).attr('disabled', false);
                        $('#successmsgwaitlist_'+requestID).html('<span style="color: #008000;">'+
                                    '<i class="fa fa-check"></i>'+result.msg+'</span>');
                        //location.href = '{!! url(Config('constants.urlVar.shiftOffer')) !!}';
                       //console.log(result);
                    },error:function(er){
                        //console.log(er.responseText);
                    }

            });
            
        } else {
            return false; 
        }  
    }
    
    /* Waitlist User Confirmation/Decline */
    
    
    </script>

<script type="text/javascript" >
    
    setTimeout("reloadPageAfterTen()", 60000);
    
    function reloadPageAfterTen(){
        location.reload();
    }
    
    
  function searchValidation(){
    var flag = true;
    var searchStartDate = $('#searchingStartDate').val();
    var searchEndDate = $('#searchingEndDate').val();
      
        if(searchStartDate == ''){
            $('#searchingStartDate').css('border', '1px solid #f00');
            flag = false;
        }else if(searchEndDate == ''){
            $('#searchingEndDate').css('border', '1px solid #f00'); 
           flag = false;  
        }else{
            $('#searchingStartDate').css('border', '');
            $('#searchingEndDate').css('border', '');
        }
      
    if(flag){
       return true; 
    }else{
        return false;
    }
  }  
</script>
@endsection
