@extends('layouts.app')
@section('content')
<div class="content">
   

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

        
<h2 class="content-heading">
    Staffing Call<small> Total Groups ({!! $totalGroupsCount !!})</small> 
</h2>



<div class="" id="all-groups">

<!--        Business Units-->

        <div class="row">
            
            @if($allGroups->count())
            
            @foreach($allGroups as $allGroup)
            
            <div class="col-lg-12">
                <!-- Simple Rating -->
                <div class="block" style="border: 2px solid lightgray;">
                    <div class="block-header block-header-default" style="background-color: #343a40;
    text-align: center;
    color: #fff;">
                        <h3 class="block-title" style="font-weight: bold;font-size: 22px;">
                            
                            
                            <a style="color: #fff;"  href="{!! url(Config('constants.urlVar.groupDetail').$allGroup->id) !!}">
                                {!! $allGroup->groupName." (".$allGroup->groupCode.")" !!} 
                          
                </a>  
                        </h3>
                    </div>
                    <div class="block-content block-content-full">
                    
                    <!-- Get Responde People-->
                     <?php 
                 $groupInfo = myHelper::getGroupInformation($allGroup->id);  
                     ?>

                    <!--Get Responde People-->
                    
                    <div class="row items-push text-center">
                            
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! $groupInfo['openPostingCount'] !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Open Requests</strong>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $groupInfo['pastPostingCount'] !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Past Requests</strong>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $groupInfo['totalUsers'] !!}</h2></div>
                            <strong style="color: #2e3238;">Number of Staff
                            </strong>
                        </div>
                        
                    </div>
                        
                        
                        
                       
<!--                        <p>
                            <button onclick="javascript:location.href = '{!! url(Config('constants.urlVar.groupDetail').$allGroup->id) !!}';" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                View Detail
                            </button>
                        </p>-->
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach
            
            @else
            <p>No Business Groups found</p>
            @endif
            
            
        </div>
<!--        Business Groups-->
   

  <div class="row">

            <div class="col-sm-12 col-md-5">
                @if($totalGroupsCount > 0)
                <div class="dataTables_paginate paging_simple_numbers" id="datatable_paginate">
                    <ul class="pagination">
                        <li class="paginate_button page-item previous disabled" id="datatable_previous">
                            <a onclick="performPagingForGroups('0')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="0" tabindex="0" class="page-link">
                                Previous
                            </a>
                        </li>

                        <?php $totalData = $totalGroupsCount;
                             if($totalData % 4 > 0){
                                  $totalPage = (int)($totalData / 4) + 1;
                             }else{
                                 $totalPage = (int)($totalData / 4);
                             }
                        ?>
                        @for($pg = 1;$pg <= $totalPage;$pg++)
                        <li class="paginate_button page-item <?php if($pg == 1){ ?>active <?php } ?>">
                            <a onclick="performPagingForGroups('<?= $pg ?>')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="<?= $pg ?>" tabindex="0" class="page-link">
                                <?= $pg ?>
                            </a>
                        </li>
                        @endfor
                        <li class="paginate_button page-item next <?php if($totalPage == 1){ ?>disabled <?php } ?>" id="datatable_next">
                            <a onclick="performPagingForGroups('2')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="2" tabindex="0" class="page-link">
                                Next
                            </a>
                        </li>
                    </ul>
                </div>
                @else
                <div class="font-size-h3 font-w600 py-30 mb-20 text-center border-b">
                  <span class="text-primary font-w700">No Groups </span>found 
              </div>
                @endif
            </div>
        </div>  
</div>


</div>
<!-- container -->


    <!--Background Loader-->  
    <div id="loadingDiv" style="display: none;">
        <div style="margin-left: 49%;
    margin-top: 13%;">
            <h3 style="color: #4d9aba;">loading ...</h3>
        </div>
    </div>
    <style type="text/css">
        #loadingDiv{
  position:fixed;
  top:0px;
  right:0px;
  width:100%;
  height:100%;
  background-color:#666;
  background-image:url('ajax-loader.gif');
  background-repeat:no-repeat;
  background-position:center;
  z-index:10000000;
  opacity: 0.4;
  filter: alpha(opacity=40); /* For IE8 and earlier */
}
        </style>
    <!--Background Loader-->

<script type="text/javascript">
     
      /* Ajax Paging Business Unit List */ 
    var requestPagingForGroupsUrl = '{!! url(Config('constants.urlVar.ajaxGroupsHomeList')) !!}';
    /* Ajax Paging Business Unit List */  
    </script>

@endsection