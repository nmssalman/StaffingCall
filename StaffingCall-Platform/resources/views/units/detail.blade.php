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

<div id="searchBox">
    <form onsubmit="return searchValidation()" action="{!! url(Config('constants.urlVar.businessUnitDetail').$unitInfo->id) !!}" method="get">
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
            <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.businessUnitDetail').$unitInfo->id) !!}'" style="cursor: pointer;" type="button" class="btn btn-info mb-10">Reset</button>
        </div>
    </form>    
</div>



@if(Auth::user()->role != 1)
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
@endif


        <div class="block" style="border: 2px solid lightgray;">
            <div class="block-header block-header-default" style="background-color: #4aa5f5;
    text-align: center;
    color: #fff;">
                <h3 class="block-title" style="font-weight: bold;font-size: 22px;color: #fff;">
                    Business Unit - {!! $unitInfo->unitName !!}
                    <small></small>
                </h3>
            </div>
            <div class="block-content block-content-full">
                
                <div class="row items-push text-center">
                            
                    <div class="col-6 col-md-6 col-xl-3">
                        @php 
                            $searchedUrl = '';
                            if($searchStartDateValue != '' && $searchEndDateValue != ''){
                               $searchedUrl = '?fromDate='.$searchStartDateValue.'&toDate='.$searchEndDateValue; 
                            }
                        
                        @endphp
                         <a href="{!! url(Config('constants.urlVar.businessUnitDetail').$unitInfo->id.$searchedUrl) !!}">
                            <div class="font-w600">

                            <h2 style="color: #42a5f5 !important;">{!! $openPostingCount !!}</h2>
                        </div>

                        <strong style="color: #2e3238;">Open Requests</strong>
                         </a>
                    </div>

                    <div class="col-6 col-md-6 col-xl-3">
                         <a href="{!! url(Config('constants.urlVar.businessUnitDetailPending').$unitInfo->id.$searchedUrl) !!}">
                           <div class="font-w600">
                            <h2 style="color: #42a5f5 !important;">{!! $pendingPostingCount !!}</h2>
                        </div>
                        <strong style="color: #2e3238;">Pending Requests</strong>
                         </a>
                    </div>

                    <div class="col-6 col-md-6 col-xl-3">
                         <a href="<?php if(Auth::user()->role == 1){ ?>#<?php }else{ ?>{!! url(Config('constants.urlVar.staffingHistory')) !!}<?php } ?>">
                            <div class="font-w600">
                            <h2 style="color: #42a5f5 !important;">{!! $pastPostingCount !!}</h2>
                        </div>
                        <strong style="color: #2e3238;">Past Requests</strong>
                         </a>
                    </div>
                    
                    <div class="col-6 col-md-6 col-xl-3">
                        <a href="<?php if(Auth::user()->role == 1){ ?>#<?php }else{ ?>{!! url(Config('constants.urlVar.staffProfileList')) !!}<?php } ?>">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $unitUsersCount !!}</h2></div>
                        <strong style="color: #2e3238;">Number of Staff
                        </strong>
                        </a>
                    </div>
                        
                </div>
                
                
            </div>
        </div>
        

     


<div class="block" style="border-radius:2px solid lightgray;">
                        <!--<ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs" role="tablist">-->
                        <ul class="nav nav-tabs nav-tabs-alt" >
                            <li class="nav-item">
                                <a class="nav-link active" href="{!! url(Config('constants.urlVar.businessUnitDetail').$unitInfo->id.$searchedUrl) !!}" >
                                    Open Staffing Requests
                                    <small></small></a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{!! url(Config('constants.urlVar.businessUnitDetailPending').$unitInfo->id.$searchedUrl) !!}" >
                                    Pending Staffing Requests
                                    <small></small></a>
                            </li>
                        </ul>
                        <div class="block-content block-content-full tab-content overflow-hidden">
                            <!-- Classic -->
                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel">

                               <!-- Active Requests --> 
                                
                                <div class="row">
            
            @if($requestPosts->count())
            
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
                           
                    <!-- Get Responde People-->
                     @php $respondedPeopleVar = myHelper::getCountOfRespondedPeople($requestPost->postID);  
                     @endphp

                    <!--Get Responde People-->
                        
                        <div class="row items-push text-center">
                            
                        <div class="col-6 col-md-6 col-xl-4">
                            <a href="{!! url(Config('constants.urlVar.staffingPostDetail').$requestPost->postID) !!}">
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['totalResponded'] !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Number of Responses</strong>
                            </a>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                            <a href="{!! url(Config('constants.urlVar.staffingPostDetail').$requestPost->postID) !!}">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['fullResponded'] !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Full Shift</strong>
                            </a>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <a href="{!! url(Config('constants.urlVar.staffingPostDetail').$requestPost->postID) !!}">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['partialResponded'] !!}</h2></div>
                            <strong style="color: #2e3238;">Partial Shift
                            </strong>
                            </a>
                        </div>
                        
                        </div>
                        
                    
                        <p><strong>Type of Staff: </strong>
                            {!! myHelper::getRequiredSkills($requestPost->requiredStaffCategoryID) !!}
                            </p>
                        @if($requestPost->notes)
                        <p><strong>Description: </strong> {!! $requestPost->notes !!}</p>
                     @endif
                        
                         <p>
                            <button onclick="location.href = '{!! url(Config('constants.urlVar.staffingPostDetail').$requestPost->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                View Detail
                            </button>
                        </p>
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach
            @else
            <div class="font-size-h3 font-w600 py-30 mb-20 text-center border-b">
                <span style="text-align: center;" class="text-primary font-w700">No Open Requests found</span> 
              </div>
            @endif
            
            
        </div>
                               
<!--          <div class="row">

            <div class="col-sm-12 col-md-5">
                @if($totalOpenRequestsCount > 0)
                <div class="dataTables_paginate paging_simple_numbers" id="datatable_paginate">
                    <ul class="pagination">
                        <li class="paginate_button page-item previous disabled" id="datatable_previous">
                            <a onclick="performPaging('0')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="0" tabindex="0" class="page-link">
                                Previous
                            </a>
                        </li>

                        <?php $totalData = $totalOpenRequestsCount;
                             if($totalData % 2 > 0){
                                  $totalPage = (int)($totalData / 2) + 1;
                             }else{
                                 $totalPage = (int)($totalData / 2);
                             }
                        ?>
                        @for($pg = 1;$pg <= $totalPage;$pg++)
                        <li class="paginate_button page-item <?php if($pg == 1){ ?>active <?php } ?>">
                            <a onclick="performPaging('<?= $pg ?>')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="<?= $pg ?>" tabindex="0" class="page-link">
                                <?= $pg ?>
                            </a>
                        </li>
                        @endfor
                        <li class="paginate_button page-item next <?php if($totalPage == 1){ ?>disabled <?php } ?>" id="datatable_next">
                            <a onclick="performPaging('2')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="2" tabindex="0" class="page-link">
                                Next
                            </a>
                        </li>
                    </ul>
                </div>
                @else
                <div class="font-size-h3 font-w600 py-30 mb-20 text-center border-b">
                  <span class="text-primary font-w700">No Active </span>Requests found 
              </div>
                @endif
            </div>
        </div>                          -->
                                
                                <!-- Active Requests --> 
                                
                            </div>
                            <!-- END Classic -->

                            <!-- Pending Requests -->
                            <div class="tab-pane fade" id="all-alerts" role="tabpanel">
                                
                                <div class="row">
            
            @if($pendingRequestPosts->count())
            
            @foreach($pendingRequestPosts as $pendingRequestPost)
            <div class="col-lg-6">
                <!-- Simple Rating -->
                <div class="block">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">{!! date("l M d, Y",strtotime($pendingRequestPost->staffingStartDate)) !!} - 
                            
                           @if($pendingRequestPost->shiftType == 1)
                           {!! date("g:i A",strtotime($pendingRequestPost->customShiftStartTime)) !!}
                           @else
                           {!! date("g:i A",strtotime($pendingRequestPost->startTime)) !!}
                           @endif
                        
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                    
                        <p><strong>Staff posting owner -</strong> {!! $pendingRequestPost->staffOwner !!}</p>
                        <p><strong>Posting description -</strong> {!! $pendingRequestPost->notes !!}</p>
                        <p><strong>Type of staff needed -</strong>
                            {!! myHelper::getRequiredSkills($requestPost->requiredStaffCategoryID) !!}
                        </p>
                        <p><strong>Number of people responded -</strong> 0</p>
                        <p><strong>Number of people accepted for full shift -</strong> 0</p>
                        <p><strong>Number of people accepted for partial shift -</strong> 0</p>
                        <p>
                            <button onclick="location.href = '{!! url(Config('constants.urlVar.staffingPostDetail').$pendingRequestPost->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                View Detail
                            </button>
                            <button onclick="approveRequest('{!! $pendingRequestPost->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-success mb-10">
                                Approve
                            </button>
                            <button onclick="disApproveRequest('{!! $pendingRequestPost->postID !!}')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-danger mb-10">
                                Disapprove
                            </button>
                        </p>
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach            
            
            @endif
            
            
        </div>
                                
                            </div>
                            <!-- Pending Requests -->

                        </div>
                    </div>




        
</div>



    
    <!--Background Loader-->  
    <div id="loadingDiv" style="display: none;">
        <div style="margin-left: 49%;
    margin-top: 13%;">
            <h3 style="color: #4d9aba;">loading ...</h3>
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

<!-- container -->
<script>
    function approveRequest(requestID){
      var r = confirm("Are you sure? Approve this request.");
        if (r == true) {
            var requestUrl = '{!! url(Config('constants.urlVar.approvePost')) !!}/'+requestID+'/1/home';
            
           location.href = requestUrl;
        } else {
            return false; 
        }  
    }
    function disApproveRequest(requestID){
       
      var r = confirm("Are you sure? Disapprove/Cancel this request.");
        if (r == true) {
           var requestUrl = '{!! url(Config('constants.urlVar.approvePost')) !!}/'+requestID+'/4/home'; 
           
           location.href = requestUrl;
        } else {
           return false; 
        }   
        
    }
    
      
        
    function changeView(){
        location.href = '{!! url(Config('constants.urlVar.userCalendarView').$unitInfo->id) !!}';
    }
    /* Ajax Paging Open Requests List */ 
    var requestPagingUrl = '{!! url(Config('constants.urlVar.ajaxOpenRequestsList').$unitInfo->id) !!}';
    /* Ajax Paging Open Requests List */
    </script>

<script type="text/javascript" >
    
    setTimeout("reloadPageAfterTen()", 30000);
    
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
