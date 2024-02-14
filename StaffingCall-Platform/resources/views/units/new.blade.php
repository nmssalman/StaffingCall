@extends('layouts.app')
@section('content')
<div class="content">
<!-- Bootstrap Forms Validation -->
<!--        <h2 class="content-heading">Manage Groups</h2>-->
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Add New Business Unit</h3>
                
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
                        <form class="js-unit-create-validation-bootstrap" action="{!! url(Config('constants.urlVar.saveNewUnit')) !!}" method="post" enctype="multipart/form-data">
                           
                            <input type="hidden" name="_token" value="{!! csrf_token() !!}" />
                    
                            
                            
                            <div class="form-group row{!! $errors->has('unitName') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="unitName">Business Unit Name <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
                                    <input type="text" value="{!! old('unitName') !!}" class="form-control" id="unitName" name="unitName" placeholder="eg. NICU">
                                 @if ($errors->has('unitName'))
                                <div id="unitName-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('unitName') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('storeNumber') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="storeNumber">Store Number <span class="text-danger">*</span></label>
                                <div class="col-lg-8">
<!--                                    <input type="text" onkeydown="return FilterInput(event)" onpaste="handlePaste(event)" value="{!! old('storeNumber') !!}" class="form-control" id="storeNumber" name="storeNumber" placeholder="Enter Store Number">-->
                                 
                                    <input type="text" value="{!! old('storeNumber') !!}" class="form-control" id="storeNumber" name="storeNumber" placeholder="Enter Store Number">
                                 @if ($errors->has('storeNumber'))
                                <div id="storeNumber-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('storeNumber') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('information') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="information">Business Unit Information</label>
                                <div class="col-lg-8">
                                    <textarea class="form-control" id="information" name="information" placeholder="Enter Business Unit Information"></textarea>
                                 @if ($errors->has('information'))
                                <div id="information-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('information') !!}</div>
                                @endif
                                </div>
                            </div>
                            
                            <div class="form-group row{!! $errors->has('offerAlgorithmID') ? ' is-invalid' : '' !!}">
                                <label class="col-lg-4 col-form-label" for="offerAlgorithmID">Offer Algorithm <span class="text-danger">*</span></label>
                                <div class="col-md-4">
                                    
                                    <select class="js-select2 form-control" id="offerAlgorithmID" name="offerAlgorithmID" style="width: 100%;" data-placeholder="Choose Offer Algorithm..">
                                        <option value=""></option>
                                        <!-- Required for data-placeholder attribute to work with Select2 plugin -->
                                         @foreach($algorithms as $algorithm)
                                         <option itemid="{!! $algorithm->type !!}" @if(old('offerAlgorithmID') == $algorithm->id) selected @endif value="{!! $algorithm->id !!}">{!! $algorithm->name." ( ".ucfirst($algorithm->type)." )" !!}</option>
                                        @endforeach
                                    </select>
                                    <div style="position: absolute;margin-left: 93%;font-size: 18px;margin-top: -10%;cursor: pointer;">
                                    <a id="selection-pop" href="javascript:void(0);"  data-backdrop="static" data-toggle="modal" data-target="#modal-top">
                                        <i title="click for more information" class="fa fa-info-circle"></i>
                                     </a>   
                                    </div>

                                     @if ($errors->has('offerAlgorithmID'))
                                    <div id="offerAlgorithmID-error" class="invalid-feedback animated fadeInDown">{!! $errors->first('businessUnitID') !!}</div>
                                    @endif
                                </div>
                            </div>
                            
                            <div id="complexOrderDisplay" style="display:none;">
                                <div class="form-group row">
                                    <label class="col-lg-4 col-form-label" for="complexOfferAlgorithmOrder">Set Pool Ordering <span class="text-danger">*</span></label>
                                    <div class="col-md-8">
                                    <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full" id="algorithmTable">
                                        <thead>


                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                        @foreach($complexOrders as $complexOrder)
                                            <?php $defaultOrder[] = $complexOrder->id; ?>
                                        @if($i == 1)
                                        <tr style="cursor: not-allowed;color: gray;" id="{!! $complexOrder->id !!}">
                                             
                                            <td colspan="2">{!! $complexOrder->name !!}</td>
                                         </tr>
                                        @else
                                        <tr style="cursor: move;" id="{!! $complexOrder->id !!}">
                                            <td style="width:10px;">
                                                <i style="color:#b5b5b5;" class="fa fa-arrows"></i> </td>
                                             <td>{!! $complexOrder->name !!}</td>
                                         </tr>
                                         @endif
                                        <?php $defaultOrdering = implode(",", $defaultOrder); ?>
                                         @php $i++; @endphp
                                        @endforeach

                                        </tbody>
                                    </table>
                                    </div>
                                    </div>

                                </div>
                            </div>
                            
                            
                          
                            
                            <div class="form-group row">
                                <div class="col-lg-8 ml-auto">
                                    <input value="{!! $defaultOrdering !!}" type="hidden" name="complexOrder" id="complexOrder" />
                                    <button type="submit" class="btn btn-alt-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
                    <!-- Bootstrap Forms Validation -->
</div>

<!-- Date selection Alert Popup -->
<div class="modal fade" id="modal-top" tabindex="-1" role="dialog" aria-labelledby="modal-top" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top" role="document">
                <div class="modal-content" style="width:760px;margin-top: 5%;">
                    
                    <div class="block block-themed block-transparent mb-0" style="max-height: 500px;overflow: auto;background-color: #343a40;">
                        <div class="block-header bg-primary-dark">
                            
                            <div class="block-options" style="background-color: #343a40;">
                                <h3 style="color:#fff;">Offer Algorithm Rules</h3>
                                @foreach($algorithms as $algorithm)
                                
                                    {!! $algorithm->name." ( ".ucfirst($algorithm->type)." Algorithm )" !!} 
                                
                                    <div class="block-content border-t">
                                        <p>
                                            {!! $algorithm->notes !!}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="block-content">
                             
                            
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="cancelBtn" style="cursor: pointer;" id="close-pop" type="button" class="btn btn-alt-secondary" data-dismiss="modal">Close</button>
                     </div>
                    
                </div>
            </div>
        </div><!-- Date selection Popup -->

<script type="text/javascript">
//    function FilterInput(event) {
//    var keyCode = ('which' in event) ? event.which : event.keyCode;
//
//    isNotWanted = (keyCode == 69 || keyCode == 101 || keyCode == 190 || keyCode == 110);
//    return !isNotWanted;
//};
//function handlePaste (e) {
//    var clipboardData, pastedData;
//
//    // Get pasted data via clipboard API
//    clipboardData = e.clipboardData || window.clipboardData;
//    pastedData = clipboardData.getData('Text').toUpperCase();
//
//    if(pastedData.indexOf('E')>-1) {
//        //alert('found an E');
//        e.stopPropagation();
//        e.preventDefault();
//    }
//};

var position = '{!! $defaultOrdering !!}';

</script>

@endsection