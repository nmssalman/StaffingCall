@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add New Shift</h3>
                
            </div>
            
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
         
            
            
            <div class="block-content">
                <div class="row justify-content-center py-20">
                    <div class="col-xl-10">
                        <form class="js-shift-setup-form-validation-bootstrap" action="{!! url(Config('constants.urlVar.saveShiftSetUp')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                            
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="groupName">Business Group</label>
                                <div class="col-lg-8">
                                   @php echo $groups->groupName." (".$groups->groupCode." )" @endphp
                                </div>
                            </div>
                            
                            
                            <div class="form-group row{!! $errors->has('businessUnitID') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="businessUnitID">Business Unit <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="businessUnitID" name="businessUnitID" style="width: 100%;" data-placeholder="Choose Business Unit">
                                                    <option value=""></option>
                                                    <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                                     @foreach($units as $unit)
                                                    <option value="{!! $unit->id !!}">{!! $unit->unitName !!}</option>
                                                    @endforeach
                                                </select>
                                    

                                     @if ($errors->has('businessUnitID'))
                                    <div id="businessUnitID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitID') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                             <div class="form-group row{!! ($errors->has('startTime') || $errors->has('endTime')) ? ' is-invalid' : '' !!}">
                            <label class="col-lg-4 col-form-label" for="timeOfShift">Time of Shift <span class="text-danger">*</span></label>
                            
                            
                             <div class="col-md-3">
                                
                                <div class="input-group date" id="datetimepicker1" required>
                                    <input type="text" value="{!! old('startTime') !!}" class="form-control" id="startTime" name="startTime" placeholder="Start Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('startTime'))
                                <div id="startTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('startTime') !!}</div>
                                @endif
                            </div>

                            <div class="col-md-3">
                                   
                                <div class="input-group date" id="datetimepicker2" required>
                                    <input type="text" value="{!! old('endTime') !!}" class="form-control" id="timeOfCall" name="endTime" placeholder="End Time">
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                                     </span>
                                    </div>

                                 @if ($errors->has('endTime'))
                                <div id="endTime-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('endTime') !!}</div>
                                @endif
                            </div>
                            
                            
                        </div>
                                
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    
                                    <button type="submit" class="btn btn-alt-primary">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>
@endsection