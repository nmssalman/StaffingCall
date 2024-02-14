<div class="row">
            
            @if($requestPosts->count())
            
            @foreach($requestPosts as $requestPost)
            <div class="col-lg-12">
                <!-- Simple Rating -->
                <div class="block" style="border: 1px solid lightgray;">
                    <div class="block-header block-header-default" style="background-color: #343a40;
    text-align: center;
    color: #fff;">
                        <h3 class="block-title" style="font-weight: bold;font-size: 22px;color: #fff;">
                            
                             {!! date("l, F d, Y",strtotime($requestPost->staffingStartDate)) !!}
                            
                            <small style="color: #fff;"><br />Owner: {!! $requestPost->staffOwner !!}</small>
                        
                        </h3>
                    </div>
                    <div class="block-content block-content-full" style="padding-bottom: 0;">
                    
                        <!-- Get Responde People-->
                     @php $respondedPeopleVar = myHelper::getCountOfRespondedPeople($requestPost->postID);  
                     @endphp

                    <!--Get Responde People-->
                        
                        <div class="row items-push text-center">
                            
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['totalResponded'] !!}</h2>
                            </div>
                            
                            <strong style="color: #2e3238;">Number of Response</strong>
                        </div>

                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600">
                                <h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['fullResponded'] !!}</h2>
                            </div>
                            <strong style="color: #2e3238;">Full Shift</strong>
                        </div>
                        <div class="col-6 col-md-6 col-xl-4">
                            <div class="font-w600"><h2 style="color: #42a5f5 !important;">{!! $respondedPeopleVar['partialResponded'] !!}</h2></div>
                            <strong style="color: #2e3238;">Partial Shift
                            </strong>
                        </div>
                        
                    </div>
                        
                    
                        <p><strong>Type of Staff: </strong>
                            {!! myHelper::getRequiredSkills($requestPost->requiredStaffCategoryID) !!}
                            </p>
                        @if($requestPost->notes)
                        <p><strong>Description: </strong> {!! $requestPost->notes !!}</p>
                     @endif
                        
                         <p>
                            <button onclick="location.href = '{!! url(Config('constants.urlVar.staffingPostDetail').$requestPost->postID) !!}'" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">
                                View Detail
                            </button>
                        </p>
                    
                    </div>
                </div>
                <!-- END Simple Rating -->
            </div>
            
            @endforeach
            
            @else
            <p>No Active Requests found</p>
            @endif
            
            
        </div>
                               
          <div class="row">

            <div class="col-sm-12 col-md-5">
                @if($totalOpenRequestsCount > 0)
                <div class="dataTables_paginate paging_simple_numbers" id="datatable_paginate">
                    <ul class="pagination">
                        <li class="paginate_button page-item <?php if(($activePage-1) == 0){ ?>disabled <?php } ?> " id="datatable_previous">
                            <a onclick="performPaging('<?= $activePage-1 ?>')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="0" tabindex="0" class="page-link">
                                Previous
                            </a>
                        </li>

                        <?php $totalData = $totalOpenRequestsCount;
                             if($totalData % 2 > 0){
                                  $totalPage = (int)($totalData / 2) + 1;
                             }else{
                                 $totalPage = (int)($totalData / 2);
                             }
                        ?>
                        @for($pg = 1;$pg <= $totalPage;$pg++)
                        <li class="paginate_button page-item <?php if($pg == $activePage){ ?>active <?php } ?>">
                            <a onclick="performPaging('<?= $pg ?>')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="<?= $pg ?>" tabindex="0" class="page-link">
                                <?= $pg ?>
                            </a>
                        </li>
                        @endfor
                        <li class="paginate_button page-item next <?php if($totalPage == $activePage){ ?>disabled <?php } ?>" id="datatable_next">
                            <a onclick="performPaging('<?= $activePage+1 ?>')" href="javascript:void(0);" aria-controls="datatable" data-dt-idx="2" tabindex="0" class="page-link">
                                Next
                            </a>
                        </li>
                    </ul>
                </div>
                @else
                <div class="font-size-h3 font-w600 py-30 mb-20 text-center border-b">
                  <span class="text-primary font-w700">No Records</span> Found 
              </div>
                @endif
            </div>
        </div>               