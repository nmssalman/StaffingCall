@extends('layouts.app')
@section('content')

<div class="bg-image bg-image-bottom" style="background-image: url({!! url('/assets/img/staffing.jpg') !!});">
                    <div class="bg-primary-dark-op py-30-custom">
                        <div class="content content-full text-center">
                            <!-- Avatar -->
                            <div class="mb-15">
                                <a class="img-link" href="#">
                                    <img class="img-avatar img-avatar96 img-avatar-thumb" src="@if($userInfo->profilePic){!! url('public/'.$userInfo->profilePic) !!}@else {!! url('/assets/img/avatar1.jpg') !!} @endif" alt="">
                                </a>
                            </div>
                            <!-- END Avatar -->

                            <!-- Personal -->
                            <h1 class="h3 text-white font-w700 mb-10">{!! $userInfo->firstName." ".$userInfo->lastName !!}
                             
                            </h1>
                            
                                            
                            
                            <!-- END Personal -->
                            
                            
                        </div>
                    </div>
                </div>

<div class="content">
    <!-- Agency Profile -->
<!--                    <h2 class="content-heading">
                     
                        <i class="si si-briefcase mr-5"></i> Agency Profile
                    </h2>-->
    
<div class="block" style="border:1px solid #4ba5f5;border-radius:2px;">
                        <ul class="nav nav-tabs nav-tabs-alt" data-toggle="tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" href="#basic-info" >Account Information</a>
                            </li>
                            
                        </ul>
    
                        <div style="width: 99px;
    float: right;
    margin-top: -40px;
    margin-right: -25px;">
                               <button style="cursor: pointer;" onclick="javascript:location.href='{!! url(Config('constants.urlVar.editProfile')) !!}'" type="button" class="btn btn-sm btn-outline-default">
                                   <i class="fa fa-edit"> <strong style="font-size: 14px;
    font-family: -apple-system, system-ui, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif;
    color: #575757;">Edit</strong></i></button> 
                            </div>   
    
    
                        <div class="block-content block-content-full tab-content overflow-hidden">
                            <!-- Classic -->
                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
<!--                                <div class="font-size-h3 font-w600 py-30 mb-20 text-center border-b">
                                    <span class="text-primary font-w700">Agency</span><mark class="text-danger"> Information</mark>
                                </div>-->
                                <div class="row items-push">
                                    <div class="col-lg-6">
<!--                                        <h4 class="h5 mb-5">
                                            <a href="javascript:void(0)">Basic Info</a>
                                        </h4>-->
                                        
                                     <div class="font-size-sm text-muted">
                                                <em><strong class="font-w600">Name : </strong> <a href="javascript:void(0)">{!! $userInfo->firstName." ".$userInfo->lastName !!}</a></em>
                                     </div> 
                                      
                                     @if(Auth::user()->role != 1)
                                     
                                    <div class="font-size-sm text-muted">
                                            <em><strong class="font-w600">Business Group : </strong> 
                                            <a href="javascript:void(0)">{!! $userInfo->groupName." (".$userInfo->groupCode.")" !!}</a>
                                            </em>
                                    </div>      
                                    @endif
                            
                                    @if(Auth::user()->role != 1 && Auth::user()->role != 2)
                                        <div class="font-size-sm text-muted">
                                            <em><strong class="font-w600">Business Unit : </strong> 
                                            <a href="javascript:void(0)">{!! $userInfo->unitName !!}</a>
                                            </em>
                                        </div>  

                                    @endif           
                                        
                                       
                                        
                                    @if(Auth::user()->role != 1 && Auth::user()->role != 2)   
                                     <div class="font-size-sm text-muted">
                                                <em><strong class="font-w600">Employee Skills : </strong> <a href="javascript:void(0)">
                                                        {!! $userSkills?implode(', ',$userSkills):'Not defined'; !!}
                                                    </a></em>
                                     </div>  
                                        
                                    @endif    
                                     <div class="font-size-sm text-muted">
                                                <em><strong class="font-w600">Email : </strong> <a href="javascript:void(0)">{!! $userInfo->email !!}</a></em>
                                     </div>   
                                        
                                     <div class="font-size-sm text-muted">
                                                <em><strong class="font-w600">Phone : </strong> <a href="javascript:void(0)">{!! $userInfo->phone !!}</a></em>
                                     </div>  
                                    
                                     
                                    
                                    </div>
                                    
                                     @if(Auth::user()->role != 1)    
                                    
                                    <div class="col-lg-6">
                                    
                                        <h4 class="h5 mb-5">
                                            <a href="javascript:void(0)">Default Home:</a>
                                        </h4>
                                        
                                        <label class="css-control css-control-primary css-radio" onclick="updateSettingsOfUser()">
                                        <input value="1" @if($userInfo->calendarView == 1) checked="checked" @endif type="radio" class="css-control-input"  id="toggleCalendarView" name="defaultHomeView">
                                        <span class="css-control-indicator"></span> 
                                        <span class=""><strong>Calendar View</strong></span>
                                    </label>
                                    <label class="css-control css-control-primary css-radio" onclick="updateSettingsOfUser()">
                                        <input value="0" @if($userInfo->calendarView == 0) checked="checked" @endif type="radio" class="css-control-input"  id="toggleListView" name="defaultHomeView">
                                        <span class="css-control-indicator"></span>
                                        <span class=""><strong>List View </strong></span>
                                    </label> 
                                        
                                        
                                    <h4 class="h5 mb-5">
                                        <br />
                                        <a href="javascript:void(0)">Notification Settings:</a>
                                    </h4>
                                        
                                        <label style="margin-left: -15px;" class="col-lg-8 col-form-label" for="pushNotification">
                                    
                                            Receive App Notification &nbsp;&nbsp;&nbsp;&nbsp; 
                                    <label class="css-control css-control-sm css-control-primary css-switch" onclick="updateSettingsOfUser()">
                                       <input value="1" @if($userInfo->pushNotification == '1') checked="" @endif type="checkbox" class="css-control-input" name="pushNotification" id="pushNotification">
                                    <span class="css-control-indicator"></span>
                                    </label>
                                    
                                </label>
                                        
                                <label style="margin-left: -15px;" class="col-lg-8 col-form-label" for="smsNotification">
                                    
                                    Receive Text Notification &nbsp;&nbsp;&nbsp;
                                    <label class="css-control css-control-sm css-control-primary css-switch" onclick="updateSettingsOfUser()">
                                       <input value="1" @if($userInfo->smsNotification == '1') checked="" @endif type="checkbox" class="css-control-input" name="smsNotification" id="smsNotification">
                                    <span class="css-control-indicator"></span>
                                    </label>
                                    
                                </label>
                                        
                                <label style="margin-left: -15px;" class="col-lg-8 col-form-label" for="emailNotification">
                                    
                                    Receive Email Notification&nbsp;&nbsp;
                                    <label class="css-control css-control-sm css-control-primary css-switch" onclick="updateSettingsOfUser()">
                                       <input value="1" @if($userInfo->emailNotification == '1') checked="" @endif type="checkbox" class="css-control-input" name="emailNotification" id="emailNotification">
                                    <span class="css-control-indicator"></span>
                                    </label>
                                    
                                </label>
                                 
                                                                               
                                    </div>
                                    
                                    @endif   
                                    
                                </div>
                            </div>
                            <!-- END Classic -->
                           

                        </div>
                    </div>
                  
                    
</div>
<script type="text/javascript">
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var dataUrl = "{!! url(Config('constants.urlVar.saveNotificationSettings')) !!}";

function updateSettingsOfUser() {
    
    var defaultHome = 0;
    var emailNotification = 0;
    var pushNotification = 0;
    var smsNotification = 0;
    
    if($('#toggleCalendarView').prop('checked') == true){
        defaultHome = 1;
    }
    if($('#pushNotification').prop('checked') == true){
        pushNotification = 1;
    }
    if($('#smsNotification').prop('checked') == true){
        smsNotification = 1;
    }
    if($('#emailNotification').prop('checked') == true){
        emailNotification = 1;
    }
    
    $.ajax({ 
        url: dataUrl,  
        type: "POST",
        data: {
            _token: CSRF_TOKEN,
            calendarView:defaultHome,
            emailNotification:emailNotification,
            pushNotification:pushNotification,
            smsNotification:smsNotification
        }  ,
        success: function(response){ 
           //console.log(response);
           location.reload();
        },
        error: function(e){
            //console.log(e);
        }
    });
}

</script>
<!-- container -->
@endsection