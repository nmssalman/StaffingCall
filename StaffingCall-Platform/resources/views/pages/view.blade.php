@extends('layouts.app')
@section('content')
<div class="content">
    <h2 class="content-heading">{!! $page->title !!}<small></small></h2>
    
    
            
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
    
    <div class="block block-bordered block-rounded mb-5" style="margin-top: 20px;">
        <div class="block-header" role="tab" id="faq1_h1">
            
            @if(Auth::user()->role == 2 || Auth::user()->role == 1)
            
            <a class="font-w600 text-body-color-dark" title="Edit page" 
               href="{!! url(Config::get('constants.urlVar.editPage').$page->id) !!}" >   
        {!! $page->title !!} <i class="fa fa-edit"> Edit </i>   
        </a>
            @else
             <a class="font-w600 text-body-color-dark" title="Edit page" 
               href="#" >   
        {!! $page->title !!} 
        </a>
            @endif
        </div>
        <div id="faq1_q1" class="collapse show" role="tabpanel" aria-labelledby="faq1_h1">
            <div class="block-content border-t">
                <p>
                    {!! $page->content !!}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
