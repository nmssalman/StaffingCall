<?php

return [
    
        'urlVar' => [
            
            /* Cron HANDLER */
            'CronHandler' => 'api/auth/cronrequest/',
            /* Cron HANDLER */

            
            /* Dynamic URL */
            'DynamicGlobalURL' => 'irawitvaruag/',
            /* Dynamic URL */
            
            /* Generate UserName And Password Of User */
            'GenerateLoginIDAndPassword' => 'secure-password/',
            /* Generate UserName And Password Of User */

            'login' => 'login/',
            'checklogin' => 'checklogin/',
            'logout' => 'logout/',
            'forgotpassword' => 'forgotpassword/',
            'resetpassword' => 'resetpassword/',
            'resetsuccess' => 'resetsuccess/',
            'tokenexpired' => 'tokenexpired/',
            'pageNotFound' => 'pagenotfound/',
            
            'changePassword' => 'changepassword/',
            
            'home' => 'dashboard/',
            
            'changeAdminView' => 'changeAdminView/',
            
            'saveNotificationSettings' => 'changeNotificationSettings/',
            
            'addNewGroup' => 'groups/new/',
            'saveNewGroup' => 'groups/save/',
            'ajaxGroupList' => 'groups/ajaxGroupList/',
            'groupList' => 'groups/groupList/',
            'ajaxManagerGroupList' => 'groups/ajaxMyGroup/',
            'managerGroupList' => 'groups/mygroup/',
            'editGroup' => 'groups/update/',
            'updateGroup' => 'groups/updateGroup/',
            'removeGroupLogo' => 'groups/removeGroupLogo/',
            
            'addNewUnit' => 'units/new/',
            'saveNewUnit' => 'units/save/',
            'editUnit' => 'units/edit/',
            'updateBusinessUnit' => 'units/update-unit/',
            'ajaxUnitList' => 'units/ajaxUnitList/',
            'unitList' => 'units/unitList/',
            
            'addNewUser' => 'users/new/',
            'saveNewUser' => 'users/save/',
            'ajaxUserList' => 'users/ajaxUserList/',
            'userList' => 'users/userList/',
            'editUser' => 'users/update/',
            'updateUser' => 'users/updateUser/',
            'myProfile' => 'users/profile/',
            'editProfile' => 'users/editprofile/',
            'updateProfile' => 'users/updateProfile/',
            'editUser' => 'users/editUser/',
            'updateUser' => 'users/updateUser/',
            
            'addNewStaffingRequest' => 'calls/newRequest/',
            'saveStaffingRequest' => 'calls/saveRequest/',
            'ajaxStaffingRequestsList' => 'calls/ajaxStaffingRequestsList/',
            'staffingRequestsList' => 'calls/requests/',
            'staffingPostDetail' => 'calls/detail/',
            'approvePost' => 'calls/postaction/',
            
            'unitSkillsCategoryList' => 'units/skills/',
            'ajaxUnitSkillsCategoryList' => 'units/ajax/skills/',
            'addNewSkillCategory' => 'units/skills/new/',
            'saveSkillCategory' => 'units/skills/save/',
            'editSkillCategory' => 'units/skills/editcategory/',
            'updateSkillCategory' => 'units/skills/update-skills/',
            
            'requestReasonsList' => 'requests/reasons/',
            'ajaxRequestReasonsList' => 'requests/ajax/reasons/',
            'addNewRequestReason' => 'requests/reasons/new/',
            'editRequestReason' => 'requests/reasons/edit/',
            'updateRequestReason' => 'requests/reasons/update/',
            'saveRequestReason' => 'requests/reasons/save/',
            
            'saveDefaultVacancyReasons' => 'vacancy/savedefaultreasons/',
            'removeVacancyReasons' => 'vacancy/remove-reason/',
            
            'saveDefaultRequestReasons' => 'request-reason/savedefault/',
            'removeRequestReasons' => 'request-reason/remove-reason/',
            
            
            'vacancyReasonsList' => 'vacancy/reasons/',
            'ajaxVacancyReasonsList' => 'vacancy/ajax/reasons/',
            'addNewVacancyReason' => 'vacancy/reasons/new/',
            'editVacancyReason' => 'vacancy/reasons/edit/',
            'updateVacancyReason' => 'vacancy/reasons/update/',
            'saveVacancyReason' => 'vacancy/reasons/save/',
            
            'unitShiftSetUpList' => 'units/shiftsetup/',
            'ajaxUnitShiftSetUpList' => 'units/ajax/shiftsetup/',
            'addNewShiftSetUp' => 'shift/new/',
            'editShiftSetUp' => 'shift/edit/',
            'updateShiftSetUp' => 'shift/update/',
            'saveShiftSetUp' => 'shift/save/',
            
            
            'ajaxChangeRequestForAddNewRequestForm' => 'calls/getNewFormData/',
            'ajaxGetSecondaryBusinessUnit' => 'users/getBusinessUnit/',
            
            
            'shiftOffer' => 'views/shift-offer/',
            'shiftOfferDetail' => 'views/offer-detail/',
            'acceptShiftOffer' => 'shift-offer/sendresponse',
            'ajaxRespondedPeopleList' => 'calls/responded-people/',
            'confirmShiftOffer' => 'shift-offer/confirm-offer',
            'declineShiftOffer' => 'shift-offer/decline-offer',
            'declineShiftRequest' => 'shift-offer/decline-request',
            'makeOfferToUser' => 'calls/make-offer/',
            'acceptToBeOnWaitlist' => 'shift-offer/toBeOnWaitlist',
            
            'scheduling' => 'staffing/scheduling/',
            
            'ajaxStaffProfileList' => 'staff-profile/ajax-staff-list/',
            'staffProfileList' => 'staff-profile/all/',
            'staffProfileDetail' => 'staff-profile/detail/',
            
            
            /* Calendar View Ajax  */
            'ajaxCalendarViewOnNextPrevious' => 'calender/view/',
            'userCalendarAvailability' => 'calender/availability/',
            'userCalendarView' => 'dashboard/calender-view/',
            
            /* Pages */
            'termsOfService' => 'staffing/terms-of-service/',
            'privacyPolicy' => 'staffing/privacy-policy/',
            'editPage' => 'staffing/page/',
            'updatePage' => 'staffing/update-page/',
            /* Pages */
            
            /* Business Unit Detail [Super Admin View] */
            'businessUnitDetail' => 'units/detail/',
            'businessUnitDetailPending' => 'units/pending-requests/',
             /* Business Unit Detail [Super Admin View] */
            
            /* Group Detail [Manager Home View] */
            'groupDetail' => 'groups/detail/',
             /* Group Detail [Manager Home View] */
            
            /* Ajax Paging Open Requests List */
            'ajaxOpenRequestsList' => 'requests/open/',
             /* Ajax Paging Open Requests List */
            
            /* Ajax Paging Units List */
            'ajaxUnitsList' => 'units/ajaxlist/',
             /* Ajax Paging Units List */
            
            /* Ajax Paging Groups Home List */
            'ajaxGroupsHomeList' => 'groups/ajaxhomelist/',
             /* Ajax Paging Groups Home List */
            
            /* Ajax Paging Groups Detail Business Unit */
            'ajaxGroupDetailBusinessUnitsPaging' => 'groups/ajax-group-detail/',
             /* Ajax Paging Groups Detail Business Unit */
            
            /* Staffing History */
            'staffingHistory' => 'calls/history/',
             /* Staffing History */
            
            /* Staffing Cancelled Requests */
            'cancelledRequests' => 'calls/cancel-requests/',
             /* Staffing Cancelled Requests */            
            
            /* Staffing Cancelled Shifts */
            'usersCancelledRequests' => 'calls/cancelled-shifts/',
            /* Staffing Cancelled Shifts */
            
            /* Staffing History */
            'usersShiftHistory' => 'calls/shift-history/',
             /* Staffing History */
            
             /* Business Units Active-Deactive*/
            'setUnitActiveDeactive' => 'units/toggle/',
              /* Business Units Active-Deactive*/
            
             /* Business Units Delete*/
             'deleteBusinessUnit' => 'units/delete/',
              /* Business Units Delete*/
            
             /* Groups Active-Deactive*/
            'setGroupActiveDeactive' => 'groups/toggle/',
              /* Groups Active-Deactive*/
            
             /* Groups Delete*/
             'deleteGroup' => 'groups/delete/',
              /* Groups Delete*/
            
             /* Users Active-Deactive*/
            'setUserActiveDeactive' => 'users/toggle/',
              /* Users Active-Deactive*/
            
             /* Users Delete*/
             'deleteUser' => 'users/delete/',
              /* Users Delete*/
            
             /* Offer Algorithm List & Update*/
             'algorithmList' => 'offer-algorithm/show/',
             'ajaxAlgorithmList' => 'offer-algorithm/ajax-show/',
             'editAlgorithm' => 'offer-algorithm/edit/',
             'updateAlgorithm' => 'offer-algorithm/update/',
             'algorithmDetail' => 'offer-algorithm/detail/',
             'gerComplexAlgorithmOrdering' => 'offer-algorithm/complex-re-order/',
             /* Offer Algorithm List & Update*/
            
        ],
    
        'apiUrl' => [
            
            /* Cron HANDLER */
            'CronHandler' => 'auth/cronrequest/',
            /* Cron HANDLER */

            'login' => 'auth/login/',
            'forgotpassword' => 'auth/forgotPassword/',
            'changePassword' => 'auth/changePassword/',
            'register' => 'auth/register/',
            'logout' => 'auth/logout/',
            'myAccount' => 'auth/myAccount/',
            'updateAccount' => 'auth/updateAccount/',
            'newRequestQuestionSetUp' => 'auth/newRequestQuestionSetUp/',
            'saveNewStaffingRequest' => 'auth/saveNewStaffingRequest/',
            'requestListHomePageForAdmin' => 'auth/adminRequestList/',
            'pendingRequestListForAdmin' => 'auth/pendingRequestList/',
            'getStaffAndShifts' => 'auth/getStaffAndShifts/',
            'endUsersRequestPostsList' => 'auth/jobList/',
            'adminStaffingPostDetail' => 'auth/staffingPostDetail/',
            'termsOfServices' => 'auth/termsOfServices/',
            'privacyPolicy' => 'auth/privacyPolicy/',
            'userSetting' => 'auth/userSetting/',
            'approvePost' => 'auth/approvePost/',
            'shiftOfferList' => 'auth/shiftOfferList/',
            'shiftOfferDetail' => 'auth/shiftOfferDetail/',
            'userResponse' => 'auth/userResponse/',
            'respondedUsers' => 'auth/respondedUsers/',
            'makeOffer' => 'auth/makeOffer/',
            'confirmOffer' => 'auth/confirmOffer/',
            'staffProfileList' => 'auth/staffProfileList/',
            'staffProfileDetail' => 'auth/staffProfileDetail/',
            'calenderView' => 'auth/calenderView/',
            'userCalendarAvailability' => 'auth/userCalendarAvailability/',
            'usersShiftHistory' => 'auth/usersShiftHistory/',
            'staffingHistory' => 'auth/staffingHistory/',
            'businessUnits' => 'auth/businessUnits/',
            'openRequests' => 'auth/openRequests/',
            'onWaitlist' => 'auth/onWaitlist/',
            'textCommunication' => 'auth/textCommunication/',
            'emailCommunication' => 'auth/emailCommunication/',
            'usersCancelledRequests' => 'auth/usersCancelledRequests/',
            'cancelledRequests' => 'auth/cancelledRequests/'
    
        ]
];


