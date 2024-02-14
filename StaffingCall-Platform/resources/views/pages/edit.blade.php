@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Update Page</h3>
                
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
                    <div class="col-xl-12">
                        <form class="js-page-validation-bootstrap" action="{!! url(Config('constants.urlVar.updatePage')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            <fieldset><legend>Page Information</legend>
                            <div class="form-group row{!! $errors->has('title') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-2 col-form-label" for="title">Title 
                                    <span class="text-danger">*</span></label>
                                <div class="col-lg-10">
                                    <input type="text" value="{!! $page->title !!}" class="form-control" id="title" name="title" placeholder="Page title">
                                 @if ($errors->has('title'))
                                <div id="title-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('title') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            <div class="form-group row{!! $errors->has('content') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-2 col-form-label" for="groupName">Content <span class="text-danger">*</span></label>
                                <div class="col-lg-10">
                                    <textarea name="content" id="js-ckeditor">{!! $page->content !!}</textarea>
                                 @if ($errors->has('content'))
                                <div id="content-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('content') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            
                            
                            </fieldset>
                           
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <input type="hidden" name="id" value="{!! $page->id !!}" />
                                    <button style="cursor: pointer;" type="submit" class="btn btn-alt-primary">Update</button>
                                    
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