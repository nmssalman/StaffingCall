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
<h2 class="content-heading">Past Staffing Requests - <small><span class="badge badge-danger">STAFFING HISTORY</span></small></h2>
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
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['totalResponded'] !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Number of Responses</strong>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['fullResponded'] !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Full Shift</strong>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['partialResponded'] !!}</h2></div>
                            <strong style="color: #2e3238;">Partial Shift
                            </strong>
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
             <p>No Past Requests Call found</p>
            @endif
            
            
        </div>
<!--        Active Posts-->
    
</div>
<!-- container -->

@endsection
