 <nav id="sidebar">
    <!-- Sidebar Scroll Container -->
    <div id="sidebar-scroll">
        <!-- Sidebar Content -->
        <div class="sidebar-content">
            <!-- Side Header -->
            <div class="content-header content-header-fullrow px-15">
                <!-- Mini Mode -->
                <div class="content-header-section sidebar-mini-visible-b">
                    <!-- Logo -->
                    <span class="content-header-item font-w700 font-size-xl float-left animated fadeIn">
                        <span class="text-dual-primary-dark">c</span><span class="text-primary">b</span>
                    </span>
                    <!-- END Logo -->
                </div>
                <!-- END Mini Mode -->

                <!-- Normal Mode -->
                <div class="content-header-section text-center align-parent sidebar-mini-hidden">
                    <!-- Close Sidebar, Visible only on mobile screens -->
                    <!-- Layout API, functionality initialized in Codebase() -> uiApiLayout() -->
                    <button type="button" class="btn btn-circle btn-dual-secondary d-lg-none align-v-r" data-toggle="layout" data-action="sidebar_close">
                        <i class="fa fa-times text-danger"></i>
                    </button>
                    <!-- END Close Sidebar -->

                    <!-- Logo -->
                    <div class="content-header-item">
                        <a class="link-effect font-w700" href="{!! url('/') !!}">
                           
                        <span class="font-size-xl text-primary-dark">
                                    <img width="90" src="{!! url('/assets/img/logo.png') !!}" />
                                </span>
                        </a>
                    </div>
                    <!-- END Logo -->
                </div>
                <!-- END Normal Mode -->
            </div>
            <!-- END Side Header -->

            <!-- Side User -->
            <div class="content-side content-side-full content-side-user px-10 align-parent">
                <!-- Visible only in mini mode -->
                <div class="sidebar-mini-visible-b align-v animated fadeIn">
                    <img class="img-avatar img-avatar32" src="@if(Auth::user()->profilePic){!! url('public/'.Auth::user()->profilePic) !!}@else {!! url('/assets/img/avatars/avatar15.jpg') !!} @endif" alt="">
                </div>
                <!-- END Visible only in mini mode -->

                <!-- Visible only in normal mode -->
                <div class="sidebar-mini-hidden-b text-center">
                    <a class="img-link" href="{!! url('/')!!}">
                        <img class="img-avatar" src="@if(Auth::user()->profilePic){!! url('public/'.Auth::user()->profilePic) !!}@else {!! url('/assets/img/avatars/avatar15.jpg') !!} @endif" alt="">
                    </a>
                    <ul class="list-inline mt-10">
                        <li class="list-inline-item">
                            <a class="link-effect text-dual-primary-dark font-size-xs font-w600 text-uppercase" href="#">{!! Auth::user()->firstName." ".Auth::user()->lastName !!}</a>
                        </li>
                        
                    </ul>
                </div>
                <!-- END Visible only in normal mode -->
            </div>
            <!-- END Side User -->

            <!-- Side Navigation -->
            <div class="content-side content-side-full">
                <ul class="nav-main">
                    
                    @if(Auth::user()->role == '1' || Auth::user()->role == '2')
                    
                    <li>
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.home') || 
                         Request::path().'/' == Config::get('constants.urlVar.userCalendarView') || 
                         Request::path() == '/') ? 'active' : '' !!}" href="{!! url('/')!!}"><i class="si si-home"></i><span class="sidebar-mini-hide">Home</span></a>
                    </li>
                    
                    @else
                        @if(Auth::user()->calendarView == '1')
                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.home') || 
                             Request::path().'/' == Config::get('constants.urlVar.userCalendarView') || 
                             Request::path() == '/') ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.userCalendarView'))!!}"><i class="si si-home"></i><span class="sidebar-mini-hide">Home</span></a>
                        </li>
                        @else
                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.home') || 
                             Request::path().'/' == Config::get('constants.urlVar.userCalendarView') || 
                             Request::path() == '/') ? 'active' : '' !!}" href="{!! url('/')!!}"><i class="si si-home"></i><span class="sidebar-mini-hide">Home</span></a>
                        </li>
                        @endif
                    @endif
                    
                    <li class="nav-main-heading"><span class="sidebar-mini-visible">SI</span><span class="sidebar-mini-hidden">Staffing Call Interface</span></li>
                  
                 
                    @if(Auth::user()->role == '1')
                    
                    
                    <li>
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.addNewGroup') || 
                         Request::path().'/' == Config::get('constants.urlVar.groupList')) ? 'active' : '' !!}" href="{!! url(Config('constants.urlVar.groupList'))!!}">
                            <i class="si si-globe"></i><span class="sidebar-mini-hide">Group Management</span>
                        </a>
                    </li>
                                       
                    @endif
                    
                    
                 
                    @if(Auth::user()->role == '2')
                    
                    <li class="{!! (Request::path().'/' == Config::get('constants.urlVar.addNewUnit') || 
                     Request::path().'/' == Config::get('constants.urlVar.managerGroupList') || 
                     Request::path().'/' == Config::get('constants.urlVar.unitList') || 
                     Request::is(Config::get('constants.urlVar.editGroup').'*') ||  
                 Request::is(Config::get('constants.urlVar.editUnit').'*')) ? 'open' : '' !!}">
                        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="fa fa-globe fa-2x"></i><span class="sidebar-mini-hide">Group Management</span></a>
                        <ul>
                           
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.managerGroupList') ||
                                  Request::is(Config::get('constants.urlVar.editGroup').'*')) ? 'active' : '' !!}" href="{!! url(Config('constants.urlVar.managerGroupList'))!!}">Business Group</a>
                            </li>
                           
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.unitList') || 
                                 Request::path().'/' == Config::get('constants.urlVar.addNewUnit') ||  
                 Request::is(Config::get('constants.urlVar.editUnit').'*')) ? 'active' : '' !!}" href="{!! url(Config('constants.urlVar.unitList'))!!}">Business Units</a>
                            </li>
                           
                            
                        </ul>
                    </li>
                   @endif  
                   
                   
                 @if(Auth::user()->role == '3' || Auth::user()->role == '2')   
                <!-- Business Unit Management For Super Admin Only-->
            
                <li class="{!! (Request::path().'/' == Config::get('constants.urlVar.unitSkillsCategoryList') || 
                 Request::path().'/' == Config::get('constants.urlVar.addNewSkillCategory') ||  
                 Request::is(Config::get('constants.urlVar.editSkillCategory').'*') || 
                 Request::path().'/' == Config::get('constants.urlVar.requestReasonsList') || 
                 Request::path().'/' == Config::get('constants.urlVar.addNewRequestReason') || 
                 Request::is(Config::get('constants.urlVar.editRequestReason').'*') || 
                 Request::path().'/' == Config::get('constants.urlVar.vacancyReasonsList') ||  
                 Request::path().'/' == Config::get('constants.urlVar.defaultVacancyReasonsList') ||
                 Request::path().'/' == Config::get('constants.urlVar.addNewVacancyReason') ||
                 Request::is(Config::get('constants.urlVar.editVacancyReason').'*') ||
                 Request::path().'/' == Config::get('constants.urlVar.unitShiftSetUpList') || 
                Request::path().'/' == Config::get('constants.urlVar.addNewShiftSetUp') || 
                Request::is(Config::get('constants.urlVar.editShiftSetUp').'*')) ? 'open' : '' !!}">
                    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
                        <i class="fa fa-universal-access fa-2x"></i>
                        <span class="sidebar-mini-hide">Business Unit Management</span>
                    </a>
                    <ul>

                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.unitSkillsCategoryList') || 
                 Request::path().'/' == Config::get('constants.urlVar.addNewSkillCategory') || 
                 Request::is(Config::get('constants.urlVar.editSkillCategory').'*')) ? 'active' : '' !!}" 
                               href="{!! url(Config('constants.urlVar.unitSkillsCategoryList')) !!}">Skill Categories</a>
                        </li>

                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.requestReasonsList') || 
                 Request::path().'/' == Config::get('constants.urlVar.addNewRequestReason') || 
                 Request::is(Config::get('constants.urlVar.editRequestReason').'*')) ? 'active' : '' !!}" 
                               href="{!! url(Config('constants.urlVar.requestReasonsList')) !!}">Request Reasons</a>
                        </li>

                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.vacancyReasonsList') || 
                 Request::path().'/' == Config::get('constants.urlVar.addNewVacancyReason') || 
                 Request::is(Config::get('constants.urlVar.editVacancyReason').'*')) ? 'active' : '' !!}" 
                               href="{!! url(Config('constants.urlVar.vacancyReasonsList')) !!}">Vacancy Reasons</a>
                        </li>

                        <li>
                            <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.unitShiftSetUpList') || 
                            Request::path().'/' == Config::get('constants.urlVar.addNewShiftSetUp') || 
                            Request::is(Config::get('constants.urlVar.editShiftSetUp').'*')) ? 'active' : '' !!}" 
                               href="{!! url(Config('constants.urlVar.unitShiftSetUpList')) !!}">Shift SetUp</a>
                        </li>

                    </ul>
                </li>
                
                <!-- Business Unit Management For Super Admin Only-->
                    
                    
             @endif      
             
             
            @if(Auth::user()->role == '2' || Auth::user()->role == '3')           
                
                
                <li>
                    <a class="{!! ((Request::path().'/' == Config::get('constants.urlVar.addNewUser')) || 
                     (Request::path().'/' == Config::get('constants.urlVar.userList')) || 
                     (Request::is(Config::get('constants.urlVar.editUser').'*'))) ? 'active' : '' !!}" href="{!! url(Config('constants.urlVar.userList'))!!}">
                        <i class="fa fa-users fa-2x"></i><span class="sidebar-mini-hide">User Management</span>
                    </a>
                </li>
                
             
              @endif  
              
                    
              @if(Auth::user()->role == '2' || Auth::user()->role == '3' || (Auth::user()->role == '4' && Session::get('defaultView') == 'admin'))           
                    
                    <li class="{!! (Request::path().'/' == Config::get('constants.urlVar.addNewStaffingRequest')) ? 'open' : '' !!}"><!--open-->
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.addNewStaffingRequest')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.addNewStaffingRequest')) !!}">
                            <i class="fa fa-plus fa-2x"></i>
                            <span class="sidebar-mini-hide">New Staffing Request</span>
                        </a>
                        
                    </li>
                    
                  @endif  
              
              @if(Auth::user()->role == '2')
                   
                    <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.algorithmList') || 
                         Request::is(Config::get('constants.urlVar.editAlgorithm').'*') || 
                         Request::is(Config::get('constants.urlVar.algorithmDetail').'*')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.algorithmList')) !!}">
                            <i class="fa fa-history fa-2x"></i>
                            <span class="sidebar-mini-hide">Offer Algorithm Setup</span>
                        </a>
                        
                    </li>
                 
                 @endif
                  
                  
              
              
                    
              @if(Auth::user()->role == '2' || Auth::user()->role == '3') 
                <li class="{!! (Request::path().'/' == Config::get('constants.urlVar.staffProfileList') || 
                     Request::is(Config::get('constants.urlVar.staffProfileDetail').'*')) ? 'open' : '' !!}"><!--open-->
                    <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.staffProfileList') || 
                     Request::is(Config::get('constants.urlVar.staffProfileDetail').'*')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.staffProfileList')) !!}">
                        <i class="fa fa-user-circle fa-2x"></i>
                        <span class="sidebar-mini-hide">Staff Profiles</span>
                    </a>
                        
                </li>
              @endif
              
              
                 @if(Auth::user()->role == '0' || (Auth::user()->role == '4' && Session::get('defaultView') == 'end-user'))    
                 
<!--                 <li class="">open
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.scheduling')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.scheduling')) !!}">
                            <i class="fa fa-calendar fa-2x"></i>
                            <span class="sidebar-mini-hide">Scheduling</span>
                        </a>
                        
                    </li>-->

                    
                 <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.shiftOffer')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.shiftOffer')) !!}">
                            <i class="fa fa-gift fa-2x"></i>
                            <span class="sidebar-mini-hide">Shift Offers</span>
                        </a>
                        
                    </li>
                 @endif
                 
                 @if(Auth::user()->role == '0' || (Auth::user()->role == '4' && Session::get('defaultView') == 'end-user'))
                   <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.usersShiftHistory')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.usersShiftHistory')) !!}">
                            <i class="fa fa-history fa-2x"></i>
                            <span class="sidebar-mini-hide">Past Staffing Requests</span>
                        </a>
                        
                    </li> 
                    
                    <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.usersCancelledRequests')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.usersCancelledRequests')) !!}">
                            <i class="fa fa-user-times fa-2x"></i>
                            <span class="sidebar-mini-hide">Cancelled Staffing Requests</span>
                        </a>
                        
                    </li> 
                    
                    
                    
                 @endif
                 
                 
                 @if(Auth::user()->role == '2' || Auth::user()->role == '3' || (Auth::user()->role == '4' && Session::get('defaultView') == 'admin'))
                   
                    <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.staffingHistory')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.staffingHistory')) !!}">
                            <i class="fa fa-history fa-2x"></i>
                            <span class="sidebar-mini-hide">Past Staffing Requests</span>
                        </a>
                        
                    </li>
                    
                    <li class="">
                        <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.cancelledRequests')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.cancelledRequests')) !!}">
                            <i class="fa fa-user-times fa-2x"></i>
                            <span class="sidebar-mini-hide">Cancelled Staffing Requests</span>
                        </a>
                        
                    </li>
                 
                 @endif
                 
                 
                 
                  
                 
                 
                    
                    <li class="{!! (Request::path().'/' == Config::get('constants.urlVar.myProfile') || 
                     Request::path().'/' == Config::get('constants.urlVar.editProfile') || 
                     Request::path().'/' == Config::get('constants.urlVar.changePassword') || 
                     Request::path().'/' == Config::get('constants.urlVar.termsOfService') || 
                     Request::path().'/' == Config::get('constants.urlVar.privacyPolicy')) ? 'open' : '' !!}"><!--open-->
                        <a class="nav-submenu" data-toggle="nav-submenu" href="#"><i class="fa fa-cog fa-2x"></i><span class="sidebar-mini-hide">Settings</span></a>
                        <ul>
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.myProfile') || 
                     Request::path().'/' == Config::get('constants.urlVar.editProfile')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.myProfile')) !!}">My Account</a>
                            </li>
                           
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.changePassword')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.changePassword')) !!}">Change Password</a>
                            </li>
                           
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.termsOfService')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.termsOfService')) !!}">Terms of Service</a>
                            </li>
                           
                            <li>
                                <a class="{!! (Request::path().'/' == Config::get('constants.urlVar.privacyPolicy')) ? 'active' : '' !!}" href="{!! url(Config::get('constants.urlVar.privacyPolicy')) !!}">Privacy Policy</a>
                            </li>
                            
                        </ul>
                    </li>
                    
                    
                </ul>
            </div>
            <!-- END Side Navigation -->
        </div>
        <!-- Sidebar Content -->
    </div>
    <!-- END Sidebar Scroll Container -->
</nav>