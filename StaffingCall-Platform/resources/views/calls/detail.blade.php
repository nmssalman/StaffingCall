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

        
<h2 class="content-heading">
Details
    
</h2>

        <div class="block" style="border: 2px solid lightgray;">
            <div class="block-header block-header-default" style="background-color: #343a40;
    text-align: center;
    color: #fff;">
                <h3 class="block-title" style="font-weight: bold;font-size: 22px;color: #fff;">
                    {!! date("l M d, Y",strtotime($requestPost->staffingStartDate)) !!}
                           
                               
                               @if($requestPost->postingStatus == 2)
                               <span class="badge badge-danger">Disapproved</span>
                               @elseif($requestPost->postingStatus == 4)
                               <span class="badge badge-danger">Cancelled</span>
                                @elseif($requestPost->closingTime < date("Y-m-d H:i:s"))
                               <span class="badge badge-danger">Close</span>
                               @elseif($requestPost->postingStatus == 1 || $requestPost->postingStatus == 3)
                               <span class="badge badge-success">Open</span>
                               @elseif($requestPost->postingStatus == 0)
                               <span class="badge badge-danger">Pending</span>
                               @endif
                             <small style="color: #fff;"><br />Owner: {!! $requestPost->staffOwner !!}  
                           </small></h3>
            </div>
            <div class="block-content block-content-full">
                
                <div class="row items-push text-center">
                            
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! count($respondedPeopleLists) !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Number of Responses</strong>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $respondedFullShiftPeopleCount !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Full Shift</strong>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $respondedPartialShiftPeopleCount !!}</h2></div>
                            <strong style="color: #2e3238;">Partial Shift
                            </strong>
                        </div>
                        
                </div>
                
                <p><strong>Business Group -</strong> 
                    {!! $requestPost->groupName." (".$requestPost->groupCode.")" !!}</p>
                <p><strong>Business Unit -</strong> {!! $requestPost->unitName !!}</p>
                <p><strong>Number of staff needed -</strong> {!! $requestPost->numberOfOffers !!}</p>
                
                
                <p><strong>Reason for requesting this request -</strong> {!! $requestPost->requestReason !!}</p>
                
                 @if($requestPost->requestReasonID == 1)
                <p><strong>Who is the staff with last minute vacancy? -</strong>{!! $requestPost->lastMinuteStaff !!}</p>
                <p><strong>What time was the call made by the staff? -</strong>
                    {!! date("g:i A",strtotime($requestPost->timeOfCallMade)) !!} </p>
                <p><strong>What was the reason given for the last minute vacancy? -</strong> 
                    {!! $requestPost->vacancyReason !!}</p>
                @endif
                <p><strong>What type of staffing is required? -</strong> 
                    {!! myHelper::getRequiredSkills($requestPost->requiredStaffCategoryID) !!}</p>
                
                
                <p><strong>Shift staff needed -</strong>
                    <span class="badge badge-primary">
                            @if($requestPost->shiftType == 1)
                                {!! date("g:i A",strtotime($requestPost->customShiftStartTime)) !!} -
                                {!! date("g:i A",strtotime($requestPost->customShiftEndTime)) !!}
                           @else
                                {!! date("g:i A",strtotime($requestPost->startTime)) !!} -
                                {!! date("g:i A",strtotime($requestPost->endTime)) !!}
                           @endif
                    </span>       
                </p>
                
                <p><strong>How long into the requested shift, would you like to have the offer remain open? -</strong>
                    <span class="badge badge-danger">
                            {!! $requestPost->closingTime?
                            date("M j, Y g:i A",strtotime($requestPost->closingTime)):
                            date("M j, Y g:i A",strtotime($requestPost->staffingStartDate)) !!}
                    </span>       
                </p>
                
                
                
                <p><strong>Offer algorithm -</strong> {!! $requestPost->algorithmName?$requestPost->algorithmName:'Open' !!}</p>
                
                
                <p><strong>Notes -</strong> {!! $requestPost->notes !!}</p>
                
                
                @if($requestPost->postingStatus == 4)
                <p><strong>Reason for cancelling this request -</strong> {!! $requestPost->cancelReason !!}</p>
                @endif
                
                @if($requestPost->postingStatus == 4)
                <p><strong>Cancelled By -</strong> {!! $requestPost->cancelledByUserName !!}</p>
                @endif
                
                @if($requestPost->postingStatus == 2)
                <p><strong>Reason for cancelling this request -</strong> Disapproved</p>
                @endif
                
                @if($requestPost->postingStatus == 2)
                <p><strong>Disapproved By -</strong> {!! $requestPost->cancelledByUserName !!}</p>
                @endif
                
                
               
                
                
                @if($requestPost->staffingStartDate >= date("Y-m-d"))
                    @if((Auth::user()->role == '2' || Auth::user()->role == '3'))
                    <p>
                       @if($requestPost->postingStatus == '0' && (Auth::user()->role == '2' || Auth::user()->role == '3'))
                        <button onclick="approveRequest('{!! $requestPost->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-success mb-10">
                            Approve
                        </button>
                        <button onclick="disApproveRequest('{!! $requestPost->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-danger mb-10">
                            Disapprove
                        </button>
                       @endif 

                       @if($requestPost->postingStatus != '4' && $requestPost->postingStatus != '2')
                       <button onclick="closeRequest('{!! $requestPost->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-danger mb-10">
                            Cancel Request
                        </button>
                       @endif

                    </p>
                    @endif
                @endif
            </div>
        </div>
        
<!--        People Accepted-->

<div class="block-header block-header-default">
            <h3 class="block-title">People Responded <small>Full/Partial</small></h3>
        </div>
@if(count($respondedUsers['data']))
    @foreach($respondedUsers['data'] as $rows)
<div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">{!! $rows['name'] !!}</h3>
        </div>
        
    
    <div class="row">
            <!-- Row #3 -->
            @if(count($rows['data']) > 0)
            @foreach($rows['data'] as $respondedUser)
            <div class="col-md-12 col-xl-12">
                <a class="block text-center" href="javascript:void(0)">
                    <div class="block-content block-content-full bg-info" style="background-color: #51b6e9!important;">
                        <img class="img-avatar img-avatar-thumb" src="{!! $respondedUser['profilePic'] !!}" alt="">
                    </div>
                    <div class="block-content block-content-full">
                        <div class="font-w600 mb-5">{!! $respondedUser['name'] !!}</div>
                        <div class="font-size-sm text-muted">
                            <strong>Skills : </strong>{!! $respondedUser['skills'] !!}
                        </div>
                        <div class="font-size-sm text-muted">
                            <strong>Shift Time : </strong>
                            {!! $respondedUser['shiftTime'] !!}
                            @if($respondedUser['responseType'] == 1)
                                (Full)
                            @elseif($respondedUser['responseType'] == 2)
                                (Partial)
                            @elseif($respondedUser['responseType'] == 3)
                                (Declined)
                            @endif
                        </div>
                        <div class="font-size-sm text-muted">
                            <strong>Overtime Shift : </strong>
                            {!! $respondedUser['overtimeStatus']?"Yes":"No" !!}
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm bg-body-light">
                        
                @if($requestPost->postingStatus == '2' || 
                        $requestPost->postingStatus == '4' || $requestPost->closingTime < date("Y-m-d H:i:s"))
                    
                        @if($respondedUser['offerStatus'] == '1')
                        <span style="color:#008000;">
                            <i class="fa fa-check"></i>Offer is in progress</span>
                            @elseif($respondedUser['offerStatus'] == '3')
                        <span style="color:#f00;">Offer Declined</span>
                        @elseif($respondedUser['offerStatus'] == '2')
                        <span style="color:#008000;"><i class="fa fa-check"></i>
                            Offer Accepted</span>
                        @elseif($respondedUser['offerStatus'] == '4')
                        <span style="color:#f00;">Waitlist option available.</span>
                        @elseif($respondedUser['offerStatus'] == '6')
                        <span style="color:#f00;">Waitlist Declined.</span>
                        
                        @else
                            @if($respondedUser['offerStatus'] == '5')
                            <span style="color:#008000;">User is on waitlist </span><br />
                            @endif
                            @if(Auth::user()->role !=1)   
                            <button onclick="javascript:alert('Posting is closed, You can not perform any action.')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">Make Offer</button>
                            @endif
                        @endif
                @elseif($respondedUser['offerStatus'] == '1')
                    <span style="color:#008000;">
                        <i class="fa fa-check"></i>Offer is in progress</span>
                @elseif($respondedUser['offerStatus'] == '2')
                            
                    <span style="color:#008000;"><i class="fa fa-check"></i>
                            Offer Accepted</span>
                @elseif($respondedUser['offerStatus'] == '3')
                    <span style="color:#008000;">
                        <i class="fa fa-check"></i>Offer Declined</span>
                @elseif($respondedUser['offerStatus'] == '4')
                <span style="color:#f00;">Waitlist option available.</span>
                @elseif($respondedUser['offerStatus'] == '6')
                <span style="color:#f00;">Waitlist Declined.</span>
                        
                @else
                    
                    @if($respondedUser['offerStatus'] == '5')
                    <span style="color:#008000;">User is on waitlist </span><br />
                    @endif
                    
                    @if(Auth::user()->role !=1)
                    <button onclick="makeOffer('{!! $respondedUser['id']!!}', '{!! $requestPost->postID !!}','{!! $respondedUser['name'] !!}', '{!! $respondedUser['shiftTime'] !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">Make Offer</button>
                    @endif
                    
                @endif
                     
                    </div>
                </a>
            </div>
            @endforeach
            @endif
            <!-- END Row #3 -->
    </div>
    
</div>
    @endforeach
@endif




<!--        People Accepted-->

        
</div>
<!-- container -->

<!--CANCEL REQUEST REASON POPUP-->
<a id="cancelReason" style="visibility: hidden;" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" ></a>
<!-- Change Password Alert Popup -->  
<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content" style="width:550px;margin-top: 7%;">

            <div class="block block-themed block-transparent mb-0" style="max-height: 500px;overflow: auto;">
                <div class="block-header bg-primary-dark" style="height: 50px;">

                    <div class="block-options" style="margin-left: 8%;margin-top: 4%;">
                        <h5 style="color:#fff;">Please give reason to cancel this staffing request.</h5>

                    </div>
                </div>
                <div class="block-content">
                    <textarea style="width: 500px;height: 150px;" name="reasonForCancel" id="reasonForCancel"></textarea>
                    <span id="reasonErr" style="color: #f00;"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button style="cursor: pointer;" type="button" class="btn btn-alt-info" onclick="confirmAndCancelRequest('{!! $requestPost->postID !!}')">Confirm</button>
                 <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Cancel</button>
             </div>

        </div>
    </div>
</div>
<!--CANCEL REQUEST REASON POPUP-->


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


<script>
    
    var dataUrl = "{!! url(Config('constants.urlVar.ajaxRespondedPeopleList').$requestPost->postID) !!}";
    function approveRequest(requestID){
      var r = confirm("Are you sure you want to approve this staffing request.");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.approvePost')) !!}/'+requestID+'/1/detail';
            
           location.href = requestUrl;
        } else {
            return false; 
        }  
    }
    
    
    function disApproveRequest(requestID){
       
      var r = confirm("Are you sure you want to disapprove this staffing request.");
        if (r == true) {
           var requestUrl = '{!! url(Config('constants.urlVar.approvePost')) !!}/'+requestID+'/4/detail'; 
           
           location.href = requestUrl;
        } else {
           return false; 
        }   
        
    }
    
    
    function confirmAndCancelRequest(requestID){
        var reasonForCancel = $('#reasonForCancel').val();
        if(reasonForCancel == ''){
            $('#reasonForCancel').css('border', '1px solid #f00');
            $('#reasonErr').html('*Field is required.');
        }else if(reasonForCancel.length > 200){
            $('#reasonForCancel').css('border', '1px solid #f00');
            $('#reasonErr').html('*Maximum text limit is 200');
        }else if(reasonForCancel.length < 10){
            $('#reasonForCancel').css('border', '1px solid #f00');
            $('#reasonErr').html('*Minimum text limit is 10');
        }else{
                $('#reasonForCancel').css('border', '');
                $('#reasonErr').html('');
                var requestUrl = '{!! url(Config('constants.urlVar.approvePost')) !!}/'+requestID+'/5/detail/'+reasonForCancel; 
                location.href = requestUrl;
        }
    }
    
    
    function closeRequest(requestID){
       
      var r = confirm("Are you sure you want to cancel this staffing request?");
        if (r == true) {
            
            $('#cancelReason').click();
          
        } else {
           return false; 
        }   
        
    }
    
    
    function makeOffer(toUser,requestID,userName,shiftTime){
        
        
        
        var r = confirm("Offer shift to "+userName+" ? "+shiftTime);
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.makeOfferToUser')) !!}';
            $('#loadingDiv').css('display', 'block');
            $.ajax({
                    url:requestUrl,
                    type:'GET',
                    data:{'toUserID':toUser,'requestID':requestID },
                    success:function(result){
                        //console.log(result);
                        $('#loadingDiv').css('display', 'none');
                        location.href = '{!! url(Config('constants.urlVar.staffingPostDetail')) !!}/'+requestID;
                       
                    },error:function(er){
                        $('#loadingDiv').css('display', 'none');
                        location.href = '{!! url(Config('constants.urlVar.staffingPostDetail')) !!}/'+requestID;
                    }

            });
            
        } else {
            return false; 
        }   
    }
    
    
    
    </script>
    
    
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
    
    setTimeout("reloadPageAfterTen()", 30000);
    
    function reloadPageAfterTen(){
        location.reload();
    }
</script>
@endsection
