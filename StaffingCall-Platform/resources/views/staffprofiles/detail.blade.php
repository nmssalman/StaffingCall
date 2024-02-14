@extends('layouts.app')
@section('content')

<div class="content">
   <h2 class="content-heading">Staff Profiles <br><small> 
           <a href="{!! url(Config::get('constants.urlVar.staffProfileList')) !!}" style="font-size: 12px !important;">
               < Back to Staff List</a>
       
       </small></h2>
                    <div class="block">
                        <div class="block-header block-header-default">
                            
                            <img style="width: 100px;height: 100px;" class="img-avatar img-avatar-thumb" src="@if($userInfo->profilePic) {!! url('public/'.$userInfo->profilePic) !!}@else {!! url('/assets/img/avatar1.jpg') !!} @endif" alt="">
                            <h2 class="block-title">&nbsp; {!! $userInfo->firstName." ".$userInfo->lastName !!}
                            </h2>
                            
                        </div>
                        @php $staffRole = 'Staff'; @endphp
                        @if($userInfo->role == '4')
                        
                        @php $staffRole = 'Admin'; @endphp
                        
                        @endif
                        @if($userInfo->role == '3')
                        
                        @php $staffRole = 'Super Admin'; @endphp
                        
                        @endif
                        
                        
                        <div class="block-content block-content-full">
                            
                            
                            
                            <div class="row items-push text-center">
                            <!--<div class="col-6 col-md-6 col-xl-2">
                                <div class="font-w600">Business Group</div>
                                <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">{!! $userInfo->groupName." (".$userInfo->groupCode.")" !!}</a>
                            </div>-->
                                <div class="col-6 col-md-6 col-xl-4">
                                    <div class="font-w600">Business Unit</div>
                                    <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">
                                        {!! $userBusinessUnits?implode(', ',$userBusinessUnits):'Not defined'; !!}</a>
                                </div>
                                <div class="col-6 col-md-6 col-xl-2">
                                    <div class="font-w600">Role</div>
                                    <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">{!! $staffRole !!}</a>
                                </div>
                                
                                <div class="col-6 col-md-6 col-xl-2">
                                    <div class="font-w600">Mobile</div>
                                    <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">{!! $userInfo->phone !!}</a>
                                </div>
                                <div class="col-6 col-md-6 col-xl-4">
                                    <div class="font-w600">Employee Category</div>
                                    <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">
                                    {!! $userSkills?implode(', ',$userSkills):'Not defined'; !!}
                                    </a>
                                </div>
                                <div class="col-6 col-md-6 col-xl-4">
                                    <div class="font-w600">Email</div>
                                    <a style="color: #42a5f5 !important;" class="" href="javascript:void(0)">
                                    {!! $userInfo->email!!}
                                    </a>
                                </div>
                            </div>
                            
                            
                
                 
                            
                 @if($userInfo->role == 3)
                     
                 <div class="row"> 
                                 
                             
                        <div class="col-md-6">
                              <div class="block">
                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Number of Staffing Calls created :
                                          <small style="color: #42a5f5 !important;">{!! $lastTimeShiftCancellationCount !!}</small>
                                      </h3>
                     
                                  </div>
                                  <div class="block-content block-content-full text-center" style="height: 250px;">
                                      <canvas id="cancellationChart" height="150"></canvas>
                                  </div>

                              </div>
                          </div>
                        
                        
                           
                            <div class="col-md-6">
                              <div class="block">
                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Number of Staffing Calls Approved :
                                          <small style="color: #42a5f5 !important;">{!! $averageTimeShiftCancellationCount !!}</small>
                                      </h3>
                     
                                  </div>
                                  <div class="block-content block-content-full text-center" style="height: 150px;">
                                      <canvas id="AvgTime" height="150"></canvas>
                                  </div>

                              </div>
                            </div>
                        
                        
                </div>  
                 @else
                    <div class="row"> 
                                 
                             
                        <div class="col-md-6">
                              <div class="block">
                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Number of Last Minute Shift Cancellations :
                                          <small style="color: #42a5f5 !important;">{!! $lastTimeShiftCancellationCount !!}</small>
                                      </h3>
                     
                                  </div>
                                  <div class="block-content block-content-full text-center" style="height: 250px;">
                                      <canvas id="cancellationChart" height="150"></canvas>
                                  </div>

                              </div>
                          </div>
                        
                        
                           
                        <div class="col-md-6">
                              <div class="block">
                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Average time of Shift Cancellations :
                                          <small style="color: #42a5f5 !important;">{!! $averageTimeShiftCancellationCount !!}</small>
                                      </h3>
                     
                                  </div>
                                  <div class="block-content block-content-full text-center" style="height: 150px;">
                                      <canvas id="AvgTime" height="150"></canvas>
                                  </div>

                              </div>
                          </div>
                        
                        
                       </div>       
                                
   
                    <div class="row"> 
                                 
                             
                        <div class="col-md-6">
                              <div class="block">
                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Number of last minute shifts willing to take :
                                          <small style="color: #42a5f5 !important;">
                                              {!! $shiftWillingToTake !!}
                                          </small>
                                      </h3>
                                      
                                      
                     
                                  </div>                                  

                                  <div class="block-header">
                                      <h3 class="block-title">
                                          Number of last minute shifts covered :
                                          <small style="color: #42a5f5 !important;">
                                              {!! $shiftCovered !!}</small>
                                      </h3>
                     
                                  </div>
                              </div>
                          </div>
                        
                        
                       </div>       
                 @endif  
                 
                 
                 
   
                            
                        </div>
                    </div> 
                    
</div>


<?php
$lastMinuteShiftCancellationTitle  = 'Shift Cancellations';
$averageTimeShiftCancellationTitle  = 'Average time';
if($userInfo->role == 3){
  $lastMinuteShiftCancellationTitle  = 'Calls created';
  $averageTimeShiftCancellationTitle  = 'Calls approved';  
} ?>
<script type="text/javascript">
    var lastMinuteShiftCancellations = {!! $lastMinuteShiftCancellations !!};
    var averageTimeShiftCancellation = {!! $averageTimeShiftCancellation !!};
    
    var lastMinuteShiftCancellationTitle = '{!! $lastMinuteShiftCancellationTitle !!}';
    var averageTimeShiftCancellationTitle = '{!! $averageTimeShiftCancellationTitle !!}';
</script>
<!-- container -->
@endsection