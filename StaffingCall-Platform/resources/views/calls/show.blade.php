@extends('layouts.app')
@section('content')
<div class="content">
<h2 class="content-heading">Group Business Units</h2>


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
                            <h3 class="block-title">Business Units <small>List</small></h3>
                        </div>
                        <div class="block-content block-content-full">
                            <!-- DataTables init on table by adding .js-dataTable-full class, functionality initialized in js/pages/be_tables_datatables.js -->
                            
                            <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="datatable">
                                <thead>
                                    <th>Business Unit Name</th>
                                    <th >Store Number</th>
                                    <th >Business Unit Information</th>
                                    <th >Action</th>
                                </thead>
                                
                                
                                
                            </table>
                            </div>
                        </div>
                    </div>
                    <!-- END Dynamic Table Full -->
</div>
<script type="text/javascript" >
var dataUrl = "{!! url(Config('constants.urlVar.ajaxUnitList')) !!}";
</script>
<!-- container -->
@endsection