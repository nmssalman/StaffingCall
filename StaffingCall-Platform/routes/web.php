<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('refresh-csrf', function(){
    return csrf_token();
});

/* DYNAMIC URL */
Route::get(Config::get('constants.urlVar.DynamicGlobalURL'), 'LoginController@DynamicGlobalURL');

    
/* DYNAMIC URL */

Route::get(Config::get('constants.urlVar.login').'{groupCode?}', 'LoginController@login');
Route::post(Config::get('constants.urlVar.checklogin'), 'LoginController@doLogin');
Route::get(Config::get('constants.urlVar.forgotpassword'), 'LoginController@forgotPassword');
Route::post(Config::get('constants.urlVar.forgotpassword'), 'LoginController@forgotPassword');
Route::get(Config::get('constants.urlVar.resetpassword').'{token?}', 'LoginController@resetPassword');
Route::post(Config::get('constants.urlVar.resetpassword').'{token}', 'LoginController@resetPassword');

Route::get(Config::get('constants.urlVar.GenerateLoginIDAndPassword').'{token?}', 'LoginController@generateUserNameAndPassword');
Route::post(Config::get('constants.urlVar.GenerateLoginIDAndPassword').'{token}', 'LoginController@generateUserNameAndPassword');

Route::get(Config::get('constants.urlVar.tokenexpired'), 'LoginController@tokenExpired');
Route::get(Config::get('constants.urlVar.resetsuccess'), 'LoginController@resetSuccess');
Route::get(Config::get('constants.urlVar.pageNotFound'), 'LoginController@pageNotFound');
Route::group(['middleware'=>'auth'], function(){
    
    Route::get(Config::get('constants.urlVar.logout'), 'LoginController@logout');      
    
    Route::group(['middleware'=>'staffingCommonActivity'], function(){  
      
  
    Route::get('/', 'IndexController@home');    
    Route::get(Config::get('constants.urlVar.home'), 'IndexController@home');   
    Route::post(Config::get('constants.urlVar.changeAdminView'), 'IndexController@changeAdminView');  
    Route::get(Config::get('constants.urlVar.scheduling'), 'IndexController@scheduling');
    Route::post(Config::get('constants.urlVar.changePassword'), 'LoginController@changePassword'); 
    Route::get(Config::get('constants.urlVar.changePassword'), 'LoginController@changePassword');
    
    /* For Only Group Admin */
    Route::get(Config::get('constants.urlVar.addNewGroup'), 'GroupsController@createGroup');    
    Route::post(Config::get('constants.urlVar.saveNewGroup'), 'GroupsController@saveGroup');     
    Route::get(Config::get('constants.urlVar.ajaxGroupList'), 'GroupsController@ajaxGroupList');      
    Route::get(Config::get('constants.urlVar.groupList'), 'GroupsController@index');      
    Route::get(Config::get('constants.urlVar.removeGroupLogo').'{groupID}', 'GroupsController@removeGroupLogo'); 
    /* For Only Group Admin */
    
    
    
    /* For Only Group Manager */
    Route::get(Config::get('constants.urlVar.ajaxManagerGroupList'), 'GroupsController@ajaxManagerGroupInfo');      
    Route::get(Config::get('constants.urlVar.managerGroupList'), 'GroupsController@managerGroupIndex'); 
    Route::get(Config::get('constants.urlVar.editGroup').'{id}', 'GroupsController@edit');
    Route::post(Config::get('constants.urlVar.updateGroup'), 'GroupsController@updateGroup');
    /* For Only Group Manager */
    
    
    
    
    /* For Only Group Manager */
    Route::get(Config::get('constants.urlVar.addNewUnit'), 'BusinessunitController@createUnit');    
    Route::post(Config::get('constants.urlVar.saveNewUnit'), 'BusinessunitController@saveUnit');
    Route::get(Config::get('constants.urlVar.editUnit').'{id}', 'BusinessunitController@editUnit');    
    Route::post(Config::get('constants.urlVar.updateBusinessUnit'), 'BusinessunitController@updateBusinessUnit');     
    Route::get(Config::get('constants.urlVar.ajaxUnitList'), 'BusinessunitController@ajaxUnitList');      
    Route::get(Config::get('constants.urlVar.unitList'), 'BusinessunitController@index'); 
    /* For Only Group Manager */
    
    /* For User Management */
    Route::get(Config::get('constants.urlVar.addNewUser'), 'UsersController@createUser');    
    Route::post(Config::get('constants.urlVar.saveNewUser'), 'UsersController@saveUser');     
    Route::get(Config::get('constants.urlVar.ajaxUserList'), 'UsersController@ajaxUserList');      
    Route::get(Config::get('constants.urlVar.userList'), 'UsersController@index');      
    Route::get(Config::get('constants.urlVar.myProfile'), 'UsersController@myProfile');       
    Route::get(Config::get('constants.urlVar.editProfile'), 'UsersController@editProfile');       
    Route::post(Config::get('constants.urlVar.updateProfile'), 'UsersController@updateProfile');       
    Route::post(Config::get('constants.urlVar.saveNotificationSettings'), 'UsersController@saveNotificationSettings');
    Route::get(Config::get('constants.urlVar.editUser').'{id}', 'UsersController@edit');
    Route::post(Config::get('constants.urlVar.updateUser'), 'UsersController@updateUser');
    
    /* Ajax Request To Get Secondary Business Unit While Adding New User  */
    Route::post(Config::get('constants.urlVar.ajaxGetSecondaryBusinessUnit'), 'UsersController@ajaxGetBusinessUnit'); 
    /* Ajax Request To Get Secondary Business Unit While Adding New User */
    
    
    /* For User Management */
    
    /* For New Staffing Requests */
    Route::get(Config::get('constants.urlVar.addNewStaffingRequest'), 'CallsController@newRequest');    
    Route::post(Config::get('constants.urlVar.saveStaffingRequest'), 'CallsController@saveRequest');     
    Route::get(Config::get('constants.urlVar.ajaxStaffingRequestsList'), 'CallsController@ajaxStaffingRequestsList');      
    Route::get(Config::get('constants.urlVar.staffingRequestsList'), 'CallsController@index');      
    Route::get(Config::get('constants.urlVar.staffingPostDetail').'{id}', 'CallsController@postDetail');      
    Route::get(Config::get('constants.urlVar.approvePost').'{id}/{status}/{returnUrl}/{cancelReason?}', 'CallsController@approvePost');
    /* For New Staffing Requests */
    
    
    /* For Super Admin & Group Manager To Manage Unit Skills Category & Reasons Of Staffing Request For End-User */
    Route::get(Config::get('constants.urlVar.unitSkillsCategoryList'), 'BusinessunitController@unitSkillsCategoryList');  
    Route::get(Config::get('constants.urlVar.ajaxUnitSkillsCategoryList'), 'BusinessunitController@ajaxSkillCategory'); 
    Route::get(Config::get('constants.urlVar.addNewSkillCategory'), 'BusinessunitController@addNewSkillCategory'); 
    Route::post(Config::get('constants.urlVar.saveSkillCategory'), 'BusinessunitController@saveSkillCategory'); 
    Route::get(Config::get('constants.urlVar.editSkillCategory').'{id}', 'BusinessunitController@editSkillCategory'); 
    Route::post(Config::get('constants.urlVar.updateSkillCategory'), 'BusinessunitController@updateSkills'); 
    
    
    
    /* Requests Reasons */
    Route::get(Config::get('constants.urlVar.requestReasonsList'), 'BusinessunitController@requestReasonsList');  
    Route::get(Config::get('constants.urlVar.ajaxRequestReasonsList'), 'BusinessunitController@ajaxRequestReasonsList'); 
    Route::get(Config::get('constants.urlVar.addNewRequestReason'), 'BusinessunitController@addNewRequestReason'); 
    Route::post(Config::get('constants.urlVar.saveRequestReason'), 'BusinessunitController@saveRequestReason'); 
    Route::get(Config::get('constants.urlVar.editRequestReason').'{id}', 'BusinessunitController@editRequestReason'); 
    Route::post(Config::get('constants.urlVar.updateRequestReason'), 'BusinessunitController@updateRequestReason'); 
    
    /* Requests Reasons */
    
    
    /* Vacancy Reasons */
    
    Route::get(Config::get('constants.urlVar.saveDefaultVacancyReasons'), 'BusinessunitController@saveDefaultVacancyReasons'); 
    Route::get(Config::get('constants.urlVar.removeVacancyReasons').'{id}', 'BusinessunitController@removeVacancyReasons');   
    
    Route::get(Config::get('constants.urlVar.saveDefaultRequestReasons'), 'BusinessunitController@saveDefaultRequestReasons'); 
    Route::get(Config::get('constants.urlVar.removeRequestReasons').'{id}', 'BusinessunitController@removeRequestReasons');   
    
    Route::get(Config::get('constants.urlVar.vacancyReasonsList'), 'BusinessunitController@vacancyReasonsList');
    Route::get(Config::get('constants.urlVar.ajaxVacancyReasonsList'), 'BusinessunitController@ajaxVacancyReasonsList'); 
    Route::get(Config::get('constants.urlVar.addNewVacancyReason'), 'BusinessunitController@addNewVacancyReason'); 
    Route::post(Config::get('constants.urlVar.saveVacancyReason'), 'BusinessunitController@saveVacancyReason'); 
    Route::get(Config::get('constants.urlVar.editVacancyReason').'{id}', 'BusinessunitController@editVacancyReason'); 
    Route::post(Config::get('constants.urlVar.updateVacancyReason'), 'BusinessunitController@updateVacancyReason'); 
    /* Vacancy Reasons */
    
    /* Ajax Request To change correspondance Values in Add New Request Form */
    Route::post(Config::get('constants.urlVar.ajaxChangeRequestForAddNewRequestForm'), 'CallsController@ajaxNewRequestFormSetting'); 
    /* Ajax Request To change correspondance Values in Add New Request Form */
    
    
    /*Business Unit Shift SetUP */
    Route::get(Config::get('constants.urlVar.unitShiftSetUpList'), 'ShiftController@index');  
    Route::get(Config::get('constants.urlVar.ajaxUnitShiftSetUpList'), 'ShiftController@ajaxList'); 
    Route::get(Config::get('constants.urlVar.addNewShiftSetUp'), 'ShiftController@addNew'); 
    Route::post(Config::get('constants.urlVar.updateShiftSetUp'), 'ShiftController@updation'); 
    Route::get(Config::get('constants.urlVar.editShiftSetUp').'{id}', 'ShiftController@edit'); 
    Route::post(Config::get('constants.urlVar.saveShiftSetUp'), 'ShiftController@save'); 
    /*Business Unit Shift SetUP */
    
    
    
     /* SHIFT - OFFER */     
       Route::get(Config::get('constants.urlVar.shiftOffer'), 'ShiftOfferController@shiftOffer');     
       Route::get(Config::get('constants.urlVar.shiftOfferDetail').'{id}', 'ShiftOfferController@shiftOfferDetail');     
       Route::post(Config::get('constants.urlVar.acceptShiftOffer'), 'ShiftOfferController@acceptRequest');     
       Route::get(Config::get('constants.urlVar.declineShiftRequest'), 'ShiftOfferController@declineRequest');        
       Route::get(Config::get('constants.urlVar.confirmShiftOffer'), 'ShiftOfferController@acceptOffer');        
       Route::get(Config::get('constants.urlVar.acceptToBeOnWaitlist'), 'ShiftOfferController@onWaitlist');          
       Route::get(Config::get('constants.urlVar.declineShiftOffer'), 'ShiftOfferController@declineOffer');   
       Route::get(Config::get('constants.urlVar.ajaxRespondedPeopleList').'{requestID}', 'CallsController@ajaxRespondedPeopleList');     
       Route::get(Config::get('constants.urlVar.makeOfferToUser'), 'CallsController@makeOfferToUser'); 
     /* SHIFT - OFFER */
    
    /* For Super Admin To Manage Unit Skills Category & Reasons Of Staffing Request For End-User */
       
       
    
     /* STAFF - PROFILE */                   
       Route::get(Config::get('constants.urlVar.staffProfileList'), 'StaffProfileController@index'); 
       Route::get(Config::get('constants.urlVar.ajaxStaffProfileList'), 'StaffProfileController@ajaxStaffProfileList'); 
       Route::get(Config::get('constants.urlVar.staffProfileDetail').'{staffID}', 'StaffProfileController@staffProfileDetail');   
     /* STAFF - PROFILE */  
       
       
       /* Calendar View */
       Route::get(Config::get('constants.urlVar.ajaxCalendarViewOnNextPrevious'), 'IndexController@ajaxCalendarViewOnNextPrevious'); 
       
       Route::post(Config::get('constants.urlVar.userCalendarAvailability'), 'IndexController@userCalendarAvailability'); 
       
       Route::get(Config::get('constants.urlVar.userCalendarView').'{unitID?}', 'IndexController@calenderView'); 
       /* Calendar View */
       
    
       /* Pages */
        Route::get(Config::get('constants.urlVar.termsOfService'), 'IndexController@termsOfService'); 
        Route::get(Config::get('constants.urlVar.privacyPolicy'), 'IndexController@privacyPolicy'); 
        Route::get(Config::get('constants.urlVar.editPage').'{id}', 'IndexController@editPage');  
        Route::post(Config::get('constants.urlVar.updatePage'), 'IndexController@updatePage'); 
       /* Pages */
        
        
        /* Business Unit Detail [Super Admin View] */
        Route::get(Config::get('constants.urlVar.businessUnitDetail').'{unitID}', 'IndexController@businessUnitDetail'); 
        
        Route::get(Config::get('constants.urlVar.businessUnitDetailPending').'{unitID}', 'IndexController@businessUnitDetailPending'); 
        /* Business Unit Detail [Super Admin View] */
        
        /* Group Detail [God Admin Home View] */
        Route::get(Config::get('constants.urlVar.groupDetail').'{groupID}', 'IndexController@groupDetail'); 
        /* Group Detail [God Admin Home View] */
        
        /* Ajax Paging Open Requests List */
        Route::get(Config::get('constants.urlVar.ajaxOpenRequestsList').'{unitID}/{pageNo}', 'IndexController@ajaxOpenRequestsList'); 
        /* Ajax Paging Open Requests List */
        
        /* Ajax Paging Unit Home List */
        Route::get(Config::get('constants.urlVar.ajaxUnitsList').'{pageNo}', 'IndexController@ajaxBusinessUnitPaging'); 
        /* Ajax Paging Unit Home List */
        
        /* Ajax Paging Groups Home List */
        Route::get(Config::get('constants.urlVar.ajaxGroupsHomeList').'{pageNo}', 'IndexController@ajaxGroupsHomeList'); 
        /* Ajax Paging Groups Home List */
        
        /* Ajax Paging Group Detail Business Units List */
        Route::get(Config::get('constants.urlVar.ajaxGroupDetailBusinessUnitsPaging').'{groupID}/{pageNo}', 'IndexController@ajaxGroupDetailBusinessUnitsPaging'); 
       /* Ajax Paging Group Detail Business Units List */
        
        /* Staffing History */
        Route::get(Config::get('constants.urlVar.staffingHistory'), 'CallsController@staffingHistory'); 
       /* Staffing History */
        
        /* Staffing History For End User*/
        Route::get(Config::get('constants.urlVar.usersShiftHistory'), 'CallsController@userArchivedShifts'); 
       /* Staffing History  For End User*/
        
        /* Staffing Cancelled Requests */
        Route::get(Config::get('constants.urlVar.cancelledRequests'), 'CallsController@cancelledRequests'); 
       /* Staffing Cancelled Requests */
        
        /* Staffing Cancelled Shifts */
        Route::get(Config::get('constants.urlVar.usersCancelledRequests'), 'CallsController@usersCancelledRequests'); 
       /* Staffing Cancelled Shifts */
        
        /* Business Units Active-Deactive*/
        Route::get(Config::get('constants.urlVar.setUnitActiveDeactive').'{id}/{status}', 'BusinessunitController@setActiveDeactive'); 
        /* Business Units Active-Deactive*/
        
        /* Business Units Active-Deactive*/
        Route::get(Config::get('constants.urlVar.deleteBusinessUnit').'{id}', 'BusinessunitController@deleteBusinessUnit'); 
        /* Business Units Active-Deactive*/
        
        /* Groups Active-Deactive*/
        Route::get(Config::get('constants.urlVar.setGroupActiveDeactive').'{id}/{status}', 'GroupsController@setActiveDeactive'); 
        /* Groups Active-Deactive*/
        
        /* Groups Active-Deactive*/
        Route::get(Config::get('constants.urlVar.deleteGroup').'{id}', 'GroupsController@deleteGroup'); 
        /* Groups Active-Deactive*/
        
        
        
        /* Users Active-Deactive*/
        Route::get(Config::get('constants.urlVar.setUserActiveDeactive').'{id}/{status}', 'UsersController@setActiveDeactive'); 
        /* Users Active-Deactive*/
        
        /* Users Active-Deactive*/
        Route::get(Config::get('constants.urlVar.deleteUser').'{id}', 'UsersController@deleteUser'); 
        /* Users Active-Deactive*/
    
    
    /* Offer Algorithm */
    Route::get(Config::get('constants.urlVar.algorithmList'), 'BusinessunitController@algorithmList');  
    Route::get(Config::get('constants.urlVar.ajaxAlgorithmList'), 'BusinessunitController@ajaxAlgorithmList'); 
    Route::get(Config::get('constants.urlVar.editAlgorithm').'{id}', 'BusinessunitController@editAlgorithm'); 
    Route::post(Config::get('constants.urlVar.updateAlgorithm'), 'BusinessunitController@updateAlgorithm'); 
    Route::get(Config::get('constants.urlVar.algorithmDetail').'{id}', 'BusinessunitController@algorithmDetail'); 
    Route::get(Config::get('constants.urlVar.gerComplexAlgorithmOrdering').'{id}', 'BusinessunitController@gerComplexAlgorithmOrdering'); 
    /* Offer Algorithm */
       
    
});
    
    
});
