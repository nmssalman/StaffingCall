@extends('layouts.app')
@section('content')
<div class="content">
<h2 class="content-heading">Shift SetUp for Business Unit</h2>


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
                            <h3 class="block-title">Business Unit Shift Setup <small></small>
                            </h3>
                        </div>
                        <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.addNewShiftSetUp')) !!}'" style="margin-left: 16px;cursor: pointer;" class="btn btn-sm btn-info mb-10">Add New</button>
                        <div class="block-content block-content-full">
                            <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/be_tables_datatables.js -->
                            
                            <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="datatable">
                                <thead>
                                    <th>Shift Time</th>
                                    <th>Business Unit</th>
                                    <th>Action</th>
                                </thead>
                                
                                
                                
                            </table>
                            </div>
                        </div>
                    </div>
                    <!-- END Dynamic Table Full -->
</div>
<script type="text/javascript" >
var dataUrl = "{!! url(Config('constants.urlVar.ajaxUnitShiftSetUpList')) !!}";
</script>
<!-- container -->
@endsection