<?php
    
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use App\User;
use App\Group;
use App\Device;
use App\ShiftOffer;
use App\RequestLog;
use App\OfferConfirmation;
use App\Requestcall;
use JWTAuthException;
use App\RequestPartialShift;
use App\Businessunit;
use Config;
use DB;
use Hash;
use myHelper;
use Illuminate\Support\Facades\Mail;

    
    
    
    class ApisController extends Controller
    {   
        private $user;
        
        public function __construct(User $user, Request $request){
            $this->user = $user;   
            $requestedFunRoute = explode('@', $request->route()->getActionName());
            $requestedApiName = $requestedFunRoute[1];
            
            if($requestedApiName != 'logout'){
                /*UPDATE USER's DEVICE IDS*/            
                $callFun = $this->checkCommonActivities($request->userID,$request->deviceID,$request->deviceType);
                /*UPDATE USER's DEVICE IDS*/
            }
        }
        
        
        
        public function sendMessageCommunication($requestID = 0, $userData = array()){
            if($requestID > 0 && $userData){
                $mobiles = array();
              foreach($userData as $userInfo)  {
                  $name = $userInfo['firstName']." ".$userInfo['lastName'];
                  if($userInfo['emailNotification'] == '1'){
                    $this->emailCommunication($requestID, $name, $userInfo['email']);
                  }
                  if($userInfo['phone'] != '' && $userInfo['smsNotification'] == '1')
                    $mobiles[] = $userInfo['phone'];
                  
              }
              
              if(count($mobiles) > 0)
              $this->textCommunication($requestID, $mobiles);
            }
            
            return true;
        }
        
        
        public function emailCommunication($requestID = 0, $name, $to){
            if($requestID > 0){
                $response = $this->dynamicURLGenerator($requestID);
                $shortLink = $response->shortLink;
                $previewLink = $response->previewLink;

                $link = $shortLink;
                $text_link = $shortLink;
                //$logo = url('/assets/img/logo.png');
                $logo = url('/assets/img/logo_light.png'); 
                $to = $to; 
                $name = $name; 
                $subject = 'StaffingCall App - New request available';	
                $data = array(
                        'name' => $name,
                        'link' => $link,
                        'logo' => $logo,
                        'to' => $to,
                        'subject' => $subject
                        ); 


                try{

                Mail::send('templates.text-communication', $data, function ($message) use ($to, $name, $subject)
                    {

                        $message->to($to, $name)->subject($subject);
                        $message->from('contact@agidev-staffingcall.com', 'Staffing Call App');

                    });
                }catch(\Exception $e){
                    //print_r($e);
                    // Get error here
                }
                
            }
            
            return true;
        }
        
        function dynamicURLGenerator($requestID = 0){
            if($requestID > 0){
                
                $keysAndURLs = myHelper::getKeysAndURLs();
                
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = $keysAndURLs['dynamicUrlForSMS'].$keysAndURLs['dynamicUrlForSMSKEY'];

                $androidPkgName = $keysAndURLs['androidPkgName'];
                $iOSBundleIdentifier = $keysAndURLs['iOSBundleIdentifier'];

                $dynamicURL = $keysAndURLs['dynamicURL'];
                $link = $protocol."".$_SERVER['HTTP_HOST']."/".Config('constants.urlVar.DynamicGlobalURL');
                $link .= "?requestID=$requestID";
                $dynamicURL .= "link=".$link;
                $dynamicURL .= "&apn=$androidPkgName";
                $dynamicURL .= "&ibi=$iOSBundleIdentifier";

                $postFields = array (
                    'longDynamicLink' => $dynamicURL
                );

                $data_string = json_encode($postFields);

                $ch = curl_init($url);                                                                      
                curl_setopt($ch, CURLOPT_POST, true);                                                                     
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . strlen($data_string))                                                                       
                );                                                                                                                   

                $result = curl_exec($ch);
                // Close connection
                curl_close($ch);
                $response = json_decode($result); 

                return $response;
            }else{
                return array();
            }
        }
        
        //public function textCommunication($to, $message, $requestID){
        public function textCommunication($requestID = 9, $mobileNumbers = array()){
            
            $response = $this->dynamicURLGenerator($requestID);
            $shortLink = $response->shortLink;
            $previewLink = $response->previewLink;
            //$mobileNumbers = array("+918318159112", "+917499812761", "+918081925717", "+919889414160");
            if(isset($response->shortLink)){
            
                
                $message = "New staffing request available. Please click below link to give your response.";
                $message .= $shortLink;
                /* Send Text SMS */
                if($mobileNumbers){
                    foreach($mobileNumbers as $k => $mobile):
                    $smsResponse = myHelper::sendSMS($mobile, $message);
                    endforeach;
                }
            }
            
            return true;
            //echo 'Message sent';
        }
        
        
        
        
        
        public function homePageCount($user, $businessUnitID = 0, $isUser = 0){
            /* Home Page Counts */
            $businessUnitName = '';
            $pendingPostingCount = 0;
            $unitUsersCount = 0;
            $cancelledCount = 0;
            if($user->role != 2){
                $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                );
               
                $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                $unitInfoSql->where('staffing_businessunits.status','=',1);
                
                if($businessUnitID > 0){
                  $unitInfoSql->where('staffing_businessunits.id','=',$businessUnitID);
                }else{

                    if($user->role == 0){
                        $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                        $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                    }
                    else {
                        $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                    }
                }

                $unitInfo = $unitInfoSql->first();
                if($unitInfo)
                $businessUnitName = $unitInfo->unitName;
                
            }else{
                $unitInfoSql = DB::table('staffing_businessunits')
                    ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                    );
               
                $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                $unitInfoSql->where('staffing_businessunits.status','=',1);

                if($businessUnitID > 0){
                  $unitInfoSql->where('staffing_businessunits.id','=',$businessUnitID);
                } else{
                   $unitInfoSql->where('staffing_businessunits.businessGroupID','=',$user->businessGroupID); 
                }
                
                $unitInfo = $unitInfoSql->first();
                if($unitInfo)
                $businessUnitName = $unitInfo->unitName;
            }
            
            if($user->role != 2){
                     
                if($user->role == 0 || ($user->role == 4 && $isUser == 1)){
                    
                     $openPostingCountSQL = DB::table('staffing_shiftoffer')
                     ->select('staffing_shiftoffer.requestID');
                     $openPostingCountSQL->join('staffing_staffrequest', 'staffing_staffrequest.id', '=', 'staffing_shiftoffer.requestID');
                     $openPostingCountSQL->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id');

                     $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                     $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                     $openPostingCountSQL->where('staffing_shiftconfirmation.offerResponse','=',1);                     
                     $openPostingCountSQL->where('staffing_shiftoffer.userID','=',$user->id);
                     
                     if($unitInfo)
                     $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id);
                    
                     $openPostingCount = $openPostingCountSQL->count();
                    
                    
                     $pastPostingCountSQL = DB::table('staffing_shiftoffer')
                     ->select('staffing_shiftoffer.requestID');
                     $pastPostingCountSQL->join('staffing_staffrequest', 'staffing_staffrequest.id', '=', 'staffing_shiftoffer.requestID');
                     $pastPostingCountSQL->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id');

                     $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));                     
                     $pastPostingCountSQL->where('staffing_shiftconfirmation.offerResponse','=',1);                     
                     $pastPostingCountSQL->where('staffing_shiftoffer.userID','=',$user->id);
                     $pastPostingCount = $pastPostingCountSQL->count();                     
                    
                     $cancelledCountSQL = DB::table('staffing_shiftoffer')
                     ->select('staffing_shiftoffer.requestID');
                     $cancelledCountSQL->join('staffing_staffrequest', 'staffing_staffrequest.id', '=', 'staffing_shiftoffer.requestID');
                     $cancelledCountSQL->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id');

                     //$cancelledCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));                     
                     $cancelledCountSQL->where('staffing_shiftconfirmation.offerResponse','=',1);                                  
                     $cancelledCountSQL->whereIn('staffing_staffrequest.postingStatus',[2,4]);                     
                     $cancelledCountSQL->where('staffing_shiftoffer.userID','=',$user->id);
                     $cancelledCount = $cancelledCountSQL->count();  

                }else{
                    
                    $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
                    if($unitInfo)
                    $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 

                    $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                    $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                    if($user->role == '4')
                        $openPostingCountSQL->where('staffing_staffrequest.ownerID', '=', $user->id);
                    $openPostingCount = $openPostingCountSQL->count(); 
                    
                    /* Pending Request Count */
                    $pendingPostingCount = 0;
                    $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                     ->select('id');

                    if($unitInfo)
                    $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id);

                    $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
                    $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));
                    if($user->role == '4')
                        $pendingPostingCountSQL->where('staffing_staffrequest.ownerID', '=', $user->id);
                    $pendingPostingCount = $pendingPostingCountSQL->count(); 
                    /* Pending Request Count */
                    

                    $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
                    if($unitInfo)
                    $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id);
                    
                    $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]);                     
                    $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                    if($user->role == '4')
                        $pastPostingCountSQL->where('staffing_staffrequest.ownerID', '=', $user->id);
                    
                        $pastPostingCount = $pastPostingCountSQL->count();
                        
                        

                    $cancelledCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
                    if($unitInfo)
                    $cancelledCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id);
                    
                    $cancelledCountSQL->whereIn('staffing_staffrequest.postingStatus', [2,4]);                     
                    //$cancelledCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                    if($user->role == '4')
                        $cancelledCountSQL->where('staffing_staffrequest.ownerID', '=', $user->id);
                    
                        $cancelledCount = $cancelledCountSQL->count();

                }
                     
                     
                $unitUsersCountSql = DB::table('staffing_usersunits')
               ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
               ->select(
                       'staffing_usersunits.id'
                   );
                
                if($unitInfo)
                $unitUsersCountSql->where('staffing_usersunits.businessUnitID','=',$unitInfo->id);
                
                $unitUsersCountSql->where('staffing_users.businessGroupID','=',$user->businessGroupID);
                $unitUsersCountSql->where('staffing_users.id','!=',$user->id);

               if($user->role == 2){//Group Manager
                  $unitUsersCountSql->whereIn('staffing_users.role',[3,4,0]);
               }

               if($user->role == 3){//Super Admin
                  $unitUsersCountSql->whereIn('staffing_users.role',[4,0]);
               }

               if($user->role == 4){//Admin
                  $unitUsersCountSql->whereIn('staffing_users.role',[0]);
               }
               
               $unitUsersCount = $unitUsersCountSql->count();

            }else{

                $openPostingCountSQL = DB::table('staffing_staffrequest')
                ->select('id');
                
                if($businessUnitID > 0)
                $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);

                $openPostingCountSQL->where('staffing_staffrequest.businessGroupID','=', $user->businessGroupID); 
                $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]);                     
                $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                $openPostingCount = $openPostingCountSQL->count();               
                
                
                /* Pending Request Count */
                $pendingPostingCount = 0;
                $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                 ->select('id');
                
                if($businessUnitID > 0)
                $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
                
                $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
                $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));
                $pendingPostingCount = $pendingPostingCountSQL->count(); 
                /* Pending Request Count */

                $pastPostingCountSQL = DB::table('staffing_staffrequest')
                ->select('id');
                if($businessUnitID > 0)
                $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
                
                $pastPostingCountSQL->where('staffing_staffrequest.businessGroupID','=', $user->businessGroupID); 
                $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]);                     
                $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                $pastPostingCount = $pastPostingCountSQL->count();
                

                $cancelledCountSQL = DB::table('staffing_staffrequest')
                ->select('id');
                if($businessUnitID > 0)
                $cancelledCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
                
                $cancelledCountSQL->where('staffing_staffrequest.businessGroupID','=', $user->businessGroupID); 
                $cancelledCountSQL->whereIn('staffing_staffrequest.postingStatus', [2,4]);                     
                //$cancelledCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                $cancelledCount = $cancelledCountSQL->count();

                
                if($businessUnitID > 0){
                    
                    $unitUsersCountSql = DB::table('staffing_usersunits')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                    ->select(
                        'staffing_usersunits.id'
                    );
                    
                    $unitUsersCountSql->where('staffing_usersunits.businessUnitID','=',$businessUnitID);
                    $unitUsersCountSql->where('staffing_users.businessGroupID','=',$user->businessGroupID);
                    $unitUsersCountSql->where('staffing_users.id','!=',$user->id);
                    $unitUsersCountSql->whereIn('staffing_users.role',[3,4,0]);  
                    $unitUsersCount = $unitUsersCountSql->count();
                                      
                }else{
                    
                    $unitUsersCount = DB::table('staffing_users')
                    ->select(
                       'staffing_users.id'
                    )->where('staffing_users.businessGroupID','=',$user->businessGroupID)
                        ->whereNotIn('staffing_users.role',[1,2])->count();  
                }


            }
                    
                    
                $shiftOfferCount = 0;
                /* Shift Offer Count For End User OR Admin as End User */
                if($user->role == 0 || ($user->role == 4 && ($isUser == 1))){
                     $userBusinessUnitsAr = array();
                     
                        if($user->role == 0){
            
                            $unitInfoSql = DB::table('staffing_businessunits')
                            ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                            ->select(
                                    'staffing_businessunits.id',
                                    'staffing_businessunits.unitName'
                                    );

                                $unitInfoSql->where('staffing_usersunits.userID','=',$user->id);
                                //$unitInfoSql->where('staffing_usersunits.primaryUnit','=',1);
                                $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                                $unitInfoSql->where('staffing_businessunits.status','=',1);
                                $unitInfo = $unitInfoSql->get();
                        }else{
                            $unitInfoSql = DB::table('staffing_businessunits')
                            ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                            ->select(
                                    'staffing_businessunits.id',
                                    'staffing_businessunits.unitName'
                                    );

                                $unitInfoSql->where('staffing_usersunits.userID','=',$user->id);
                                $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                                $unitInfoSql->where('staffing_businessunits.status','=',1);
                                $unitInfo = $unitInfoSql->get();
                        }


                        if($unitInfo){
                           foreach($unitInfo as $row){
                               $userBusinessUnitsAr[] = $row->id;
                           } 
                        }
                     
                     
                    $endUserSkills = array();
                    $endUserSkills = $user->skills?unserialize($user->skills):array();
                    
                    $userExperiencedLevel = $user->experiencedLevel;
                    
                    $shiftOfferSql = DB::table('staffing_staffrequest')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                    ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                    ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                    ->leftJoin('staffing_shiftoffer', function($join) use($user)
                        {
                            $join->on('staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id');
                            $join->on('staffing_shiftoffer.userID', '=', DB::raw($user->id));
                        })

                    ->select('staffing_staffrequest.id AS postID', 
                            'staffing_staffrequest.requiredStaffCategoryID', 
                            'staffing_staffrequest.staffingStartDate', 
                            'staffing_staffrequest.shiftType', 
                            'staffing_staffrequest.staffingShiftID');
                        
//                        if($businessUnitID > 0){
//                            $shiftOfferSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
//                        }else{
                            if(count($userBusinessUnitsAr) > 0){
                                $shiftOfferSql->whereIn('staffing_staffrequest.businessUnitID', $userBusinessUnitsAr);
                            }
//                        }
                        
                        
                        
                        $shiftOfferSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
                        $shiftOfferSql->where('staffing_staffrequest.closingTime','>=',date("Y-m-d"));
                        
                        /* Get Those Offers Which Matched With User Experienced Levels */
//                            if($userExperiencedLevel == 3){//Experienced
//                                $shiftOfferSql->whereIn('staffing_staffrequest.requiredExperiencedLevel', [0,1,2,3]);
//                            }else if($userExperiencedLevel == 2){//Intermediate
//                                $shiftOfferSql->whereIn('staffing_staffrequest.requiredExperiencedLevel', [0,1,2]); 
//                            } else if($userExperiencedLevel == 1){//Junior
//                                $shiftOfferSql->whereIn('staffing_staffrequest.requiredExperiencedLevel',[0,1]);  
//                            }else{
//                                $shiftOfferSql->whereIn('staffing_staffrequest.requiredExperiencedLevel',[0,1]);
//                            } 
                        /* Get Those Offers Which Matched With User Experienced Levels */
                        
                        
                        
                        /* Active Requests */
                        $shiftOfferCountRows = $shiftOfferSql->get();
                        
                        
                        foreach($shiftOfferCountRows as $shiftOfferCountRow){
                            
                            $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                            ->select('userID','shiftID');
                                $userAvailabilitySql->where('onDate','=',$shiftOfferCountRow->staffingStartDate);
                                $userAvailabilitySql->whereIn('availabilityStatus',  [0, 2]);
                                $userAvailabilitySql->where('userID','=', $user->id);
                                
                                if($shiftOfferCountRow->shiftType == 0 && $shiftOfferCountRow->staffingShiftID > 0){
                                  $userAvailabilitySql->where('shiftID','=', $shiftOfferCountRow->staffingShiftID);  
                                }
                            
                            $userAvailability = $userAvailabilitySql->count();
                            if($userAvailability > 0 && $shiftOfferCountRow->shiftType != 1){
                            }else{
                                $requiredStaffCategoryIDs = ($shiftOfferCountRow->requiredStaffCategoryID ? 
                                explode(",", $shiftOfferCountRow->requiredStaffCategoryID):array()); 
                                if($requiredStaffCategoryIDs && $endUserSkills){
                                    //if(array_intersect($requiredStaffCategoryIDs, $endUserSkills)){
                                    
                            if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){
                                        $shiftOfferCount++; 
                                    }
                                }
                            }
                        }
                        

                }
                
                /* For End User OR Admin as End User */ 
                
                $responseJson['countInfo'] = array(
                    'activeRequestCount' => (string)$openPostingCount,
                    'openRequestCount' => (string)$openPostingCount,
                    'pastRequestCount' => (string)$pastPostingCount,
                    'cancelRequestCount' => (string)$cancelledCount,
                    'pendingRequestCount' => (string)$pendingPostingCount,
                    'staffProfileCount' => (string)$unitUsersCount,
                    'shiftOfferCount' => (string)$shiftOfferCount,
                    'businessUnitName' => (string)$businessUnitName
                );  
                
                /* Home Page Counts */  
                
                return $responseJson['countInfo'];
        }
        
        
        public static function checkCommonActivities($userID,$deviceID,$deviceTypes, $responseStatus = 2){
            
            if($userID){

                $userInfo = User::find($userID);
                
                if ($userInfo) {
                    if($userInfo->deleteStatus == 1){
                       echo json_encode(['status'=>(string)$responseStatus,
                          'message'=>'Your account is deleted. Please contact management.']); 
                       die;
                    } else if($userInfo->status == 0 ){
                      echo json_encode(['status'=>(string)$responseStatus,
                          'message'=>'Your account is deactivated. Please contact management.']);
                       die; 
                    }
                    
                    $userGroupID = $userInfo->businessGroupID;
                    $groupInfo = Group::find($userGroupID);
                    if($groupInfo->deleteStatus == 1){
                       echo json_encode(['status'=>(string)$responseStatus,
                      'message'=>'Your Organization account is deleted. Please contact management.']); 
                       die;
                    } else if($groupInfo->status == 0){
                        echo json_encode(['status'=> (string) $responseStatus,
                          'message'=>'Your Organization account is deactivated. Please contact management.']); 
                       die;
                    } 
                    /* Check Active And Delete */
                    if($userInfo->role == 3 || $userInfo->role == 4 || $userInfo->role == 0) {
                        $unitInfoSql = DB::table('staffing_businessunits')
                         ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                         ->select(
                                 'staffing_businessunits.id',
                                 'staffing_businessunits.status',
                                 'staffing_businessunits.deleteStatus'
                            );
                            if($userInfo->role == 0){
                                $unitInfoSql->where([['staffing_usersunits.userID','=',$userInfo->id]])->first();
                                $unitInfoSql->where([['staffing_usersunits.primaryUnit','=',1]])->first();
                            }else{
                               $unitInfoSql->where([['staffing_usersunits.userID','=',$userInfo->id]])->first(); 
                            }
                            
                            $unitInfo = $unitInfoSql->first();
                        if($unitInfo){
                            if($unitInfo->deleteStatus == 1){
                               echo json_encode(['status'=>(string)$responseStatus,
                               'message'=>'Your Business Unit account is deleted. Please contact management.']);
                                die;
                            } else if($unitInfo->status == 0){
                              echo json_encode(['status'=>(string)$responseStatus,
                                'message'=>'Your Business Unit account is deactivated. Please contact management.']);
                                die;
                            }
                        }else{
                            echo json_encode(['status'=>(string)$responseStatus,
                           'message'=>'Your Business Unit account is deleted. Please contact management.']);
                            die;
                        }
                        
                    }
                    /* Check Active And Delete */
                    
                    
                    
                    
                    if($deviceID && $deviceID!=''){
                        
                       $deviceType = '0';//WEB                         
                       if($deviceTypes == '1')
                        $deviceType = '1';//iOS
                       elseif($deviceTypes == '2')
                        $deviceType = '2';//Android 
                       
                       $chkExistance = DB::table('staffing_devices')
                               ->select('id')->where([
                                   ['userID','=',$userID],
                                   ['deviceID','=',$deviceID],
                                   ['deviceType','=',$deviceType]
                                   ])->first();
                       if(count($chkExistance) > 0){
                         $deviceInfo = Device::find($chkExistance->id);
                         $deviceInfo->userID = $userID;
                         $deviceInfo->deviceID = $deviceID;
                         $deviceInfo->deviceType = $deviceType;
                         $deviceInfo->loginTime = date("Y-m-d H:i:s");
                         $deviceInfo->save();
                         
                       }else{
                            $deviceInfo = new Device;
                            $deviceInfo->userID = $userID;
                            $deviceInfo->deviceID = $deviceID;
                            $deviceInfo->deviceType = $deviceType;
                            $deviceInfo->loginTime = date("Y-m-d H:i:s");
                            $deviceInfo->save(); 
                       }
                       
                       
                    }
                }
            }
            
            return null;
        }
        
        
        public function getUserIDCheck($userID){
            $rows = DB::table('staffing_users')->select('userName')->where([['userName', '=', $userID]])->first();
            
            return count($rows);
	}
        
        public function getUserInfoByEmail($email){
            $rows = DB::table('staffing_users')->select('id','userName','firstName','email','profilePic','role')
                    ->where([['email', '=', $email], 
                        ['status', '=', 1], 
                        ['deleteStatus', '=', 0]])->first();
            
            return $rows;
	}
        
        public function getUserInfoByUserName($loginID){
            $rows = DB::table('staffing_users')->select(
                    'id',
                    'firstName',
                    'lastName',
                    'email',
                    'role',
                    'businessGroupID',
                    'passwordAlertNotice',
                    'profilePic')->where([['userName', '=', $loginID],['deleteStatus', '=', 0]])->first();
            
            return $rows;
	}
        
        public function checkEmail($email, $groupID = 0, $userID = 0){
            $rowsSql = DB::table('staffing_users')->select('id');
            $rowsSql->where('email', '=', $email);
            $rowsSql->where('deleteStatus', '=', 0);
            
            if($groupID > 0){
              $rowsSql->where('businessGroupID', '=', $groupID);  
            }         
            
            if($userID > 0){
              $rowsSql->where('id', '!=', $userID);  
            }         
            
            $rows = $rowsSql->first();
            
            return count($rows);
	}
       
        public function register(Request $request){
            $requestID = 71;
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $url = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=AIzaSyAhOWKp-29bLW9tta7k1SFwXddOFZuGH98';

                 $androidPkgName = "a.appguys.staffingCallBeta";
                $iOSBundleIdentifier = "ca.appguys.staffingCallBeta";

//                $androidPkgName = "ca.appguys.staffingCallSandbox";
//                $iOSBundleIdentifier = "ca.appguys.staffingCallSandbox";

                $dynamicURL = "https://r4348.app.goo.gl/?";
                $link = $protocol."".$_SERVER['HTTP_HOST']."/".Config('constants.urlVar.DynamicGlobalURL');
                $link .= "?requestID=$requestID";
                $dynamicURL .= "link=".$link;
                $dynamicURL .= "&apn=$androidPkgName";
                $dynamicURL .= "&ibi=$iOSBundleIdentifier";

                $postFields = array (
                    'longDynamicLink' => $dynamicURL
                );

                $data_string = json_encode($postFields);

                $ch = curl_init($url);                                                                      
                curl_setopt($ch, CURLOPT_POST, true);                                                                     
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . strlen($data_string))                                                                       
                );                                                                                                                   

                $result = curl_exec($ch);
                // Close connection
                curl_close($ch);
                $response = json_decode($result); 
                echo '<pre>';print_r($response);
                myHelper::testPushiOS();
                echo 'testing push';
                die;
            
            if($request->email && $request->password){
                
                $is_unique = $this->checkEmail($request->email);				 			
                if ($is_unique > 0) {
                    return response()->json(['status'=>'0','message'=>'Email-id already registered.']);                      

                } else {
                
                
                    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';                        

                    $password = isset($request->password)?$request->password:'';
                    if($password == ''){
                        $password   = substr(str_shuffle($str),0,6);
                    }
                    
                    $usernameStr = substr(str_shuffle($str),0,7);
                    
                    do{
                        $userID = $usernameStr."-".time()."".rand(0,1000);
                        $uniqueCheck = $this->getUserIDCheck($userID);

                    }while($uniqueCheck > 0);


                    $firstName = $request->firstName;
                    
                    $lastName = $request->lastName;
                    $email = $request->email;
                    $password = $request->password;
                    
                    $user = new User;
                    $user->userName = $userID;
                    $user->firstName = $firstName;
                    $user->lastName = $lastName;
                    $user->email = $email;
                    $user->password = Hash::make($password);
                    $user->role = 0;
                    $user->remember_token = $request->_token;

                    

                    if($user->save()){  
                        $userInfo = $this->getUserInfoByEmail($request->email);
                        $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Congrats! You are successfully registered with us.';//'Success';
                        
                        $userInfo->role = (string)$userInfo->role;
                        $userInfo->profilePic = $userInfo->profilePic?$userInfo->profilePic:'';
                        $responseJson['userInfo'] = $userInfo;
                        return response()->json($responseJson);
                    }else{
                        return response()->json(['status'=>'0','message'=>'Registration failed.']); 
                    }
                }
            }else{
                return response()->json(['status'=>'0','message'=>'Request not found']);
            }
        }
        
        public function updateUserInfoByEmail($email, $data = array()){
            if(DB::table('staffing_users')
               ->where('email', $email)
               ->update($data))
               return true;
            else
                return  false;
        } 
        
        
        public function login(Request $request){
            
             
            //Delete Devices
            $this->deleteDevices($request->deviceID, $request->deviceType);
            $userID = $request->loginID;
            $password = $request->password;
            $credentials = $request->only('userName', 'password');
            $token = null;
                //To check on specific or another table.
                //Config::set('auth.providers.user.model', \App\User::class);
            
               if (Auth::attempt(['userName'=>$userID, 'password'=>$password, 'deleteStatus'=> '0'])) {
                    $userInfo = $this->getUserInfoByUserName($userID);
                    if($userInfo->role == '1'){
                        $responseJson['status'] = '0';//'YES';
                        $responseJson['message'] = 'Sorry, username or password you have entered is incorrect, please try again.'; 
                    }else{
                      
                    $success = $this->checkCommonActivities($userInfo->id, $request->deviceID, $request->deviceType, 0);
                    if($success == null){
                        /* Password Alert Notice For Group-Manager*/
                        $responseJson['passwordAlertNotice'] = '0';//'Don't show alert to change password.';
                        if($userInfo->role == '2'){
                          $responseJson['passwordAlertNotice'] = (string)$userInfo->passwordAlertNotice; 
                            //Show alert to change password. 
                          
                          /* Update flag for alert (do not show alert to change password.) */
                          $editUser = User::find($userInfo->id);
                          $editUser->passwordAlertNotice = 0;
                          $editUser->save();
                          /* Update flag for alert (do not show alert to change password.) */
                          
                        }
                        /* Password Alert Notice For Group-Manager*/
                        $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Congrats! You are successfully logged in.';
                        
                        
                    
                        $userInfo->role = (string)$userInfo->role;
                        $userInfo->userID = (string)$userInfo->id;
                        $userInfo->profilePic = $userInfo->profilePic?url('public/'.$userInfo->profilePic):'';
                        unset($userInfo->id);
                        $responseJson['userInfo'] = $userInfo;
                        $usersUnits = array();
                        if($userInfo->role == 2){
                           $usersUnits = DB::table('staffing_businessunits')
                            ->select(
                            'staffing_businessunits.id AS businessUnitID',
                            'staffing_businessunits.unitName AS name'
                           )->where([
                                 ['staffing_businessunits.businessGroupID', '=',$userInfo->businessGroupID],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])
                                   ->orderBy('staffing_businessunits.unitName','ASC')->get(); 
                        }elseif($userInfo->role == 0){
                            $usersUnits = DB::table('staffing_usersunits')
                            ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                            ->select(
                            'staffing_usersunits.businessUnitID',
                            'staffing_businessunits.unitName AS name'
                         )->where([
                                 ['staffing_usersunits.userID', '=',$userInfo->userID],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])->orderBy('staffing_businessunits.unitName','ASC')->get();

                        //   ,['staffing_usersunits.primaryUnit', '=',1]
                        
                        
                        }else{
                           $usersUnits = DB::table('staffing_usersunits')
                            ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                            ->select(
                            'staffing_usersunits.businessUnitID',
                            'staffing_businessunits.unitName AS name'
                           )->where([
                                 ['staffing_usersunits.userID', '=',$userInfo->userID],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])->orderBy('staffing_businessunits.unitName','ASC')->get();  
                        }

                        $responseJson['userInfo']->businessUnits = $usersUnits;

                        /* Get Business Group Info */
                        $groupInfo = Group::select('groupName',
                                'groupCode',
                                'logo')->where([
                                    ['id','=',$userInfo->businessGroupID]
                                    ])->first();
                        $responseJson['userInfo']->groupName = $groupInfo->groupName;
                        $responseJson['userInfo']->groupCode = $groupInfo->groupCode;
                        $responseJson['userInfo']->groupLogo = $groupInfo->logo?url('public/'.$groupInfo->logo):"";
                        /* Get Business Group Info */
                        }else{
                           return $success; 
                        }
                        
                    }
                    
                    return response()->json($responseJson);
                
               }else{
                return response()->json(['status'=>'0','message'=>'Sorry, username or password you have entered is incorrect, please try again.'], 500);
                }
            
        }
        
        
        public function deleteDevices($deviceID = NULL, $deviceType = NULL){
            if($deviceID && $deviceType!=''){
                        
                                   
                       if($deviceType == '1')
                        $deviceType = '1';//iOS
                       elseif($deviceType == '2')
                        $deviceType = '2';//Android 
                       else
                       $deviceType = '0';//WEB             
                       
                       $chkExistance = DB::table('staffing_devices')
                               ->select('id')->where([
                                   ['deviceID','=',$deviceID],
                                   ['deviceType','=',$deviceType]
                                   ])->first();
                       if(count($chkExistance) > 0){
                         $deviceInfo = Device::find($chkExistance->id);
                         $deleteDevice = DB::table('staffing_devices')
                                 ->where([['deviceID','=',$deviceID],
                                   ['deviceType','=',$deviceType]])->delete();
                                 //->where('id',$deviceInfo->id)->delete();
                         
                       }
                       
                       
            }
            return true;
        }




        public function logout(Request $request) 
        {
            $userID = $request->userID;
            $this->deleteDevices($request->deviceID, $request->deviceType);
            $responseJson['status'] = '1';//'YES';
            $responseJson['message'] = 'Congrats! You are successfully logged out.';      
         
            return response()->json($responseJson);
        }
        
        
        
        public function updateToken($token, $email){
            if(DB::table('staffing_users')
               ->where('email', $email)
               ->where('status', 1)
               ->where('deleteStatus', 0)
               ->update(['token' => $token]))
               return true;
            else
                return  false;
        }   
        
        
        public function forgotPassword(Request $request){
            if($request->email){

                $email = $request->email;

                    $userInfo = $this->getUserInfoByEmail($email);
                    if ($userInfo) {
                        // output data of each row
                        $name = "User";//$userInfo->firstName;
                        $useremail = $userInfo->email;
                        $keyGenrate = $this->generateKey(70);
                        $userID = $userInfo->id;
                        $timestamp = time();
                        $tokenKey = $timestamp."G#@T".$useremail."G#@T".$userID."G#@T".$keyGenrate;
                        $token = base64_encode($tokenKey);
                        $tokenUpdate = $this->updateToken($token, $useremail);
                        
                        $link = url(Config('constants.urlVar.resetpassword').$token);
                        if($tokenUpdate){
                            $sentMail = true;
                            $sentMail = $this->sentMailForForgotPassword($token, $useremail, $name);					
                            if($sentMail){
                              return response()->json(['status'=>'1','success'=>'1','message'=>'Password reset link has been sent to your Email'], 200);    
                            }else{
                             return response()->json(['status'=>'0','success'=>'0','message'=>'Failed to sent reset password link to email-id.'], 422);   
                            }
                        }else{
                            return response()->json(['status'=>'0','success'=>'0','message'=>'Failed to reset Password. Please try again.'], 422);
                        }

                    } else {
                      return response()->json(['status'=>'1','success'=>'0','message'=>'Email is not registered with us.'], 500);    
                    }
            }else{
              return response()->json(['status'=>'0','message'=>'Email can not be empty.'], 500);  
              
            }
        }
        
        
        public function sentMailForForgotPassword($token, $email, $name){
            $link = url(Config('constants.urlVar.resetpassword').$token);
            $text_link = url(Config('constants.urlVar.resetpassword').$token);
            //$logo = url('/assets/img/logo.png'); 
            $logo = url('/assets/img/logo_light.png'); 
            $to = $email; 
            $subject = 'StaffingCall App - Forgot Password';	
            $data = array(
                    'name' => $name,
                    'link' => $link,
                    'logo' => $logo,
                    'to' => $to,
                    'subject' => $subject
                    ); 
           	
            
            
            Mail::send('templates.forgotemailapi', $data, function ($message) use ($to, $name, $subject)
            {

                $message->to($to, $name)->subject($subject);
                $message->from('contact@agidev-staffingcall.com', 'Staffing Call App');

            });

            return true;
        }
 
 
        private function generateKey($length = 6) {
            $characters = '0123456789abcdefghijklmnopqrstuvwx yzABCDEFGHIJKL MNOPQRSTUVWXYZ';
            $string = '';

            for ($i = 0; $i < $length; $i ++) {
                    $string .= $characters[
                    mt_rand(0, strlen($characters) - 1)];
            }

            return $string;
	}
        
        
        
        
        
        public function getAuthUser(Request $request){
            Config::set('auth.providers.users.model', \App\User::class);
            $user = JWTAuth::toUser($request->token);
            return response()->json(['result' => $user]);
        }
        
        
        public function getUserInfoByID($userID){
            $rows = DB::table('staffing_users')->select(
                    'id',
                    'firstName',
                    'lastName',
                    'email',
                    'phone',
                    'skills',
                    'role',
                    'businessGroupID',
                    'profilePic')->where([['id', '=', $userID]])->first();
            
            return $rows;
	}
        
        public function myAccount(Request $request){
           if($request->userID){

                $id = $request->userID;

                 $userInfo = $this->getUserInfoByID($id);
                      if ($userInfo) {
                        $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Success.';

                        $userInfo->role = (string)$userInfo->role;
                        $userInfo->userID = (string)$userInfo->id;
                        $userInfo->phone = (string)($userInfo->phone?$userInfo->phone:'');
                        $userInfo->profilePic = $userInfo->profilePic?url('public/'.$userInfo->profilePic):url('/assets/images/profile.jpeg');
                        $userInfo->position = ($userInfo->role == 2)?'Group Manager':(($userInfo->role == 3)?'Super Admin':(($userInfo->role == 4)?'Admin':''));
                       
                        
                        /* Get User Skills */
                        $skillsArr = array();
                        $skillsArr = $userInfo->skills?unserialize($userInfo->skills):array();

                        $userSkills = array();
                        if($skillsArr){
                           $usersCategory = DB::table('staffing_skillcategory')
                        ->select(
                                'id',
                                'skillName'
                                )->whereIn(
                                        'id',$skillsArr)->orderBy('skillName','ASC')->get();

                           foreach($usersCategory as $userSkill){
                               $userSkills[] = $userSkill->skillName;
                           }

                        }
                        /* Get User Skills */
                        
                        
                    $userBusinessUnits = array();
                    if($userInfo->role == 3 || $userInfo->role == 4 || $userInfo->role == 0){    
                        
                        /* Get User's Business Unit */
                        
                        $usersUnits = DB::table('staffing_usersunits')
                         ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                         ->select(
                             'staffing_usersunits.businessUnitID',
                             'staffing_businessunits.unitName'
                             )->where([
                                     ['staffing_usersunits.userID', '=',$id],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])->orderBy('staffing_businessunits.unitName','ASC')->get();
                        
                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        }
                        /* Get User's Business Unit */
                       
                    }elseif($userInfo->role == 2){
                       $usersUnits = DB::table('staffing_businessunits')
                              ->select(
                             'id',
                             'unitName'
                             )->where([
                                     ['businessGroupID', '=',$userInfo->businessGroupID]])->orderBy('unitName','ASC')->get();

                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        } 
                    }
                    
                    
                        if($userInfo->role == 2){
                            $userInfo->skills = 'Group Manager';
                        }else{
                        $userInfo->skills = $userSkills?implode(', ',$userSkills):'';
                        }
                        
                        $userInfo->categoryName = $userBusinessUnits?implode(', ',$userBusinessUnits):'';
                        $userInfo->businessGroupID = (string)$userInfo->businessGroupID;
                        unset($userInfo->id);
                        $responseJson['userInfo'] = $userInfo;
                        return response()->json($responseJson);
                      } else {
                        return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                      }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        public function updateAccount(Request $request){
           if($request->userID){

                $id = $request->userID;

                 $user = User::find($id); 
                 
                      if ($user) {
                        
                          if($request->firstName){
                             $user->firstName =  $request->firstName;
                          }
                          
                             $user->lastName =  $request->lastName?$request->lastName:'';
                             
                          if($request->email){
                              
                            if($user->role == 2){  
                                $is_unique = $this->checkEmail($request->email, $user->businessGroupID, $user->id);
                                if ($is_unique > 0)
                                  return response()->json(['status'=>'0','message'=>'Email-id already exist.']);                      

                            }else{
                                
                                $is_unique = $this->checkEmail($request->email, $user->businessGroupID, $user->id);
                                if ($is_unique > 0)
                                  return response()->json(['status'=>'0','message'=>'Email-id already exist.']);  
                                
                            }
                              
                             $user->email =  $request->email;
                          }
                          
                          if($request->phone){
                             $user->phone =  "+1".(str_replace(array("+91","+1"), "", $request->phone));
                          }
                          
                          if ($request->hasFile('profilePic')) {
                            $originalName = $request->profilePic->getClientOriginalName();
                            $getimageName = time().'.'.$request->profilePic->getClientOriginalExtension();
                            $request->profilePic->move(public_path('assets/uploads/users'), $getimageName);        
                            $avatarUrl = $getimageName;
                            $user->profilePic = 'assets/uploads/users/'.$avatarUrl;
                          }
                       
                          $user->status = 1;
                          
                        if($user->save())
                        {  
                        return response()->json(['status'=>'1','message'=>'Profile updated successfully.']);
                        }else{
                          return response()->json(['status'=>'0','message'=>'Failed to updated your account.']);  
                        }
                        
                        
                        
                      } else {
                        return response()->json(['status'=>'0','message'=>'User not found.']);    
                      }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.']);  
              
            }
        }
        
        
        
        public function changePassword(Request $request){
            
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                 
                    if ($user) {
                        
                        if($request->currentPassword && $request->newPassword){
                        
                            $oldPassword = $request->currentPassword;
                            $newPassword = $request->newPassword;

                            /* ####Check Old Password#### */
                            $checkInfo = DB::table('staffing_users')
                                    ->select('id','password')
                                    ->where([['id', '=', $user->id]])->first();

                            //$hashed_password = Auth::user()->password;
                            $hashed_password = $checkInfo->password;            
                            $current_password = $oldPassword;      
                            if (Hash::check($current_password, $hashed_password)) {     
                                $updatePassword = DB::table('staffing_users')
                                    ->where('id', $user->id)->update(['password' => Hash::make($newPassword)]); 
                                return response()->json(['status'=>'1','message'=>'Password changed successfully.']);
                                   
                            }
                            else{
                                  
                                return response()->json(['status'=>'0','message'=>'Current password did not match.']);  
                            }
                            /* ####Check Old Password#### */
                        }else{
                           return response()->json(['status'=>'0','message'=>'Password field is empty.']);  
                        }
                    }else {
                        return response()->json(['status'=>'0','message'=>'User not found.']);    
                      }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.']);  
              
            }
                
            
            
            
        }
        
        
        
        
        /* STAFFING CALL NEW REQUSET API */
        
        //Admin Requests FOR ALL Higher Level User Home Page Scheduled Calls
        public function adminRequestList(Request $request){
           if($request->userID){
                $id = $request->userID;
                $user = User::find($id);  
                
                if ($user) {                    
                    $requestsList = array();                    
                    if($user->role != '2' && $user->role != '1'){     
                        $unitInfo = DB::table('staffing_businessunits')
                         ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                         ->select(
                                 'staffing_businessunits.id',
                                 'staffing_businessunits.unitName'
                            )->where([['staffing_usersunits.userID','=',$id],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]
                                ])->first();
                    }
        
                $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.postingStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        )->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                
                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                
                if($user->role != '2' && $user->role != '1'){
                    
                    if($request->businessUnitID > 0){
                        $unitInfo->id = $request->businessUnitID;
                    }
                    
                    $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $unitInfo->id);
                
                    if($user->role == '4')
                        $requestPostsSql->where('staffing_staffrequest.ownerID', '=', $id);
                    
                    
                }
                
                if($user->role == '2'){
                    
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $request->businessUnitID);
                    }
                    
                    
                    $requestPostsSql->where('staffing_staffrequest.businessGroupID', '=', $user->businessGroupID);
                }
                
               $requestPosts = $requestPostsSql->get();
                    
                    
                    
                    
                    foreach($requestPosts as $requestPost){
                        
                        
                        /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPost->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */        
                        
                        
                        
                        
                        if($requestPost->shiftType == 1)
                        $shiftTime = $requestPost->customShiftStartTime;
                        else
                        $shiftTime = $requestPost->startTime;
                        
                        $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime))))));
                            
                        $shiftTimeReal = (date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".$shiftTime)));
                        
                        
                        $requiredTypeOfStaffSkills = '';
                            
                        if($requestPost->requiredStaffCategoryID != ''){
                           $requiredStaffCategoryIDs = explode(",", $requestPost->requiredStaffCategoryID);
                           $getSkills = DB::table('staffing_skillcategory')
                             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                           $skillsName = array();
                           foreach($getSkills as $getSkill){
                              $skillsName[] = $getSkill->skillName; 
                           }

                           if($skillsName)
                             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                        }     
                        
                        
                       $requestsList[] = array(
                         'id' => (string)$requestPost->postID,
                         'ownerName' => $requestPost->staffOwner,
                         'notes' => $requestPost->notes?$requestPost->notes:"", 
                         'shiftTime' => (string)$requestPost->staffingStartDate,//(string)$shiftTimeStamp , 
                         'shiftTimeRealFormat' => (string)$shiftTimeReal ,
                         'typeOfStaff' => (string)$requiredTypeOfStaffSkills  ,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount ,
                         'requestStatus' => (string)$requestPost->postingStatus 
                           
                       );
                    }
                    
                   $responseJson['status'] =  '1';
                   $responseJson['message'] =  "Success";
                   $responseJson['data'] =  $requestsList;
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                  return response()->json($responseJson);  
                    
                }else{
                    return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
                }
                
            } else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        public function pendingRequestList(Request $request){
           if($request->userID){
                $id = $request->userID;
                $user = User::find($id);  
                
                if ($user) {                    
                    $requestsList = array();                    
                    if($user->role != '2' && $user->role != '1'){     
                        $unitInfo = DB::table('staffing_businessunits')
                         ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                         ->select(
                                 'staffing_businessunits.id',
                                 'staffing_businessunits.unitName'
                            )->where([['staffing_usersunits.userID','=',$id],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])->first();
                    }
        
                $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.postingStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        )->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                
                $requestPostsSql->where('staffing_staffrequest.postingStatus', '=', 0); 
                
                if($user->role != '2' && $user->role != '1'){
                    
                    if($request->businessUnitID > 0){
                        $unitInfo->id = $request->businessUnitID;
                    }
                    
                    $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $unitInfo->id);
                
                    if($user->role == '4')
                        $requestPostsSql->where('staffing_staffrequest.ownerID', '=', $id);
                    
                    
                }
                
                if($user->role == '2'){
                    
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $request->businessUnitID);
                    }
                    
                    
                    $requestPostsSql->where('staffing_staffrequest.businessGroupID', '=', $user->businessGroupID);
                }
                
               $requestPosts = $requestPostsSql->get();
                    
                    
                    
                    
                    foreach($requestPosts as $requestPost){
                        
                        
                        /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPost->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */        
                        
                        
                        
                        
                        if($requestPost->shiftType == 1)
                        $shiftTime = $requestPost->customShiftStartTime;
                        else
                        $shiftTime = $requestPost->startTime;
                        
                        $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime))))));
                            
                        $shiftTimeReal = (date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".$shiftTime)));
                       
                        
                        $requiredTypeOfStaffSkills = '';
                            
                        if($requestPost->requiredStaffCategoryID != ''){
                           $requiredStaffCategoryIDs = explode(",", $requestPost->requiredStaffCategoryID);
                           $getSkills = DB::table('staffing_skillcategory')
                             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                           $skillsName = array();
                           foreach($getSkills as $getSkill){
                              $skillsName[] = $getSkill->skillName; 
                           }

                           if($skillsName)
                             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                        } 
                        
                        
                        
                       $requestsList[] = array(
                         'id' => (string)$requestPost->postID,
                         'ownerName' => $requestPost->staffOwner,
                         'notes' => $requestPost->notes?$requestPost->notes:"", 
                         'shiftTime' => (string)$requestPost->staffingStartDate,//(string)$shiftTimeStamp , 
                         'shiftTimeRealFormat' => (string)$shiftTimeReal,
                         'typeOfStaff' => (string)$requiredTypeOfStaffSkills,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount,
                         'requestStatus' => (string)$requestPost->postingStatus 
                           
                       );
                    }
                    
                   $responseJson['status'] =  '1';
                   $responseJson['message'] =  "Success";
                   $responseJson['data'] =  $requestsList; 
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                    
                  return response()->json($responseJson);  
                    
                }else{
                    return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
                }
                
            } else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        /* Open Staffing Requests For Higher Admins */
        
        public function openRequests(Request $request){ /* Not In Use */
           if($request->userID){

                $id = $request->userID;

                $user = User::find($id);                  
                if ($user) {
                    
                    $requestsList = array();
                    
                if($user->role != '2' && $user->role != '1'){     
                    $unitInfo = DB::table('staffing_businessunits')
                     ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                     ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                        )->where([['staffing_usersunits.userID','=',$id],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]])->first();
                }
        
                $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.postingStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        )->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                
                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                
                if($user->role != '2' && $user->role != '1'){
                    
                    if($request->businessUnitID > 0){
                        $unitInfo->id = $request->businessUnitID;
                    }
                    
                    $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $unitInfo->id);
                
                    if($user->role == '4')
                        $requestPostsSql->where('staffing_staffrequest.ownerID', '=', $id);
                    
                    
                }
                
                if($user->role == '2'){
                    
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $request->businessUnitID);
                    }
                    
                    
                    $requestPostsSql->where('staffing_staffrequest.businessGroupID', '=', $user->businessGroupID);
                }
                
               $requestPosts = $requestPostsSql->get();
                    
                    
                    
                    
                    foreach($requestPosts as $requestPost){
                        
                        
                        /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPost->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */        
                        
                        
                        
                        
                        if($requestPost->shiftType == 1)
                        $shiftTime = $requestPost->customShiftStartTime;
                        else
                        $shiftTime = $requestPost->startTime;
                        
                        $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime))))));
                            
                        $shiftTimeReal = (date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".$shiftTime)));
                       
                        $requiredTypeOfStaffSkills = '';
                            
                        if($requestPost->requiredStaffCategoryID != ''){
                           $requiredStaffCategoryIDs = explode(",", $requestPost->requiredStaffCategoryID);
                           $getSkills = DB::table('staffing_skillcategory')
                             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                           $skillsName = array();
                           foreach($getSkills as $getSkill){
                              $skillsName[] = $getSkill->skillName; 
                           }

                           if($skillsName)
                             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                        }     
                        
                        
                       $requestsList[] = array(
                         'id' => (string)$requestPost->postID,
                         'ownerName' => $requestPost->staffOwner,
                         'notes' => $requestPost->notes?$requestPost->notes:"", 
                         'shiftTime' => (string)$requestPost->staffingStartDate,//$shiftTimeStamp , 
                         'shiftTimeRealFormat' => (string)$shiftTimeReal ,
                         'typeOfStaff' => (string)$requiredTypeOfStaffSkills,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount ,
                         'requestStatus' => (string)$requestPost->postingStatus
                       );
                    }
                    
                   $responseJson['status'] =  '1';
                   $responseJson['message'] =  "Success";
                   $responseJson['data'] =  $requestsList;
                  
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                    
                    
                  return response()->json($responseJson);  
                    
                }else{
                    return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
                }
                
            } else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        /* Open Staffing Requests For Higher Admins */
        
        
        
        public function newRequestQuestionSetUp(Request $request){
           if($request->userID){

            $id = $request->userID;

            $user = User::find($id); 
                 
                 
                 
                if ($user && $user->role != '0') {
                    
                 if($user->role == '3' || $user->role == '4'){
                    $groupAndUnitsInfo = DB::table('staffing_businessunits')
                    ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_businessunits.businessGroupID')
                    ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                    ->select(
                    'staffing_businessunits.id AS businessUnitID',
                    'staffing_businessunits.unitName',
                    'staffing_groups.groupCode',
                    'staffing_groups.groupName'
                    )->where([
                        ['staffing_groups.id','=',$user->businessGroupID],
                         ['staffing_usersunits.userID','=',$user->id],
                                ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]
                            
                            ])->get();  
                    
                    $groupsInformation = DB::table('staffing_groups')
                       ->select(
                        'groupCode',
                        'groupName'
                        )->where([
                        ['id','=',$user->businessGroupID]])->first();     
                    
                 }else{

                   $groupAndUnitsInfo = DB::table('staffing_businessunits')
                    ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_businessunits.businessGroupID')
                    ->select(
                    'staffing_businessunits.id AS businessUnitID',
                    'staffing_businessunits.unitName',
                    'staffing_groups.groupCode',
                    'staffing_groups.groupName'
                    )->where([['staffing_groups.id','=',$user->businessGroupID],
                                ['staffing_businessunits.deleteStatus','=',0],
                        ['staffing_businessunits.status','=',1]])->get(); 
                    
                    $groupsInformation = DB::table('staffing_groups')
                       ->select(
                        'groupCode',
                        'groupName'
                        )->where([
                        ['id','=',$user->businessGroupID]])->first();     
                 }

                    $jsonResponse['status'] = '1';
                          
                    if($groupsInformation){
                        
                        $jsonResponse['groupInfo'] = array(
                            'groupID' => (string)$user->businessGroupID,
                            'groupCode' => (string)$groupsInformation->groupCode,
                            'name' => (string)$groupsInformation->groupName                                
                        );
                        
                        $jsonResponse['businessUnits'] = array();
                        
                        foreach($groupAndUnitsInfo as $unit){
                            $jsonResponse['businessUnits'][] = array(
                                  'businessUnitID' =>   (string)$unit->businessUnitID,
                                  'name' =>  (string)$unit->unitName
                                );
                        }
                        
                        
                        
//                        $reasons = DB::table('staffing_requestreasons')
//                        ->select(
//                        'id',
//                        'reasonName',
//                        'defaultOf',
//                        'status'        
//                        )->orWhere('businessGroupID','=',$user->businessGroupID)
//                         ->orWhere('businessGroupID','=',0)->orderBy('id','ASC')    
//                        ->get();
                        
                        $reasons = DB::table('staffing_requestreasons')
                        ->select(
                        'id',
                        'reasonName',
                        'defaultOf',
                        'status'        
                        )->where('businessGroupID','=',$user->businessGroupID)
                         ->where('status','=',1)->orderBy('id','ASC')    
                        ->get();
                        
                        
                        
                        $jsonResponse['requestReasons'] = array();
                        foreach($reasons as $reason){  
                            $changable = 0;
                            if($reason->defaultOf > 0 && $reason->defaultOf == '1'){
                              $changable = 1;  
                            }
                                $jsonResponse['requestReasons'][] = array(
                                    'requestReasonID' => (string)$reason->id,
                                    'name' => $reason->reasonName,
                                    'changable' => (string)$changable
                                );
                        }
                        
                        
//                        $vacancyReasons = DB::table('staffing_vacancyreasons')
//                        ->select(
//                        'id',
//                        'reasonName',
//                        'status'        
//                        )->orWhere('businessGroupID','=',$user->businessGroupID)
//                         ->orWhere('businessGroupID','=',0)->get();
                        
                        $vacancyReasons = DB::table('staffing_vacancyreasons')
                        ->select(
                        'id',
                        'reasonName',
                        'status'        
                        )->where('businessGroupID','=',$user->businessGroupID)
                         ->where('status','=',1)->get();
                        
                        $jsonResponse['vacancyReasons'] = array();
                        foreach($vacancyReasons as $vacancyReason){  
                            $jsonResponse['vacancyReasons'][] = array(
                                'vacancyReasonID' => (string)$vacancyReason->id,
                                'name' => $vacancyReason->reasonName
                                );
                        }
                       
                        
                        $staffingCategorys = DB::table('staffing_skillcategory')
                        ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',$user->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
                        
                        $jsonResponse['staffingCategory'] = array();
                        foreach($staffingCategorys as $staffingCategory){  
                            $jsonResponse['staffingCategory'][] = array(
                                'categoryID' => (string)$staffingCategory->id,
                                'name' => $staffingCategory->skillName
                                );
                        }
                        
                        

                        $jsonResponse['staffingShiftTime'] = array(
                            ['shiftBlockID' => '1',
                            'startTime' => '2:00 PM',
                            'endTime' => '6:00 PM',
                            'name' => 'Day Shift'],
                            ['shiftBlockID' => '2',
                            'startTime' => '6:00 PM',
                            'endTime' => '10:00 PM',
                            'name' => 'Night Shift'
                                ]
                        );
                        
                        $jsonResponse['shiftClosingTime'] = array(
                            ['name' => '30 Minutes',
                            'id' => '30'],
                            ['name' => '1 Hour',
                            'id' => '60'
                                ] ,
                            ['name' => '1 Hour 30 Minutes',
                            'id' => '90'
                                ] ,
                            ['name' => '2 Hours',
                            'id' => '120'
                                ] ,
                            ['name' => '2 Hours 30 Minutes',
                            'id' => '150'
                                ] ,
                            ['name' => '3 Hours',
                            'id' => '180'
                                ] 
                        );
                        
                        /* Offer Algorithm */
                            $algorithmsSql = DB::table('staffing_offeralgorithm')
                                ->select(
                                'id',
                                'businessGroupID',
                                'name',
                                'notes',
                                'type',
                                'status'        
                                );

                            $algorithmsSql->where('businessGroupID','=',$user->businessGroupID);
                            $algorithmsSql->where(function ($query){
                                    $query->orWhere('type', '=', 'simple');
                                    $query->orWhere('type', '=', 'open'); 
                            });

                            $algorithmsSql->orderBy('id','ASC');

                            $algorithms = $algorithmsSql->get();
                        /* Offer Algorithm */
                        
                        if($algorithms){
                            foreach($algorithms as $algorithm){
                                $jsonResponse['offerAlgorithm'][] = array(
                                    'id' => $algorithm->id,
                                    'name' => $algorithm->name." (".$algorithm->type.")"
                                );
                            }
                        }
                        
                          
                          return response()->json($jsonResponse);
                    }else{
                     return response()->json(['status'=>'0','message'=>'Group not found.'], 500);    
                    }
                        
                      } else {
                        return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                      }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        
        
        
        
        public function getStaffAndShifts(Request $request){
           if($request->userID){

            $id = $request->userID;
            $businessUnitID = $request->businessUnitID;

            $user = User::find($id); 
                 
                if ($user) {

                    $jsonResponse['status'] = '1';
                          
                    if($businessUnitID){
                        
                        $staffsSql = DB::table('staffing_users')
                        ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id')
                        ->select(
                                'staffing_users.id',
                                DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name")
                        );
                        $staffsSql->whereIn('staffing_users.role',[0, 4]);
                        $staffsSql->where('staffing_usersunits.businessUnitID','=',$businessUnitID);
                        $staffsSql->where('staffing_users.deleteStatus','=',0);
                        $staffsSql->where('staffing_users.status','=',1);
                        
                        $staffsSql->orderBy('staffing_users.firstName','ASC');
                        $staffs = $staffsSql->get();
        
        
        
        
                    $staffingCategory = DB::table('staffing_skillcategory')
                        ->select(
                        'id AS categoryID',
                        'skillName AS name'
                        )->where([
                                ['businessGroupID','=',$user->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
        
                        $shifts = DB::table('staffing_shiftsetup')
                        ->select(
                        'id AS shiftBlockID',
                        'shiftType',
                        DB::raw('DATE_FORMAT(startTime, "%h:%i %p") as startTime'),
                        DB::raw('DATE_FORMAT(endTime, "%h:%i %p") as endTime'),
                        DB::raw("CONCAT(DATE_FORMAT(startTime, \"%h:%i %p\"), ' - ', DATE_FORMAT(endTime, \"%h:%i %p\")) AS name")        
                        )->where([
                                ['businessUnitID','=',$businessUnitID]
                            ])->orderBy('startTime','ASC')->get();
                        
                        $jsonResponse['staff'] = $staffs;
                        $jsonResponse['staffingCategory'] = $staffingCategory;
                        $jsonResponse['staffingShiftTime'] = $shifts; 
                        
                          return response()->json($jsonResponse);
                          
                    }else{
                     return response()->json(['status'=>'0','message'=>'Business unit not found.'], 500);    
                    }
                        
                } else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        public function saveNewStaffingRequest(Request $request){
            
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                
                if ($user) {
                    
                  if($user->role == 2 || $user->role == 3 || $user->role == 4){
                    
                    $businessGroupID = $user->businessGroupID;
        
                    $businessUnitID = $request->businessUnitID;
                    $requestReasonID = $request->requestReasonID;
                    $changableForDefaultOfForRequestReasonID = $request->changable;


                    $lastMinuteStaffID = $request->staffID;
                    //$timeOfCallMade = date("H:i:s",strtotime($request->callMadeTime));
                    $timeOfCallMade = date("Y-m-d H:i:s",strtotime($request->callMadeTime));
                    $vacancyReasonID = $request->vacancyReasonID;


                    if($request->categoryID != ''){
                        $skillCategoryIDs = explode(',', $request->categoryID);
                        $requiredStaffCategoryID = implode(',', $skillCategoryIDs);
                    }
                    
                    //0=>Required All Type,1=>Junior,2=>Intermediate,3=>Experienced
                    $requiredExperiencedLevel = $request->requiredExperiencedLevel?$request->requiredExperiencedLevel:0;
                    
                    $numberOfOffers = $request->numberOfOffers;
                    
                    $startDateOfPost = (string)$request->startDateOfStaffing;
                    $endDateOfPost = $startDateOfPost;//(string)$request->endDateOfStaffing;
                    
                    $staffingStartDate = date("Y-m-d", strtotime($startDateOfPost));
                    $staffingEndDate = date("Y-m-d", strtotime($endDateOfPost));
                    
                    $shiftType = ($request->shiftBlockID > 0)?0:1;
                    $staffingShiftID = ($request->shiftBlockID > 0)?$request->shiftBlockID:0;
                    
                    if($request->shiftBlockID > 0){
                        $customShiftStartTime = '';
                        $customShiftEndTime = '';
                    }else{
                         $customShiftStartTime = date("H:i:s",strtotime($request->startTime));
                        $customShiftEndTime = date("H:i:s",strtotime($request->endTime));
                    }
                    
                    
                    $notes = $request->notes;

                    $ownerID = $user->id;
                    $updatedBy = $user->id;
                    if($user->role == 4 && $user->needApproval != 1){
                      $approvedBy = $user->id;
                      $postingStatus = 1;  
                    }else{
                    $approvedBy = ($user->role == 2 || $user->role == 3)?$user->id:0;
                    $postingStatus = ($user->role == 2 || $user->role == 3)?1:0;
                    }
                    
                    
                    $post = new Requestcall;
                    $post->businessGroupID = $businessGroupID;
                    $post->businessUnitID = $businessUnitID;
                    $post->requestReasonID = $requestReasonID;

                    if($requestReasonID == 1 || $changableForDefaultOfForRequestReasonID == 1){
                        $post->lastMinuteStaffID = $lastMinuteStaffID;
                        $post->timeOfCallMade = $timeOfCallMade;
                        $post->vacancyReasonID = $vacancyReasonID; 
                    }

                    if($requiredStaffCategoryID){
                        $post->requiredStaffCategoryID = $requiredStaffCategoryID;
                    }
                    
                    //0=>Required All Type,1=>Junior,2=>Intermediate,3=>Experienced
                    $post->requiredExperiencedLevel = $requiredExperiencedLevel?$requiredExperiencedLevel:0;
                    
                    $post->numberOfOffers = $numberOfOffers;
                    $post->staffingStartDate = $staffingStartDate;
                    
                    $post->shiftType = $shiftType;

                    if($shiftType == 1){
                        $post->customShiftStartTime = $customShiftStartTime;
                        $post->customShiftEndTime = $customShiftEndTime;
                        $staffingShiftID = 0;
                    }
            
          
            
            
                    $post->staffingShiftID = $staffingShiftID?$staffingShiftID:0;
                    $post->notes = $notes;
                    $post->ownerID = $ownerID;
                    $post->updatedBy = $updatedBy;
                    $post->approvedBy = $approvedBy;
                    $post->postingStatus = $postingStatus; 
            
                    /* Offer Algorithm */
                    $post->offerAlgorithmID = $request->offerAlgorithmID?$request->offerAlgorithmID:0;
                    /* Offer Algorithm */
                    
                    
                   
                    
                                 
                
                if($staffingShiftID > 0 && $shiftType != 1){
                    $getShiftTiming = DB::table('staffing_shiftsetup')
                            ->select('startTime','endTime')->where([['id', '=', $staffingShiftID]])->first();

                    $shiftStartTimeForPartial = $getShiftTiming->startTime;
                    $shiftEndTimeForPartial = $getShiftTiming->endTime;
                
                } else{
                    $shiftStartTimeForPartial = $customShiftStartTime;
                    $shiftEndTimeForPartial = $customShiftEndTime;
                }
                
                
                
                    
                    $shiftStartTime = $staffingStartDate." ".(date("g:i A",strtotime($shiftStartTimeForPartial)));
                    $shiftEndTime = $staffingEndDate." ".(date("g:i A",strtotime($shiftEndTimeForPartial)));
                    
                    
                    if(strtotime($shiftStartTime) > strtotime($shiftEndTime)){
                        $staffingEndDate = (date("Y-m-d",strtotime($staffingEndDate . " +1 day")));                        
                    }
                    
                    $post->staffingEndDate = $staffingEndDate;
                
                
                    $closingTimeOfShift = $shiftStartTimeForPartial;
                    
                    /* Posting Closing Time */
                    if($request->shiftCloseTime > 0){
                        $startTimeOfShiftForClosing = date("Y-m-d H:i:s",strtotime($staffingStartDate." ".$closingTimeOfShift));
                        $minuteToAdd = (string)$request->shiftCloseTime;
                        $minutes_to_add = $minuteToAdd;

                        $time = new \DateTime($startTimeOfShiftForClosing);
                        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));

                        $stamp = $time->format('Y-m-d H:i:s');
                        
                        
                        $post->closingTime = $stamp;
                        
                    }else{
                        $post->closingTime = date("Y-m-d H:i:s",strtotime($staffingStartDate." ".$closingTimeOfShift));
                    }
                    /* Posting Closing Time */
            
                    
            
        
                    if($post->save())
                    {
                        
                        
            
                        /* Update Group column updated_at */
                        $groupUpdate = Group::find($businessGroupID);
                        $groupUpdate->updated_at = date("Y-m-d H:i:s");
                        $groupUpdate->save();
                        /* Update Group column updated_at */
            
                        /* Update Business Unit column updated_at */
                        $unitUpdate = Businessunit::find($businessUnitID);
                        $unitUpdate->updated_at = date("Y-m-d H:i:s");
                        $unitUpdate->save();
                        /* Update Business Unit column updated_at */
            
                        
                        if($user->role == 4 && $user->needApproval == 1 && $post->approvedBy == 0){
                            
                            $androidDevicesArray = array();
                            $iosDevicesArray = array();
                        /* Send Alert To Super Admin And Group Manager */ 
                            $getSuperAdminInfo = DB::table('staffing_users')
                            ->select('staffing_users.id AS userID')
                            ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id')
                            ->where([['staffing_usersunits.businessUnitID','=',$businessUnitID],
                            ['staffing_users.role','=',3],
                            ['staffing_users.businessGroupID','=',$businessGroupID],
                            ['staffing_users.pushNotification','=',1]
                            ])->get();
                            if(count($getSuperAdminInfo) > 0){

                                foreach($getSuperAdminInfo as $superAdminInfo){  
                                    $getSuperAdminDevices = DB::table('staffing_devices')
                                      ->select('deviceID','deviceType')
                                      ->where([['userID','=',$superAdminInfo->userID]])->get();

                                    if(count($getSuperAdminDevices) > 0){
                                        foreach($getSuperAdminDevices as $getSuperAdminDevice){
                                          $deviceToken = $getSuperAdminDevice->deviceID?$getSuperAdminDevice->deviceID:''; 
                                            if($getSuperAdminDevice->deviceType == '1'){
                                                if($deviceToken)
                                                  $iosDevicesArray[] = $deviceToken;
                                            }elseif($getSuperAdminDevice->deviceType == '2'){
                                                if($deviceToken)
                                                   $androidDevicesArray[] = $deviceToken;
                                            } 
                                        } 
                                    }

                                }
                            }


                            /* Group Manager INFO */ 
                            $getManagerInfo = DB::table('staffing_users')
                             ->select('staffing_users.id AS userID')
                             ->where([['staffing_users.role','=',2],
                                 ['staffing_users.businessGroupID','=',$businessGroupID],
                                 ['staffing_users.pushNotification','=',1]
                                 ])->first();
                            if(count($getManagerInfo) > 0){
                                $getManagerDevices = DB::table('staffing_devices')
                                ->select('deviceID','deviceType')
                                ->where([['userID','=',$getManagerInfo->userID]])->get();

                                if(count($getManagerDevices) > 0){
                                    foreach($getManagerDevices as $getManagerDevice){
                                      $deviceToken = $getManagerDevice->deviceID?$getManagerDevice->deviceID:''; 
                                        if($getManagerDevice->deviceType == '1'){
                                            if($deviceToken)
                                              $iosDevicesArray[] = $deviceToken;
                                        }elseif($getManagerDevice->deviceType == '2'){
                                            if($deviceToken)
                                               $androidDevicesArray[] = $deviceToken;
                                        } 
                                    } 
                                }
                            }  
                            /* Group Manager INFO */ 
                            
                                                               
                            /* Get Push Message */
                             $pushMessage2 = $this->getRequestInformationForPush($post);
                             /* Get Push Message */ 

                            $msg_payload = array (
                            'mtitle' => $user->firstName." ".$user->lastName." created New staffing request.",
                            'mdesc' => $pushMessage2,
                            'notificationStatus' => 1,
                            'requestID' => $post->id
                            );

                            $msg_payloadAndroid = array (
                            'mtitle' => $user->firstName." ".$user->lastName." created New staffing request.",
                            'mdesc' => $pushMessage2,
                            'notificationStatus' => 1,
                            'requestID' => $post->id
                            );   


                            if($androidDevicesArray){
                                if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                              $androidPusStatus = true;
                            }

                            if($iosDevicesArray){
                                if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                    $iosPusStatus = true;
                            }          
                            /* Send Alert To Super Admin And Group Manager */
                        }else{
                            
                            $this->sendPushNotificationToEndUsers($user, 
                                $businessUnitID, 
                                $businessGroupID, 
                                $post, 
                                $requiredStaffCategoryID, 
                                $staffingStartDate, 
                                $staffingShiftID, 
                                $requiredExperiencedLevel, 
                                $shiftStartTimeForPartial, 
                                $shiftEndTimeForPartial);
                            
                            $this->createLogForNewRequest($post);
                        }
                        
                        return response()->json(['status'=>'1',
                        'message'=>'Staffing request created successfully.'
                        ]); 
                      
                    }else{
                       return response()->json(['status'=>'0','message'=>'Failed to save New Staffing Call.'], 500); 
                    }
                  }else{
                     return response()->json(['status'=>'0','message'=>'You are not allowed to post new call.'], 500);     
                  }
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        public function createLogForNewRequest($post){ /*To send notification using CRON */
            
            $requestLog = new RequestLog;
            $requestLog->requestID = $post->id;
            $requestLog->notificationStatus = 0;
            $requestLog->save();
            
        }
        
        public function acceptCronRequest(Request $request){
            
            
            $cronRequests = RequestLog::where([['notificationStatus', '=', '0']])->get();
            if($cronRequests->count() > 0){
                foreach($cronRequests as $cronRequest){
                    $post = Requestcall::find($cronRequest->requestID);
                    $businessUnitID = $post->businessUnitID;
                    $businessGroupID = $post->businessGroupID;
                    $requiredStaffCategoryID = $post->requiredStaffCategoryID;
                    $staffingStartDate = $post->staffingStartDate;
                    $staffingShiftID = $post->staffingShiftID;
                    $requiredExperiencedLevel = $post->requiredExperiencedLevel;
                    if($post){
                        $requestLog = RequestLog::find($cronRequest->id);
                        if($requestLog)
                        $requestLog->delete();
                        $user = User::find($post->ownerID);
                        if($user){
                            $this->sendNotificationAfterNewRequest($user, 
                                $businessUnitID, 
                                $businessGroupID, 
                                $post, 
                                $requiredStaffCategoryID, 
                                $staffingStartDate, 
                                $staffingShiftID, 
                                $requiredExperiencedLevel);

                        }
                    }
                }
            }
            
                    return response()->json(['success'=>'1',
                        'message'=>'Scheduled'
                        ]); 
            
        }
        
        
        
    public function getRequestInformationForPush($post){
        $businessUnitID = $post->businessUnitID;
        $staffingShiftID = $post->staffingShiftID;
        $customShiftStartTime = $post->customShiftStartTime;
        $customShiftEndTime = $post->customShiftEndTime;
        $shiftStartTimeForPartial = '';
        $shiftEndTimeForPartial = '';
            if($staffingShiftID > 0){
                $getShiftTiming = DB::table('staffing_shiftsetup')
                        ->select('startTime','endTime')->where([['id', '=', $staffingShiftID]])->first();

                $shiftStartTimeForPartial = $getShiftTiming->startTime;
                $shiftEndTimeForPartial = $getShiftTiming->endTime;
                
            } else{
                $shiftStartTimeForPartial = $customShiftStartTime;
                $shiftEndTimeForPartial = $customShiftEndTime;
            }
            
            
            $getBusinessUnitInfo = Businessunit::find($businessUnitID);
            $unitName = '';
            if($getBusinessUnitInfo){
                $unitName = $getBusinessUnitInfo->unitName;
            }

            $requestDate = date("j M Y",strtotime($post->staffingStartDate));

            $shiftTime = '';
            if($shiftStartTimeForPartial && $shiftEndTimeForPartial){
             $shiftTime = date("g:i A", strtotime($shiftStartTimeForPartial)).' - '.date("g:i A", strtotime($shiftEndTimeForPartial));   
            }

            $pushMessage = $unitName.", ".$requestDate;
            $pushMessage .= ", ".$shiftTime;   
            
            return $pushMessage;
            
    }
    

        
        public function sendPushNotificationToEndUsers($user, 
                        $businessUnitID, 
                        $businessGroupID, 
                        $post, 
                        $requiredStaffCategoryID, 
                        $staffingStartDate, 
                        $staffingShiftID, 
                        $requiredExperiencedLevel, 
                        $shiftStartTimeForPartial = NULL, 
                        $shiftEndTimeForPartial = NULL){
            
            
              
        $androidDevicesArray = array();
        $iosDevicesArray = array();  
            
            if($user->role == 2 || 
                    $user->role == 3 || 
                    ($user->role == 4 && ($user->needApproval != 1 || $post->approvedBy != 0)))
                {
                    
                
                    /* Send To All End-Users which meet Criteria */
                    if($requiredStaffCategoryID){
                        $requiredStaffCategoryIDs = explode(",", $requiredStaffCategoryID); 
                    }
                    
                    /* Check User Availability for Shift Start Date */
                    $unavailableUsers = array();
                    $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                        ->select('userID','shiftID');

                    $userAvailabilitySql->where('onDate','=',$staffingStartDate);
                    $userAvailabilitySql->whereIn('availabilityStatus', [0, 2]);
                    $userAvailability = $userAvailabilitySql->get();

                    if($staffingShiftID > 0){
                        if($userAvailability){
                            foreach($userAvailability as $userAvailabile){
                                if($userAvailabile->shiftID == $staffingShiftID){
                                   $unavailableUsers[] = $userAvailabile->userID;
                                }    
                            }
                        }                                
                    }else{
                        if($userAvailability && $post->shiftType != 1){
                            foreach($userAvailability as $userAvailabile){
                                   $unavailableUsers[] = $userAvailabile->userID;
                            }
                        }  
                    }
                    /* Check User Availability for Shift Start Date */

                    $getEndUserInfoSql = DB::table('staffing_users')
                    ->select('staffing_users.id AS userID','staffing_users.skills')
                    ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id');

                    $getEndUserInfoSql->where('staffing_usersunits.businessUnitID','=',$businessUnitID);
                    $getEndUserInfoSql->where('staffing_users.businessGroupID','=',$businessGroupID);
                    //$getEndUserInfoSql->where('staffing_users.pushNotification','=',1);
                    $getEndUserInfoSql->whereIn('staffing_users.role',[0,4]);

//                    if($requiredExperiencedLevel > 0){
//                        if($requiredExperiencedLevel == 3){//Experienced
//                          $getEndUserInfoSql->where('staffing_users.experiencedLevel', '=', 3);  
//                        }else if($requiredExperiencedLevel == 2){//Intermediate
//                          $getEndUserInfoSql->whereIn('staffing_users.experiencedLevel',[2,3]);  
//                        } else if($requiredExperiencedLevel == 1){//Junior
//                          $getEndUserInfoSql->whereIn('staffing_users.experiencedLevel',[0,1,2,3]);  
//                        } 
//                    }

                    if(count($unavailableUsers) > 0)
                    $getEndUserInfoSql->whereNotIn('staffing_users.id',$unavailableUsers);

                    $getEndUserInfo = $getEndUserInfoSql->get();

                    if(count($getEndUserInfo) > 0){
                        $newEndUsers = array();
                        foreach($getEndUserInfo as $endUserInfo){
                          /* Fetch Those Users who matched with required Staff */ 
                            $endUserSkills = array();
                            $endUserSkills = $endUserInfo->skills?unserialize($endUserInfo->skills):array();
                            if($requiredStaffCategoryIDs){
                                //if(array_intersect($requiredStaffCategoryIDs, $endUserSkills)){
                                
                            if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){
                                    $newEndUsers[] = $endUserInfo->userID;
                                }
                            }else{
                                $newEndUsers[] = $endUserInfo->userID;
                            }
                            /* Fetch Those Users who matched with required Staff */  
                        }

                        /* Fetch Required Staffs */
                        $requiredStaffs = DB::table('staffing_users')
                        ->select('staffing_users.id AS userID',
                                'staffing_users.phone',
                                'staffing_users.email',
                                'staffing_users.firstName',
                                'staffing_users.lastName',
                                'staffing_users.emailNotification',
                                'staffing_users.pushNotification',
                                'staffing_users.smsNotification')
                            ->whereIn('staffing_users.id',$newEndUsers)->get();    
                        /* Fetch Required Staffs */
                        $requiredStaffsUsers = array();
                        $requiredStaffsUsersEmailAndSms = array();
                        if(count($requiredStaffs) > 0){
                            foreach($requiredStaffs as $requiredStaff){  
                                if($requiredStaff->pushNotification == '1'){
                                $requiredStaffsUsers[] = $requiredStaff->userID;
                                }
                                /* Sent Email & SMS */
                                $requiredStaffsUsersEmailAndSms[] = array(
                                  'email' => $requiredStaff->email,  
                                  'phone' => $requiredStaff->phone  ,   
                                  'userID' => $requiredStaff->userID  , 
                                  'firstName' => $requiredStaff->firstName  ,  
                                  'lastName' => $requiredStaff->lastName , 
                                  'emailNotification' => $requiredStaff->emailNotification  ,  
                                  'smsNotification' => $requiredStaff->smsNotification  ,  
                                );
                                /* Sent Email & SMS */
                            }
                            if(count($requiredStaffsUsers) > 0){
                                $getEndUserDevices = DB::table('staffing_devices')
                                  ->select('deviceID','deviceType')
                                  ->whereIn('userID',$requiredStaffsUsers)->get();

                                if(count($getEndUserDevices) > 0){
                                    $androidDevicesArray = array();
                                    $iosDevicesArray = array();
                                    foreach($getEndUserDevices as $getEndUserDevice){
                                      $deviceToken = $getEndUserDevice->deviceID?$getEndUserDevice->deviceID:''; 
                                        if($getEndUserDevice->deviceType == '1'){
                                            if($deviceToken)
                                              $iosDevicesArray[] = $deviceToken;
                                        }elseif($getEndUserDevice->deviceType == '2'){
                                            if($deviceToken)
                                               $androidDevicesArray[] = $deviceToken;
                                        } 
                                    } 
                                }
                            }
                            
                            
                            
                            /* Get Push Message */
                             $pushMessage = $this->getRequestInformationForPush($post);
                             /* Get Push Message */
                            
                            $msg_payload = array (
                            'mtitle' => 'New staffing request available.',
                            'mdesc' => $pushMessage,
                            'notificationStatus' => 3,
                            'requestID' => $post->id
                            );

                            $msg_payloadAndroid = array (
                            'mtitle' => 'New staffing request available.',
                            'mdesc' => $pushMessage,
                            'notificationStatus' => 3,
                            'requestID' => $post->id
                            );

                            if($androidDevicesArray){
                                if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                              $androidPusStatus = true;
                            }
                            
                            if($iosDevicesArray){
                                if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                    $iosPusStatus = true;
                            }
                            
                            /* Sent Email & SMS */
//                            if($requiredStaffsUsersEmailAndSms){
//                                $this->sendMessageCommunication($post->id, $requiredStaffsUsersEmailAndSms);
//                            }
                            /* Sent Email & SMS */



                        }
                    } 
                    /* Send To All End-Users which meet Criteria */  
                } 
                
                return true;
        }


        public function sendNotificationAfterNewRequest($user, 
                        $businessUnitID, 
                        $businessGroupID, 
                        $post, 
                        $requiredStaffCategoryID, 
                        $staffingStartDate, 
                        $staffingShiftID, 
                        $requiredExperiencedLevel)
        { 
            
            $androidDevicesArray = array();
            $iosDevicesArray = array();
            if($user->role == 4 && $user->needApproval == 1 && $post->approvedBy == 0){
               
                
            }else{
                /* Send Push Notifications To User */ 
                     
                if($user->role == 2 || 
                    $user->role == 3 || 
                    ($user->role == 4 && ($user->needApproval != 1 || $post->approvedBy != 0)))
                {
                    
                    
                    /* Send To All End-Users which meet Criteria */
                    if($requiredStaffCategoryID)
                    $requiredStaffCategoryIDs = explode(",", $requiredStaffCategoryID);  
                    /* Check User Availability for Shift Start Date */
                    $unavailableUsers = array();
                    $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                        ->select('userID','shiftID');

                    $userAvailabilitySql->where('onDate','=',$staffingStartDate);
                    $userAvailabilitySql->whereIn('availabilityStatus', [0, 2]);
                    $userAvailability = $userAvailabilitySql->get();

                    if($staffingShiftID > 0){
                        if($userAvailability){
                            foreach($userAvailability as $userAvailabile){
                                if($userAvailabile->shiftID == $staffingShiftID){
                                   $unavailableUsers[] = $userAvailabile->userID;
                                }    
                            }
                        }                                
                    }else{
                      if($userAvailability){
                            foreach($userAvailability as $userAvailabile){
                                   $unavailableUsers[] = $userAvailabile->userID;
                            }
                        }  
                    }
                    /* Check User Availability for Shift Start Date */

                    $getEndUserInfoSql = DB::table('staffing_users')
                    ->select('staffing_users.id AS userID','staffing_users.skills')
                    ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id');

                    $getEndUserInfoSql->where('staffing_usersunits.businessUnitID','=',$businessUnitID);
                    $getEndUserInfoSql->where('staffing_users.businessGroupID','=',$businessGroupID);
                    //$getEndUserInfoSql->where('staffing_users.pushNotification','=',1);
                    $getEndUserInfoSql->whereIn('staffing_users.role',[0,4]);

//                    if($requiredExperiencedLevel > 0){
//                        if($requiredExperiencedLevel == 3){//Experienced
//                          $getEndUserInfoSql->where('staffing_users.experiencedLevel', '=', 3);  
//                        }else if($requiredExperiencedLevel == 2){//Intermediate
//                          $getEndUserInfoSql->whereIn('staffing_users.experiencedLevel',[2,3]);  
//                        } else if($requiredExperiencedLevel == 1){//Junior
//                          $getEndUserInfoSql->whereIn('staffing_users.experiencedLevel',[0,1,2,3]);  
//                        } 
//                    }

                    if(count($unavailableUsers) > 0)
                    $getEndUserInfoSql->whereNotIn('staffing_users.id',$unavailableUsers);

                    $getEndUserInfo = $getEndUserInfoSql->get();

                    if(count($getEndUserInfo) > 0){
                        $newEndUsers = array();
                        foreach($getEndUserInfo as $endUserInfo){
                          /* Fetch Those Users who matched with required Staff */ 
                            $endUserSkills = array();
                            $endUserSkills = $endUserInfo->skills?unserialize($endUserInfo->skills):array();
                            if($requiredStaffCategoryIDs){
    if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){

                                    $newEndUsers[] = $endUserInfo->userID;
                                }
                            }else{
                                $newEndUsers[] = $endUserInfo->userID;
                            }
                            /* Fetch Those Users who matched with required Staff */  
                        }

                        /* Fetch Required Staffs */
                        $requiredStaffs = DB::table('staffing_users')
                        ->select('staffing_users.id AS userID',
                                'staffing_users.phone',
                                'staffing_users.email',
                                'staffing_users.firstName',
                                'staffing_users.lastName',
                                'staffing_users.emailNotification',
                                'staffing_users.pushNotification',
                                'staffing_users.smsNotification')
                            ->whereIn('staffing_users.id',$newEndUsers)->get();    
                        /* Fetch Required Staffs */
                        $requiredStaffsUsers = array();
                        $requiredStaffsUsersEmailAndSms = array();
                        if(count($requiredStaffs) > 0){
                            foreach($requiredStaffs as $requiredStaff){  
                                if($requiredStaff->pushNotification == '1'){
                                $requiredStaffsUsers[] = $requiredStaff->userID;
                                }
                                /* Sent Email & SMS */
                                $requiredStaffsUsersEmailAndSms[] = array(
                                  'email' => $requiredStaff->email,  
                                  'phone' => $requiredStaff->phone  ,   
                                  'userID' => $requiredStaff->userID  , 
                                  'firstName' => $requiredStaff->firstName  ,  
                                  'lastName' => $requiredStaff->lastName , 
                                  'emailNotification' => $requiredStaff->emailNotification  ,  
                                  'smsNotification' => $requiredStaff->smsNotification  ,  
                                );
                                /* Sent Email & SMS */
                            }
                            
                            /* Sent Email & SMS */
                            if($requiredStaffsUsersEmailAndSms){
                                $this->sendMessageCommunication($post->id, $requiredStaffsUsersEmailAndSms);
                            }
                            /* Sent Email & SMS */



                        }
                    } 
                    /* Send To All End-Users which meet Criteria */  
                } 
                        
                /* Send Push Notifications To User */   
            } 
                     
        }
        
        
        public function getPartialShiftsFiftyPercente($shiftStartDate,$shiftEndDate,$shiftStartTime,$shiftEndTime){
        
//        $staffingStartDate = date("Y-m-d",strtotime($shiftStartDate));
//        $staffingEndDate = date("Y-m-d",strtotime($shiftEndDate));
//        
//        $customShiftStartTime = date("H:i:s",strtotime($shiftStartTime));
//        $customShiftEndTime = date("H:i:s",strtotime($shiftEndTime));
         
        $time1 = $shiftStartDate." ".$shiftStartTime;
        $time2 = $shiftEndDate." ".$shiftEndTime;
        
        $dteStart = new \DateTime($time1); 
        $dteEnd   = new \DateTime($time2); 
        $dteDiff  = $dteStart->diff($dteEnd); 
        $diff = $dteDiff->format("%H:%I"); 
        $time_array = explode(':', $diff);
        $hours = (int)$time_array[0];
        $minutes = (int)$time_array[1];

        $total_mins = ($hours * 60) + ($minutes);

        $average = floor($total_mins / 2);
        $minutes_to_add = $average;

        $time = new \DateTime($time1); 
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));

        $partialTime1StartDate = $time1;

        $partialTime1EndDate = $time->format('Y-m-d H:i:s');        

        $time22 = new \DateTime($partialTime1EndDate); 
        $time22->add(new \DateInterval('PT' . $minutes_to_add . 'M'));
        
        $stamp2 = $time22->format('Y-m-d H:i:s');
        
        $partialTime2StartDate = $partialTime1EndDate;

        $partialTime2EndDate = $stamp2;
        
        
        $partialShifts = array();
        
        $partialShifts[] = array(
            'partialShiftOneStartTime' => $partialTime1StartDate,
            'partialShiftOneEndTime' => $partialTime1EndDate,
            'partialShiftTwoStartTime' => $partialTime2StartDate,
            'partialShiftTwoEndTime' => $partialTime2EndDate
            );
        
        return $partialShifts;
        
    }
        
        
        
        
        /* STAFFING CALL NEW REQUSET API */
        
        
        /* END-USER HOME POSTS REQUESTS/JOB LIST */
        
        
        
        public function jobList(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                if ($user) {
                    
                    
                   $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',$user->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   $jsonResponse['data'] = array();
                   $jsonResponse['status'] = '1';
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                       if(count($postIDs) > 0){
                           
                           
                           
                            $unitInfoSql = DB::table('staffing_businessunits')
                             ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                             ->select(
                                     'staffing_businessunits.id',
                                     'staffing_businessunits.unitName'
                                     );
                            
                            
                               $unitInfoSql->where('staffing_usersunits.userID','=',$user->id);
                            if($user->role == 0){
                               $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                            }
                            
                             $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                             $unitInfoSql->where('staffing_businessunits.status','=',1);
                            
                                $unitInfo = $unitInfoSql->first();
                                
                        if($unitInfo){

                                $requestPostsSql = DB::table('staffing_staffrequest')
                                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                                ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                                ->select(
                                   'staffing_staffrequest.id AS postID',
                                   'staffing_staffrequest.staffingStartDate',
                                   'staffing_staffrequest.staffingEndDate',
                                   'staffing_staffrequest.shiftType',
                                   'staffing_staffrequest.requiredStaffCategoryID',
                                   'staffing_shiftsetup.startTime',
                                   'staffing_shiftsetup.endTime',
                                   'staffing_staffrequest.customShiftStartTime',
                                   'staffing_staffrequest.customShiftEndTime',
                                   'staffing_staffrequest.notes',
                                   'staffing_groups.groupName',
                                   'staffing_groups.groupCode',
                                   DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                                   );

                                if($unitInfo){
                                    $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                                }
                                if(count($postIDs) > 0){
                                $requestPostsSql->whereIn(
                                   'staffing_staffrequest.id',$postIDs);
                                }
                         
                         
                         $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                         
                         $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));

                         $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                         /* Active Requests */
                         $offers = $requestPostsSql->get();

                            foreach($offers as $offer){
                             $startDateOfShift = $offer->staffingStartDate;

                                if($offer->shiftType == 1):
                                $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                               else:
                                $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                               endif;


                                $jsonResponse['data'][] = [
                                  'id' => (string)$offer->postID,
                                  'shiftName' => (string)$shiftTimes,
                                  'groupName' => $offer->groupName ,
                                  'groupCode' => $offer->groupCode ,
                                  'businessUnitName' => $unitInfo->unitName ,
                                  'shiftTime' => (string)($startDateOfShift),//strtotime($startDateOfShift), 
                                  'notes' => $offer->notes?$offer->notes:''  
                                ];
                            }
                        }
                    }
                }
                   
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $jsonResponse['countInfo'] = $homePageCount;
                   
                   
                   
                   
                   
                   return response()->json($jsonResponse); 
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        public function shiftOfferList(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                if ($user) {                    
                    /* Check User Skill Experienced Level */
                    $userExperiencedLevel = $user->experiencedLevel;
                    /* Check User Skill Experienced Level */
                    
                    
                   $jsonResponse['status'] = '1';
                   
                   $userBusinessUnitsAr = array();
                   
                   $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        );
                   
                   
                    $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                    $unitInfoSql->where('staffing_businessunits.status','=',1);
                   
                   $unitInfoSql->where('staffing_usersunits.userID','=',$user->id);
                   
//                   if($user->role == 0){
//                      $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
//                   }
                   
                   $unitInfo = $unitInfoSql->get();
                   
                    if($unitInfo){
                        foreach($unitInfo as $row){
                            $userBusinessUnitsAr[] = $row->id;
                        } 
                    }
                    
                    
                        /* END USER ASSIGNED UNIT */
                    
                   
                            $unitInfoSql2 = DB::table('staffing_businessunits')
                            ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                            ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                             );


                            $unitInfoSql2->where('staffing_businessunits.deleteStatus','=',0);
                            $unitInfoSql2->where('staffing_businessunits.status','=',1);

                            $unitInfoSql2->where('staffing_usersunits.userID','=',$user->id);

                            if($user->role == 0){
                               $unitInfoSql2->where('staffing_usersunits.primaryUnit','=',1); 
                            }
                            
                            
                            
                            if($request->businessUnitID > 0){
                                $unitInfoSql2->where('staffing_businessunits.id','=',$request->businessUnitID); 
                            }

                            $unitInfo2 = $unitInfoSql->first();
                        /* END USER ASSIGNED UNIT */
                    
                    
                   
                    $requestPostsSql = DB::table('staffing_staffrequest')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                    ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                    ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_staffrequest.businessUnitID')
                    ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                    
                    ->leftJoin('staffing_shiftoffer', function($join) use($user)
                    {
                        $join->on('staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id');
                        $join->on('staffing_shiftoffer.userID', '=', DB::raw($user->id));
                     })
                
                    ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.staffingShiftID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.requiredExperiencedLevel',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.numberOfOffers',
                        'staffing_shiftoffer.id AS responseOfferID',
                        'staffing_shiftoffer.userID AS responseUserID',
                        'staffing_shiftoffer.responseType',
                        'staffing_shiftoffer.overTime',
                        'staffing_shiftoffer.inWaitList',
                        'staffing_shiftoffer.partialShiftTimeID',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode',
                        'staffing_businessunits.id AS staffingUnitID',
                        'staffing_businessunits.unitName as bunitName',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
                     
                     
                     
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $request->businessUnitID); 
                    }else{
                        if(count($userBusinessUnitsAr) > 0){
                            $requestPostsSql->whereIn('staffing_staffrequest.businessUnitID', $userBusinessUnitsAr);
                        }
                    }
        
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            
            $requestPostsSql->where('staffing_staffrequest.closingTime','>=',date("Y-m-d H:i:s"));
            
            /* Get Those Offers Which Matched With User Experienced Levels */
//                if($userExperiencedLevel == 3){//Experienced
//                    $requestPostsSql->whereIn('staffing_staffrequest.requiredExperiencedLevel', [0,1,2,3]);
//                }else if($userExperiencedLevel == 2){//Intermediate
//                    $requestPostsSql->whereIn('staffing_staffrequest.requiredExperiencedLevel', [0,1,2]); 
//                } else if($userExperiencedLevel == 1){//Junior
//                    $requestPostsSql->whereIn('staffing_staffrequest.requiredExperiencedLevel',[0,1]);  
//                }else{
//                    $requestPostsSql->whereIn('staffing_staffrequest.requiredExperiencedLevel',[0,1]);
//                } 
            /* Get Those Offers Which Matched With User Experienced Levels */
            
            
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            /* Active Requests */
              $offers = $requestPostsSql->get();
              $jsonResponse['data'] = array();     
             foreach($offers as $offer){
                 
                 /* Check if any user accepted/confirmed offer and request is scheduled? */
                        $offerConfirmedUser = array();
                        $isOfferAccepted =  0;
                        $requestShiftOffers = DB::table('staffing_shiftoffer')
                            ->select('id','userID')->where([['requestID','=',$offer->postID]])->get();
                        if(count($requestShiftOffers) > 0){
                            foreach($requestShiftOffers as $requestShiftOffer){
                               
                               $offerConfirmCheckSql = DB::table('staffing_shiftconfirmation')
                                ->select('id');
                              $offerConfirmCheckSql->where('shiftOfferID','=',$requestShiftOffer->id);
                              $offerConfirmCheckSql->whereIn('offerResponse', [1,2]);
                              $offerConfirmCheck = $offerConfirmCheckSql->first(); 

                             if(count($offerConfirmCheck) > 0){
                                $offerConfirmedUser[] =  $requestShiftOffer->userID;
                                $isOfferAccepted =  1;
                             }
                               
                               
                               
                            }
                        }
                        
                /* Check if any user accepted/confirmed offer and request is scheduled? */
                 
                    $startDateOfShift = $offer->staffingStartDate;
                    $endDateOfShift = $startDateOfShift;//$offer->staffingEndDate;
                    
                        if($offer->shiftType == 1):
                        $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                        else:
                        $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                        endif;
                 
                        
                        if($offer->shiftType == 1):
                            $shiftStartTime = $startDateOfShift." ".(date("g:i A",strtotime($offer->customShiftStartTime)));
                            $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->customShiftEndTime)));
                        else:
                            $shiftStartTime = $startDateOfShift." ".(date("g:i A",strtotime($offer->startTime)));
                            $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->endTime)));
                        endif;
                        
                        if(strtotime($shiftStartTime) > strtotime($shiftEndTime)){
                           $endDateOfShift = (date("Y-m-d",strtotime($endDateOfShift . " +1 day")));
                            if($offer->shiftType == 1):
                                $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->customShiftEndTime)));
                            else:
                                $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->endTime)));
                            endif; 
                           
                        }
                   
                   $respondStatus = 0;//No response given By User
                   $cancelStatus = 0;
                   $partialShiftTimeID = 0;
                   $overTimeStatus = $offer->overTime?1:0;
                   
                   if($offer->responseUserID == $user->id){
                       if($offer->responseType == 0)
                          $respondStatus = 1; //Full Shift
                       if($offer->responseType == 1){
                          $respondStatus = 2; //Partial Shift
                          $partialShiftTimeID = $offer->partialShiftTimeID;
                       }
                       
                       if($offer->responseType == 2){
                          $respondStatus = 3; //Shift Declined
                          $cancelStatus = 3;
                       }
                       
                   }
                   
                   
                   if($offer->responseOfferID > 0){
                       $checkAdminConfirmationRequest = DB::table('staffing_shiftconfirmation')
                         ->select('id','offerResponse')->where([['shiftOfferID','=',$offer->responseOfferID]])->first();
                       if($checkAdminConfirmationRequest){
                          if($checkAdminConfirmationRequest->offerResponse == 0){
                             $respondStatus = 4; //Offer For Confirmation
                          } 
                          if($checkAdminConfirmationRequest->offerResponse == 1){
                             $respondStatus = 5; //Offer Already accepted by this user
                          } 
                          if($checkAdminConfirmationRequest->offerResponse == 2){
                             $respondStatus = 6; //Offer Already Declined by this user
                             $cancelStatus = 6;
                          } 
                       }
                   
                       
                        if($isOfferAccepted == 1){//By Any of users?
                          if(in_array($user->id, $offerConfirmedUser)){

                          }else{

                            if(count($offerConfirmedUser) >= $offer->numberOfOffers)  
                               $respondStatus = 7; 
                               //You are on waitlist please response either you want be on waitlist 
                               //someone other has accepted and scheduled.
                          }
                        }
                        
                        
                        if($respondStatus == 7){
                            if($offer->inWaitList == 1){
                               //User confirmed to be on waitlist
                               $respondStatus = 8; 

                            }else if($offer->inWaitList == 2){
                               //User declined to be on waitlist
                                $respondStatus = 9; 
                            }else if($offer->inWaitList == 3){
                                if($cancelStatus > 0){
                                  $respondStatus = $cancelStatus;
                                }else{
                                  $respondStatus = 4; //Offer again sent after waitlist confirmation.  
                                }
                            }  
                       }
                       
                       
                    }
                   
                   
                        /* Get Partial Shift */
                        $partialShiftStartTime = '';
                        $partialShiftEndTime = '';
                        if($partialShiftTimeID > 0){

                            $getPartialShifts = DB::table('staffing_requestpartialshifts')->select('id',
                                 'partialShiftStartTime',
                                 'partialShiftEndTime'
                             )->where([['id','=',$partialShiftTimeID]])->first();

                            if($getPartialShifts){
                               $partialShiftStartTime =  (date("Y-m-d g:i A",strtotime($getPartialShifts->partialShiftStartTime)));
                               $partialShiftEndTime =  (date("Y-m-d g:i A",strtotime($getPartialShifts->partialShiftEndTime)));
                            }
                        }
                        /* Get Partial Shift */
                  
                    $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                    ->select('userID','shiftID');
                        $userAvailabilitySql->where('onDate','=',$offer->staffingStartDate);
                        $userAvailabilitySql->whereIn('availabilityStatus', [0,2]);
                        $userAvailabilitySql->where('userID','=', $user->id);

                        if($offer->shiftType == 0 && $offer->staffingShiftID > 0){
                          $userAvailabilitySql->where('shiftID','=', $offer->staffingShiftID);  
                        }
                            
                    $userAvailability = $userAvailabilitySql->count();
                    
                    if($userAvailability > 0 && $offer->shiftType != 1){
                    }else{
                        $endUserSkills = array();
                        $endUserSkills = $user->skills?unserialize($user->skills):array();
                        
                        $requiredStaffCategoryIDs = ($offer->requiredStaffCategoryID ? 
                        explode(",", $offer->requiredStaffCategoryID):array()); 
                        if($requiredStaffCategoryIDs && $endUserSkills){
                            //if(array_intersect($requiredStaffCategoryIDs, $endUserSkills)){
                            if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){
                                $jsonResponse['data'][] = [
                                    'id' => (string)$offer->postID,
                                    'shiftName' => (string)$shiftTimes,
                                    'shiftStartTime' => (string)$shiftStartTime,
                                    'shiftEndTime' => (string)$shiftEndTime,
                                    'groupName' => $offer->groupName ,
                                    'groupCode' => $offer->groupCode ,
                                    'businessUnitName' =>$offer->bunitName?$offer->bunitName:'', //$unitInfo2->unitName ,
                                    'shiftTime' => (string)$startDateOfShift,//strtotime($startDateOfShift), 
                                    'notes' => $offer->notes?$offer->notes:''?$offer->notes?$offer->notes:'':''  , 
                                    'respondStatus' => (string)$respondStatus ,
                                    'partialShiftTimeID' => (string)$partialShiftTimeID ,
                                    'overtimeStatus' => (string)$overTimeStatus ,
                                    'partialShiftStartTime' => (string)$partialShiftStartTime,
                                    'partialShiftEndTime' => (string)$partialShiftEndTime,
                                ];
                            }
                        }
                    }
                   
                   
                    
             }
                   
                   return response()->json($jsonResponse); 
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        public function shiftOfferDetail(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                if ($user) {
                    
                   $requestInfo = Requestcall::find($request->requestID); 
                   if($requestInfo){ 
                       
                       if($requestInfo->businessGroupID == $user->businessGroupID){
                       
                   
                        $unitInfoSql = DB::table('staffing_businessunits')
                             ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                             ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                             );
                   
                   
                        $unitInfoSql->where('staffing_usersunits.userID','=',$user->id);

                        if($user->role == 0){
                           $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                        }
                   
                        $unitInfo = $unitInfoSql->first();
                   
                        if($request->businessUnitID > 0){
                           $unitInfo->id =  $request->businessUnitID;
                        }
        
                        $requestPostsSql = DB::table('staffing_staffrequest')
                        ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                        ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                        ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                    
                        ->leftJoin('staffing_shiftoffer', function($join) use($user)
                        {
                            $join->on('staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id');
                            $join->on('staffing_shiftoffer.userID', '=', DB::raw($user->id));
                        })
                
                        ->select(
                            'staffing_staffrequest.id AS postID',
                            'staffing_staffrequest.staffingStartDate',
                            'staffing_staffrequest.staffingEndDate',
                            'staffing_staffrequest.shiftType',
                            'staffing_staffrequest.postingStatus',
                            'staffing_shiftsetup.startTime',
                            'staffing_shiftsetup.endTime',
                            'staffing_staffrequest.customShiftStartTime',
                            'staffing_staffrequest.customShiftEndTime',
                            'staffing_staffrequest.notes',
                            'staffing_staffrequest.numberOfOffers',
                            'staffing_shiftoffer.id AS responseOfferID',
                            'staffing_shiftoffer.userID AS responseUserID',
                            'staffing_shiftoffer.responseType',
                            'staffing_shiftoffer.overTime',
                            'staffing_shiftoffer.inWaitList',
                            'staffing_shiftoffer.partialShiftTimeID',
                            'staffing_groups.groupName',
                            'staffing_groups.groupCode',
                            DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
        
                        $requestPostsSql->where('staffing_staffrequest.id','=', $requestInfo->id); 
                        //$requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            
        
                        //$requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            
                        //$requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                        /* Active Requests */
                        $offer = $requestPostsSql->first();
                        if($offer){
                            
                            
                            /* Check if any user accepted/confirmed offer and request is scheduled? */
                            $offerConfirmedUser = array();
                            $isOfferAccepted =  0;
                            $requestShiftOffers = DB::table('staffing_shiftoffer')
                                ->select('id','userID')->where([['requestID','=',$offer->postID]])->get();
                            if(count($requestShiftOffers) > 0){
                                foreach($requestShiftOffers as $requestShiftOffer){
                                   
                                  $offerConfirmCheckSql = DB::table('staffing_shiftconfirmation')
                                    ->select('id');
                                  $offerConfirmCheckSql->where('shiftOfferID','=',$requestShiftOffer->id);
                                  $offerConfirmCheckSql->whereIn('offerResponse', [1,2]);
                                  $offerConfirmCheck = $offerConfirmCheckSql->first(); 

                                 if(count($offerConfirmCheck) > 0){
                                    $offerConfirmedUser[] =  $requestShiftOffer->userID;
                                    $isOfferAccepted =  1;
                                 } 
                                   


                                }
                            }
                        
                             /* Check if any user accepted/confirmed offer and request is scheduled? */
                 
                 
                            $startDateOfShift = $offer->staffingStartDate;
                            $endDateOfShift = $startDateOfShift;//$offer->staffingEndDate;
                    
                        if($offer->shiftType == 1):
                            $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                        else:
                            $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                        endif;
                        
                        if($offer->shiftType == 1):
                            $shiftStartTime = $startDateOfShift." ".(date("g:i A",strtotime($offer->customShiftStartTime)));
                            $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->customShiftEndTime)));
                        else:
                            $shiftStartTime = $startDateOfShift." ".(date("g:i A",strtotime($offer->startTime)));
                            $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->endTime)));
                        endif;
                        
                        if(strtotime($shiftStartTime) > strtotime($shiftEndTime)){
                           $endDateOfShift = (date("Y-m-d",strtotime($endDateOfShift . " +1 day")));
                            if($offer->shiftType == 1):
                                $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->customShiftEndTime)));
                            else:
                                $shiftEndTime = $endDateOfShift." ".(date("g:i A",strtotime($offer->endTime)));
                            endif; 
                           
                        }
                 
                        
                        $cancelStatus = 0;
                        $respondStatus = 0;//No response given By User
                        $partialShiftTimeID = 0;
                        $overTimeStatus = $offer->overTime?1:0;
                   
                        if($offer->responseUserID == $user->id){
                            if($offer->responseType == 0)
                               $respondStatus = 1; //Full Shift
                            if($offer->responseType == 1){
                               $respondStatus = 2; //Partial Shift
                               $partialShiftTimeID = $offer->partialShiftTimeID;
                            }

                            if($offer->responseType == 2){
                               $respondStatus = 3; //Declined
                               $cancelStatus = 3;
                            }

                        }
                   
                   
                        if($offer->responseOfferID > 0){
                            $checkAdminConfirmationRequest = DB::table('staffing_shiftconfirmation')
                            ->select('id','offerResponse')->where([['shiftOfferID','=',$offer->responseOfferID]])->first();
                            if($checkAdminConfirmationRequest){
                               if($checkAdminConfirmationRequest->offerResponse == 0){
                                  $respondStatus = 4; //Offer For Confirmation
                               } 
                               if($checkAdminConfirmationRequest->offerResponse == 1){
                                  $respondStatus = 5; //Offer Already accepted by this user
                               } 
                               if($checkAdminConfirmationRequest->offerResponse == 2){
                                  $respondStatus = 6; //Offer Already Declined by this user
                                  $cancelStatus = 6;
                               } 
                            }
                            
                            
                       
                            if($isOfferAccepted == 1){//By Any of users?
                              if(in_array($user->id, $offerConfirmedUser)){

                              }else{

                                if(count($offerConfirmedUser) >= $offer->numberOfOffers)  
                                   $respondStatus = 7; 
                                   //You are on waitlist please response either you want be on waitlist 
                                   //someone other has accepted and scheduled.
                              }
                            }


                            if($respondStatus == 7){
                                if($offer->inWaitList == 1){
                                   //User confirmed to be on waitlist
                                   $respondStatus = 8; 

                                }else if($offer->inWaitList == 2){
                                   //User declined to be on waitlist
                                    $respondStatus = 9; 
                                }else if($offer->inWaitList == 3){
                                    if($cancelStatus > 0){
                                      $respondStatus = $cancelStatus;
                                    }else{
                                      $respondStatus = 4; //Offer again sent after waitlist confirmation.  
                                    }
                                }  
                           }  
                            
                        }
                        
                        
                        
                        if($offer->postingStatus == 2 || $offer->postingStatus == 4){
                           $respondStatus = 10; 
                        }
                        
                        /* Get Partial Shift */
                        $partialShiftStartTime = '';
                        $partialShiftEndTime = '';
                        if($partialShiftTimeID > 0){
                        
                            $getPartialShifts = DB::table('staffing_requestpartialshifts')->select('id',
                                 'partialShiftStartTime',
                                 'partialShiftEndTime'
                             )->where([['id','=',$partialShiftTimeID]])->first();

                            if($getPartialShifts){
                               $partialShiftStartTime =  (date("Y-m-d g:i A",strtotime($getPartialShifts->partialShiftStartTime)));
                               $partialShiftEndTime =  (date("Y-m-d g:i A",strtotime($getPartialShifts->partialShiftEndTime)));
                            }
                        }
                        /* Get Partial Shift */
                   
                   
                   
                        $jsonResponse = [
                          'id' => (string)$offer->postID,
                          'shiftName' => (string)$shiftTimes,
                          'shiftStartTime' => (string)$shiftStartTime,
                          'shiftEndTime' => (string)$shiftEndTime,
                          'groupName' => $offer->groupName ,
                          'groupCode' => $offer->groupCode ,
                          'businessUnitName' => $unitInfo->unitName ,
                          'shiftTime' => (string)$startDateOfShift,//strtotime($startDateOfShift), 
                          'notes' => $offer->notes?$offer->notes:''?$offer->notes?$offer->notes:'':''  , 
                          'respondStatus' => (string)$respondStatus ,
                          'partialShiftTimeID' => (string)$partialShiftTimeID ,
                          'overtimeStatus' => (string)$overTimeStatus ,
                          'partialShiftStartTime' => (string)$partialShiftStartTime,
                          'partialShiftEndTime' => (string)$partialShiftEndTime,
                            
                          'status' => (string)1  
                        ];
                   
                        return response()->json($jsonResponse); 
                    }else{
                      return response()->json(['status'=>'0','message'=>'Shift Offer not found.'], 500);    
                    }
                    
                  }else{
                    return response()->json(['status'=>'0','message'=>'Shift Offer not found.'], 500);      
                  }
                  
                   }else {
                  return response()->json(['status'=>'0','message'=>'Shift Offer not found.'], 500);    
                }
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        /* END-USER HOME POSTS REQUESTS/JOB LIST/Shift-Offers */
        
        
        
        /* For Admin Or Higher Level */
    public function staffingPostDetail(Request $request){
        if($request->userID){

            $id = $request->userID;
            $user = User::find($id);

            if ($user) {

                $requestPostID = $request->requestID;
                if($requestPostID > 0){

                    $post = Requestcall::find($requestPostID);
                     if($post){
                         
                       $requestPosts = DB::table('staffing_staffrequest')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.closingTime',
                        'staffing_staffrequest.postingStatus',
                        'staffing_staffrequest.cancelReason',
                        'staffing_staffrequest.cancelledBy',
                        'staffing_staffrequest.approvedBy',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode',
                        'staffing_businessunits.unitName',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.numberOfOffers',
                        'staffing_staffrequest.timeOfCallMade',
                        'staffing_requestreasons.reasonName AS requestReason',
                        'staffing_requestreasons.defaultOf AS requestReasonDefaultOf',
                        'staffing_staffrequest.requestReasonID',
                        'staffing_vacancyreasons.reasonName AS vacancyReason',
                        'staffing_offeralgorithm.name AS algorithmName',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner"),
                        DB::raw("CONCAT(u.firstName, ' ', u.lastName) AS lastMinuteStaff")
                        )
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_staffrequest.businessUnitID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->join('staffing_requestreasons', 'staffing_requestreasons.id', '=', 'staffing_staffrequest.requestReasonID')
                ->leftJoin('staffing_vacancyreasons', 'staffing_vacancyreasons.id', '=', 'staffing_staffrequest.vacancyReasonID')
                ->leftJoin('staffing_users AS u', 'u.id', '=', 'staffing_staffrequest.lastMinuteStaffID')->where([
                            ['staffing_staffrequest.id','=', $post->id]
                                ])
                ->leftJoin('staffing_offeralgorithm', 'staffing_offeralgorithm.id', '=', 'staffing_businessunits.offerAlgorithmID')
                ->first(); 
                       
                       
                       
                            if($post->shiftType == 1):
                                $shiftTime = strtotime($requestPosts->customShiftStartTime) ;
                                $shiftStartTime = date("g:i A",strtotime($requestPosts->customShiftStartTime)) ;
                                $shiftEndTime = date("g:i A",strtotime($requestPosts->customShiftEndTime)) ;
                                $shiftTime2 = $requestPosts->customShiftStartTime;
                            else:
                               $shiftTime =  strtotime($requestPosts->startTime) ;
                                $shiftStartTime = date("g:i A",strtotime($requestPosts->startTime)) ;
                                $shiftEndTime = date("g:i A",strtotime($requestPosts->endTime)) ;
                                $shiftTime2 = $requestPosts->startTime;
                            endif;
                    
                            
                    $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPosts->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime2))))));        
                            
                    /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPosts->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPosts->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPosts->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */ 
                      
                      
                      $requiredTypeOfStaffSkills = '';
                            
                       if($requestPosts->requiredStaffCategoryID != ''){
                          $requiredStaffCategoryIDs = explode(",", $requestPosts->requiredStaffCategoryID);
                          $getSkills = DB::table('staffing_skillcategory')
                            ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                          $skillsName = array();
                          foreach($getSkills as $getSkill){
                             $skillsName[] = $getSkill->skillName; 
                          }
                          
                          if($skillsName)
                            $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                       } 
                       
                       
                       $requestCancelReason = '';
                       $cancelledBy = '';
                       
                       if($requestPosts->postingStatus == 4){//Manually Cancelled
                           
                           $requestCancelReason = $requestPosts->cancelReason?$requestPosts->cancelReason:'';
                           
                           
                            if($requestPosts->cancelledBy > 0){
                               $cancelledUser = User::find($requestPosts->cancelledBy); 
                               if($cancelledUser){

                                   $cancelledUserRole = 'Admin';

                                   if($cancelledUser->role == 2){
                                       $cancelledUserRole = 'Group Manager';
                                   }
                                   if($cancelledUser->role == 3){
                                       $cancelledUserRole = 'Super Admin';
                                   }


                                  $cancelledBy =  $cancelledUser->firstName." ".$cancelledUser->lastName." (".$cancelledUserRole.")";
                               }
                            }
                           
                       }
                       
                       if($requestPosts->postingStatus == 2){//Disapproved
                           $requestCancelReason = "Disapproved";
                           
                           if($requestPosts->approvedBy > 0){
                               $cancelledUser = User::find($requestPosts->approvedBy); 
                               if($cancelledUser){

                                   $cancelledUserRole = 'Admin';

                                   if($cancelledUser->role == 2){
                                       $cancelledUserRole = 'Group Manager';
                                   }
                                   if($cancelledUser->role == 3){
                                       $cancelledUserRole = 'Super Admin';
                                   }


                                  $cancelledBy =  $cancelledUser->firstName." ".$cancelledUser->lastName." (".$cancelledUserRole.")";
                               }
                            }
                           
                       }
                       
                       $jsonResponse = array(
                         'requestID' => $requestPosts->postID,
                         'ownerName' => $requestPosts->staffOwner,
                         'notes' => $requestPosts->notes, 
                         'shiftTime' => (string)$requestPosts->staffingStartDate,//$shiftTimeStamp ,
                         'typeOfStaff' => (string)$requiredTypeOfStaffSkills  ,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount ,
                         'requestStatus' => (string)$requestPosts->postingStatus,
                         'businessUnitName' => $requestPosts->unitName,
                         'groupName' => $requestPosts->groupName,
                         'requestReasonID' => (string)($requestPosts->requestReasonID),
                         'requestReason' => $requestPosts->requestReason,
                         'changable' => (string)(($requestPosts->requestReasonDefaultOf == 1)?1:0),
                         'staffName' => $requestPosts->lastMinuteStaff?$requestPosts->lastMinuteStaff:'',
                         'callMadeTime' => (string)($requestPosts->timeOfCallMade?(date("M j, Y g:i A", strtotime($requestPosts->timeOfCallMade))):''),
                         'vacancyReason' => $requestPosts->vacancyReason?$requestPosts->vacancyReason:'',
                         'numberOfOffers' => (string)$requestPosts->numberOfOffers,
                         'staffingStartDate' => (string)((date("Y-m-d", strtotime($requestPosts->staffingStartDate)))),
                         'staffingEndDate' => (string)((date("Y-m-d", strtotime($requestPosts->staffingEndDate)))),
                         'shiftName' => $shiftStartTime." - ".$shiftEndTime,
                         'status' => '1',
                         'cancelReason' => $requestCancelReason,
                         'cancelledBy' => $cancelledBy,
                         'offerAlgorithm' => $requestPosts->algorithmName?$requestPosts->algorithmName:'Open',
                         'shiftCloseTime' => $requestPosts->closingTime?date("M j, Y g:i A",strtotime($requestPosts->closingTime)):date("M j, Y g:i A",strtotime($requestPosts->staffingStartDate))
                           
                       );
                       
                      
                      return response()->json($jsonResponse); 
                         

                     } else{
                       return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                     }  


                 }else{
                   return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                 }
                    
            }else {
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
            }
        }else{
          return response()->json(['status'=>'0','message'=>'User not found.'], 500);  

        }
                            
                            
                        
        }
        /* For Admin Or Higher Level */
        
        
        public function termsOfServices(Request $request){
            $userID = $request->userID;
            $user = User::find($userID);
            $userBusinessGroup = $user->businessGroupID;
            if($user){
                $page = DB::table('staffing_staticpages')
                        ->select('id','title','content')
                        ->where([['businessGroupID','=',$userBusinessGroup],
                            ['type','=','terms']])
                        ->first();

                if($page){

                }else{
                  $saveNewForThisGroup = $this->saveNewPageInformation($userBusinessGroup, 'terms'); 
                  $page = DB::table('staffing_staticpages')
                        ->select('id','title','content')
                        ->where([['businessGroupID','=',$userBusinessGroup],
                            ['type','=','terms']])
                        ->first();
                }
            
                return view('apis.page',['page' => $page]);
            }else{
               return view('apis.terms'); 
            }
            
        }
        
        public function privacyPolicy(Request $request){
            $userID = $request->userID;
            $user = User::find($userID);
            $userBusinessGroup = $user->businessGroupID;
            if($user){
                $page = DB::table('staffing_staticpages')
                        ->select('id','title','content','businessGroupID')
                        ->where([['businessGroupID','=',$userBusinessGroup],
                            ['type','=','privacy']])
                        ->first();

                if($page){

                }else{
                  $saveNewForThisGroup = $this->saveNewPageInformation($userBusinessGroup, 'privacy'); 
                  $page = DB::table('staffing_staticpages')
                        ->select('id','title','content')
                        ->where([['businessGroupID','=',$userBusinessGroup],
                            ['type','=','privacy']])
                        ->first();
                }
            
                return view('apis.page',['page' => $page]);
            }else{
            return view('apis.privacy');
            }
        }
        
        
         public function saveNewPageInformation($userBusinessGroup = 0, $pageType = 'privacy'){
            
            /* GET DEFAULT TOS & PP */
            $page = DB::table('staffing_staticpages')
                    ->select('id','title','content')
                    ->where([['businessGroupID','=',0],
                        ['type','=',$pageType]])
                    ->first();
            /* GET DEFAULT TOS & PP */
            
            $success = false;
            if($pageType == 'terms')
                $title = 'Terms of Service';
            else
                $title = 'Privacy Policy';    
            
            if($userBusinessGroup > 0){
                
                if($page){
                
                    $insertedData = array(
                        ['businessGroupID' => $userBusinessGroup,
                           'title' => $page->title,
                          'type' => $pageType,
                         'content' => $page->content]                
                    );

                    $success = DB::table('staffing_staticpages')->insert($insertedData); 
                }
            }else{
               $success = true; 
            }
            
            if($success){
              return true;  
            }else{
               return false; 
            }
            
        }
        
        
         public function userSetting(Request $request){
            
            if($request->userID){

                $userID = $request->userID;
                $user = User::find($userID);
                
                   if ($user) {
                       $updateStr = array();
                      if($request->emailNotification !='') {
                          if($request->emailNotification == '0'){
                             $updateStr = array(
                               'emailNotification' => 0 
                             ) ;
                          }
                          if($request->emailNotification == '1'){
                              $updateStr = array(
                               'emailNotification' => 1 
                             ) ;
                          }
                      }
                      
                      
                      
                      if($request->pushNotification !='') {
                          if($request->pushNotification == '0'){
                             $updateStr = array(
                               'pushNotification' => 0 
                             ) ;
                          }
                          
                          if($request->pushNotification == '1'){
                              $updateStr = array(
                               'pushNotification' => 1 
                             );
                          }
                      }
                      
                      
                      if($request->smsNotification !='') {
                          if($request->smsNotification == '0'){
                             $updateStr = array(
                               'smsNotification' => 0 
                             );
                          }
                          
                          if($request->smsNotification == '1'){
                              $updateStr = array(
                               'smsNotification' => 1 
                             );
                          }
                      }
                      
                      
                      if($updateStr){
                          DB::table('staffing_users')
                            ->where('id', $userID)
                            ->update($updateStr);
                      }
                      
                      
                        $rows = DB::table('staffing_users')->select(
                            'emailNotification',
                            'pushNotification',
                            'smsNotification')
                          ->where([['id', '=', $userID]])->first();
                        
                        $responseJson['status'] = '1';
                        $responseJson['message'] = 'Your setting has been updated.';
                        $responseJson['emailNotification'] = (string)$rows->emailNotification;
                        $responseJson['pushNotification'] = (string)$rows->pushNotification;
                        $responseJson['smsNotification'] = (string)$rows->smsNotification;
                       return response()->json($responseJson);  
                          
                   }else {
                     return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                   }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
	}
        
        
        /* Approved Post By Super Admin */    
        public function approvePost(Request $request){
            
            $iosDevicesArray = array();
            $androidDevicesArray = array();
            if($request->userID){

                $id = $request->userID;
                $user = User::find($id);
                
                   if ($user) {
                       
                       $requestPostID = $request->requestID;
                       if($requestPostID > 0){
                           
                           $post = Requestcall::find($requestPostID);
                        if($post){
                          if($user->role == '2' || $user->role == '3'){ 
                               /* Send Push To ADmin */ 
                              $postOwnerAdminInfo = User::find($post->ownerID);
                            if($postOwnerAdminInfo->pushNotification == '1'){  
                                $getAdminDevices = DB::table('staffing_devices')
                                    ->select('deviceID','deviceType')
                                    ->where([['userID','=',$post->ownerID]])->get();
                                    
                                if(count($getAdminDevices) > 0){
                                    foreach($getAdminDevices as $adminDevice){
                                      $deviceToken = $adminDevice->deviceID?$adminDevice->deviceID:''; 
                                        if($adminDevice->deviceType == '1'){
                                            if($deviceToken)
                                              $iosDevicesArray[] = $deviceToken;
                                        }elseif($adminDevice->deviceType == '2'){
                                            if($deviceToken)
                                               $androidDevicesArray[] = $deviceToken;
                                        } 
                                    }
                                }
                            }
                                
                                
                                /* Send Push To ADmin */  
                              
                              
                              
                                if($request->approveStatus == '1' && ($post->postingStatus != 1 || $post->postingStatus != 3)){//Approve the post
                                    $post->postingStatus = 1; 
                                    $post->approvedBy = $user->id; //Approved By
                                    if($post->save()){
                                     /* Send Push To ADmin */  
                                        
                                       
                                    /* Get Push Message */
                                     $pushMessage = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */ 
                                        
                                       $msg_payload = array (
                                        'mtitle' => 'Staffing request approved and open to Staff members.',
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 2,
                                        'requestID' => $post->id
                                        );

                                        $msg_payloadAndroid = array (
                                        'mtitle' => 'Staffing request approved and open to Staff members.',
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 2,
                                        'requestID' => $post->id
                                        ); 
                                        if($androidDevicesArray){
                                            if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                          $androidPusStatus = true;
                                        }
                                        if($iosDevicesArray){
                                            if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                                $iosPusStatus = true;
                                        } 
                                      /* Send Push To ADmin */ 
                                        
                                    /* Send Push Notification To Staff Members that Meet Criteria */
                                        $businessUnitID = $post->businessUnitID;
                                        $businessGroupID = $post->businessGroupID;
                                        $requiredStaffCategoryID = $post->requiredStaffCategoryID;
                                        $staffingStartDate = $post->staffingStartDate;
                                        $staffingShiftID = $post->staffingShiftID;
                                        $requiredExperiencedLevel = $post->requiredExperiencedLevel;
                                        $user2 = User::find($post->ownerID);
                                        
                                      $this->sendPushNotificationToEndUsers($user2, 
                                        $businessUnitID, 
                                        $businessGroupID, 
                                        $post, 
                                        $requiredStaffCategoryID, 
                                        $staffingStartDate, 
                                        $staffingShiftID, 
                                        $requiredExperiencedLevel);  
                                      
                                      $this->createLogForNewRequest($post);  
                                    /* Send Push Notification To Staff Members that Meet Criteria */
                                        
                                      return response()->json(['status'=>'1',
                                      'message'=>'Staffing request sucessfully approved.']);   
                                    }else{
                                       return response()->json(['status'=>'0',
                                      'message'=>'Failed to approve Staffing request.']);   
                                    }
                                }elseif($request->approveStatus == '0'){
                                    $post->postingStatus = 2; 
                                    $post->approvedBy = $user->id; //DisApproved By
                                    if($post->save()){
                                        
                                        
                                        
                                    /* Get Push Message */
                                     $pushMessage = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */ 
                                       
                                        /* Send Push To ADmin */      
                                       $msg_payload = array (
                                        'mtitle' => 'Staffing request disapproved.',
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 2,
                                        'requestID' => $post->id
                                        );

                                    $msg_payloadAndroid = array (
                                        'mtitle' => 'Staffing request disapproved.',
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 2,
                                        'requestID' => $post->id
                                        ); 
                                    if($androidDevicesArray){
                                        if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                      $androidPusStatus = true;
                                    }
                                    if($iosDevicesArray){
                                        if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                            $iosPusStatus = true;
                                    } 
                                      /* Send Push To ADmin */ 
                                        
                                        
                                      return response()->json(['status'=>'1',
                                      'message'=>'Staffing request sucessfully disapproved.']);   
                                    }else{
                                       return response()->json(['status'=>'0',
                                      'message'=>'Failed to disapprove Staffing request.']);   
                                    }
                                    
                                   return response()->json(['status'=>'1',
                                     'message'=>'Staffing request sucessfully disapproved.']);   
                                }else{
                                    return response()->json(['status'=>'0',
                                     'message'=>'Bad request.']);   
                                }
                                
                            
                          
                          
                          }else{
                            return response()->json(['status'=>'0',
                                'message'=>'You are not allowed to approve/disapprove staffing request.']);   
                          }
                          
                        } else{
                          return response()->json(['status'=>'0','message'=>'Request call not found.']);       
                        }  
                    
                        
                       }else{
                         return response()->json(['status'=>'0','message'=>'Request call not found.']);     
                       }
                    
                   }else {
                     return response()->json(['status'=>'0','message'=>'User not found.']);    
                   }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.']);  
              
            }
        }
        /* Approved Post By Super Admin */      
        
        public function userResponse(Request $request){
            
            if($request->userID){

                $id = $request->userID;
                $user = User::find($id);
                $userID = $user->id;
                
                   if ($user) {
                       
                       $requestPostID = $request->requestID;
                       if($requestPostID > 0){
                           
                           $post = Requestcall::find($requestPostID);
                           
                        if($post){
                            
                          if($user->role == '0' || $user->role == '4'){ 
                             
                            $partialShiftID =  $request->partialShiftTimeID?$request->partialShiftTimeID:0;
                            $responseType =  $request->responseType; 
                            //1=>Full Shift, 2=>Partial Shift,3=>Decline
                            
                            $msg = 'Thank you for your response, you will be notified shortly.';
                            if($responseType == 1)
                                $responseType = 0;//Full Shift
                            if($responseType == 2)
                                $responseType = 1;//Partial Shift
                            if($responseType == 3){
                                $responseType = 2;//Decline Shift
                                $msg = 'Thank you for reply.';
                            }
                              
                            $overtimeStatus = $request->overtimeStatus?1:0;//1 =>Yes,0=>No
                            $partialShiftStartTime = '';
                            $partialShiftEndTime = '';
                            
                         if($responseType == 1)//Partial Shift
                         {      
                           $partialShiftStartTime = date("Y-m-d H:i:s", strtotime($request->partialShiftStartTime));   
                           $partialShiftEndTime = date("Y-m-d H:i:s", strtotime($request->partialShiftEndTime));
                           
                            if($partialShiftID > 0){
                                $savePartialShift = RequestPartialShift::find($partialShiftID);
                                if($savePartialShift){
                                    $savePartialShift->partialShiftStartTime = $partialShiftStartTime;
                                    $savePartialShift->partialShiftEndTime = $partialShiftEndTime;
                                    if($savePartialShift->save()){
                                        $partialShiftID = $savePartialShift->id;
                                    }
                                }else{
                                    $saveNewPartialShift = new RequestPartialShift;
                                    $saveNewPartialShift->requestID = $requestPostID;
                                    $saveNewPartialShift->partialShiftStartTime = $partialShiftStartTime;
                                    $saveNewPartialShift->partialShiftEndTime = $partialShiftEndTime;
                                    if($saveNewPartialShift->save()){
                                        $partialShiftID = $saveNewPartialShift->id;
                                    }   
                                }
                            }else{
                                $savePartialShift = new RequestPartialShift;
                                $savePartialShift->requestID = $requestPostID;
                                $savePartialShift->partialShiftStartTime = $partialShiftStartTime;
                                $savePartialShift->partialShiftEndTime = $partialShiftEndTime;
                                if($savePartialShift->save()){
                                    $partialShiftID = $savePartialShift->id;
                                } 
                            }
                         }   
           
                        $checkAlready = DB::table('staffing_shiftoffer')
                            ->select(
                        'id'
                            )->where([['requestID','=',$requestPostID],
                            ['userID','=',$userID]])->first();
                        
                            $success = false;
                            
                            if(count($checkAlready) > 0){
                                $shiftOffer = ShiftOffer::find($checkAlready->id);
                                $shiftOffer->requestID = $requestPostID;
                                $shiftOffer->userID = $userID;
                                $shiftOffer->responseType = $responseType;
                                $shiftOffer->partialShiftTimeID = $partialShiftID;
                                $shiftOffer->overTime = $overtimeStatus;
                                 if($shiftOffer->save()){
                                     $success = true;
                                     $responseJson['status'] = '1';
                                     $responseJson['message'] = $msg;
                                 }else{
                                  $responseJson['status'] = '0';
                                  $responseJson['message'] = 'Failed to send response.';  
                                 }  

                            }else{
                              $shiftOffer = new ShiftOffer;

                                $shiftOffer->requestID = $requestPostID;
                                $shiftOffer->userID = $userID;
                                $shiftOffer->responseType = $responseType;
                                $shiftOffer->partialShiftTimeID = $partialShiftID;
                                $shiftOffer->overTime = $overtimeStatus;
                                if($shiftOffer->save()){
                                    $success = true;
                                    $responseJson['status'] = '1';
                                    $responseJson['message'] = $msg;
                                }else{
                                    
                                    $responseJson['status'] = '0';
                                    $responseJson['message'] = 'Failed to send response.';
                                }  
                            }
                            
                            if($success){
                               $iosDevicesArray = array();
                                $androidDevicesArray = array();
                                 /* Send Push To ADmin */
                                
                                $postOwnerAdminInfo = User::find($post->ownerID);
                                if($postOwnerAdminInfo->pushNotification == '1'){
                                
                                    $getAdminDevices = DB::table('staffing_devices')
                                        ->select('deviceID','deviceType')
                                        ->where([['userID','=',$post->ownerID]])->get();

                                    if(count($getAdminDevices) > 0){
                                        $i = 1;
                                        foreach($getAdminDevices as $adminDevice){
                                          $deviceToken = $adminDevice->deviceID?$adminDevice->deviceID:''; 
                                          
                                            if($adminDevice->deviceType == '1'){
                                                if($deviceToken)
                                                  $iosDevicesArray[] = $deviceToken;
                                            }elseif($adminDevice->deviceType == '2'){
                                                if($deviceToken)
                                                   $androidDevicesArray[] = $deviceToken;
                                            }
                                            $i++;
                                        }

                                        
                                        $overTimeMsg = ' without OT.';
                                        if($overtimeStatus == 1){
                                            $overTimeMsg = ' with OT.';
                                        }
                                        
                                        $partialShiftTimingMsg = '';
                                        if($partialShiftStartTime && $partialShiftEndTime){
                                        $partialShiftTimingMsg = " (".date("g:i A",strtotime($partialShiftStartTime))." - " 
                .date("g:i A",strtotime($partialShiftEndTime)).")" ;
                                        }
                                        
                                         if($request->responseType == 1)
                                            $pushMsg = $user->firstName." ".$user->lastName." accepted Full Shift".$overTimeMsg;//Full Shift
                                         if($request->responseType == 2)
                                            $pushMsg = $user->firstName." ".$user->lastName." accepted Partial Shift".$partialShiftTimingMsg.$overTimeMsg;//Partial Shift
                                         if($request->responseType == 3)
                                            $pushMsg = $user->firstName." ".$user->lastName." declined request.";//Decline Shift

                                         
                                      
                                    /* Get Push Message */
                                     $pushMessage = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */     
                                         

                                        $msg_payload = array (
                                        'mtitle' => $pushMsg,
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 4,
                                        'requestID' => $post->id
                                        );

                                        $msg_payloadAndroid = array (
                                        'mtitle' => $pushMsg,
                                        'mdesc' => $pushMessage,
                                        'notificationStatus' => 4,
                                        'requestID' => $post->id
                                        ); 
                                       if($androidDevicesArray){
                                           if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                         $androidPusStatus = true;
                                       }
                                       if($iosDevicesArray){
                                           if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                               $iosPusStatus = true;
                                       } 

                                    }  
                                }
                                /* Send Push To ADmin */ 
                                
                                
                                /* Send Push To User */ 
//                                $iosDevicesArray = array();
//                               $androidDevicesArray = array();    
//                                $getUserDevices = DB::table('staffing_devices')
//                                    ->select('deviceID','deviceType')
//                                    ->where([['userID','=',$user->id]])->get();
//
//                                if(count($getUserDevices) > 0){
//                                    foreach($getUserDevices as $getUserDevice){
//                                      $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
//                                        if($getUserDevice->deviceType == '1'){
//                                            if($deviceToken)
//                                              $iosDevicesArray[] = $deviceToken;
//                                        }elseif($getUserDevice->deviceType == '2'){
//                                            if($deviceToken)
//                                               $androidDevicesArray[] = $deviceToken;
//                                        } 
//                                    } 
//
//
//                                   
//                                $pushmessage = "Thank you for your response, you will be notified shortly.";
//                                     $msg_payload = array (
//                                     'mtitle' => 'Staffing Call',
//                                     'mdesc' => $pushmessage,
//                                     'notificationStatus' => 0,
//                                     'requestID' => $post->id
//                                     );
//
//                                     $msg_payloadAndroid = array (
//                                     'mtitle' => 'Staffing Call',
//                                     'mdesc' => $pushmessage,
//                                     'notificationStatus' => 0,
//                                     'requestID' => $post->id
//                                     );   
//
//
//                                     //if($androidDevicesArray){
//                                         //if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
//                                       //$androidPusStatus = true;
//                                     //}
//
//                                     if($iosDevicesArray){
//                                         if(myHelper::iOS($msg_payload,$iosDevicesArray))
//                                             $iosPusStatus = true;
//                                     }
//
//                                }
                                  /* Send Push To User */  
                                
                                
                            }
                            
                            
                        
                             return response()->json($responseJson); 
                            
                          
                          
                          }else{
                            return response()->json(['status'=>'0',
                                'message'=>'You are not allowed to send response.'], 500);   
                          }
                          
                        } else{
                          return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                        }  
                    
                        
                       }else{
                         return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                       }
                    
                   }else {
                     return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                   }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        } 
        
        
        
    public function respondedUsersOLD(Request $request){

        if($request->userID){

            $id = $request->userID;
            $user = User::find($id);
            $userID = $user->id;

            if ($user) {

                $requestPostID = $request->requestID;
                if($requestPostID > 0){

                    //$post = Requestcall::find($requestPostID);

                    $post = DB::table('staffing_staffrequest')
                     ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                      ->select('staffing_staffrequest.id',
                     'staffing_staffrequest.staffingStartDate',
                     'staffing_staffrequest.staffingEndDate',
                     'staffing_staffrequest.postingStatus',
                     'staffing_staffrequest.numberOfOffers',
                     'staffing_staffrequest.offerAlgorithmID',
                     'staffing_staffrequest.shiftType',
                     'staffing_shiftsetup.startTime',
                     'staffing_shiftsetup.endTime',
                     'staffing_staffrequest.customShiftStartTime',
                     'staffing_staffrequest.customShiftEndTime')->where([
                         ['staffing_staffrequest.id','=',$requestPostID]
                         ])->first();

                 if(count($post) > 0){


                     /* Check if any user accepted/confirmed offer and request is scheduled? */
                     $offerConfirmedUser = array();
                     $isOfferAccepted =  0;
                     $requestShiftOffers = DB::table('staffing_shiftoffer')
                         ->select('id','userID')->where([['requestID','=',$requestPostID]])->get();
                     if(count($requestShiftOffers) > 0){
                         foreach($requestShiftOffers as $requestShiftOffer){
                             
                            $offerConfirmCheckSql = DB::table('staffing_shiftconfirmation')
                                                      ->select('id');
                            $offerConfirmCheckSql->where('shiftOfferID','=',$requestShiftOffer->id);
                            $offerConfirmCheckSql->whereIn('offerResponse', [1,2]);
                            $offerConfirmCheck = $offerConfirmCheckSql->first(); 

                           if(count($offerConfirmCheck) > 0){
                              $offerConfirmedUser[] =  $requestShiftOffer->userID;
                              $isOfferAccepted =  1;
                           }


                         }
                     }

                     /* Check if any user accepted/confirmed offer and request is scheduled? */

                   if($user->role != '0'){ 

                       $respondUsersSql = DB::table('staffing_users')

                       ->join('staffing_shiftoffer', function($join) use($post)
                          {
                             $join->on('staffing_shiftoffer.userID', '=', 'staffing_users.id');
                         })
                     ->leftJoin('staffing_shiftconfirmation','staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                     ->leftJoin('staffing_requestpartialshifts', 
                     'staffing_requestpartialshifts.id', 
                     '=', 'staffing_shiftoffer.partialShiftTimeID') 

                     ->select(
                         'staffing_shiftoffer.userID AS responseUserID',
                         'staffing_shiftoffer.responseType',
                         'staffing_shiftoffer.overTime',
                         'staffing_shiftoffer.inWaitList',
                         'staffing_shiftoffer.partialShiftTimeID',
                         'staffing_users.profilePic',
                         'staffing_users.skills',
                         'staffing_shiftconfirmation.offerResponse',
                         'staffing_shiftconfirmation.id AS offerID',
                         'staffing_requestpartialshifts.partialShiftStartTime',
                         'staffing_requestpartialshifts.partialShiftEndTime',
                     DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS userName")
                     );

                     $respondUsersSql->where('staffing_shiftoffer.requestID','=', $post->id); 

                     if($request->requestType == 1){//FULL SHIFT
                       $respondUsersSql->where('staffing_shiftoffer.responseType','=', 0);   
                     }elseif($request->requestType == 2){//PARTIAL SHIFT
                        $respondUsersSql->where('staffing_shiftoffer.responseType','=', 1);  
                     }
                     
                    if($post->offerAlgorithmID > 0){
                        $offerAlgorithm = DB::table('staffing_offeralgorithm')
                            ->select('type')->where([['id', '=', $post->offerAlgorithmID]])->first();
                        if($offerAlgorithm){
                            if($offerAlgorithm->type == 'simple'){
                                $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'ASC');
                                $respondUsersSql->orderBy('staffing_shiftoffer.overTime', 'ASC'); 
                            }else{
                                $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                                $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC'); 
                            }
                        }else{
                                $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                                $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC'); 
                            }
                    } else {
                        $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                        $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC'); 
                    }

                     
                     /* Active Requests */
                     $users = $respondUsersSql->get();
                     $responseJson['status'] = '1';
                     $responseJson['data'] = array();
                     $progressStatus = 0;
                     foreach($users as $user){
                         $profilePic = $user->profilePic?url('public/'.$user->profilePic):url('/assets/images/profile.jpeg');

                         /* Get User Skills */
                         $skillsArr = array();
                         $skillsArr = $user->skills?unserialize($user->skills):array();

                         $userSkills = array();
                         if($skillsArr){
                            $usersCategory = DB::table('staffing_skillcategory')
                         ->select(
                                 'id',
                                 'skillName'
                                 )->whereIn(
                                         'id',$skillsArr)->orderBy('skillName','ASC')->get();

                            foreach($usersCategory as $userSkill){
                                $userSkills[] = $userSkill->skillName;
                            }

                         }
                         /* Get User Skills */

                         $partialShiftID =  $user->partialShiftTimeID?$user->partialShiftTimeID:0;
                         $responseType =  $user->responseType; 
                         //1=>Full Shift, 2=>Partial Shift,3=>Decline

                         $cancelStatus = 0;
                         if($responseType == 0)
                             $responseTypeStaus = 1;//Full Shift

                         if($responseType == 1)
                             $responseTypeStaus = 2;//Partial Shift

                         if($responseType == 2){
                             $responseTypeStaus = 3;//Decline Shift 
                             $cancelStatus = 3;
                         }


                         $overtimeStatus = $user->overTime?1:0;//1 =>Yes,0=>No

                         $offerStatus = 0;//Offer not sent yet.
                         if($user->offerID){
                             if($user->offerResponse == 0){
                                 $offerStatus = 1;//Offer sent but user doesn't reply.
                                 $progressStatus = 1; 
                             }
                             if($user->offerResponse == 1){
                                 $offerStatus = 2;//Offer is accepted by user.
                             }
                             if($user->offerResponse == 2){
                                 $offerStatus = 3;//Offer is declined by user
                                 $cancelStatus = 0;
                             }
                         }


                         /* Chk if user is in waitlist? */
                         if($isOfferAccepted == 1){
                            if(in_array($user->responseUserID, $offerConfirmedUser)){

                            } else{
                               if(count($offerConfirmedUser) >= $post->numberOfOffers) 
                               $offerStatus = 4;//Other Users are in Waitlist || waitlist on-going. 
                            }
                         }
                         /* Chk if user is in waitlist? */


                         /* Check if call in going on waitlist then is user be on waitlist or not? */
                         if($offerStatus == 4){
                             if($user->inWaitList == 1){
                                $offerStatus = 5;//User confirmed to be on waitlist. 
                             }else if($user->inWaitList == 1){
                                $offerStatus = 6;//User declined to be on waitlist. 
                             }else if($user->inWaitList == 3){
                               //Offer again sent after waitlist confirmation.
                                 if($cancelStatus > 0){
                                     $offerStatus = $cancelStatus;
                                     $progressStatus = 1;  
                                 }else{
                                   $offerStatus = 1;//Offer sent but user doesn't reply.
                                   $progressStatus = 1;  
                                 }

                             }  
                         }
                         /* Check if call in going on waitlist then is user be on waitlist or not? */


                         /* if User declined Full / Partial then Set Offer as declined */
                         if($responseTypeStaus == 3)
                             $offerStatus = 3;
                         /* if User declined Full / Partial then Set Offer as declined */

                         /* Shift Timing Which User Accepted */

                         if($user->responseType == 0){
                             if($post->shiftType == 1){
                                 $shiftTimeForUser =  date("g:i A",strtotime($post->customShiftStartTime))." - "
                                     .date("g:i A",strtotime($post->customShiftEndTime));


                             $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;

                             }else{
                              $shiftTimeForUser =  date("g:i A",strtotime($post->startTime))." - " 
                                 .date("g:i A",strtotime($post->endTime)) ;


                             $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;


                             }


                         }elseif($user->responseType == 1){

                          $shiftTimeForUser =  date("g:i A",strtotime($user->partialShiftStartTime))." - " 
                            .date("g:i A",strtotime($user->partialShiftEndTime)) ;

                             $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;
                         }else{
                            $shiftTimeForUser = ''; 
                         }


                         /* Shift Timing Which User Accepted */


                         $responseJson['data'][] = array(
                           'id' => (string)$user->responseUserID,
                           'name' => $user->userName  ,
                           'profilePic' => $profilePic  ,
                           'skills' => $userSkills?implode(', ',$userSkills):'',
                           'responseType' => (string)$responseTypeStaus,  
                           'overtimeStatus' => (string)$overtimeStatus,   
                           'shiftTime' => (string)$shiftTimeForUser,   
                           'offerStatus' => (string)$offerStatus, 
                           //0=>Offer not sent,1=>Offer Sent,
                           //2 => Accepted By User,3=>Declined By User,4=>You are on waitlist

                         );

                     }

                     if($post->postingStatus == 2 || 
                        $post->postingStatus == 4 || $post->staffingStartDate < date("Y-m-d")){
                         $progressStatus = 2;//Request Post/Call is closed.
                     }

                     $responseJson['requestStatus'] = (string)$progressStatus;
                     //0=>Offer not sent, 1=>In progress, 2=> Request is closed

                     return response()->json($responseJson);   



                   }else{
                     return response()->json(['status'=>'0',
                         'message'=>'You are not allowed to view this page.'], 500);   
                   }

                 } else{
                   return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                 }  


                }else{
                  return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                }

            } else {
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
            }
            }else{
                return response()->json(['status'=>'0','message'=>'User not found.'], 500);  

        }
    } 
    
    
    
    public function respondedUsers(Request $request){

        if($request->userID){

            $id = $request->userID;
            $user = User::find($id);
            $userID = $user->id;

            if ($user) {

                $requestPostID = $request->requestID;
                if($requestPostID > 0){

                    $post = DB::table('staffing_staffrequest')
                     ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                      ->select('staffing_staffrequest.id',
                     'staffing_staffrequest.staffingStartDate',
                     'staffing_staffrequest.staffingEndDate',
                     'staffing_staffrequest.businessUnitID',
                     'staffing_staffrequest.postingStatus',
                     'staffing_staffrequest.numberOfOffers',
                     'staffing_staffrequest.shiftType',
                     'staffing_shiftsetup.startTime',
                     'staffing_shiftsetup.endTime',
                     'staffing_staffrequest.customShiftStartTime',
                     'staffing_staffrequest.customShiftEndTime')->where([
                         ['staffing_staffrequest.id','=',$requestPostID]
                         ])->first();

                 if(count($post) > 0){


                     /* Check if any user accepted/confirmed offer and request is scheduled? */
                     $offerConfirmedUser = array();
                     $isOfferAccepted =  0;
                     $requestShiftOffers = DB::table('staffing_shiftoffer')
                         ->select('id','userID')->where([['requestID','=',$requestPostID]])->get();
                     if(count($requestShiftOffers) > 0){
                         
                         foreach($requestShiftOffers as $requestShiftOffer){
                            
                            $offerConfirmCheckSql = DB::table('staffing_shiftconfirmation')
                                                      ->select('id');
                            $offerConfirmCheckSql->where('shiftOfferID','=',$requestShiftOffer->id);
                            $offerConfirmCheckSql->whereIn('offerResponse', [1,2]);
                            $offerConfirmCheck = $offerConfirmCheckSql->first();
                            if(count($offerConfirmCheck) > 0){
                               $offerConfirmedUser[] =  $requestShiftOffer->userID;
                               $isOfferAccepted =  1;
                            }


                         }
                     }

                     /* Check if any user accepted/confirmed offer and request is scheduled? */

                    if($user->role != '0'){ 
                        $algorithmName = 'open';
                        $complexPoolOrder = '';
                        if($post->businessUnitID > 0){
                            $businessUnitInfo = Businessunit::find($post->businessUnitID);
                            if($businessUnitInfo->id > 0){
                                $offerAlgorithm = DB::table('staffing_offeralgorithm')
                                    ->select('type')->where([['id', '=', $businessUnitInfo->offerAlgorithmID]])->first();
                                if($offerAlgorithm){
                                    if($offerAlgorithm->type == 'simple'){
                                        $algorithmName = 'simple';
                                    }else if($offerAlgorithm->type == 'complex'){
                                        $algorithmName = 'complex';
                                        $complexPoolOrder = $businessUnitInfo->complexPoolOrder;
                                    }
                                } 
                            }
                        }
                        
                        /* ## For Open ##
                            * 1 => Responses are sorted by response received list 
                            * regardless of Full time shift / Part time shift, 
                            * Overtime and not overtime. Sort declined responses 
                            * at the bottom of candidates list.
                         * ## For Simple ##  
                            * 1 => Full shift available , not overtime 
                            * 2 => Full shift available , overtime 
                            * 3 => Partial shift available , not overtime 
                            * 4 => Partial shift available , overtime  
                         * ## For Complex ##  
                            * 1 => First choice pool A ; not overtime, full time shift 
                         
                            *  ### The Below Order can be changed according to Manager set in their Unit ###
                          
                            * 2 => Pool B - Not overtime, partial shift ; show in order or most hours possible 
                            * 3 => Pool C - Overtime with full shift 
                            * 4 => Pool D - Overtime with partial shift  ; show in order or most hours possible  
                         */
                        
                    if($request->requestType == 1){//FULL SHIFT
                        if($algorithmName == 'simple'){
                            
                            
                            
                            /* Full Shift No-OverTime */
                            $fullShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                1, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Full Shift-No Overtime"  ,
                               'data' => $fullShiftNoOvertimeUsers  
                            );
                            /* Full Shift No-OverTime */
                            
                            /* Full Shift OverTime */
                            $fullShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                2, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            
                            $responseJson['data'][1] = array(
                                'name' => "Full Shift-Overtime"  ,
                                'data' => $fullShiftOvertimeUsers  
                            );
                            /* Full Shift OverTime */
                            
                        }else if($algorithmName == 'complex'){
                            
                            /* Full Shift No-OverTime */
                            $fullShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                1, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Full Shift-No Overtime"  ,
                               'data' => $fullShiftNoOvertimeUsers  
                            );
                            /* Full Shift No-OverTime */
                            
                            /* Full Shift OverTime */
                            $fullShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                2, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            
                            $responseJson['data'][1] = array(
                                'name' => "Full Shift-Overtime"  ,
                                'data' => $fullShiftOvertimeUsers  
                            );
                            /* Full Shift OverTime */
                        }else{
                            
                            $allUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                0, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Open"  ,
                               'data' => $allUsers  
                            );  
                        } 
                        
                    }elseif($request->requestType == 2){//PARTIAL SHIFT
                        
                         /* Partial Shift No-OverTime */
                        if($algorithmName == 'simple'){
                            $partialShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                3, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Partial Shift-No Overtime"  ,
                               'data' => $partialShiftNoOvertimeUsers  
                            );
                            /* Partial Shift No-OverTime */
                            
                            /* Partial Shift OverTime */
                            $partialShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                4, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][1] = array(
                               'name' => "Partial Shift-Overtime"  ,
                               'data' => $partialShiftOvertimeUsers  
                            );
                            /* Partial Shift OverTime */
                            
                        }else if($algorithmName == 'complex'){
                            
                            /* Partial Shift No-OverTime */
                            $partialShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                3, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Partial Shift-No Overtime"  ,
                               'data' => $partialShiftNoOvertimeUsers  
                            );
                            /* Partial Shift No-OverTime */
                            
                            /* Partial Shift OverTime */
                            $partialShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                4, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][1] = array(
                               'name' => "Partial Shift-Overtime"  ,
                               'data' => $partialShiftOvertimeUsers  
                            );
                            /* Partial Shift OverTime */
                            
                        }else{
                            
                            $allUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                0, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Open"  ,
                               'data' => $allUsers  
                            );  
                        }   
                    }else{
                       
                        if($algorithmName == 'simple'){
                            
                            /* Full Shift No-OverTime */
                            $fullShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                1, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Full Shift-No Overtime"  ,
                               'data' => $fullShiftNoOvertimeUsers  
                            );
                            /* Full Shift No-OverTime */
                            
                            /* Full Shift OverTime */
                            $fullShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                2, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            
                            $responseJson['data'][1] = array(
                                'name' => "Full Shift-Overtime"  ,
                                'data' => $fullShiftOvertimeUsers  
                            );
                            /* Full Shift OverTime */
                            
                            /* Partial Shift No-OverTime */
                            $partialShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                3, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][2] = array(
                               'name' => "Partial Shift-No Overtime"  ,
                               'data' => $partialShiftNoOvertimeUsers  
                            );
                            /* Partial Shift No-OverTime */
                            
                            /* Partial Shift OverTime */
                            $partialShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                4, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][3] = array(
                               'name' => "Partial Shift-Overtime"  ,
                               'data' => $partialShiftOvertimeUsers  
                            );
                            /* Partial Shift OverTime */
                            
                            /* Declined Shift */
                            $declinedUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                5, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][4] = array(
                               'name' => "Declined"  ,
                               'data' => $declinedUsers  
                            );
                            /* Declined Shift */
                            
                        }else if($algorithmName == 'complex'){
                            
                            /* Full Shift No-OverTime */
                            $fullShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                1, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            /* Full Shift No-OverTime */
                            
                            /* Full Shift OverTime */
                            $fullShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                2, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            /* Full Shift OverTime */
                            
                            /* Partial Shift No-OverTime */
                            $partialShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                3, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            /* Partial Shift No-OverTime */
                            
                            /* Partial Shift OverTime */
                            $partialShiftOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                4, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                            /* Partial Shift OverTime */
                            
                            if($complexPoolOrder != ''){
                               /* ###Defaul Complex Order### 
                                * 1=>Full Shift No-OverTime
                                * 2=>Partial Shift No-OverTime
                                * 3=>Full Shift OverTime
                                * 4=>Partial Shift OverTime
                                */
                               
                               $getOrder = explode(',', $complexPoolOrder);
                               
                               /* Set First Order */
                                if($getOrder[0] == '1'){
                                    $responseJson['data'][0] = array(
                                      'name' => "Full Shift-No Overtime"  ,
                                      'data' => $fullShiftNoOvertimeUsers  
                                    );  
                                }else if($getOrder[0] == '2'){
                                    $responseJson['data'][0] = array(
                                        'name' => "Partial Shift-No Overtime"  ,
                                        'data' => $partialShiftNoOvertimeUsers  
                                    ); 
                                }else if($getOrder[0] == '3'){
                                   $responseJson['data'][0] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                    ); 
                                }else if($getOrder[0] == '4'){
                                   $responseJson['data'][0] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                    ); 
                                }else{
                                   $responseJson['data'][0] = array(
                                   'name' => "Full Shift-No Overtime"  ,
                                   'data' => $fullShiftNoOvertimeUsers  
                                    ); 
                                }
                                /* Set First Order */
                               
                               /* Set Second Order */
                                if($getOrder[1] == '1'){
                                    $responseJson['data'][1] = array(
                                      'name' => "Full Shift-No Overtime"  ,
                                      'data' => $fullShiftNoOvertimeUsers  
                                    );  
                                }else if($getOrder[1] == '2'){
                                    $responseJson['data'][1] = array(
                                        'name' => "Partial Shift-No Overtime"  ,
                                        'data' => $partialShiftNoOvertimeUsers  
                                    ); 
                                }else if($getOrder[1] == '3'){
                                   $responseJson['data'][1] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                    ); 
                                }else if($getOrder[1] == '4'){
                                   $responseJson['data'][1] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                    ); 
                                }else{
                                   $responseJson['data'][1] = array(
                                   'name' => "Partial Shift-No Overtime"  ,
                                   'data' => $partialShiftNoOvertimeUsers  
                                    );
                                }
                                /* Set Second Order */
                               
                               /* Set Third Order */
                                if($getOrder[2] == '1'){
                                    $responseJson['data'][2] = array(
                                      'name' => "Full Shift-No Overtime"  ,
                                      'data' => $fullShiftNoOvertimeUsers  
                                    );  
                                }else if($getOrder[2] == '2'){
                                    $responseJson['data'][2] = array(
                                        'name' => "Partial Shift-No Overtime"  ,
                                        'data' => $partialShiftNoOvertimeUsers  
                                    ); 
                                }else if($getOrder[2] == '3'){
                                   $responseJson['data'][2] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                    ); 
                                }else if($getOrder[2] == '4'){
                                   $responseJson['data'][2] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                    ); 
                                }else{
                                    $responseJson['data'][2] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                    );
                                }
                                /* Set Third Order */
                               
                               /* Set Fourth/Last Order */
                                if($getOrder[3] == '1'){
                                    $responseJson['data'][3] = array(
                                      'name' => "Full Shift-No Overtime"  ,
                                      'data' => $fullShiftNoOvertimeUsers  
                                    );  
                                }else if($getOrder[3] == '2'){
                                    $responseJson['data'][3] = array(
                                        'name' => "Partial Shift-No Overtime"  ,
                                        'data' => $partialShiftNoOvertimeUsers  
                                    ); 
                                }else if($getOrder[3] == '3'){
                                   $responseJson['data'][3] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                    ); 
                                }else if($getOrder[3] == '4'){
                                   $responseJson['data'][3] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                    ); 
                                }else{
                                   $responseJson['data'][3] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                    );
                                }
                                /* Set Fourth/Last Order */
                                
                                
                                
                            }else{
                            
                                $responseJson['data'][0] = array(
                                   'name' => "Full Shift-No Overtime"  ,
                                   'data' => $fullShiftNoOvertimeUsers  
                                );

                                $responseJson['data'][1] = array(
                                   'name' => "Partial Shift-No Overtime"  ,
                                   'data' => $partialShiftNoOvertimeUsers  
                                );

                                $responseJson['data'][2] = array(
                                    'name' => "Full Shift-Overtime"  ,
                                    'data' => $fullShiftOvertimeUsers  
                                );

                                $responseJson['data'][3] = array(
                                   'name' => "Partial Shift-Overtime"  ,
                                   'data' => $partialShiftOvertimeUsers  
                                );
                            }
                            
                            
                            
                            /* Declined Shift */
                            $declinedUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                5, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][4] = array(
                               'name' => "Declined"  ,
                               'data' => $declinedUsers  
                            );
                            /* Declined Shift */
                            
                            
                        }else{
                            
                            $allUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                0, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Open"  ,
                               'data' => $allUsers  
                            ); 
                            
                            /* Declined Shift */
                            $declinedUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                5, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][1] = array(
                               'name' => "Declined"  ,
                               'data' => $declinedUsers  
                            );
                            /* Declined Shift */
                            
                            
                        }
                    }
                    
                    
                        $progressStatus = 0;

                        if($post->postingStatus == 2 || 
                           $post->postingStatus == 4 || $post->staffingStartDate < date("Y-m-d")){
                            $progressStatus = 2;//Request Post/Call is closed.
                        }

                        $responseJson['status'] = (string)1;
                        $responseJson['requestStatus'] = (string)$progressStatus;
                        //0=>Offer not sent, 1=>In progress, 2=> Request is closed
                        return response()->json($responseJson);   



                    }else{
                      return response()->json(['status'=>'0',
                          'message'=>'You are not allowed to view this page.'], 500);   
                    }

                } else{
                  return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                }  


                }else{
                  return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                }

            } else {
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
            }
            }else{
                return response()->json(['status'=>'0','message'=>'User not found.'], 500);  

        }
    } 
        
        
    public function getRespondedUsersFollowedByAlgorithm($sorting = 0, 
                                $post, 
                                $algorithmName,  
                                $complexPoolOrder, 
                                $request, 
                                $isOfferAccepted,
                                $offerConfirmedUser){
        
        $respondedUsersArr = array();
        
        $respondUsersSql = DB::table('staffing_users')

        ->join('staffing_shiftoffer', function($join) use($post)
        {
            $join->on('staffing_shiftoffer.userID', '=', 'staffing_users.id');
        })
        ->leftJoin('staffing_shiftconfirmation','staffing_shiftconfirmation.shiftOfferID', 
                '=', 'staffing_shiftoffer.id')
        ->leftJoin('staffing_requestpartialshifts', 
            'staffing_requestpartialshifts.id', 
            '=', 'staffing_shiftoffer.partialShiftTimeID') 
        ->select(
            'staffing_shiftoffer.userID AS responseUserID',
            'staffing_shiftoffer.responseType',
            'staffing_shiftoffer.overTime',
            'staffing_shiftoffer.inWaitList',
            'staffing_shiftoffer.partialShiftTimeID',
            'staffing_users.profilePic',
            'staffing_users.skills',
            'staffing_shiftconfirmation.offerResponse',
            'staffing_shiftconfirmation.id AS offerID',
            'staffing_requestpartialshifts.partialShiftStartTime',
            'staffing_requestpartialshifts.partialShiftEndTime',
            DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS userName")
        );

        $respondUsersSql->where('staffing_shiftoffer.requestID','=', $post->id);
        
        if($sorting == 1){//Full Shift - No OverTime
          $respondUsersSql->where('staffing_shiftoffer.responseType','=', 0); 
          $respondUsersSql->where('staffing_shiftoffer.overTime','=', 0);
          $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
        }else if($sorting == 2){//Full Shift - OverTime
           $respondUsersSql->where('staffing_shiftoffer.responseType','=', 0); 
           $respondUsersSql->where('staffing_shiftoffer.overTime','=', 1); 
           $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
        }else if($sorting == 3){//Partial Shift - No OverTime
           $respondUsersSql->where('staffing_shiftoffer.responseType','=', 1); 
           $respondUsersSql->where('staffing_shiftoffer.overTime','=', 0);  
           $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
        }else if($sorting == 4){//Partial Shift - OverTime
           $respondUsersSql->where('staffing_shiftoffer.responseType','=', 1); 
           $respondUsersSql->where('staffing_shiftoffer.overTime','=', 1);
           $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
        }else if($sorting == 5){//Partial Shift - OverTime
           $respondUsersSql->where('staffing_shiftoffer.responseType','=', 2);
           $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
        }else{
            if($request->requestType == 1){ 
                 $respondUsersSql->where('staffing_shiftoffer.responseType','=', 0);  
                 $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                 $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC');  
            }else if($request->requestType == 2){
                 $respondUsersSql->where('staffing_shiftoffer.responseType','=', 1); 
                 $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                 $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC');
            }else{
                 $respondUsersSql->where('staffing_shiftoffer.responseType','!=', 2); 
                 $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                 $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC');   
            }
        }
        
        
//        if($algorithmName == 'simple'){
//            $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'ASC');
//            $respondUsersSql->orderBy('staffing_shiftoffer.overTime', 'ASC'); 
//        }else if($algorithmName == 'complex'){
//            $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
//            $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC'); 
//        }else{
//            $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
//            $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC'); 
//        }
            
         
        /* Active Requests */
        $users = $respondUsersSql->get();
        $responseJson['status'] = '1';
        $responseJson['data'] = array();
        $progressStatus = 0;
        foreach($users as $user){
            $profilePic = $user->profilePic?url('public/'.$user->profilePic):url('/assets/images/profile.jpeg');
            /* Get User Skills */
            $skillsArr = array();
            $skillsArr = $user->skills?unserialize($user->skills):array();
            $userSkills = array();
            if($skillsArr){
                $usersCategory = DB::table('staffing_skillcategory')
                ->select(
                   'id',
                   'skillName'
                   )->whereIn(
                       'id',$skillsArr)->orderBy('skillName','ASC')->get();

               foreach($usersCategory as $userSkill){
                   $userSkills[] = $userSkill->skillName;
               }
            }
            
            /* Get User Skills */
            $partialShiftID =  $user->partialShiftTimeID?$user->partialShiftTimeID:0;
            $responseType =  $user->responseType; 
            //1=>Full Shift, 2=>Partial Shift,3=>Decline
            
            $cancelStatus = 0;
            if($responseType == 0)
                $responseTypeStaus = 1;//Full Shift
            if($responseType == 1)
                $responseTypeStaus = 2;//Partial Shift
            if($responseType == 2){
                $responseTypeStaus = 3;//Decline Shift 
                $cancelStatus = 3;
            }

            $overtimeStatus = $user->overTime?1:0;//1 =>Yes,0=>No
            $offerStatus = 0;//Offer not sent yet.
            if($user->offerID){
                if($user->offerResponse == 0){
                    $offerStatus = 1;//Offer sent but user doesn't reply.
                    $progressStatus = 1; 
                }
                if($user->offerResponse == 1){
                    $offerStatus = 2;//Offer is accepted by user.
                }
                if($user->offerResponse == 2){
                    $offerStatus = 3;//Offer is declined by user
                    $cancelStatus = 0;
                }
            }
            /* Chk if user is in waitlist? */
            if($isOfferAccepted == 1){
               if(in_array($user->responseUserID, $offerConfirmedUser)){

               } else{
                  if(count($offerConfirmedUser) >= $post->numberOfOffers) 
                  $offerStatus = 4;//Other Users are in Waitlist || waitlist on-going. 
               }
            }
            /* Chk if user is in waitlist? */

            /* Check if call in going on waitlist then is user be on waitlist or not? */
            if($offerStatus == 4){
                if($user->inWaitList == 1){
                   $offerStatus = 5;//User confirmed to be on waitlist. 
                }else if($user->inWaitList == 2){
                   $offerStatus = 6;//User declined to be on waitlist. 
                }else if($user->inWaitList == 3){
                  //Offer again sent after waitlist confirmation.
                    if($cancelStatus > 0){
                        $offerStatus = $cancelStatus;
                        $progressStatus = 1;  
                    }else if($user->offerID > 0 && $user->offerResponse == 2){
                       $offerStatus = 3;//Offer Declined after waitlist.
                       $progressStatus = 1; 
                    }else{
                      $offerStatus = 1;//Offer sent but user doesn't reply.
                      $progressStatus = 1;  
                    }

                }  
            }
            /* Check if call in going on waitlist then is user be on waitlist or not? */
            /* if User declined Full / Partial then Set Offer as declined */
            if($responseTypeStaus == 3)
                $offerStatus = 3;
            
            /* if User declined Full / Partial then Set Offer as declined */
            /* Shift Timing Which User Accepted */
            if($user->responseType == 0){
                if($post->shiftType == 1){
                    $shiftTimeForUser =  date("g:i A",strtotime($post->customShiftStartTime))." - "
                        .date("g:i A",strtotime($post->customShiftEndTime));
                    $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;
                }else{
                    $shiftTimeForUser =  date("g:i A",strtotime($post->startTime))." - " 
                        .date("g:i A",strtotime($post->endTime)) ;
                    $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;
                }

            }elseif($user->responseType == 1){

                $shiftTimeForUser =  date("g:i A",strtotime($user->partialShiftStartTime))." - " 
                .date("g:i A",strtotime($user->partialShiftEndTime)) ;
                $shiftTiming = date("l M d, Y",strtotime($post->staffingStartDate))." - ".$shiftTimeForUser;
            }else{
               $shiftTimeForUser = ''; 
            }
            
                /* Shift Timing Which User Accepted */
                $respondedUsersArr[] = array(
                  'id' => (string)$user->responseUserID,
                  'name' => $user->userName  ,
                  'profilePic' => $profilePic  ,
                  'skills' => $userSkills?implode(', ',$userSkills):'',
                  'responseType' => (string)$responseTypeStaus,  
                  'overtimeStatus' => (string)$overtimeStatus,   
                  'shiftTime' => (string)$shiftTimeForUser,   
                  'offerStatus' => (string)$offerStatus, 
                  //0=>Offer not sent,1=>Offer Sent,
                  //2 => Accepted By User,3=>Declined By User,4=>You are on waitlist
                );

        } 
        
        return $respondedUsersArr;
    } 
         
        
        public function makeOffer(Request $request){
            
            if($request->userID){

                $id = $request->userID;
                $user = User::find($id);
                $userID = $user->id;
                
                   if ($user) {
                       
                       $requestPostID = $request->requestID;
                       $requestedUserID = $request->requestedUserID;
                       if($requestPostID > 0){
                           
                           $post = Requestcall::find($requestPostID);
                           
                        if($post){
                            
                           $requestedUserInfo = User::find($requestedUserID); 
                         if($requestedUserInfo){  
                            
                          if($user->role != '0'){
                              
                            if($requestedUserInfo->role == 0 || $requestedUserInfo->role == 4){
                                                            
                                $checkExist = DB::table('staffing_shiftoffer')
                                    ->select(
                                'id','userID','inWaitList'
                                    )->where([['requestID','=',$requestPostID],
                                    ['userID','=',$requestedUserID]])->first();

                                if(count($checkExist) > 0){
                                    
                                    $checkOffer = DB::table('staffing_shiftconfirmation')
                                    ->select(
                                'id','shiftOfferID','offerResponse','created_at'
                                    )->where([['shiftOfferID','=',$checkExist->id]])->first();
                                    
                                    if(count($checkOffer) > 0){
                                        if($checkOffer->offerResponse == 0){
                                            
                                            /* If User was on waitlist then Update status for second offer */
                                            if($checkExist->inWaitList == 1){
                                                $updateWaitlistStatus = DB::table('staffing_shiftoffer')
                                                ->where([['id', $checkExist->id],['userID', $requestedUserID]])
                                                ->update(['inWaitList' => 3]);
                                            }
                                            /* If User was on waitlist then Update status for second offer */
                                            
                                            $responseJson['status'] = '1';
                                            $responseJson['message'] = 'Offer already sent.';                                           
                                            
                                        }
                                        if($checkOffer->offerResponse == 1){
                                            
                                            $responseJson['status'] = '1';
                                            $responseJson['message'] = 'Offer accepted by user.';                                             
                                            
                                        }
                                        if($checkOffer->offerResponse == 2){
                                            
                                            $responseJson['status'] = '1';
                                            $responseJson['message'] = 'Offer declined by user.';
                                            
                                            
                                        }
                                        
                                    }else{
                                       $offerConfirmation = new OfferConfirmation;

                                        $offerConfirmation->shiftOfferID = $checkExist->id;
                                        $offerConfirmation->offerResponse = 0;
                                        if($offerConfirmation->save()){
                                            
                                            /* If User was on waitlist then Update status for second offer */
                                            if($checkExist->inWaitList == 1){
                                                $updateWaitlistStatus = DB::table('staffing_shiftoffer')
                                                ->where([['id', $checkExist->id],['userID', $requestedUserID]])
                                                ->update(['inWaitList' => 3]);
                                            }
                                            /* If User was on waitlist then Update status for second offer */
                                            
                                            
                                            $iosDevicesArray = array();
                                            $androidDevicesArray = array();
                                            
                                           /* Send Push Notifications */
                                            if($requestedUserInfo->pushNotification == '1'){
                                                $getDevices = DB::table('staffing_devices')
                                                    ->select('deviceID','deviceType')
                                                    ->where([['userID','=',$requestedUserID]])->get();

                                                if(count($getDevices) > 0){
                                                    foreach($getDevices as $getDevice){
                                                      $deviceToken = $getDevice->deviceID?$getDevice->deviceID:''; 
                                                        if($getDevice->deviceType == '1'){
                                                            if($deviceToken)
                                                              $iosDevicesArray[] = $deviceToken;
                                                        }elseif($getDevice->deviceType == '2'){
                                                            if($deviceToken)
                                                               $androidDevicesArray[] = $deviceToken;
                                                        } 
                                                    }
                                                    
                                                    
                                                     
                                    /* Get Push Message */
                                     $pushMessage = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */ 
                                            
                                                    $msg_payload = array (
                                                    'mtitle' => "Congratulations, You've been offered a shift, please respond",
                                                    'mdesc' => $pushMessage,
                                                    'notificationStatus' => 5,
                                                    'requestID' => $post->id
                                                    );

                                                    $msg_payloadAndroid = array (
                                                    'mtitle' => "Congratulations, You've been offered a shift, please respond",
                                                    'mdesc' => $pushMessage,
                                                    'notificationStatus' => 5,
                                                    'requestID' => $post->id
                                                    );   
                                

                                                    if($androidDevicesArray){
                                                        if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                                      $androidPusStatus = true;
                                                    }

                                                    if($iosDevicesArray){
                                                        if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                                            $iosPusStatus = true;
                                                    }
                                            
                                                }
                                            }
                                           /* Send Push Notifications */ 
                                            
                                            $responseJson['status'] = '1';
                                            $responseJson['message'] = 'Response sent.';
                                            
                                        }else{
                                            
                                         $responseJson['status'] = '0';
                                         $responseJson['message'] = 'Failed to send offer.';  
                                            
                                         
                                        }   
                                    }
                                    
                                    
                                   return response()->json($responseJson);  


                                }else{
                                    return response()->json(['status'=>'0',
                                    'message'=>'You can not make offer to this user'], 500); 
                                    
                                }
                            
                            }else{
                               return response()->json(['status'=>'0',
                                    'message'=>'You can not make offer to this user'], 500);  
                            }
                            
                          
                          }else{
                            return response()->json(['status'=>'0',
                                'message'=>'You are not allowed to send response.'], 500);   
                          }
                         }else{
                           return response()->json(['status'=>'0','message'=>'Requested user not found.'], 500);  
                         }
                          
                        } else{
                          return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                        }  
                    
                        
                       }else{
                         return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                       }
                    
                   }else {
                     return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                   }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        } 
    
    
        function setUserAsUnAvailableOnAdjecentShifts($fromDate, $toDate, $businessUnitID, $shiftID, $loginUserID){
            $getShifts = DB::table('staffing_shiftsetup')
            ->select('id','startTime','endTime','shiftType')
            ->where([['businessUnitID','=',$businessUnitID]])->orderBy('startTime', 'ASC')->get();
            $insertData = array();
            if($getShifts){
              $flag = 0;
              
             
              
              foreach($getShifts as $getShift){
                  if($shiftID == $getShift->id){
                      
                      
                      
                      if($flag == 0){
                          
                          
                          //Previous shift unavailabel
                          if(isset($getShifts[count($getShifts)-1])){
                                $getPreShiftID = $getShifts[count($getShifts)-1]->id; 

                                $Date1 = date("Y-m-d", strtotime($fromDate . " -1 day"));
                                $Date2 = date("Y-m-d", strtotime($toDate . " -1 day"));
                                $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                                foreach($daysCount as $k=>$v){
                                   $insertData[] = array(
                                    'userID' => $loginUserID,
                                    'onDate' => $v,   
                                    'shiftID' => $getPreShiftID ,
                                    'availabilityStatus' => 0 
                                    );  
                                }
                          }
                          
                          if(count($getShifts) == 1){
                              
                                $getNextShiftID = $getShifts[0]->id; 

                                $Date1 = date("Y-m-d", strtotime($fromDate . " +1 day"));
                                $Date2 = date("Y-m-d", strtotime($toDate . " +1 day"));
                                $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                                foreach($daysCount as $k=>$v){
                                   $insertData[] = array(
                                    'userID' => $loginUserID,
                                    'onDate' => $v,   
                                    'shiftID' => $getNextShiftID ,
                                    'availabilityStatus' => 0 
                                    );  
                                } 
                          }else{
                          
                          

                                if(isset($getShifts[$flag+1])){ 
                                   $getNextShiftID = $getShifts[$flag+1]->id; 

                                   $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                                   foreach($daysCount as $k=>$v){
                                      $insertData[] = array(
                                       'userID' => $loginUserID,
                                       'onDate' => $v,   
                                       'shiftID' => $getNextShiftID ,
                                       'availabilityStatus' => 0 
                                       );  
                                   }
                                }
                          }
                          break;

                      }else if($flag == count($getShifts)-1){

                          $getNextShiftID = $getShifts[0]->id; 

                          $Date1 = date("Y-m-d", strtotime($fromDate . " +1 day"));
                          $Date2 = date("Y-m-d", strtotime($toDate . " +1 day"));
                          $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                          foreach($daysCount as $k=>$v){
                             $insertData[] = array(
                              'userID' => $loginUserID,
                              'onDate' => $v,   
                              'shiftID' => $getNextShiftID ,
                              'availabilityStatus' => 0 
                              );  
                          }
                          
                          if(isset($getShifts[$flag-1])){

                            $getPreShiftID = $getShifts[$flag-1]->id; 

                            $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                            foreach($daysCount as $k=>$v){
                               $insertData[] = array(
                                'userID' => $loginUserID,
                                'onDate' => $v,   
                                'shiftID' => $getPreShiftID ,
                                'availabilityStatus' => 0 
                                );  
                            }
                          }
                          break;


                      }else{
                          $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                          foreach($daysCount as $k=>$v){
                              
                            if(isset($getShifts[$flag+1]) && isset($getShifts[$flag-1])){  
                              
                              $getLastShiftID = $getShifts[$flag-1]->id; 
                              $getnextShiftID = $getShifts[$flag+1]->id; 
                                  $insertData[] = array(
                                  'userID' => $loginUserID,
                                  'onDate' => $v,   
                                  'shiftID' => $getLastShiftID ,
                                  'availabilityStatus' => 0 
                                  ); 
                                  $insertData[] = array(
                                  'userID' => $loginUserID,
                                  'onDate' => $v,   
                                  'shiftID' => $getnextShiftID ,
                                  'availabilityStatus' => 0 
                                  ); 
                            }
                          }
                      break;
                      }

                  }   
                  $flag++;
              } 

          }
          
          if(count($insertData) > 0){
            if(DB::table('staffing_usercalendarsettings')
            ->where('userID','=',$loginUserID)
            ->where('shiftID','=',$shiftID)
            ->whereIn('onDate', $daysCount)->delete()){ }
            $success = DB::table('staffing_usercalendarsettings')->insert($insertData);
          }
          
          return true;

    }
             
        
        public function confirmOffer(Request $request){//Confirm Offer By End-User
            
            if($request->userID){

                $id = $request->userID;
                $user = User::find($id);
                $userID = $user->id;
                
                   if ($user) {
                       
                       $requestPostID = $request->requestID;
                       if($requestPostID > 0){
                           
                           $post = Requestcall::find($requestPostID);
                           
                        if($post){
                            
                          if($user->role == '0' || $user->role == '4'){ 
                             
           
                        $checkAlready = DB::table('staffing_shiftoffer')
                            ->select(
                        'id'
                            )->where([['requestID','=',$requestPostID],
                            ['userID','=',$userID]])->first();
                            
                            if(count($checkAlready) > 0){
                                
                                $confirmationInfo = DB::table('staffing_shiftconfirmation')
                                ->select(
                                'id'
                                )->where([['shiftOfferID','=',$checkAlready->id]])->first();
                                
                                
                             if($confirmationInfo){   
                                
                                $shiftOfferConfirmation = OfferConfirmation::find($confirmationInfo->id);
                                
                                $shiftOfferConfirmation->offerResponse = 0;
                                
                                $success = false;
                                
                                if($request->offerResponse == '1'){
                                    $shiftOfferConfirmation->offerResponse = 1;
                                    $message = "Offer accepted. You are expected to come on Scheduled time.";
                                    $success = true;
                                }else if($request->offerResponse == '2'){
                                    $shiftOfferConfirmation->offerResponse = 2;
                                    $message = "Offer declined.";
                                    $success = true;
                                }
                                
                                if($success){
                                
                                    if($shiftOfferConfirmation->save()){
                                        if($request->offerResponse == '1'){
                          /* Set Adjecent Shift As Unavailable For User As this shift is Sheduled for him. */
                    /* Only if Request has Pre-Shift Not Custom Shift */     
                                if($post->staffingShiftID > 0 && $post->shiftType == 0) {        
                                    $this->setUserAsUnAvailableOnAdjecentShifts($post->staffingStartDate, 
                                        $post->staffingStartDate, $post->businessUnitID, 
                                            $post->staffingShiftID,$userID);
                                }
                        /* Set Adjecent Shift As Unavailable For User As this shift is Sheduled for him. */
                         
                                        }
                                        
                                        
                                       /* Send Push Notifications */
                                        $iosDevicesArray = array();
                                       $androidDevicesArray = array();
                                       /* Send Push To ADmin */     
                                        
                                      $postOwnerAdminInfo = User::find($post->ownerID);
                                        if($postOwnerAdminInfo->pushNotification == '1'){ 
                                            $getAdminDevices = DB::table('staffing_devices')
                                            ->select('deviceID','deviceType')
                                            ->where([['userID','=',$post->ownerID]])->get();
                                    
                                            if(count($getAdminDevices) > 0){
                                                foreach($getAdminDevices as $adminDevice){
                                                  $deviceToken = $adminDevice->deviceID?$adminDevice->deviceID:''; 
                                                    if($adminDevice->deviceType == '1'){
                                                        if($deviceToken)
                                                          $iosDevicesArray[] = $deviceToken;
                                                    }elseif($adminDevice->deviceType == '2'){
                                                        if($deviceToken)
                                                           $androidDevicesArray[] = $deviceToken;
                                                    } 
                                                } 


                                                if($request->offerResponse == '1'){
                                                    $pushmessage = $user->firstName." ".$user->lastName." has accepted offer.";
                                                }else if($request->offerResponse == '2'){
                                                    $pushmessage = $user->firstName." ".$user->lastName." has declined offer.";
                                                }

                                                
                                                           
                                    /* Get Push Message */
                                     $pushMessage2 = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */     
                                                

                                                $msg_payload = array (
                                                'mtitle' => $pushmessage,
                                                'mdesc' => $pushMessage2,
                                                'notificationStatus' => 6,
                                                'requestID' => $post->id
                                                );

                                                $msg_payloadAndroid = array (
                                                'mtitle' => $pushmessage,
                                                'mdesc' => $pushMessage2,
                                                'notificationStatus' => 6,
                                                'requestID' => $post->id
                                                );   


                                                if($androidDevicesArray){
                                                    if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                                  $androidPusStatus = true;
                                                }

                                                if($iosDevicesArray){
                                                    if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                                        $iosPusStatus = true;
                                                }
                                            
                                            }
                                        } 
                                        /* Send Push To ADmin */
                                if($request->offerResponse == '1'){        
                                        /* Check If Required Number Of Staff Fullfilled */
                                        /* Check if any user accepted/confirmed offer and request is scheduled? */
                                            $offerConfirmedUser = array();
                                            $isOfferAccepted =  0;
                                            $requestShiftOffers = DB::table('staffing_shiftoffer')
                                                ->select('id','userID')->where([['requestID','=',$post->id]])->get();
                                            if(count($requestShiftOffers) > 0){
                                                foreach($requestShiftOffers as $requestShiftOffer){
                                                    
                                                   $offerConfirmCheckSql = DB::table('staffing_shiftconfirmation')
                                                      ->select('id');
                                                    $offerConfirmCheckSql->where('shiftOfferID','=',$requestShiftOffer->id);
                                                    $offerConfirmCheckSql->whereIn('offerResponse', [1,2]);
                                                    $offerConfirmCheck = $offerConfirmCheckSql->first(); 

                                                   if(count($offerConfirmCheck) > 0){
                                                      $offerConfirmedUser[] =  $requestShiftOffer->userID;
                                                      $isOfferAccepted =  1;
                                                   } 
                                                    

                                                }
                                            }
                        
                                        /* Check if any user accepted/confirmed offer and request is scheduled? */
                                        if(count($offerConfirmedUser) >= $post->numberOfOffers) { 
                                           $usersWhoNotConfirmedOffersSql = DB::table('staffing_shiftoffer')
                                                ->select('staffing_shiftoffer.id','staffing_shiftoffer.userID');
                                           $usersWhoNotConfirmedOffersSql->where('staffing_shiftoffer.requestID','=',$post->id);
                                                 
                                        $usersWhoNotConfirmedOffersSql->whereNotIn('staffing_shiftoffer.userID', $offerConfirmedUser);
                                            $usersWhoNotConfirmedOffers = $usersWhoNotConfirmedOffersSql->get(); 
                                           
                                           $notConfirmedUsers = array();
                                            if($usersWhoNotConfirmedOffers){
                                               
                                               foreach($usersWhoNotConfirmedOffers as $usersWhoNotConfirmedOffer){
                                                   $notConfirmedUsers[] = $usersWhoNotConfirmedOffer->userID;
                                               }
                                               
                                                $iosDevicesArray = array();
                                                $androidDevicesArray = array();
                                                if(count($notConfirmedUsers) > 0){    
                                                    $getUserDevices = DB::table('staffing_devices')
                                                   ->select('deviceID','deviceType')
                                                   ->whereIn('userID', $notConfirmedUsers)->get();
                                    
                                                    if(count($getUserDevices) > 0){
                                                        foreach($getUserDevices as $getUserDevice){
                                                          $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
                                                            if($getUserDevice->deviceType == '1'){
                                                                if($deviceToken)
                                                                  $iosDevicesArray[] = $deviceToken;
                                                            }elseif($getUserDevice->deviceType == '2'){
                                                                if($deviceToken)
                                                                   $androidDevicesArray[] = $deviceToken;
                                                            } 
                                                        } 


                                                                         
                                    /* Get Push Message */
                                     $pushMessage2 = $this->getRequestInformationForPush($post);
                                     /* Get Push Message */     
                                                   
                                                        
                                                        
                                                               $pushmessage = "Would you like to remain AVAILABLE as per your response?";
                                                                $msg_payload = array (
                                                                'mtitle' => $pushmessage,
                                                                'mdesc' => $pushMessage2,
                                                                'notificationStatus' => 3,
                                                                'requestID' => $post->id
                                                                );

                                                                $msg_payloadAndroid = array (
                                                                'mtitle' => $pushmessage,
                                                                'mdesc' => $pushMessage2,
                                                                'notificationStatus' => 3,
                                                                'requestID' => $post->id
                                                                );   


                                                                if($androidDevicesArray){
                                                                    if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
                                                                  $androidPusStatus = true;
                                                                }

                                                                if($iosDevicesArray){
                                                                    if(myHelper::iOS($msg_payload,$iosDevicesArray))
                                                                        $iosPusStatus = true;
                                                                }

                                                    }

                                                }
                                            }
                                        }
                                        
                                        /* Check If Required Number Of Staff Fullfilled */
                                }       
                                        
                                        
                                        
                                        /* Send Push To User */ 
//                                        $iosDevicesArray = array();
//                                       $androidDevicesArray = array();    
//                                        $getUserDevices = DB::table('staffing_devices')
//                                            ->select('deviceID','deviceType')
//                                            ->where([['userID','=',$user->id]])->get();
//                                    
//                                        if(count($getUserDevices) > 0){
//                                            foreach($getUserDevices as $getUserDevice){
//                                              $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
//                                                if($getUserDevice->deviceType == '1'){
//                                                    if($deviceToken)
//                                                      $iosDevicesArray[] = $deviceToken;
//                                                }elseif($getUserDevice->deviceType == '2'){
//                                                    if($deviceToken)
//                                                       $androidDevicesArray[] = $deviceToken;
//                                                } 
//                                            } 
//                                            
//                                            
//                                            if($request->offerResponse == '1'){
//                                               $pushmessage = "Offer accepted. You are expected to come on Scheduled time.";
//                                                $msg_payload = array (
//                                                'mtitle' => 'Staffing Call',
//                                                'mdesc' => $pushmessage,
//                                                'notificationStatus' => 0,
//                                                'requestID' => $post->id
//                                                );
//
//                                                $msg_payloadAndroid = array (
//                                                'mtitle' => 'Staffing Call',
//                                                'mdesc' => $pushmessage,
//                                                'notificationStatus' => 0,
//                                                'requestID' => $post->id
//                                                );   
//
//
//                                                //if($androidDevicesArray){
//                                                    //if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
//                                                  //$androidPusStatus = true;
//                                                //}
//
//                                                if($iosDevicesArray){
//                                                    if(myHelper::iOS($msg_payload,$iosDevicesArray))
//                                                        $iosPusStatus = true;
//                                                }
//                                            }
//                                            
//                                        }
                                          /* Send Push To User */  
                                        
                                        
                                           /* Send Push Notifications */  
                                        
                                        return response()->json([
                                            'status'=>'1',
                                            'message'=>$message    
                                        ]);
                                    }else{
                                     return response()->json([
                                            'status'=>'0',
                                            'message'=>'Failed to send response.'    
                                        ]);     
                                    }
                                    
                                }else{
                                   return response()->json(['status'=>'0',
                                'message'=>'Please response either accept or decline offer.'], 500); 
                                }
                             }else{
                               return response()->json(['status'=>'0',
                                'message'=>'You have not any offer to accept/decline this shift.'], 500);  
                             }

                            }else{
                              return response()->json(['status'=>'0',
                                'message'=>'You do not have permission to Confirm/accept this offer.'], 500);   
                            }
                            
                            
                          
                          }else{
                            return response()->json(['status'=>'0',
                                'message'=>'You are not allowed to send response.'], 500);   
                          }
                          
                        } else{
                          return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);       
                        }  
                    
                        
                       }else{
                         return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);     
                       }
                    
                   }else {
                     return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                   }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        } 
        
        
        
        public function staffProfileList(Request $request){
            if($request->userID){

                $id = $request->userID;
                $user = User::find($id);
                $userID = $user->id;

               if ($user && $user->role != 0) {
                   
                 if($user->role != 2)  {
                     
                     
                    $unitInfoSql = DB::table('staffing_businessunits')
                        ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                        ->select(
                                'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        ); 
                   
                    if($user->role == 0){
                            $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                            $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                    }
                    elseif($user->role != 2) {
                        $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                    }
                        
                   $unitInfo = $unitInfoSql->first();
                   
                   if($request->businessUnitID > 0)
                       $unitInfo->id = $request->businessUnitID;
                   
                    
                    $staffProfilesSql = DB::table('staffing_usersunits')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                    ->select(
                    'staffing_users.id',
                    DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                    'staffing_users.email',
                    'staffing_users.phone',
                    'staffing_users.skills',
                    'staffing_users.role',
                    'staffing_users.businessGroupID',
                    'staffing_users.profilePic');
                    
                    $staffProfilesSql->where('staffing_usersunits.businessUnitID','=',$unitInfo->id);
                    
                    $staffProfilesSql->where('staffing_users.businessGroupID','=',$user->businessGroupID);
                    $staffProfilesSql->where('staffing_users.id','!=',$userID);
                    
                    if($user->role == 2){//Group Manager
                       $staffProfilesSql->whereIn('staffing_users.role',[3,4,0]);
                    }
                    
                    if($user->role == 3){//Super Admin
                       $staffProfilesSql->whereIn('staffing_users.role',[4,0]);
                    }
                    
                    if($user->role == 4){//Admin
                       $staffProfilesSql->whereIn('staffing_users.role',[0]);
                    }
                    
               }else{
                   
                   
                   if($request->businessUnitID > 0){
                      $staffProfilesSql = DB::table('staffing_usersunits')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                    ->select(
                    'staffing_users.id',
                    DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                    'staffing_users.email',
                    'staffing_users.phone',
                    'staffing_users.skills',
                    'staffing_users.role',
                    'staffing_users.businessGroupID',
                    'staffing_users.profilePic');
                    
                    $staffProfilesSql->where('staffing_usersunits.businessUnitID','=',$request->businessUnitID);
                    
                    $staffProfilesSql->where('staffing_users.businessGroupID','=',$user->businessGroupID);
                      
                    $staffProfilesSql->whereNotIn('staffing_users.role',[1,2]);
                      
                   }else{   
               
                        $staffProfilesSql = DB::table('staffing_users')
                         ->select(
                         'id',
                         DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                         'email',
                         'phone',
                         'skills',
                         'role',
                         'businessGroupID',
                         'profilePic')->where('staffing_users.businessGroupID','=',$user->businessGroupID)
                                  ->whereNotIn('staffing_users.role',[1,2]);
                   }
               }
                    
                        $staffProfiles = $staffProfilesSql->get();
                        $responseJson['data'] = array();
                        $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Success.';
                        
                        foreach($staffProfiles as $staffProfile){

                        $staffProfile->role = (string)$staffProfile->role;
                        $staffProfile->userID = (string)$staffProfile->id;
                        $staffProfile->phone = (string)($staffProfile->phone?$staffProfile->phone:'');
                        $staffProfile->profilePic = $staffProfile->profilePic?url('public/'.$staffProfile->profilePic):url('/assets/images/profile.jpeg');
                        $staffProfile->position = ($staffProfile->role == 2)?'Group Manager':(($staffProfile->role == 3)?'Super Admin':(($staffProfile->role == 4)?'Admin':'End-User'));
                       
                        
                        /* Get User Skills */
                        $skillsArr = array();
                        $skillsArr = $staffProfile->skills?unserialize($staffProfile->skills):array();

                        $userSkills = array();
                        if($skillsArr){
                           $usersCategory = DB::table('staffing_skillcategory')
                        ->select(
                                'id',
                                'skillName'
                                )->whereIn(
                                        'id',$skillsArr)->orderBy('skillName','ASC')->get();

                           foreach($usersCategory as $userSkill){
                               $userSkills[] = $userSkill->skillName;
                           }

                        }
                        /* Get User Skills */
                        
                        
                    
                    if($staffProfile->role == 3 || $staffProfile->role == 4 || $staffProfile->role == 0){    
                        
                        /* Get User's Business Unit */
                        $userBusinessUnits = array();
                        
                        $usersUnits = DB::table('staffing_usersunits')
                         ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                         ->select(
                             'staffing_usersunits.businessUnitID',
                             'staffing_businessunits.unitName'
                             )->where([
                                     ['staffing_usersunits.userID', '=',$staffProfile->id]])->orderBy('staffing_businessunits.unitName','ASC')->get();

                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        }
                        /* Get User's Business Unit */
                       
                    }elseif($staffProfile->role == 2){
                       $usersUnits = DB::table('staffing_businessunits')
                              ->select(
                             'id',
                             'unitName'
                             )->where([
                                     ['businessGroupID', '=',$staffProfile->businessGroupID]])->orderBy('unitName','ASC')->get();

                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        } 
                    }else{
                       $userBusinessUnits = '' ;
                    } 
                    
                    
                        if($staffProfile->role == 2){
                            $staffProfile->skills = 'Group Manager';
                        }else{
                        $staffProfile->skills = $userSkills?implode(', ',$userSkills):'';
                        }
                        
                        $staffProfile->categoryName = $userBusinessUnits?implode(', ',$userBusinessUnits):'';
                        $staffProfile->businessGroupID = (string)$staffProfile->businessGroupID;
                        unset($staffProfile->id);
                        $responseJson['data'][] = $staffProfile;
                 }
                 
                        return response()->json($responseJson);
                   
               }else{
                  return response()->json(['status'=>'0','message'=>'You are not authorized to view this page.'], 500);  
               }
            }else{
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
               }
        }
        
        
        public function staffProfileDetail(Request $request){
            if($request->userID && $request->staffID){

                $id = $request->userID;
                $user = User::find($id);
                $userID = $user->id;

               if ($user && $user->role != 0) {
                    $profileid = $request->staffID;
                    $profile = DB::table('staffing_users')
                     ->select(
                    'id',
                    DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                    'email',
                    'phone',
                    'skills',
                    'role',
                    'businessGroupID',
                    'profilePic')->where([['id','=',$profileid]])->first(); 
                    
                    
                    $profileID = $profile->id; 
                    if($profile){
                      
                      $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Success.';

                        $profile->role = (string)$profile->role;
                        $profile->userID = (string)$profile->id;
                        $profile->phone = (string)($profile->phone?$profile->phone:'');
                        $profile->profilePic = $profile->profilePic?url('public/'.$profile->profilePic):url('/assets/images/profile.jpeg');
                        $profile->position = ($profile->role == 2)?'Group Manager':(($profile->role == 3)?'Super Admin':(($profile->role == 4)?'Admin':'End-User'));
                       
                        
                        /* Get User Skills */
                        $skillsArr = array();
                        $skillsArr = $profile->skills?unserialize($profile->skills):array();

                        $userSkills = array();
                        if($skillsArr){
                           $usersCategory = DB::table('staffing_skillcategory')
                        ->select(
                                'id',
                                'skillName'
                                )->whereIn(
                                        'id',$skillsArr)->orderBy('skillName','ASC')->get();

                           foreach($usersCategory as $userSkill){
                               $userSkills[] = $userSkill->skillName;
                           }

                        }
                        /* Get User Skills */
                        
                        
                    
                    if($profile->role == 3 || $profile->role == 4 || $profile->role == 0){    
                        
                        /* Get User's Business Unit */
                        
                        $usersUnits = DB::table('staffing_usersunits')
                         ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                         ->select(
                             'staffing_usersunits.businessUnitID',
                             'staffing_businessunits.unitName'
                             )->where([
                                     ['staffing_usersunits.userID', '=',$profileID]])->orderBy('staffing_businessunits.unitName','ASC')->get();

                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        }
                        /* Get User's Business Unit */
                       
                    }elseif($userInfo->role == 2){
                       $usersUnits = DB::table('staffing_businessunits')
                              ->select(
                             'id',
                             'unitName'
                             )->where([
                                     ['businessGroupID', '=',$profile->businessGroupID]])->orderBy('unitName','ASC')->get();

                        foreach($usersUnits as $usersUnit){
                            $userBusinessUnits[] = $usersUnit->unitName;
                        } 
                    }else{
                       $userBusinessUnits = '' ;
                    } 
                    
                    
                        if($profile->role == 2){
                            $profile->skills = 'Group Manager';
                        }else{
                        $profile->skills = $userSkills?implode(', ',$userSkills):'';
                        }
                        
                        $profile->categoryName = $userBusinessUnits?implode(', ',$userBusinessUnits):'';
                        $profile->businessGroupID = (string)$profile->businessGroupID;
                        unset($profile->id);
                        
                        
                        
                   if($profile->role == 3){
                     $getCreatedRequestsSql = DB::table('staffing_staffrequest')->select(
                            DB::raw(
                                    'YEAR(created_at) AS y,'
                                    . ' MONTH(created_at) AS m, '
                                    . 'COUNT(DISTINCT id) AS totalCount')
                           );
                     
                     $getCreatedRequestsSql->whereRaw('YEAR(created_at) = YEAR(CURDATE())');
                     $getCreatedRequestsSql->where('ownerID','=',$profileID);
                     $getCreatedRequestsSql->groupBy(['y','m']);
                     $getCreatedRequests = $getCreatedRequestsSql->get();
                     
                     $getApprovedRequestsSql = DB::table('staffing_staffrequest')->select(
                            DB::raw(
                                    'YEAR(created_at) AS y,'
                                    . ' MONTH(created_at) AS m, '
                                    . 'COUNT(DISTINCT id) AS totalCount')
                           );
                     
                     $getApprovedRequestsSql->whereRaw('YEAR(created_at) = YEAR(CURDATE())');
                     $getApprovedRequestsSql->where('approvedBy','=',$profileID);
                     $getApprovedRequestsSql->groupBy(['y','m']);
                     $getApprovedRequests = $getApprovedRequestsSql->get();
                     
                     $searchableUsrArr = array();
                    $getCreatedRequestsCount = 0;
                    foreach($getCreatedRequests as $getCreatedRequest){
                       $searchableUsrArr[$getCreatedRequest->m] = $getCreatedRequest->totalCount;
                       $getCreatedRequestsCount += $getCreatedRequest->totalCount;
                    }


                    $createdCalls = array();
                    for($u = 1;$u<=12;$u++){
                            if(array_key_exists($u, $searchableUsrArr)){
                                $graphData[] = (string)$searchableUsrArr[$u]; 
                            }else{
                                $graphData[] = (string)0; 
                            }
                    }
                
                
                       /*Get Users monthwise of current year Shift cancellation*/
                        
                        $profile->lastMinuteShiftCancellation = array(
                            'count' => (string)$getCreatedRequestsCount,
                            'graphData' =>$graphData
                        );
                     
                     
                     $searchableUsrArr2 = array();
                    $getApprovedRequestsCount = 0;
                    foreach($getApprovedRequests as $getApprovedRequest){
                       $searchableUsrArr2[$getApprovedRequest->m] = $getApprovedRequest->totalCount;
                       $getApprovedRequestsCount += $getApprovedRequest->totalCount;
                    }    
                        
                        
                        for($m=1;$m<=12;$m++){
                        
                            if(array_key_exists($m, $searchableUsrArr2)){
                                $graphData2[] = (string)$searchableUsrArr2[$m]; 
                            }else{
                                $graphData2[] = (string)0; 
                            }
                        }
                        
                        
                        $profile->averageTimeShiftCancellation = array(
                            'count' => (string)$getApprovedRequestsCount,
                            'graphData' =>$graphData2
                        );
                     
                     
                     
                   }else{  
                    /*Get Users monthwise of current year Shift cancellation*/
                    $lastTimeShiftCancellationsSql = DB::table('staffing_staffrequest')->select(
                            DB::raw(
                                    'YEAR(staffingStartDate) AS y,'
                                    . ' MONTH(staffingStartDate) AS m, '
                                    . 'COUNT(DISTINCT id) AS totalCount')
                           );

                    $lastTimeShiftCancellationsSql->whereRaw('YEAR(staffingStartDate) = YEAR(CURDATE())');
                    $lastTimeShiftCancellationsSql->where('lastMinuteStaffID','=',$profileID);
                    $lastTimeShiftCancellationsSql->groupBy(['y','m']);
                    $lastTimeShiftCancellations = $lastTimeShiftCancellationsSql->get();

                    $searchableUsrArr = array();
                    $lastTimeShiftCancellationCount = 0;
                    foreach($lastTimeShiftCancellations as $lastTimeShiftCancellation){
                       $searchableUsrArr[$lastTimeShiftCancellation->m] = $lastTimeShiftCancellation->totalCount;
                       $lastTimeShiftCancellationCount += $lastTimeShiftCancellation->totalCount;
                    }


                    $lastMinuteShiftCancellations = array();
                    for($u = 1;$u<=12;$u++){
                            if(array_key_exists($u, $searchableUsrArr)){
                                $graphData[] = (string)$searchableUsrArr[$u]; 
                            }else{
                                $graphData[] = (string)0; 
                            }
                    }
                
                
                       /*Get Users monthwise of current year Shift cancellation*/
                        
                        $profile->lastMinuteShiftCancellation = array(
                            'count' => (string)$lastTimeShiftCancellationCount,
                            'graphData' =>$graphData
                        );
                        
                        
                        
                        /* Get Average Time Shift Cancellation */
                        $avgTimeShiftCancellationsSql = DB::table('staffing_staffrequest')
                                ->select('staffing_staffrequest.staffingStartDate',
                                        'staffing_staffrequest.staffingEndDate',
                                        'staffing_staffrequest.timeOfCallMade',
                                        'staffing_staffrequest.lastMinuteStaffID',
                                        'staffing_shiftsetup.startTime',
                                        'staffing_shiftsetup.endTime',
                                        'staffing_staffrequest.shiftType',
                                        'staffing_staffrequest.customShiftStartTime',
                                        'staffing_staffrequest.customShiftEndTime',
                                    DB::raw(
                                    'YEAR(staffing_staffrequest.staffingStartDate) AS y,'
                                    . ' MONTH(staffing_staffrequest.staffingStartDate) AS m ')
                           );
                            $avgTimeShiftCancellationsSql->leftJoin(
                                'staffing_shiftsetup', 
                                'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID'
                                );
                        $avgTimeShiftCancellationsSql->whereRaw('YEAR(staffing_staffrequest.staffingStartDate) = YEAR(CURDATE())');
                        $avgTimeShiftCancellationsSql->where('staffing_staffrequest.lastMinuteStaffID','=',$profileID);
                        $avgTimeShiftCancellations = $avgTimeShiftCancellationsSql->get();
                        
                        $yearVarCount = 0;
                        $yearAvgTimeCount = 0;//In minutes
                        $yearCancellationTime = 0;
                        for($m=1;$m<=12;$m++){
                            if($avgTimeShiftCancellations){
                                $monthVarCount = 0;
                                $monthAvgTimeCount = 0;//In minutes
                                $cancellationTime = 0;
                                foreach($avgTimeShiftCancellations as $avgTimeShiftCancellation){
                                    if($avgTimeShiftCancellation->m == $m){
                                        $monthVarCount ++;
                                        if($avgTimeShiftCancellation->shiftType == 0){
                                            $callStartTime =  date("Y-m-d H:i:s",
                                            strtotime($avgTimeShiftCancellation->staffingStartDate." ".$avgTimeShiftCancellation->startTime));
                                            
                                            $callEndTime =  date("Y-m-d H:i:s",
                                            strtotime($avgTimeShiftCancellation->staffingEndDate." ".$avgTimeShiftCancellation->endTime));
                                        }
                                        else{
                                            $callStartTime =  date("Y-m-d H:i:s",
                                            strtotime($avgTimeShiftCancellation->staffingStartDate." ".$avgTimeShiftCancellation->customShiftStartTime));
                                            
                                            $callEndTime =  date("Y-m-d H:i:s",
                                            strtotime($avgTimeShiftCancellation->staffingEndDate." ".$avgTimeShiftCancellation->customShiftEndTime));
                                        }
                                        
                                            //$callMadeTimeOfStaff = $avgTimeShiftCancellation->timeOfCallMade;
                                            if($callEndTime > $callStartTime){
                                               $dteStart = new \DateTime($callStartTime); 
                                               $dteEnd   = new \DateTime($callEndTime); 
                                               $dteDiff  = $dteStart->diff($dteEnd); 
                                               $diff = $dteDiff->format("%H:%I"); 
                                               $time_array = explode(':', $diff);
                                               $hours = (int)$time_array[0];
                                               $minutes = (int)$time_array[1];
                                               $minDiff = ($hours * 60) + ($minutes); 
                                               $cancellationTime += $minDiff;  
                                            }else{
                                               $cancellationTime += 0;  
                                            }
                                        
                                    }else{
                                     $cancellationTime += 0;    
                                    }
                                }
                                
                                if($monthVarCount > 0)
                                $monthAvgTimeCount = $cancellationTime / $monthVarCount;
                                
                                $graphData2[] = (string)round($monthAvgTimeCount, 0);
                                
                                $yearCancellationTime +=  $cancellationTime;
                                $yearVarCount += $monthVarCount;
                                
                            }else{
                              $graphData2[] = (string)0;  
                            }
                        
                            
                        }
                        
                        
                        if($yearVarCount > 0)
                           $yearAvgTimeCount = $yearCancellationTime / $yearVarCount;
                        /* Get Average Time Shift Cancellation */
                        
                        $countVarMsg = round($yearAvgTimeCount, 0).' min (+/- 30 min)';
                        if(round($yearAvgTimeCount, 0) > 60)
                           $countVarMsg = round($yearAvgTimeCount/60, 0).' Hr (+/- 30 min)';
                        $profile->averageTimeShiftCancellation = array(
                            'count' => (string)$countVarMsg,
                            'graphData' =>$graphData2
                        );
                   }   
                        
                    /* User's Shift willing to take */  
                     $shiftWillingToTake = ShiftOffer::where([
                         ['userID','=',$profileID],
                         ['responseType','!=',2]
                         ])->count();
                    /* User's Shift willing to take */ 
                     
                     
                     /* User's Shift Covered */  
                     $shiftCovered = ShiftOffer::select('staffing_shiftoffer.id')
                             ->join('staffing_shiftconfirmation', 
                                     'staffing_shiftconfirmation.shiftOfferID', 
                                     '=', 'staffing_shiftoffer.id')
                             ->where([
                         ['staffing_shiftoffer.userID','=',$profileID],
                         ['staffing_shiftoffer.responseType','!=',2],
                         ['staffing_shiftconfirmation.offerResponse','=',1]
                         ])->count();
                     /* User's Shift Covered */   
                        
                        
                        $profile->shiftWillingToTake = (string)$shiftWillingToTake;
                        $profile->shiftCovered = (string)$shiftCovered;
                        
                        $responseJson['data'] = $profile;
                        
                        
                        
                        return response()->json($responseJson);  
                        
                        
                    }else{
                  return response()->json(['status'=>'0','message'=>'Request profile not found.'], 500);  
                    }
               }else{
                  return response()->json(['status'=>'0','message'=>'You are not authorized to view this page.'], 500);  
               }
            }else{
                  return response()->json(['status'=>'0','message'=>'Request profile not found.'], 500);  
               }
        }
        
        
        
        public function calenderView(Request $request){
            
                $isUser = 0;
                $loginUserID = $request->userID;
                $user = User::find($loginUserID);
                $loginUserID = $userID = $user->id;
                
                $fromDate = date("Y-m-d",strtotime($request->startDate));
                $toDate = date("Y-m-d",strtotime($request->endDate));
                $userBusinessUnitID = $request->businessUnitID;
            if ($user){ 
              
               if($request->startDate && $request->endDate && $userBusinessUnitID){ 
                   
                     $responseJson['status'] = '1';//'YES';
                     $responseJson['message'] = 'Success.';
                     
                    if ($user->role == 0) {
                        
                       $shiftsArray = myHelper::getCalenderDataForAPI($fromDate,$toDate,$userBusinessUnitID,$loginUserID,$user->role, $isUser); 
                    }else{
                        if($user->role == 4 && $request->isUser == 1)
                            $isUser = 1;
                       $shiftsArray = myHelper::getCalenderDataForAPI($fromDate,$toDate,$userBusinessUnitID,$loginUserID,$user->role, $isUser);
                    }
                    
                     $responseJson['shifts'] = $shiftsArray[0]['shifts'];
                     $responseJson['data'] = $shiftsArray;
                     
                     
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                     
                     
                     return response()->json($responseJson);  
               }else{
                  return response()->json(['status'=>'0','message'=>'Request not found.'], 500);     
               }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
            }
            
        }
        
        
        
        public function userCalendarAvailability(Request $request){
           $loginUserID = $request->userID;
                $user = User::find($loginUserID);
                $loginUserID = $userID = $user->id;
                
                $fromDate = date("Y-m-d",strtotime($request->startDate));
                $toDate = date("Y-m-d",strtotime($request->endDate));
                $shiftID = $request->shiftID?$request->shiftID:0;
            if ($user){ 
              
               if($fromDate && $toDate){ 
                   
                   
                        $unitInfoSql = DB::table('staffing_businessunits')
                            ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                            ->select(
                            'staffing_businessunits.id',
                            'staffing_businessunits.unitName'
                        );
        
                        if($user->role == 0){
                            $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                            $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                        }
                        else {
                            $unitInfoSql->where('staffing_usersunits.userID','=',$user->id); 
                        }
                        
                        $unitInfo = $unitInfoSql->first(); 
                        
                         /* Get Business Unit All Shifts */
                            if($unitInfo){
                            $getShifts = DB::table('staffing_shiftsetup')
                            ->select('id','startTime','endTime','shiftType')
                            ->where([['businessUnitID','=',$unitInfo->id]])->orderBy('startTime', 'ASC')->get();
                            }
                        /* Get Business Unit All Shifts */
                   
                   
                   
                   if ($user->role == 0 || $user->role == 4) {
                        $availabilityStatus = $request->availabilityStatus;
                        if($availabilityStatus == '1'){
                            $availabilityStatus = '1';//Available
                        }else if($availabilityStatus == '2'){//Working Somewhere else
                            $availabilityStatus = '2';//Working Somewhere else
                        }else{
                            $availabilityStatus = '0';//Unavailable
                        }
                     
                    $success = false;   
                    if($shiftID == 0){
                        if($getShifts){
                            
                            if($availabilityStatus == '2'){
                                $fromDate = date("Y-m-d", strtotime($fromDate . " -1 day"));
                                $toDate = date("Y-m-d", strtotime($toDate . " +1 day"));
                            }
                            
                            $flag = 0;
                           foreach($getShifts as $getShift){
                               
                              if($fromDate == $toDate){
                                $daysCount[] = $fromDate;
                                $insertData[] = array(
                                   'userID' => $loginUserID,
                                   'onDate' => $fromDate,   
                                   'shiftID' => $getShift->id ,
                                   'availabilityStatus' => $availabilityStatus 
                                    ); 
                                }else{ 
                                    $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                                    foreach($daysCount as $k=>$v){
                                        if($availabilityStatus == '2' && $fromDate == $v && $flag == count($getShifts) - 1){
                                          $insertData[] = array(
                                           'userID' => $loginUserID,
                                           'onDate' => $v,   
                                           'shiftID' => $getShift->id ,
                                           'availabilityStatus' => 0 
                                          ); 
                                        } else if($availabilityStatus == '2' && $toDate == $v && $flag == 0){
                                           $insertData[] = array(
                                           'userID' => $loginUserID,
                                           'onDate' => $v,   
                                           'shiftID' => $getShift->id ,
                                           'availabilityStatus' => 0 
                                          );  
                                        }else if($availabilityStatus == '2' && $fromDate != $v && $toDate != $v){

                                          $insertData[] = array(
                                           'userID' => $loginUserID,
                                           'onDate' => $v,   
                                           'shiftID' => $getShift->id ,
                                           'availabilityStatus' => $availabilityStatus 
                                          ); 
                                        }else if($availabilityStatus != '2'){

                                          $insertData[] = array(
                                           'userID' => $loginUserID,
                                           'onDate' => $v,   
                                           'shiftID' => $getShift->id ,
                                           'availabilityStatus' => $availabilityStatus 
                                          ); 
                                        }
                                   }

                                }
                                
                                $flag++;
                           }
                        } 
                    }else{
                        
                        if($fromDate == $toDate){
                            $daysCount[] = $fromDate;
                            $insertData[] = array(
                               'userID' => $loginUserID,
                               'onDate' => $fromDate,   
                               'shiftID' => $shiftID ,
                               'availabilityStatus' => $availabilityStatus 
                           ); 
                        }else{ 
                           $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                           foreach($daysCount as $k=>$v){
                              $insertData[] = array(
                               'userID' => $loginUserID,
                               'onDate' => $v,   
                               'shiftID' => $shiftID ,
                               'availabilityStatus' => $availabilityStatus 
                              );  
                           }

                        } 
                        
                        if($availabilityStatus == '2'){
                          $preDate = date("Y-m-d", strtotime($fromDate . " -1 day"));
                          $nextDate = date("Y-m-d", strtotime($toDate . " +1 day")); 
                            if($getShifts){
                                $flag = 0;
                                foreach($getShifts as $getShift){
                                    if($shiftID == $getShift->id){
                                        if($flag == 0){
                                            //Previous shift unavailabel
                                            $getPreShiftID = $getShifts[count($getShifts)-1]->id; 
                                            
                                            $Date1 = date("Y-m-d", strtotime($fromDate . " -1 day"));
                                            $Date2 = date("Y-m-d", strtotime($toDate . " -1 day"));
                                            $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                                            foreach($daysCount as $k=>$v){
                                               $insertData[] = array(
                                                'userID' => $loginUserID,
                                                'onDate' => $v,   
                                                'shiftID' => $getPreShiftID ,
                                                'availabilityStatus' => 0 
                                                );  
                                            }
                                            
                                            if(count($getShifts) == 1){

                                                $getNextShiftID = $getShifts[0]->id; 

                                                $Date1 = date("Y-m-d", strtotime($fromDate . " +1 day"));
                                                $Date2 = date("Y-m-d", strtotime($toDate . " +1 day"));
                                                $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                                                foreach($daysCount as $k=>$v){
                                                   $insertData[] = array(
                                                    'userID' => $loginUserID,
                                                    'onDate' => $v,   
                                                    'shiftID' => $getNextShiftID ,
                                                    'availabilityStatus' => 0 
                                                    );  
                                                } 
                                            }else{



                                                $getNextShiftID = $getShifts[$flag+1]->id; 

                                                $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                                                foreach($daysCount as $k=>$v){
                                                   $insertData[] = array(
                                                    'userID' => $loginUserID,
                                                    'onDate' => $v,   
                                                    'shiftID' => $getNextShiftID ,
                                                    'availabilityStatus' => 0 
                                                    );  
                                                }
                                            }
                                            break;
                                             
                                        }else if($flag == count($getShifts)-1){
                                            
                                            $getNextShiftID = $getShifts[0]->id; 
                                            
                                            $Date1 = date("Y-m-d", strtotime($fromDate . " +1 day"));
                                            $Date2 = date("Y-m-d", strtotime($toDate . " +1 day"));
                                            $daysCount = myHelper::getAllDatesOfAPI($Date1,$Date2); 
                                            foreach($daysCount as $k=>$v){
                                               $insertData[] = array(
                                                'userID' => $loginUserID,
                                                'onDate' => $v,   
                                                'shiftID' => $getNextShiftID ,
                                                'availabilityStatus' => 0 
                                                );  
                                            }
                                            
                                            $getPreShiftID = $getShifts[$flag-1]->id; 
                                            
                                            $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                                            foreach($daysCount as $k=>$v){
                                               $insertData[] = array(
                                                'userID' => $loginUserID,
                                                'onDate' => $v,   
                                                'shiftID' => $getPreShiftID ,
                                                'availabilityStatus' => 0 
                                                );  
                                            }
                                            break;
                                            
                                             
                                        }else{
                                            $daysCount = myHelper::getAllDatesOfAPI($fromDate,$toDate); 
                                            foreach($daysCount as $k=>$v){
                                                $getLastShiftID = $getShifts[$flag-1]->id; 
                                                $getnextShiftID = $getShifts[$flag+1]->id; 
                                                    $insertData[] = array(
                                                    'userID' => $loginUserID,
                                                    'onDate' => $v,   
                                                    'shiftID' => $getLastShiftID ,
                                                    'availabilityStatus' => 0 
                                                    ); 
                                                    $insertData[] = array(
                                                    'userID' => $loginUserID,
                                                    'onDate' => $v,   
                                                    'shiftID' => $getnextShiftID ,
                                                    'availabilityStatus' => 0 
                                                    ); 
                                            }
                                        break;
                                        }
                                        
                                    }   
                                    $flag++;
                                } 
                                     
                            }
                            
                        }
                        
                    }
                    
                    
                        if($shiftID == 0){
                             if(DB::table('staffing_usercalendarsettings')
                                 ->where('userID','=',$loginUserID)
                                 ->whereIn('onDate', $daysCount)->delete()){ }   
                        }else{

                         if(DB::table('staffing_usercalendarsettings')
                                 ->where('userID','=',$loginUserID)
                                 ->where('shiftID','=',$shiftID)
                                 ->whereIn('onDate', $daysCount)->delete()){ }
                        }
                    
                      
                      $success = DB::table('staffing_usercalendarsettings')->insert($insertData);
                     if($success){  
                        $responseJson['status'] = '1';//'YES';
                        $responseJson['message'] = 'Your availability has been saved.';
                     }else{
                         $responseJson['status'] = '0';//'YES';
                        $responseJson['message'] = 'No.';
                     }
                    
                     return response()->json($responseJson); 
                   }else{
                    return response()->json(['status'=>'0','message'=>'You are not permitted to do this configuration.'], 500);    
                   }
               }else{
                  return response()->json(['status'=>'0','message'=>'Request not found.'], 500);     
               }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
            } 
        }
        
        
        
        
        
        public function usersShiftHistory(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                if ($user) {
                    
                    
                   $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',$user->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   $jsonResponse['data'] = array();
                   $jsonResponse['status'] = '1';
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                        if(count($postIDs) > 0){
                       
                            $unitInfo = DB::table('staffing_businessunits')
                             ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                             ->select(
                                     'staffing_businessunits.id',
                                     'staffing_businessunits.unitName'
                                     )->where([['staffing_usersunits.userID','=',$user->id]])->first();

                                 $requestPostsSql = DB::table('staffing_staffrequest')
                                 ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                                 ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                                 ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                                 ->select(
                                     'staffing_staffrequest.id AS postID',
                                     'staffing_staffrequest.staffingStartDate',
                                     'staffing_staffrequest.staffingEndDate',
                                     'staffing_staffrequest.shiftType',
                                     'staffing_shiftsetup.startTime',
                                     'staffing_shiftsetup.endTime',
                                     'staffing_staffrequest.customShiftStartTime',
                                     'staffing_staffrequest.customShiftEndTime',
                                     'staffing_staffrequest.notes',
                                     'staffing_groups.groupName',
                                     'staffing_groups.groupCode',
                                     DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                                     );

                         //$requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                         if(count($postIDs) > 0){
                         $requestPostsSql->whereIn(
                            'staffing_staffrequest.id',$postIDs);
                         }
                         
                         
                         $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                         
                         $requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));

                         $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                         /* Active Requests */
                         $offers = $requestPostsSql->get();
                   
                        foreach($offers as $offer){
                         $startDateOfShift = $offer->staffingStartDate;
                    
                            if($offer->shiftType == 1):
                            $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                           else:
                            $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                           endif;
                 
                   
                            $jsonResponse['data'][] = [
                              'id' => (string)$offer->postID,
                              'shiftName' => (string)$shiftTimes,
                              'groupName' => $offer->groupName ,
                              'groupCode' => $offer->groupCode ,
                              'businessUnitName' => $unitInfo->unitName ,
                              'shiftTime' => (string)$startDateOfShift,//strtotime($startDateOfShift), 
                              'notes' => $offer->notes?$offer->notes:''  
                            ];
                        }
                       }
                   }
                   
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $jsonResponse['countInfo'] = $homePageCount;
                   
                   
                   
                   return response()->json($jsonResponse); 
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        public function usersCancelledRequests(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id); 
                if ($user) {
                    
                    
                   $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',$user->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   $jsonResponse['data'] = array();
                   $jsonResponse['status'] = '1';
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                        if(count($postIDs) > 0){
                       
                            $unitInfo = DB::table('staffing_businessunits')
                             ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                             ->select(
                                     'staffing_businessunits.id',
                                     'staffing_businessunits.unitName'
                                     )->where([['staffing_usersunits.userID','=',$user->id]])->first();

                                 $requestPostsSql = DB::table('staffing_staffrequest')
                                 ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                                 ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_staffrequest.businessGroupID')
                                 ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                                 ->select(
                                     'staffing_staffrequest.id AS postID',
                                     'staffing_staffrequest.staffingStartDate',
                                     'staffing_staffrequest.staffingEndDate',
                                     'staffing_staffrequest.shiftType',
                                     'staffing_shiftsetup.startTime',
                                     'staffing_shiftsetup.endTime',
                                     'staffing_staffrequest.customShiftStartTime',
                                     'staffing_staffrequest.customShiftEndTime',
                                     'staffing_staffrequest.notes',
                                     'staffing_groups.groupName',
                                     'staffing_groups.groupCode',
                                     DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                                     );

                         //$requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                         if(count($postIDs) > 0){
                         $requestPostsSql->whereIn(
                            'staffing_staffrequest.id',$postIDs);
                         }
                         
                         
                         $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [2,4]); 
                         
                         //$requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));

                         $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                         /* Active Requests */
                         $offers = $requestPostsSql->get();
                   
                        foreach($offers as $offer){
                         $startDateOfShift = $offer->staffingStartDate;
                    
                            if($offer->shiftType == 1):
                            $shiftTimes = date("g:i A",strtotime($offer->customShiftStartTime))." - ".date("g:i A",strtotime($offer->customShiftEndTime));
                           else:
                            $shiftTimes = date("g:i A",strtotime($offer->startTime))." - ".date("g:i A",strtotime($offer->endTime));
                           endif;
                 
                   
                            $jsonResponse['data'][] = [
                              'id' => (string)$offer->postID,
                              'shiftName' => (string)$shiftTimes,
                              'groupName' => $offer->groupName ,
                              'groupCode' => $offer->groupCode ,
                              'businessUnitName' => $unitInfo->unitName ,
                              'shiftTime' => (string)$startDateOfShift,//strtotime($startDateOfShift), 
                              'notes' => $offer->notes?$offer->notes:''  
                            ];
                        }
                       }
                   }
                   
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $jsonResponse['countInfo'] = $homePageCount;
                   
                   
                   
                   return response()->json($jsonResponse); 
                
                }else {
                  return response()->json(['status'=>'0','message'=>'User not found.'], 500);    
                }
            }else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        public function staffingHistory(Request $request){
           if($request->userID){

                $id = $request->userID;

                $user = User::find($id);                  
                if ($user) {
                    
                    $requestsList = array();
                    
                if($user->role != '2' && $user->role != '1'){     
                    $unitInfoSql = DB::table('staffing_businessunits')
                     ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                     ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                        );
                    
                    
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',$id);
                    if($user->role == '0'){
                        $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1);
                    }
                    $unitInfo = $unitInfoSql->first();
                    
                }
        
                $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.postingStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        )->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                
                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                
                if($user->role == 2){
                    $unitInfo = DB::table('staffing_businessunits')
                     ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                        )->where([['staffing_businessunits.businessGroupID','=',$user->businessGroupID]])->first();
                }
                 
                if($request->businessUnitID > 0){
                   $unitInfo->id =  $request->businessUnitID;
                }
                
                if($user->role != '2' && $user->role != '1'){
                    $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $unitInfo->id);
                
                    if($user->role == '4')
                        $requestPostsSql->where('staffing_staffrequest.ownerID', '=', $id);
                    
                    
                }
                
                if($user->role == '2'){
                    $requestPostsSql->where('staffing_staffrequest.businessGroupID', '=', $user->businessGroupID);
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $request->businessUnitID);
                     }
                }
                
               $requestPosts = $requestPostsSql->get();
                    
                    
                    
                    
                    foreach($requestPosts as $requestPost){
                        
                        
                        /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPost->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */        
                        
                        
                        
                        
                        if($requestPost->shiftType == 1)
                        $shiftTime = $requestPost->customShiftStartTime;
                        else
                        $shiftTime = $requestPost->startTime;
                        
                        $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime))))));
                            
                        $shiftTimeReal = (date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".$shiftTime)));
                            
                       
                        $requiredTypeOfStaffSkills = '';
                            
                        if($requestPost->requiredStaffCategoryID != ''){
                           $requiredStaffCategoryIDs = explode(",", $requestPost->requiredStaffCategoryID);
                           $getSkills = DB::table('staffing_skillcategory')
                             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                           $skillsName = array();
                           foreach($getSkills as $getSkill){
                              $skillsName[] = $getSkill->skillName; 
                           }

                           if($skillsName)
                             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                        }  
                        
                        
                        $requestsList[] = array(
                         'id' => (string)$requestPost->postID,
                         'ownerName' => $requestPost->staffOwner,
                         'notes' => $requestPost->notes?$requestPost->notes:"", 
                         'shiftTime' => (string)$requestPost->staffingStartDate,//$shiftTimeStamp , 
                         'shiftTimeRealFormat' => (string)$shiftTimeReal ,
                         'typeOfStaff' => $requiredTypeOfStaffSkills  ,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount ,
                         'requestStatus' => (string)$requestPost->postingStatus 
                           
                       );
                    }
                    
                   $responseJson['status'] =  '1';
                   $responseJson['message'] =  "Success";
                   $responseJson['data'] =  $requestsList;
                 
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                    
                    
                  return response()->json($responseJson);  
                    
                }else{
                    return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
                }
                
            } else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        public function cancelledRequests(Request $request){
           if($request->userID){

                $id = $request->userID;

                $user = User::find($id);                  
                if ($user) {
                    
                    $requestsList = array();
                    
                if($user->role != '2' && $user->role != '1'){     
                    $unitInfoSql = DB::table('staffing_businessunits')
                     ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                     ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                        );
                    
                    
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',$id);
                    if($user->role == '0'){
                        $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1);
                    }
                    $unitInfo = $unitInfoSql->first();
                    
                }
        
                $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.notes',
                        'staffing_staffrequest.postingStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        )->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                
                //$requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [2,4]); 
                
                if($user->role == 2){
                    $unitInfo = DB::table('staffing_businessunits')
                     ->select(
                             'staffing_businessunits.id',
                             'staffing_businessunits.unitName'
                        )->where([['staffing_businessunits.businessGroupID','=',$user->businessGroupID]])->first();
                }
                 
                if($request->businessUnitID > 0){
                   $unitInfo->id =  $request->businessUnitID;
                }
                
                if($user->role != '2' && $user->role != '1'){
                    $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $unitInfo->id);
                
                    if($user->role == '4')
                        $requestPostsSql->where('staffing_staffrequest.ownerID', '=', $id);
                    
                    
                }
                
                if($user->role == '2'){
                    $requestPostsSql->where('staffing_staffrequest.businessGroupID', '=', $user->businessGroupID);
                    if($request->businessUnitID > 0){
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID', '=', $request->businessUnitID);
                     }
                }
                
               $requestPosts = $requestPostsSql->get();
                    
                    
                    
                    
                    foreach($requestPosts as $requestPost){
                        
                        
                        /* Get Total Responded People */  
                      $respondedPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestPost->postID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestPost->postID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */        
                        
                        
                        
                        
                        if($requestPost->shiftType == 1)
                        $shiftTime = $requestPost->customShiftStartTime;
                        else
                        $shiftTime = $requestPost->startTime;
                        
                        $shiftTimeStamp = strtotime(date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".(date("H:i:s", strtotime($shiftTime))))));
                            
                        $shiftTimeReal = (date("Y-m-d H:i:s",strtotime(date("Y-m-d",strtotime($requestPost->staffingStartDate))." ".$shiftTime)));
                            
                       
                        $requiredTypeOfStaffSkills = '';
                            
                        if($requestPost->requiredStaffCategoryID != ''){
                           $requiredStaffCategoryIDs = explode(",", $requestPost->requiredStaffCategoryID);
                           $getSkills = DB::table('staffing_skillcategory')
                             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
                           $skillsName = array();
                           foreach($getSkills as $getSkill){
                              $skillsName[] = $getSkill->skillName; 
                           }

                           if($skillsName)
                             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
                        }  
                        
                        
                        $requestsList[] = array(
                         'id' => (string)$requestPost->postID,
                         'ownerName' => $requestPost->staffOwner,
                         'notes' => $requestPost->notes?$requestPost->notes:"", 
                         'shiftTime' => (string)$requestPost->staffingStartDate,//$shiftTimeStamp , 
                         'shiftTimeRealFormat' => (string)$shiftTimeReal ,
                         'typeOfStaff' => $requiredTypeOfStaffSkills  ,
                         'respondedPeople' => (string)$respondedPeopleCount,
                         'peopleAcceptedFullShift'  => (string)$respondedFullShiftPeopleCount,
                         'peopleAcceptedPartialShift' => (string)$respondedPartialShiftPeopleCount ,
                         'requestStatus' => (string)$requestPost->postingStatus 
                           
                       );
                    }
                    
                   $responseJson['status'] =  '1';
                   $responseJson['message'] =  "Success";
                   $responseJson['data'] =  $requestsList;
                 
                   
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                    
                    
                  return response()->json($responseJson);  
                    
                }else{
                    return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
                }
                
            } else{
              return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
              
            }
        }
        
        
        
        
        public function businessUnits(Request $request){
            if($request->userID){

                $id = $request->userID;

                $user = User::find($id);                  
                if ($user && $user->role == 2) {
                    
                    
                    
                    $managerUnitsSql = DB::table('staffing_businessunits')
                        ->select(
                            'staffing_businessunits.id',
                            'staffing_businessunits.unitName'
                    );
                    
                    
            $managerUnitsSql->where('staffing_businessunits.businessGroupID','=',$user->businessGroupID);
             $managerUnitsSql->where('staffing_businessunits.deleteStatus','=',0);
            $managerUnitsSql->where('staffing_businessunits.status','=',1);
//                    if($request->pendingSort == 1){
//                        
//                        $managerUnitsSql->orderBy();
//                    }
                    $managerUnits = $managerUnitsSql->get();
                    
                    
                    
                    $responseJson['status'] = '1';
                    $responseJson['message'] = 'Success';
                    $responseJson['data'] = array();
                    foreach($managerUnits as $managerUnit){
                        $openPostingCount = 0;
                        $openPostingCountSQL = DB::table('staffing_staffrequest')
                         ->select('id'); 
            
                        $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $managerUnit->id);

                        $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                        $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));

                        $openPostingCount = $openPostingCountSQL->count(); 
                        
                        /* Pending Request Count */
                        $pendingPostingCount = 0;
                        $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                         ->select('id'); 
            
                        $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $managerUnit->id);

                        $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
                        $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));

                        $pendingPostingCountSQL = $pendingPostingCountSQL->count(); 
                        /* Pending Request Count */
                        
                        /* Get Past Staffing Request */ 
                        $pastPostingCount = 0; 
                        $pastPostingCountSQL = DB::table('staffing_staffrequest')
                                ->select('id'); 

                        $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $managerUnit->id);

                        $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                        $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d"));

                        $pastPostingCount = $pastPostingCountSQL->count();              
                        /* Get Past Staffing Request */
                        
                        /* Get Cancelled Staffing Request */ 
                        $cancelledRequestCount = 0; 
                        $cancelledRequestCountSQL = DB::table('staffing_staffrequest')
                                ->select('id'); 

                        $cancelledRequestCountSQL->where('staffing_staffrequest.businessUnitID','=', $managerUnit->id);

                        $cancelledRequestCountSQL->whereIn('staffing_staffrequest.postingStatus', [2,4]); 
                        //$cancelledRequestCountSQL->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d"));

                        $cancelledRequestCount = $cancelledRequestCountSQL->count();              
                        /* Get Cancelled Staffing Request */
                        
                        
                        /* Get Users Count */  
                        $totalUsers = 0; 
                        $totalUsersSQL = DB::table('staffing_usersunits')
                            ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                            ->select(
                                    'staffing_usersunits.id'
                                    );
                        $totalUsersSQL->where('staffing_usersunits.businessUnitID','=', $managerUnit->id);            
                        $totalUsersSQL->whereIn('staffing_users.role', [0,3,4]); 
                        $totalUsers = $totalUsersSQL->count();              
                        /* Get Users Count */      
                        
                        $responseJson['data'][] = array(
                           'id' => (string)$managerUnit->id,
                            'name' => $managerUnit->unitName,
                            'activeRequestCount' => (string)$openPostingCount,
                            'openRequestCount' => (string)$openPostingCount,
                            'pendingRequestCount' => (string)$pendingPostingCountSQL,
                            'pastRequestCount' => (string)$pastPostingCount,
                            'cancelRequestCount' => (string)$cancelledRequestCount,
                            'staffProfileCount' => (string)$totalUsers
                        );
                    }
                    
                    
                    
                   
                   $requestedBusinessUnitID = $request->businessUnitID?$request->businessUnitID:0;
                   $isAdminAsUser = $request->isUser?$request->isUser:0;
                   $homePageCount = $this->homePageCount($user, $requestedBusinessUnitID, $isAdminAsUser);
                   
                   $responseJson['countInfo'] = $homePageCount;
                    
                    
                    
                    
                    
                    return response()->json($responseJson);  
                    
                }else{
                   return response()->json(['status'=>'0','message'=>'User not found.'], 500);   
                }
            }else{
                return response()->json(['status'=>'0','message'=>'User not found.'], 500);  
            }
            
        }
        
        
        public function onWaitlist(Request $request){//User will confirm that he want be on waitlist or not?
            $userID = $request->userID;
            $requestID = $request->requestID;
            $user = User::find($userID);
            if($user && ($user->role == 0 || $user->role == 4)){
               $requestInfo = Requestcall::find($requestID); 
               if($requestInfo){
                 $waitListStatus = $request->waitListStatus?$request->waitListStatus:0;
                 //1=>Yes,user wants be on waitlist, 2=> No, user doesn't want be on waitlist.                 
                 if($waitListStatus == 1){
                     $waitListStatus = 1;
                     $msg = 'You are on waitlist now.';
                 }else if($waitListStatus == 0){
                    $waitListStatus = 2;
                    $msg = 'Thank you for your response.';
                 }else{
                   return response()->json(['status'=>'0',
                       'message'=>'Something went wrong. Please try once again.'], 500);  
                 }
                 
                    $success = DB::table('staffing_shiftoffer')
                        ->where([['requestID', $requestInfo->id],['userID', $user->id]])
                        ->update(['inWaitList' => $waitListStatus]);
                     
                    return response()->json(['status'=>'1',
                       'message'=>$msg], 500);      
                     
                 
                 
               }else{
                 return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);   
               }
            }else{
               return response()->json(['status'=>'0','message'=>'User not found.'], 500); 
            }
        }
        
        
    } 