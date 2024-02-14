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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post(Config::get('constants.apiUrl.register'), 'v5\ApisController@register');
Route::post(Config::get('constants.apiUrl.login'), 'v5\ApisController@login');
Route::post(Config::get('constants.apiUrl.forgotpassword'), 'v5\ApisController@forgotPassword');
Route::post(Config::get('constants.apiUrl.logout'), 'v5\ApisController@logout');
Route::post(Config::get('constants.apiUrl.myAccount'), 'v5\ApisController@myAccount');
Route::post(Config::get('constants.apiUrl.updateAccount'), 'v5\ApisController@updateAccount');
Route::post(Config::get('constants.apiUrl.newRequestQuestionSetUp'), 'v5\ApisController@newRequestQuestionSetUp');
Route::post(Config::get('constants.apiUrl.saveNewStaffingRequest'), 'v5\ApisController@saveNewStaffingRequest');
Route::post(Config::get('constants.apiUrl.requestListHomePageForAdmin'), 'v5\ApisController@adminRequestList');
Route::post(Config::get('constants.apiUrl.getStaffAndShifts'), 'v5\ApisController@getStaffAndShifts');
Route::post(Config::get('constants.apiUrl.endUsersRequestPostsList'), 'v5\ApisController@jobList');
Route::post(Config::get('constants.apiUrl.adminStaffingPostDetail'), 'v5\ApisController@staffingPostDetail');
Route::get(Config::get('constants.apiUrl.termsOfServices'), 'v5\ApisController@termsOfServices');
Route::get(Config::get('constants.apiUrl.privacyPolicy'), 'v5\ApisController@privacyPolicy');
Route::post(Config::get('constants.apiUrl.userSetting'), 'v5\ApisController@userSetting');
Route::post(Config::get('constants.apiUrl.approvePost'), 'v5\ApisController@approvePost');
Route::post(Config::get('constants.apiUrl.shiftOfferList'), 'v5\ApisController@shiftOfferList');
Route::post(Config::get('constants.apiUrl.userResponse'), 'v5\ApisController@userResponse');
Route::post(Config::get('constants.apiUrl.respondedUsers'), 'v5\ApisController@respondedUsers');
Route::post(Config::get('constants.apiUrl.makeOffer'), 'v5\ApisController@makeOffer');
Route::post(Config::get('constants.apiUrl.confirmOffer'), 'v5\ApisController@confirmOffer');
Route::post(Config::get('constants.apiUrl.staffProfileList'), 'v5\ApisController@staffProfileList');
Route::post(Config::get('constants.apiUrl.staffProfileDetail'), 'v5\ApisController@staffProfileDetail');
Route::post(Config::get('constants.apiUrl.calenderView'), 'v5\ApisController@calenderView');
Route::post(Config::get('constants.apiUrl.userCalendarAvailability'), 'v5\ApisController@userCalendarAvailability');
Route::post(Config::get('constants.apiUrl.usersShiftHistory'), 'v5\ApisController@usersShiftHistory');
Route::post(Config::get('constants.apiUrl.staffingHistory'), 'v5\ApisController@staffingHistory');
Route::post(Config::get('constants.apiUrl.businessUnits'), 'v5\ApisController@businessUnits');
Route::post(Config::get('constants.apiUrl.openRequests'), 'v5\ApisController@openRequests');
Route::group(['middleware' => 'auth'], function () {
    //Route::post('user', 'v5\ApisController@getAuthUser');  
});
