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
<!--<div style="margin-bottom: 16px;">
    <label class="css-control css-control-sm css-control-primary css-switch" title="Toggle to switch Calendar view" onclick="changeView();">
        <input type="checkbox" checked="" class="css-control-input"  id="toggleView">
        <span class="css-control-indicator"></span> List View
</label>
</div>-->

<!--        Business Units-->
<!--<h2 class="content-heading">Business Units - <small><span class="badge badge-success">List</span></small></h2>-->

<div id="searchBox">
    <form action="{!! url(Config('constants.urlVar.home')) !!}" method="get">
        <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
        <div class="form-group row">
            <label class="col-lg-1 col-form-label" for="search">Search :</label>
            <div class="col-lg-4">
                <input type="text" value="{!! $searchValue !!}" class="form-control" id="search" name="search" placeholder="Business Unit Name">
                
            </div>
            <button style="cursor: pointer;" type="submit" class="btn btn-outline-info mb-10">Search</button>
        </div>
    </form>    
</div>


<div class="" id="all-units">

<div class="row">
            
            @if($managerUnits->count())
            
            @foreach($managerUnits as $managerUnit)
            
            <div class="col-lg-12">
                <!-- Simple Rating -->
               
                <div class="block" style="border: 2px solid lightgray;">
                    <div class="block-header block-header-default" style="background-color: #343a40;text-align: center;color: #fff;">
                        <h3 class="block-title" style="font-weight: bold;font-size: 22px;">
                            
<!--                           <a style="color: #fff;"  href="{!! url(Config('constants.urlVar.businessUnitDetail').$managerUnit->id) !!}">
                                {!! $managerUnit->unitName !!} 
                          
                            </a>-->
                            
                            @if(Auth::user()->calendarView == '1')
                            <a style="color: #fff;"  href="{!! url(Config('constants.urlVar.userCalendarView').$managerUnit->id) !!}">
                                {!! $managerUnit->unitName !!} 
                          
                                
                            </a>

                            <button onclick="location.href='{!! url(Config('constants.urlVar.userCalendarView').$managerUnit->id) !!}'" style="cursor: pointer;float: right;border-color: #fff;color: #fff;" type="button" class="btn btn-sm btn-outline-info">
                                View Detail
                            </button>
                            @else
                            <a style="color: #fff;"  href="{!! url(Config('constants.urlVar.businessUnitDetail').$managerUnit->id) !!}">
                                {!! $managerUnit->unitName !!} 
                          
                                
                            </a>

                            <button onclick="location.href='{!! url(Config('constants.urlVar.businessUnitDetail').$managerUnit->id) !!}'" style="cursor: pointer;float: right;border-color: #fff;color: #fff;" type="button" class="btn btn-sm btn-outline-info">
                                View Detail
                            </button>
                            @endif
                            
                        </h3>
                    </div>
                    
                    <div class="block-content block-content-full">
                    
                    <!-- Get Responde People-->
                     <?php 
                 $businessUnitInfo = myHelper::getBusinessUnitInformation($managerUnit->id);  
                     ?>

                    <!--Get Responde People-->
                        
                        <div class="row items-push text-center">
                            
                        <div class="col-6 col-md-6 col-xl-4">
                             <a href="{!! url(Config('constants.urlVar.businessUnitDetail').$managerUnit->id) !!}">
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! $businessUnitInfo['openPostingCount'] !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Open Requests</strong>
                             </a>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                             <a href="{!! url(Config('constants.urlVar.staffingHistory')) !!}">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $businessUnitInfo['pastPostingCount'] !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Past Requests</strong>
                             </a>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <a href="{!! url(Config('constants.urlVar.staffProfileList')) !!}">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $businessUnitInfo['totalUsers'] !!}</h2></div>
                            <strong style="color: #2e3238;">Number of Staff
                            </strong>
                            </a>
                        </div>
                        
                    </div>
                    
<!--                        <p>
                            <button onclick="javascript:location.href = '{!! url(Config('constants.urlVar.businessUnitDetail').$managerUnit->id) !!}';" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                View Detail
                            </button>
                        </p>-->
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach
            
            @else
            <p>No Business Units of this group found</p>
            @endif
            
            
        </div>



<!--        Business Units-->
</div>       
</div>
<!-- container -->

    
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
    
    
    
@if(Auth::user()->role == 2 && Session::has('passwordPrompt'))

<a id="changePassAlertNotice" style="visibility: hidden;" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" ></a>
<!-- Change Password Alert Popup -->  
<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content" style="width:450px;margin-top: 7%;">

            <div class="block block-themed block-transparent mb-0" style="max-height: 500px;overflow: auto;">
                <div class="block-header bg-primary-dark" style="height: 50px;">

                    <div class="block-options" style="margin-left: 12%;margin-top: 4%;">
                        <h5 style="color:#fff;">You must change your password!</h5>

                    </div>
                </div>
                <div class="block-content">
                   
                </div>
            </div>
            <div class="modal-footer">
                <button style="cursor: pointer;" type="button" class="btn btn-alt-info" onclick="javascript:location.href='{!! url(Config('constants.urlVar.changePassword')) !!}'">Change</button>
                 <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Cancel</button>
             </div>

        </div>
    </div>
</div>
<!-- Change Password Alert Popup -->  

<script type="text/javascript">
    setTimeout('showPassAlert()', 1000);
    function showPassAlert(){    
    document.getElementById('changePassAlertNotice').click();
    }
</script>

@endif

<script type="text/javascript">
       
        
    function changeView(){
        location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
    }
        
      /* Ajax Paging Business Unit List */ 
    var requestPagingForUnitUrl = '{!! url(Config('constants.urlVar.ajaxUnitsList')) !!}';
    /* Ajax Paging Business Unit List */   
        </script>
@endsection