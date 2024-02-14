@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Update Vacancy Reason</h3>
                
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
                        <form class="js-vacancy-reasons-form-validation-bootstrap" action="{!! url(Config('constants.urlVar.updateVacancyReason')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                            
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="groupName">Business Group</label>
                                <div class="col-lg-8">
                                   @php echo $groups->groupName." (".$groups->groupCode." )" @endphp
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('reasonName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="reasonName">Request Reason <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $vacancyReason->reasonName !!}" class="form-control" id="reasonName" name="reasonName" placeholder="Enter Reason of request..">
                                 @if ($errors->has('reasonName'))
                                <div id="reasonName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('reasonName') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <input type="hidden" name="id" value="{!! $vacancyReason->id !!}" />
                                    <button type="submit" class="btn btn-alt-primary">Update</button>
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