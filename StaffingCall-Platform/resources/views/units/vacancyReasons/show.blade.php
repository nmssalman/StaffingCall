@extends('layouts.app')
@section('content')
<div class="content">
<h2 class="content-heading">Staffing Vacancy Reasons</h2>


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

                    <!-- Dynamic Table Full -->
                    <div class="block">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Reasons <small></small>
                            </h3>
                        </div>
                        <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.addNewVacancyReason')) !!}'" style="margin-left: 16px;cursor: pointer;" class="btn btn-sm btn-info mb-10">Add New</button>
<button href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top" style="margin-left: 16px;cursor: pointer;" class="btn btn-sm btn-default mb-10">Choose from default</button>
                        <div class="block-content block-content-full">
                            <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/be_tables_datatables.js -->
                            
                            <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="datatable">
                                <thead>
                                    <th>Vacancy Reason</th>
                                    <th>Action</th>
                                </thead>
                                
                                
                                
                            </table>
                            </div>
                        </div>
                    </div>
                    <!-- END Dynamic Table Full -->
</div>

<!--Deafault Vacancy Reasons-->
<!-- Date selection Alert Popup -->
<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
    <div class="modal-dialog modal-dialog-top" role="document">
        <div class="modal-content" style="width:760px;margin-top: 5%;">

            <div class="block block-themed block-transparent mb-0" style="max-height: 500px;overflow: auto;">
                <div class="block-header bg-primary-dark">

                    <div class="block-options">
                        <h3 style="color:#fff;">Default Vacany Reasons</h3>

                    </div>
                </div>
                <div class="block-content">

                    <button onclick="addAllReasons()" style="cursor: pointer;" class="btn btn-sm btn-danger mb-10">
                        Add Selected</button>

                    <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full">
                        <thead>
                        <th><input onclick="chkAll(this)" type="checkbox" id="multipleCheckBox"/> </th>
                        <th>Reason Name</th>
                        <th>Action</th>
                        </thead>
                        <tbody id="checkboxes">

                            @foreach($defaultReasons as $defaultReason)

                            <tr>
                                <td>
                                    <input type="checkbox" id="defaultVacancyIDs_{!! $defaultReason->id !!}" name="defaultVacancyIDs[]" value="{!! $defaultReason->id !!}"/> 
                                </td>
                                <td>{!! $defaultReason->reasonName !!}</td>
                                <td>
                                    <button style="cursor: pointer;" class="btn btn-sm btn-success mb-10" onclick="addSingle('{!! $defaultReason->id !!}')">
            Add</button></td>
                            </tr>
                            @endforeach

                        </tbody>
                    </table> 
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Close</button>
             </div>

        </div>
    </div>
</div>
<!-- Date selection Popup -->

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



<script type="text/javascript" >
var dataUrl = "{!! url(Config('constants.urlVar.ajaxVacancyReasonsList')) !!}";

function addSingle(checkBoxID){
    $('#defaultVacancyIDs_'+checkBoxID).attr('checked', true);
    addAllReasons();
}

function addAllReasons(){
    var selected = [];
    $('#checkboxes input:checked').each(function() {
        selected.push($(this).attr('value'));
    });
    if(selected.length > 0){  
        $('#loadingDiv').show();
       var requestUrl = '{!! url(Config('constants.urlVar.saveDefaultVacancyReasons')) !!}';
        $.ajax({
            url:requestUrl,
            type:'GET',
            data:{'defaultVacancyIDs':selected},
            success:function(result){
                console.log(result);
                //$('#loadingDiv').hide();
                location.href = '{!! url(Config('constants.urlVar.vacancyReasonsList')) !!}';
               //console.log(result);
            },error:function(er){
                console.log(er.responseText);
            }

        });
    }else{
        alert("Please select reason first.");
    }
}

function chkAll(ele){
   var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     } 
}

</script>
<!-- container -->
@endsection