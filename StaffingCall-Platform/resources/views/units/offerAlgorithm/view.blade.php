@extends('layouts.app')
@section('content')
<div class="content">
    <h2 class="content-heading">Offer Algorithm Detail<br><small> 
           <a href="{!! url(Config::get('constants.urlVar.algorithmList')) !!}" style="font-size: 12px !important;">
               < Back to Offer Algorithm List</a>
       
       </small></h2>
    
    
            
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
            
            
        {!! $algorithm->name." ( ".ucfirst($algorithm->type)." Algorithm )" !!} 
        
        </div>
        <div id="faq1_q1" class="collapse show" role="tabpanel" aria-labelledby="faq1_h1">
            <div class="block-content border-t">
                <p>
                    {!! $algorithm->notes !!}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
