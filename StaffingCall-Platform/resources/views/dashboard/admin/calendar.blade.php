@extends('layouts.app')
@section('content')

<link href="{!! asset('/assets/calendarScript/fullcalendar.min.css') !!}" rel='stylesheet' />
<link href="{!! asset('/assets/calendarScript/fullcalendar.print.min.css') !!}" rel='stylesheet' media='print' />
<script src="{!! asset('/assets/calendarScript/lib/moment.min.js') !!}"></script>
<script src="{!! asset('/assets/calendarScript/lib/jquery.min.js') !!}"></script>
<script src="{!! asset('/assets/calendarScript/fullcalendar.min.js') !!}"></script>
<script>

	$(document).ready(function() {
            $('#loadingDiv').css('display','block');
            var availabilityFromDate = '';
            var availabilityToDate = '';
            var shiftArr = '{!! $shiftArray !!}';
            var totalShifts = '{!! count($unitShifts) !!}';
            var contentHeightVar = 1200;
            if(totalShifts <=3){
               contentHeightVar = 1000; 
            }
            //console.log(shiftArr);
		jQuery('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				/*right: 'month,agendaWeek,agendaDay,listWeek'*/
				right: 'month,basicWeek,agendaWeek'
			},
                        views: {
                           month : {
                             buttonText: 'Monthly'  
                           } ,
                           basicWeek : {
                             buttonText: 'Weekly'  
                           } ,
                           agendaWeek : {
                             buttonText: 'Bi-weekly'  
                           } 
                        },
                        
			defaultDate: '{!! $defaultDate !!}',
			navLinks: true, // can click day/week names to navigate views,
                        
			selectable: false,
			selectHelper: true,
			select: function(start, end) {
                            
                            var d3 = new Date(end);
                            d3.setDate(d3.getDate() - 1);
                            
                            var d4 = new Date(start);
                            
                            var allEvents = [];
                            allEvents = $('#calendar').fullCalendar('clientEvents');
                            var event = $.grep(allEvents, function (v) {
                                return +v.start === +start;
                            });
                            var startHasEvent =  event.length > 0;
                            
                            allEvents = $('#calendar').fullCalendar('clientEvents');
                            var event = $.grep(allEvents, function (v2) {
                                return +v2.start === +d3;
                            });
                            var endHasEvent =  event.length > 0;
                            
                            
                            
                        var today = new Date();
                         today.setHours(0,0,0,0);
                         d3.setHours(0,0,0,0);
                         d4.setHours(0,0,0,0);
                         if(d4 < today || d3 < today){
                            alert("You can not perform any action on past dates");                            
                            $('#calendar').fullCalendar('unselect');
                        }else if(startHasEvent == false || endHasEvent == false){
                            alert("Please select date which has Shifts.");
                            $('#calendar').fullCalendar('unselect');
                        }else{
                            formatDate(start,end);
                            var d = new Date(start);
                            var startDate = d.toDateString();
                        
                        
                            var d2 = new Date(end);
                            d2.setDate(d2.getDate() - 1);
                            var endDate = d2.toDateString();
                        
                            if(startDate == endDate){
                                $('#selectedDateID').html('<strong">'+startDate+'</strong>')
                            }else{
                                $('#selectedDateID').html('<strong">'+startDate+'</strong>'+' -  <strong>'+endDate+'</strong>')
                            }
                            $('#selection-pop').click();
                            
				$('#calendar').fullCalendar('unselect');
                            }    
			},
			editable: false,
			eventLimit: true, // allow "more" link when too many events//			
                        events : JSON.parse(shiftArr),
                        eventRender: function(event, element, view) {
                            console.log(view.name);
                            var cellHeight = 100;
                            if(view.name === 'basicWeek') {
                                contentHeightVar = 400;
                                cellHeight = (contentHeightVar - 70) / totalShifts ;
                                
                                $(element).height(cellHeight);
                                
                            }else if(view.name === 'month'){  
                                cellHeight = contentHeightVar / 6;
                                $(element).height('auto');
                                contentHeightVar = 1200;
                                    if(totalShifts <=3){
                                       contentHeightVar = 1000; 
                                    }
                            }else if(view.name === 'agendaWeek'){
                                contentHeightVar = 400; 
                                 cellHeight = ((contentHeightVar / 2) - 70) / totalShifts ;
                                
                                $(element).height(cellHeight);
                                //$(element).height(70);
                            }
                             
                            $('#calendar').fullCalendar('option', 'contentHeight', contentHeightVar);
                        }
		});
                
               
            
               
               
            var activeCaledarDate = '{!! $defaultDate !!}';
            var getViewOfCalendar = '{!! $currentViewOfCalendar !!}';
            
            if(getViewOfCalendar == '2'){//Wekly
                $('#basicWeek').click();
            }
            if(getViewOfCalendar == '3'){//Bi-Wekly
                $('#agendaWeek').click();
            }
            
            
            
             $('body').on('click', 'button.fc-prev-button', function() {
                
               $('#loadingDiv').css('display','block'); 
            var activeView = $('.fc-state-active').attr('id');
            var defaultMonth = '{!! date("Y-m-d", strtotime("-1 month", strtotime($defaultDate))); !!}';
            
                var currentViewOfCalendar = '1';

                 if(activeView == 'basicWeek'){
                currentViewOfCalendar = '2';//Weekly
                defaultMonth = '{!! date("Y-m-d", strtotime("-7 day", strtotime($defaultDate))); !!}';
                }else if(activeView == 'agendaWeek'){
                    currentViewOfCalendar = '3';//Bi-Weekly
                    defaultMonth = '{!! date("Y-m-d", strtotime("-14 day", strtotime($defaultDate))); !!}';
                }
               var requestUrl = '{!! url(Config('constants.urlVar.ajaxCalendarViewOnNextPrevious')) !!}';
                    $.ajax({ 
                            url: requestUrl,  
                            type: "GET",
                            data: {defaultMonth: defaultMonth,currentViewOfCalendar:currentViewOfCalendar}  ,
                            success: function(response){
                                location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
                            },
                            error:function(e){
                                console.log(e);
                            }
                   });
                
                
                
            });

$('body').on('click', 'button.fc-next-button', function() {
     $('#loadingDiv').css('display','block'); 
var activeView = $('.fc-state-active').attr('id');
var defaultMonth = '{!! date("Y-m-d", strtotime("+1 month", strtotime($defaultDate))); !!}';

    var currentViewOfCalendar = '1';

    if(activeView == 'basicWeek'){
        currentViewOfCalendar = '2';//Weekly
        defaultMonth = '{!! date("Y-m-d", strtotime("+7 day", strtotime($defaultDate))); !!}';
    }else if(activeView == 'agendaWeek'){
        currentViewOfCalendar = '3';//Bi-Weekly
        defaultMonth = '{!! date("Y-m-d", strtotime("+14 day", strtotime($defaultDate))); !!}';
    }
    
    
    
   var requestUrl = '{!! url(Config('constants.urlVar.ajaxCalendarViewOnNextPrevious')) !!}';
        $.ajax({ 
                url: requestUrl,  
                type: "GET",
                data: {defaultMonth: defaultMonth,currentViewOfCalendar:currentViewOfCalendar}  ,
                success: function(response){
                    location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
                },
                error:function(e){
                    console.log(e);
                }
       });
        
});    

$('#today').on('click', function() {
     $('#loadingDiv').css('display','block'); 
  var activeView = $('.fc-state-active').attr('id');
            var defaultMonth = '{!! date("Y-m-d"); !!}';
            
                var currentViewOfCalendar = '1';

                if(activeView == 'basicWeek'){
                    currentViewOfCalendar = '2';//Weekly
                }else if(activeView == 'agendaWeek'){
                    currentViewOfCalendar = '3';//Bi-Weekly
                }
               var requestUrl = '{!! url(Config('constants.urlVar.ajaxCalendarViewOnNextPrevious')) !!}';
                    $.ajax({ 
                            url: requestUrl,  
                            type: "GET",
                            data: {defaultMonth: defaultMonth,currentViewOfCalendar:currentViewOfCalendar}  ,
                            success: function(response){
                                location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
                            },
                            error:function(e){
                                console.log(e);
                            }
                   });
                
});    
	$('#loadingDiv').css('display','none'); 	
	});


function formatDate(startDate,endDate) {
    var d = new Date(startDate),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    
    var d2 = new Date(endDate),
        month2 = '' + (d.getMonth() + 1),
        day2 = '' + d2.getDate(),
        year2 = d2.getFullYear();

    if (month2.length < 2) month2 = '0' + month2;
    if (day2.length < 2) day2 = '0' + day2;

    var startDay = [year, month, day].join('-');
    var endDay = [year2, month2, day2].join('-');
    
    availabilityFromDate = startDay;
    availabilityToDate = endDay;
    
    return true;
}

</script>

<style>

	body {
		margin: 40px 10px;
		padding: 0;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		font-size: 14px;
	}

	#calendar {
		max-width: 1160px;/*900px;*/
		margin: 0 auto;
	}

</style>


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

  <small><span class="badge badge-success">Calender view</span></small>  
  
  
  
</h2>

<div style="float: right;margin-top: -2%;"><strong style="color:#f00;">Switch to :</strong>

    <button onclick="changeAdminView();" title="Switch to user" style="cursor: pointer;margin-top: 4%;" type="button" class="btn btn-sm btn-secondary mb-10">User</button>
    <button title="You are logged-in as admin" style="cursor: pointer;margin-top: 4%;" type="button" class="btn btn-sm btn-success mb-10">Admin</button>

</div>

<div style="margin-bottom: 16px;">
     
    
<label class="css-control css-control-primary css-radio" >
    <input checked="checked" value="0" type="radio" class="css-control-input" >
    <span class="css-control-indicator"></span> 
    <span class=""><strong>Calendar View</strong></span>
</label>
<label class="css-control css-control-primary css-radio"  onclick="changeView();">
    <input type="radio" class="css-control-input" id="toggleView" name="toggleView">
    <span class="css-control-indicator"></span>
    <span class=""><strong>List View </strong></span>
</label>  
    
</div>
       
<!--        Active Posts-->
        <div class="row">
            
<!--            @if($requestPosts)
            
            @foreach($requestPosts as $requestPost)
           
            @endforeach
            
            @else
            
            @endif-->
            
            



 <div class="col-lg-12">
<div id='calendar'></div>

 </div>
        </div>






<!--        Active Posts-->

        
</div>

<!-- container -->


<a id="selection-pop" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" style="visibility: hidden;"></a>
<!-- Date selection Alert Popup -->
<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content">
                    
                  <form class="alert-resolve-bootstrap" action="#" method="post" >    
                    
                    <div class="block block-themed block-transparent mb-0">
                        <div class="block-header bg-primary-dark">
                            <h3 class="block-title">How would like to mark these days ?</h3>
                            <div class="block-options">
<!--                                <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                                    <i class="si si-close"></i>
                                </button>-->
                            </div>
                        </div>
                        <div class="block-content">
                          
                            <div class="form-group row">
                                <div class="col-12">
                                    <h4><span id="selectedDateID"></span></h4>
                                    <br />
                                    <select class="js-select2 form-control" id="availableShifts" name="availableShifts" data-placeholder="Choose Shifts..">
                                        
                                        <option value="0">Full Day</option>
                                        @foreach($unitShifts as $unitShift)
                                        <option value="{!! $unitShift->id !!}">
                                            {!! date("g:iA",strtotime($unitShift->startTime))." - ".
                                                date("g:iA",strtotime($unitShift->endTime))
                                            !!}
                                        </option>
                                        @endforeach
                                    </select>
                                    
                                    
                                   
                                </div>
                                
                            </div>
                             
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        
                       
                        
                        <button id="userAvailability" style="cursor: pointer;" type="button" class="btn btn-success">Available
                            <img style="visibility:hidden;" id="availabilityLoader" width="30" height="10" src="{!! url('/assets/images/loading.gif') !!}" />
                        </button>
                        <button id="userUnavailability" style="cursor: pointer;" type="button" class="btn btn-danger">Unavailable
                            <img style="visibility:hidden;" id="unAvailabilityLoader" width="30" height="10" src="{!! url('/assets/images/loading.gif') !!}" />
                        </button>
                        <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Cancel</button>
                        
                        
                    </div>
                    
                   </form> 
                    
                </div>
            </div>
        </div><!-- Date selection Popup -->

        
        
    <!--Background Loader-->  
    <div id="loadingDiv" style="display: none;">
        <div style="margin-left: 49%;
    margin-top: 13%;">
            <h3 style="color: #4d9aba;">loading calendar...</h3>
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
        
    <script type="text/javascript">
        var CSRF_TOKEN = "{!! csrf_token(); !!}";
        $('#userAvailability').click(function (){
           $('#availabilityLoader').css('visibility','visible'); 
           $('#userAvailability').attr('disabled',true); 
           $('#userUnavailability').attr('disabled',true);  
           $('#cancelBtn').attr('disabled',true); 
           
           var selectedShift = $('#availableShifts').val();
           var fromDate = availabilityFromDate;
           var toDate = availabilityToDate;
           
            var requestUrl = '{!! url(Config('constants.urlVar.userCalendarAvailability')) !!}';
             $.ajax({ 
                    url: requestUrl,  
                    type: "POST",
                    data: {_token:CSRF_TOKEN,startDate: fromDate,endDate:toDate,shiftID:selectedShift,availabilityStatus:1}  ,
                    success: function(response){
                        location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
                    },
                    error:function(e){
                        console.log(e);
                    }
             });
           
           
        });
        $('#userUnavailability').click(function (){
            
           $('#unAvailabilityLoader').css('visibility','visible');  
           $('#userAvailability').attr('disabled',true); 
           $('#userUnavailability').attr('disabled',true);  
           $('#cancelBtn').attr('disable',true);
           
           var selectedShift = $('#availableShifts').val();
           var fromDate = availabilityFromDate;
           var toDate = availabilityToDate;
           
            var requestUrl = '{!! url(Config('constants.urlVar.userCalendarAvailability')) !!}';
             $.ajax({ 
                    url: requestUrl,  
                    type: "POST",
                    data: {_token:CSRF_TOKEN,startDate: fromDate,endDate:toDate,shiftID:selectedShift,availabilityStatus:0}  ,
                    success: function(response){
                        location.href = '{!! url(Config('constants.urlVar.userCalendarView')) !!}';
                    },
                    error:function(e){
                        console.log(e.responseText);
                    }
             });
           
            
        });
     
var CSRF_TOKEN = "{!! csrf_token(); !!}";
var dataUrl = "{!! url(Config('constants.urlVar.changeAdminView')) !!}";
function changeAdminView(){
    var defaultView = '0';//Change to User view

    
    $.ajax({ 
            url: dataUrl,  
            type: "POST",
            data: {_token: CSRF_TOKEN,defaultView:defaultView}  ,
            success: function(response){ 
               window.location = "{!! url(Config('constants.urlVar.userCalendarView')) !!}";
            }
        });
}   
        
    function changeView(){
        location.href = '{!! url(Config('constants.urlVar.home')) !!}';
    }
        
        
        </script>
@endsection
