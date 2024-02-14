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
        
        <!--     <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Business Unit - <span class="badge badge-info">{!! $unitInfo->unitName !!}</span><small></small></h3>
            </div>
            
        </div>-->
           
        
<!--        Shift History Posts-->
<h2 class="content-heading">Cancelled Staffing Requests 
    <!--<small><span class="badge badge-danger">Staffing History</span></small>-->
</h2>
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
                    
                    
                    <div class="block-content block-content-full" style="padding-bottom: 0;">
                    
                        <p><strong>Posting Description -</strong> {!! $requestPost->notes !!}</p>
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
                        
<!--                            <p style="color: #008000;"><i class="fa fa-check"></i> 
                                You are expected to come on Scheduled time.</p>-->
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

@endsection
