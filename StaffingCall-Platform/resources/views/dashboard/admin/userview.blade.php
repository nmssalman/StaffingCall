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

<div style="float: right;margin-top: -2%;"><strong style="color:#f00;">Switch to :</strong>

    <button title="You are logged-in as user" style="cursor: pointer;margin-top: 4%;" type="button" class="btn btn-sm btn-success mb-10">User</button>
    <button onclick="changeAdminView();" title="Switch to admin" style="cursor: pointer;margin-top: 4%;" type="button" class="btn btn-sm btn-secondary mb-10">Admin</button>

</div>
<div style="margin-bottom: 16px;">
    
    
    
  <label class="css-control css-control-primary css-radio" onclick="changeView();">
    <input value="0" type="radio" class="css-control-input"  id="toggleView" name="toggleView">
    <span class="css-control-indicator"></span> 
    <span class=""><strong>Calendar View</strong></span>
</label>
<label class="css-control css-control-primary css-radio">
    <input  checked="checked" type="radio" class="css-control-input">
    <span class="css-control-indicator"></span>
    <span class=""><strong>List View </strong></span>
</label>    
    
    
</div>

       
<!--        Active Posts-->
<h2 class="content-heading">Active Requests - <small><span class="badge badge-success">LIVE</span></small></h2>
        <div class="row">
            
            @if($requestPosts)
            
            @foreach($requestPosts as $requestPost)
            <div class="col-lg-12">
                <!-- Simple Rating -->
                <div class="block" style="border: 1px solid lightgray;">
                    <div class="block-header block-header-default" style="background-color: #343a40;
    text-align: center;
    color: #fff;">
                        <h3 class="block-title" style="font-weight: bold;font-size: 22px;color: #fff;">
                            
                            {!! date("l, F d, Y",strtotime($requestPost->staffingStartDate)) !!}
                            
                            <small style="color: #fff;"><br />Owner: {!! $requestPost->staffOwner !!}</small>
                        
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                    
                        <p><strong>Description -</strong> {!! $requestPost->notes !!}</p>
                        <p><strong>Type of staff needed -</strong>
                            {!! myHelper::getRequiredSkills($requestPost->requiredStaffCategoryID) !!}
                              </p>
                       
                        <p><strong>Shift Time -</strong> 
                        
                                @if($requestPost->shiftType == 1)
                           {!! date("g:i A",strtotime($requestPost->customShiftStartTime)) !!}
                           -
                           {!! date("g:i A",strtotime($requestPost->customShiftEndTime)) !!}
                           @else
                           {!! date("g:i A",strtotime($requestPost->startTime)) !!}
                           -
                           {!! date("g:i A",strtotime($requestPost->endTime)) !!}
                           @endif
                        
                        </p>
                        
                        @if($requestPost->confirmOfferID > 0)
                        
                        
                            @if($requestPost->confirmationOfferStatus == 1)
                            
                            
                            <p><strong>Your Shift -</strong> 
                                @if($requestPost->responseType == 1)
                                    {!! date("g:i A",strtotime($requestPost->partialShiftStartTime)) !!}
                                    -
                                    {!! date("g:i A",strtotime($requestPost->partialShiftEndTime)) !!}
                                    
                                    (Partial Shift)
                                    
                                @else
                                    @if($requestPost->shiftType == 1)
                                        {!! date("g:i A",strtotime($requestPost->customShiftStartTime)) !!}
                                        -
                                        {!! date("g:i A",strtotime($requestPost->customShiftEndTime)) !!}
                                    @else
                                        {!! date("g:i A",strtotime($requestPost->startTime)) !!}
                                        -
                                        {!! date("g:i A",strtotime($requestPost->endTime)) !!}
                                    
                                    (Full Shift)
                                    
                                    @endif
                                @endif    
                            </p>
                        
                            
                            
                            <p style="color: #008000;"><i class="fa fa-check"></i> 
                                You are expected to come on Scheduled time.</p>
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
<script type="text/javascript" >
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var dataUrl = "{!! url(Config('constants.urlVar.changeAdminView')) !!}";
function changeAdminView(){
   
    var defaultView = '1';//Change to Admin view

    
    $.ajax({ 
            url: dataUrl,  
            type: "POST",
            data: {_token: CSRF_TOKEN,defaultView:defaultView}  ,
            success: function(response){ 
               window.location = "{!! url(Config('constants.urlVar.home')) !!}";
            }
        });
}


    
    function changeView(){
        location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
    }


</script>
<!-- container -->

@endsection
