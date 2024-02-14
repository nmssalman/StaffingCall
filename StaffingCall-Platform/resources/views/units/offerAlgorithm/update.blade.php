@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Update Offer Algorithm</h3>
                
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
                        <form class="js-vacancy-reasons-form-validation-bootstrap" action="{!! url(Config('constants.urlVar.updateAlgorithm')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                            
                            <div class="form-group row">
                                <label class="col-lg-4 col-form-label" for="groupName">Business Group</label>
                                <div class="col-lg-8">
                                   @php echo $groups->groupName." (".$groups->groupCode." )" @endphp
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('name') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="name">Algorithm Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! $algorithms->name !!}" class="form-control" id="name" name="name" placeholder="Enter Offer Algorithm Name..">
                                 @if ($errors->has('name'))
                                <div id="name-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('name') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('notes') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="name">Description </label>
                                <div class="col-lg-8">
                                    <textarea rows="10" class="form-control" id="js-ckeditor" name="notes" placeholder="Enter Some description about algorithm..">
                                    {!! $algorithms->notes !!}
                                    </textarea>
                                 @if ($errors->has('notes'))
                                <div id="notes-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('notes') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <input type="hidden" name="id" value="{!! $algorithms->id !!}" />
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Update</button>
                                    <button onclick="javascript:location.href='{!! url(Config('constants.urlVar.algorithmList')) !!}'" style="cursor: pointer;" type="button" class="btn btn-alt-danger">Cancel</button>
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