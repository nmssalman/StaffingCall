<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get(Config::get('constants.apiUrl.CronHandler'), 'ApisController@acceptCronRequest');

Route::post(Config::get('constants.apiUrl.register'), 'ApisController@register');
Route::post(Config::get('constants.apiUrl.login'), 'ApisController@login');
Route::post(Config::get('constants.apiUrl.forgotpassword'), 'ApisController@forgotPassword');
Route::post(Config::get('constants.apiUrl.changePassword'), 'ApisController@changePassword');
Route::post(Config::get('constants.apiUrl.logout'), 'ApisController@logout');
Route::post(Config::get('constants.apiUrl.myAccount'), 'ApisController@myAccount');
Route::post(Config::get('constants.apiUrl.updateAccount'), 'ApisController@updateAccount');
Route::post(Config::get('constants.apiUrl.newRequestQuestionSetUp'), 'ApisController@newRequestQuestionSetUp');
Route::post(Config::get('constants.apiUrl.saveNewStaffingRequest'), 'ApisController@saveNewStaffingRequest');
Route::post(Config::get('constants.apiUrl.requestListHomePageForAdmin'), 'ApisController@adminRequestList');
Route::post(Config::get('constants.apiUrl.getStaffAndShifts'), 'ApisController@getStaffAndShifts');
Route::post(Config::get('constants.apiUrl.endUsersRequestPostsList'), 'ApisController@jobList');
Route::post(Config::get('constants.apiUrl.adminStaffingPostDetail'), 'ApisController@staffingPostDetail');
Route::get(Config::get('constants.apiUrl.termsOfServices'), 'ApisController@termsOfServices');
Route::get(Config::get('constants.apiUrl.privacyPolicy'), 'ApisController@privacyPolicy');
Route::post(Config::get('constants.apiUrl.userSetting'), 'ApisController@userSetting');
Route::post(Config::get('constants.apiUrl.approvePost'), 'ApisController@approvePost');
Route::post(Config::get('constants.apiUrl.shiftOfferList'), 'ApisController@shiftOfferList');
Route::post(Config::get('constants.apiUrl.shiftOfferDetail'), 'ApisController@shiftOfferDetail');
Route::post(Config::get('constants.apiUrl.userResponse'), 'ApisController@userResponse');
Route::post(Config::get('constants.apiUrl.respondedUsers'), 'ApisController@respondedUsers');
Route::post(Config::get('constants.apiUrl.makeOffer'), 'ApisController@makeOffer');
Route::post(Config::get('constants.apiUrl.confirmOffer'), 'ApisController@confirmOffer');
Route::post(Config::get('constants.apiUrl.staffProfileList'), 'ApisController@staffProfileList');
Route::post(Config::get('constants.apiUrl.staffProfileDetail'), 'ApisController@staffProfileDetail');
Route::post(Config::get('constants.apiUrl.calenderView'), 'ApisController@calenderView');
Route::post(Config::get('constants.apiUrl.userCalendarAvailability'), 'ApisController@userCalendarAvailability');
Route::post(Config::get('constants.apiUrl.usersShiftHistory'), 'ApisController@usersShiftHistory');
Route::post(Config::get('constants.apiUrl.staffingHistory'), 'ApisController@staffingHistory');
Route::post(Config::get('constants.apiUrl.businessUnits'), 'ApisController@businessUnits');
Route::post(Config::get('constants.apiUrl.openRequests'), 'ApisController@openRequests');
Route::post(Config::get('constants.apiUrl.onWaitlist'), 'ApisController@onWaitlist');
Route::post(Config::get('constants.apiUrl.pendingRequestListForAdmin'), 'ApisController@pendingRequestList');
Route::get(Config::get('constants.apiUrl.textCommunication'), 'ApisController@textCommunication');
Route::get(Config::get('constants.apiUrl.emailCommunication'), 'ApisController@emailCommunication');
Route::post(Config::get('constants.apiUrl.usersCancelledRequests'), 'ApisController@usersCancelledRequests');
Route::post(Config::get('constants.apiUrl.cancelledRequests'), 'ApisController@cancelledRequests');
Route::group(['middleware' => 'auth'], function () {
    //Route::post('user', 'ApisController@getAuthUser');  
});
