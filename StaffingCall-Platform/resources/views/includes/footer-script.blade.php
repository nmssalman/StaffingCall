<!--<script src="{!! asset('/assets/js/core/jquery.min.js') !!}"></script>-->
<!--Above is the code base theme jquery-->
<!--Below jquery is replaced with above due to calender problem Start-->

@if(Request::is(Config::get('constants.urlVar.userCalendarView').'*') || 
(Request::path().'/' == Config::get('constants.urlVar.userCalendarView')))
@else  
<script src="{!! asset('/assets/js/jquery/jquery-2.1.0.min.js')!!}"></script>
@endif

<!--Below jquery is replaced with above due to calender problem End-->


<script src="{!! asset('/assets/js/core/popper.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/bootstrap.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/jquery.slimscroll.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/jquery.scrollLock.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/jquery.appear.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/jquery.countTo.min.js') !!}"></script>
<script src="{!! asset('/assets/js/core/js.cookie.min.js') !!}"></script>
<script src="{!! asset('/assets/js/codebase.js') !!}"></script>

 

@if(Request::path().'/' == Config::get('constants.urlVar.groupList') || 
Request::path().'/' == Config::get('constants.urlVar.managerGroupList') || 
Request::path().'/' == Config::get('constants.urlVar.unitList') || 
Request::path().'/' == Config::get('constants.urlVar.userList') || 
Request::path().'/' == Config::get('constants.urlVar.unitSkillsCategoryList') || 
Request::path().'/' == Config::get('constants.urlVar.requestReasonsList') || 
Request::path().'/' == Config::get('constants.urlVar.vacancyReasonsList') || 
Request::path().'/' == Config::get('constants.urlVar.unitShiftSetUpList') || 
Request::is(Config::get('constants.urlVar.staffingPostDetail').'*') || 
Request::path().'/' == Config::get('constants.urlVar.staffProfileList') || 
Request::path().'/' == Config::get('constants.urlVar.algorithmList'))
<!--Grid Pages Javascript-->
<!-- Page JS Plugins -->
<script src="{!! asset('/assets/js/plugins/datatables/jquery.dataTables.min.js') !!}"></script>
<script src="{!! asset('/assets/js/plugins/datatables/dataTables.bootstrap4.min.js') !!}"></script>

<!-- Page JS Code -->
<!--<script src="{!! asset('/assets/js/pages/be_tables_datatables.js') !!}"></script>-->

<script src="{!! asset('/assets/js/staffing-call-app.js') !!}"></script>

<!--Grid Pages Javascript-->
@endif


@if(Request::path().'/' == Config::get('constants.urlVar.addNewGroup') || 
    Request::is(Config::get('constants.urlVar.editGroup').'*') || 
    Request::is(Config::get('constants.urlVar.editUnit').'*') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewUnit') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewUser') || 
    Request::is(Config::get('constants.urlVar.editUser').'*') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewStaffingRequest') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewSkillCategory') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewRequestReason') || 
    Request::is(Config::get('constants.urlVar.editRequestReason').'*') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewVacancyReason') || 
    Request::is(Config::get('constants.urlVar.editVacancyReason').'*') ||
    Request::path().'/' == Config::get('constants.urlVar.addNewShiftSetUp') || 
    Request::is(Config::get('constants.urlVar.editShiftSetUp').'*') ||
    Request::path().'/' == Config::get('constants.urlVar.shiftOffer') || 
    Request::is(Config::get('constants.urlVar.shiftOfferDetail').'*') ||
    Request::path().'/' == Config::get('constants.urlVar.editProfile') || 
            Request::is(Config::get('constants.urlVar.businessUnitDetail').'*') || 
            Request::is(Config::get('constants.urlVar.businessUnitDetailPending').'*') || 
            Request::path().'/' == Config::get('constants.urlVar.home') || 
            Request::path() == '/')
<!--Form Page Javascripts-->
<!-- Page JS Plugins -->
<script src="{!! asset('/assets/js/plugins/select2/select2.full.min.js') !!}"></script>

<script src="{!! asset('/assets/js/plugins/jquery-validation/jquery.validate.min.js') !!}"></script>
<script src="{!! asset('/assets/js/plugins/jquery-validation/additional-methods.min.js') !!}"></script>



<!--Form Page Javascripts-->

        @if(Request::path().'/' == Config::get('constants.urlVar.addNewStaffingRequest') ||
            Request::path().'/' == Config::get('constants.urlVar.addNewShiftSetUp') || 
            Request::is(Config::get('constants.urlVar.editShiftSetUp').'*') ||
            Request::path().'/' == Config::get('constants.urlVar.shiftOffer') || 
            Request::is(Config::get('constants.urlVar.shiftOfferDetail').'*') || 
            Request::is(Config::get('constants.urlVar.businessUnitDetail').'*') || 
            Request::is(Config::get('constants.urlVar.businessUnitDetailPending').'*') || 
            Request::path().'/' == Config::get('constants.urlVar.home') || 
            Request::path() == '/')
            <link href="{!! asset('/assets/css/bootstrap.min.css') !!}" rel="stylesheet" type="text/css">
            <link href="{!! asset('/assets/js/plugins/bootstrap-datepicker/css/bootstrap-datetimepicker.css') !!}" rel="stylesheet" type="text/css">

            <script src="{!! asset('/assets/js/bootstrap/bootstrap.js')!!}"></script>
            <script src="{!! asset('/assets/js/plugins/bootstrap-datepicker/moment.min.js') !!}"></script>
            <script src="{!! asset('/assets/js/plugins/bootstrap-datepicker/bootstrap-datetimepicker.min.js') !!}"></script>
            
            @if(Request::is(Config::get('constants.urlVar.businessUnitDetail').'*') || 
            Request::is(Config::get('constants.urlVar.businessUnitDetailPending').'*') || 
            Request::path().'/' == Config::get('constants.urlVar.home') || 
            Request::path() == '/' ||
            Request::path().'/' == Config::get('constants.urlVar.shiftOffer'))
            
            <script>
            $(function() {                
                $('#searchingStartDatePicker,#searchingEndDatePicker,#searchingStartDate,#searchingEndDate').datetimepicker({
                            format: 'YYYY-MM-DD',
                            minDate: '2005-12-25'
                });
            });

            </script>
            
            @endif
            
            @if(Request::is(Config::get('constants.urlVar.shiftOfferDetail').'*'))
            <script>
                
                var defaultDateOfStartPartialCalPicker = moment().year(shiftStartDefaultYearForCalendar).month(shiftStartDefaultMonthForCalendar-1).date(shiftStartDefaultDayForCalendar).millisecond(0).second(0).minute(calendarStartDefaultTimeInMins).hour(calendarStartDefaultTimeInHour).format('YYYY-MM-DD HH:mm');
                var minDateOfEndPartialCalPickerWithDiff = moment(defaultDateOfStartPartialCalPicker).add(minToAddForCalendar, 'm').toDate();
                var minDateOfEndPartialCalPicker = new Date(minDateOfEndPartialCalPickerWithDiff);
                $('#partialtimepicker2').datetimepicker({
                    useCurrent: false,
                    format: 'hh:mm A',
                    stepping: 10,
                    maxDate: moment().year(shiftStartMaxYearForCalendar).month(shiftStartMaxMonthForCalendar-1).date(shiftStartMaxDayForCalendar).millisecond(0).second(0).minute(calendarStartMaxTimeInMins).hour(calendarStartMaxTimeInHour).format('YYYY-MM-DD HH:mm'),
                    minDate: moment().year(shiftStartMinYearForCalendar).month(shiftStartMinMonthForCalendar-1).date(shiftStartMinDayForCalendar).millisecond(0).second(0).minute(calendarStartMinTimeInMins).hour(calendarStartMinTimeInHour).format('YYYY-MM-DD HH:mm'),
                    date: defaultDateOfStartPartialCalPicker
                });
                
                $('#partialtimepicker3').datetimepicker({
                    useCurrent: false,
                    format: 'hh:mm A',
                    stepping: 10,
                    minDate: minDateOfEndPartialCalPicker,
                    maxDate: moment().year(shiftEndMaxYearForCalendar).month(shiftEndMaxMonthForCalendar-1).date(shiftEndMaxDayForCalendar).millisecond(0).second(0).minute(calendarEndMaxTimeInMins).hour(calendarEndMaxTimeInHour).format('YYYY-MM-DD HH:mm'),
                    date: moment().year(shiftEndDefaultYearForCalendar).month(shiftEndDefaultMonthForCalendar-1).date(shiftEndDefaultDayForCalendar).millisecond(0).second(0).minute(calendarEndDefaultTimeInMins).hour(calendarEndDefaultTimeInHour).format('YYYY-MM-DD HH:mm')
               });
               
               
               $("#partialtimepicker2").on("dp.change", function (e) {
                   $('#partialShiftEndTime').val(''); 
                   var selectedDate = new Date(e.date);
                   var minimumDate = moment(selectedDate).add(minToAddForCalendar, 'm').toDate();
                   var newMinimumDateime = new Date(minimumDate);
                  $('#partialtimepicker3').data("DateTimePicker").minDate(newMinimumDateime);
                  $('#partialtimepicker3').data("DateTimePicker").date(newMinimumDateime);
                  
                   
               });
            </script>
            @endif
            <script>
            $(function() {
                $('#datetimepicker1,#datetimepicker2,#datetimepicker3').datetimepicker({
                            format: 'hh:mm A',
                            stepping: 30
                });
                
                
                $('#timeOfCallMadePicker').datetimepicker({
                            format: 'YYYY-MM-DD hh:mm A',
                            stepping: 30
                });
                
                
                $('#staffingStartDatePicker').datetimepicker({
                            format: 'YYYY-MM-DD',
                           minDate: moment().add(0, 'h')
                });
                
                
//                $('#staffingEndDatePicker').datetimepicker({
//                            format: 'YYYY-MM-DD',
//                            useCurrent: false, //Important!
//                           //minDate: moment().add(0, 'h')
//                });
                
                
//                $("#staffingStartDatePicker").on("dp.change", function (e) {
//                   // $('#staffingEndDatePicker').data("DateTimePicker").minDate(e.date);
//                   var selectedDate = new Date(e.date);
//                   selectedDate.setDate(selectedDate.getDate() + 1);
//                   $('#staffingEndDatePicker').data("DateTimePicker").maxDate(selectedDate).enabledDates([e.date,selectedDate]);
//                   $('#staffingEndDatePicker').data("DateTimePicker").minDate(e.date).enabledDates([e.date,selectedDate]);
//                   $('#staffingEndDate').val('');
//                });
                
                
//                $("#staffingEndDatePicker").on("dp.change", function (e) {
//                    //$('#staffingStartDatePicker').data("DateTimePicker").maxDate(e.date);
//                    //alert(e.date);
//                    //$('#staffingStartDatePicker').data("DateTimePicker").minDate(e.date).maxDate(e.date).enabledDates([e.date]);
//                });
                
                
            });

            </script>
        
        @endif



        <script>
            jQuery(function () {
                // Init page helpers (Select2 plugin)
                Codebase.helpers(['select2']);
            });
        </script>
        
        
        @if(Request::is(Config::get('constants.urlVar.editGroup').'*') || 
    Request::path().'/' == Config::get('constants.urlVar.editProfile'))
        <script src="{!! asset('/assets/js/cropping/jquery.cropit.js') !!}"></script>        
        <script src="{!! asset('/assets/js/picture-cropping-tool.js') !!}"></script>
    @endif    
        
        <script src="{!! asset('/assets/js/staffing-call-vali.js') !!}"></script>
        
<!--Custom Javascript Code-->
@endif

@if(Request::path().'/' == Config::get('constants.urlVar.addNewUser') ||  
    Request::is(Config::get('constants.urlVar.editUser').'*'))

<script src="{!! asset('/assets/js/add-new-user-unit-setup.js') !!}"></script>
@endif

@if(Request::path().'/' == Config::get('constants.urlVar.addNewStaffingRequest'))

<script src="{!! asset('/assets/js/staffing-new-request-form-setting.js') !!}"></script>
@endif


@if(Request::is(Config::get('constants.urlVar.staffProfileDetail').'*'))
<script src="{!! asset('/assets/js/chart.js') !!}"></script>
<script src="{!! asset('/assets/js/bar-chart.js') !!}"></script>
@endif


<!-- Ajax Paging For Home Page for Requests Call-->
<script src="{!! asset('/assets/js/ajax-paging.js') !!}"></script>
<!-- Ajax Paging For Home Page for Requests Call-->



@if(Request::is(Config::get('constants.urlVar.editUnit').'*') || 
    Request::path().'/' == Config::get('constants.urlVar.addNewUnit'))
    <script src="{!! asset('/assets/js/AlgorithmReOrder/jquery-ui.js') !!}"></script> 
    <script src="{!! asset('/assets/js/staffing-call-algorithm-reorder.js') !!}"></script>
@endif

@if(Request::is(Config::get('constants.urlVar.editPage').'*') || 
Request::is(Config::get('constants.urlVar.editAlgorithm').'*'))

<script src="{!! asset('/assets/js/plugins/ckeditor/ckeditor.js') !!}"></script>
<script>
            jQuery(function () {
                // Init page helpers (CKEditor + SimpleMDE plugins)
                Codebase.helpers(['ckeditor']);
            });
        </script>
@endif
<style type="text/css" >
.img_style{
    margin: 5px;
    border:3px solid #3f89e5;
    border-radius: 500px;
    -webkit-border-radius: 500px;
    -moz-border-radius: 500px;
}
    
</style>

