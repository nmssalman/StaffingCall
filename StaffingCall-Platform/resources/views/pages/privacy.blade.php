@extends('layouts.app')
@section('content')
<div class="content">
    <h2 class="content-heading">{!! $page->title !!}<small></small></h2>
    <div class="block block-bordered block-rounded mb-5" style="margin-top: 20px;">
                                    <div class="block-header" role="tab" id="faq1_h1">
                                        <a class="font-w600 text-body-color-dark" data-toggle="collapse" data-parent="#faq1" href="#faq1_q1" aria-expanded="true" aria-controls="faq1_q1">
                                            {!! $page->title !!}</a>
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
