<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ShiftOffer;
use App\Businessunit;
use App\Requestcall;
use App\RequestPartialShift;
use App\OfferConfirmation;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Session;
use myHelper;

class ShiftOfferController extends Controller
{   
        
    /* END-USER Shift-Offer */
    
    
    
    public function shiftOffer(Request $request){
        
        
        if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $offers = array();
        
        if(Auth::user()->role == 0 || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)){
            
            
            $userBusinessUnitsAr = array();
            
            if(Auth::user()->role == 0){
            
                $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        );
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id);
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
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id);
                    $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                    $unitInfoSql->where('staffing_businessunits.status','=',1);
                    $unitInfo = $unitInfoSql->get();
            }
            
            
            if($unitInfo){
               foreach($unitInfo as $row){
                   $userBusinessUnitsAr[] = $row->id;
               } 
            }
            
            
            
            $userSkills = Auth::user()->skills;
            $skills = $userSkills?unserialize($userSkills):array();
         
        
            $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                
                ->leftJoin('staffing_shiftoffer', function($join)
                   {
                        $join->on('staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id');
                        $join->on('staffing_shiftoffer.userID', '=', DB::raw(Auth::user()->id));
                     })
                ->leftJoin('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')     
                   
                ->leftJoin('staffing_requestpartialshifts', 
                        'staffing_requestpartialshifts.id', 
                        '=', 'staffing_shiftoffer.partialShiftTimeID')  
                             
                 ->leftJoin('staffing_requestreasons', 'staffing_requestreasons.id', '=', 'staffing_staffrequest.requestReasonID')
                
                    ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.staffingShiftID',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_staffrequest.shiftType',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.numberOfOffers',
                        'staffing_staffrequest.notes',
                        'staffing_shiftoffer.userID AS responseUserID',
                        'staffing_shiftoffer.id AS userResponseID',
                        'staffing_shiftoffer.responseType',
                        'staffing_shiftoffer.overTime',
                        'staffing_shiftoffer.inWaitList',
                        'staffing_shiftoffer.partialShiftTimeID',
                        'staffing_requestpartialshifts.partialShiftStartTime',
                        'staffing_requestpartialshifts.partialShiftEndTime',
                        'staffing_shiftconfirmation.id AS confirmOfferID',
                        'staffing_shiftconfirmation.offerResponse AS confirmationOfferStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner"),
                        'staffing_requestreasons.reasonName AS requestReason'
                        );
        
            $requestPostsSql->where('staffing_staffrequest.closingTime','>=', date("Y-m-d H:i:s")); 
            
            if(count($userBusinessUnitsAr) > 0){
                $requestPostsSql->whereIn('staffing_staffrequest.businessUnitID', $userBusinessUnitsAr);
            }
        
                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
          
//           if($skills){
//              $requestPostsSql->whereIn('staffing_staffrequest.requiredStaffCategoryID', $skills); 
//           }
                
            $searchStartDateValue = '';  
            $searchEndDateValue = '';  
            if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                $searchStartDateValue = $request->fromDate;
                $searchEndDateValue = $request->toDate;
                $requestPostsSql->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
            }     
           
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            /* Active Requests */
            $offersResults = $requestPostsSql->get();
            $offers = array();
            
            foreach($offersResults as $offer){
                
                
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
                        
                        
                        
                $respondStatus = 0;//No response given By User
                   $cancelStatus = 0;
                   $partialShiftTimeID = 0;
                   $overTimeStatus = $offer->overTime?1:0;
                   
                   if($offer->responseUserID == Auth::user()->id){
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
                   
                   
                   if($offer->userResponseID > 0){
                       $checkAdminConfirmationRequest = DB::table('staffing_shiftconfirmation')
                         ->select('id','offerResponse')->where([['shiftOfferID','=',$offer->userResponseID]])->first();
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
                          if(in_array(Auth::user()->id, $offerConfirmedUser)){

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
                    
                $offer->respondStatus = $respondStatus;
                
                    $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                        ->select('userID','shiftID');
                    
                        $userAvailabilitySql->where('onDate','=',$offer->staffingStartDate);
                        $userAvailabilitySql->whereIn('availabilityStatus', [0,2]);
                        $userAvailabilitySql->where('userID','=', Auth::user()->id);

                        if($offer->shiftType == 0 && $offer->staffingShiftID > 0){
                          $userAvailabilitySql->where('shiftID','=', $offer->staffingShiftID);  
                        }
                            
                    $userAvailability = $userAvailabilitySql->count();
                    
                    
                    if($userAvailability > 0 && $offer->shiftType != 1){
                    }else{
                        $endUserSkills = array();
                        $endUserSkills = Auth::user()->skills?unserialize(Auth::user()->skills):array();
                        
                        $requiredStaffCategoryIDs = ($offer->requiredStaffCategoryID ? 
                        explode(",", $offer->requiredStaffCategoryID):array()); 
                        
                        if($requiredStaffCategoryIDs && $endUserSkills){
                            //if(array_intersect($requiredStaffCategoryIDs, $endUserSkills)){
                            if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){
                                
                                $offers[] = $offer;
                                
                                
                            } 
                        }
                    }
            }
            
            
            
        }
        
        
        
        return view('shiftoffer.shiftoffer',[
                'groupInfo' => $groupInfo,
                'offers' => $offers,
                'searchStartDateValue' => $searchStartDateValue,
                'searchEndDateValue' => $searchEndDateValue]);
    }
    
    
    
    public function shiftOfferDetail($id){
        
        if(Auth::user()->role != 0 &&  Session::get('defaultView') != 'end-user'){
          return redirect(Config('constants.urlVar.staffingPostDetail').$id);   
        }
        
        $post = Requestcall::find($id); 
        
        if(!$post){
            return redirect(Config('constants.urlVar.shiftOffer'))->with('error','The page you are requested is not found.');    
        }
        
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $offers = array();
        
        if(Auth::user()->role == 0 || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)){
            
            $userBusinessUnitsAr = array();
            
            if(Auth::user()->role == 0){
            
                $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        );
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id);
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
                    
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id);
                    $unitInfoSql->where('staffing_businessunits.deleteStatus','=',0);
                    $unitInfoSql->where('staffing_businessunits.status','=',1);
                    $unitInfo = $unitInfoSql->get();
            }
            
            
            if($unitInfo){
               foreach($unitInfo as $row){
                   $userBusinessUnitsAr[] = $row->id;
               } 
            }
            
                                        
            
            $userSkills = Auth::user()->skills;
            $skills = $userSkills?unserialize($userSkills):array();
         
        
            $requestPostsSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                
                ->leftJoin('staffing_shiftoffer', function($join)
                   {
                        $join->on('staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id');
                        $join->on('staffing_shiftoffer.userID', '=', DB::raw(Auth::user()->id));
                     })
                ->leftJoin('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')     
                   
                ->leftJoin('staffing_requestpartialshifts', 
                        'staffing_requestpartialshifts.id', 
                        '=', 'staffing_shiftoffer.partialShiftTimeID')  
                             
                 ->leftJoin('staffing_requestreasons', 'staffing_requestreasons.id', '=', 'staffing_staffrequest.requestReasonID')
                
                    ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.staffingShiftID',
                        'staffing_staffrequest.shiftType',
                        'staffing_staffrequest.requiredStaffCategoryID',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime',
                        'staffing_staffrequest.numberOfOffers',
                        'staffing_staffrequest.notes',
                        'staffing_shiftoffer.userID AS responseUserID',
                        'staffing_shiftoffer.id AS userResponseID',
                        'staffing_shiftoffer.responseType',
                        'staffing_shiftoffer.overTime',
                        'staffing_shiftoffer.inWaitList',
                        'staffing_shiftoffer.partialShiftTimeID',
                        'staffing_requestpartialshifts.partialShiftStartTime',
                        'staffing_requestpartialshifts.partialShiftEndTime',
                        'staffing_shiftconfirmation.id AS confirmOfferID',
                        'staffing_shiftconfirmation.offerResponse AS confirmationOfferStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner"),
                        'staffing_requestreasons.reasonName AS requestReason'
                        );
            
            //$requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                     
            if(count($userBusinessUnitsAr) > 0){
                $requestPostsSql->whereIn('staffing_staffrequest.businessUnitID', $userBusinessUnitsAr);
            }         
        
           $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
          
//           if($skills){
//              $requestPostsSql->whereIn('staffing_staffrequest.requiredStaffCategoryID', $skills); 
//           }
           
            $requestPostsSql->where('staffing_staffrequest.id','=', $id); 
           
            //$requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            /* Active Requests */
            $offer = $requestPostsSql->first();
            
            
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
                        
                        
                        
                $respondStatus = 0;//No response given By User
                   $cancelStatus = 0;
                   $partialShiftTimeID = 0;
                   $overTimeStatus = $offer->overTime?1:0;
                   
                   if($offer->responseUserID == Auth::user()->id){
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
                   
                   
                   if($offer->userResponseID > 0){
                       $checkAdminConfirmationRequest = DB::table('staffing_shiftconfirmation')
                         ->select('id','offerResponse')->where([['shiftOfferID','=',$offer->userResponseID]])->first();
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
                          if(in_array(Auth::user()->id, $offerConfirmedUser)){

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
                    
                $offer->respondStatus = $respondStatus;
                
                
                $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                    ->select('userID','shiftID');
                        $userAvailabilitySql->where('onDate','=',$offer->staffingStartDate);
                        $userAvailabilitySql->whereIn('availabilityStatus', [0,2]);
                        $userAvailabilitySql->where('userID','=', Auth::user()->id);

                        if($offer->shiftType == 0 && $offer->staffingShiftID > 0){
                          $userAvailabilitySql->where('shiftID','=', $offer->staffingShiftID);  
                        }
                            
                    $userAvailability = $userAvailabilitySql->count();
                    
                    
                    if($userAvailability > 0 && $offer->shiftType != 1){
                       return redirect(Config('constants.urlVar.shiftOffer'))->with('error','The page you are requested is not found.');    
                    }else{
                        $endUserSkills = array();
                        $endUserSkills = Auth::user()->skills?unserialize(Auth::user()->skills):array();
                        
                        $requiredStaffCategoryIDs = ($offer->requiredStaffCategoryID ? 
                        explode(",", $offer->requiredStaffCategoryID):array()); 
                        
                        if($requiredStaffCategoryIDs && $endUserSkills){
                            //if(array_intersect($requiredStaffCategoryIDs, $endUserSkills)){
                            if(count(array_intersect($requiredStaffCategoryIDs, $endUserSkills)) == count($requiredStaffCategoryIDs)){
                                
                            }else{
                              return redirect(Config('constants.urlVar.shiftOffer'))->with('error','The page you are requested is not found.');      
                            } 
                        }else{
                           return redirect(Config('constants.urlVar.shiftOffer'))->with('error','The page you are requested is not found.');     
                        }
                    }
            
            
            
            
        }
        
        
        
        
        
        
        return view('shiftoffer.offer-detail',[
                'groupInfo' => $groupInfo,
                'offer' => $offer]);
    }
    
    
    
    public function onWaitlist(Request $request){//User will confirm that he want be on waitlist or not?
            $userID = Auth::user()->id;
            $requestID = $request->requestID;
            $user = User::find($userID);
            if($user && ($user->role == 0 || $user->role == 4)){
               $requestInfo = Requestcall::find($requestID); 
                if($requestInfo){
                    
                    $checkAlready = DB::table('staffing_shiftoffer')
                    ->select(
                            'id'
                    )->where([['requestID','=',$requestID],
                        ['userID','=',$userID]])->first();
           
                    if(count($checkAlready) > 0){
                   
                        $waitListStatus = $request->waitListStatus?$request->waitListStatus:0;
                        //1=>Yes,user wants be on waitlist, 2=> No, user doesn't want be on waitlist.                 
                        if($waitListStatus == 1){
                            $waitListStatus = 1;
                            $msg = 'You are on waitlist now.';
                        }else if($waitListStatus == 2){
                           $waitListStatus = 2;//Decline to be on waitlist
                           $msg = 'Thank you for your response.';
                        }else{
                          return response()->json(['status'=>'0',
                              'message'=>'Something went wrong. Please try once again.']);  
                        }

                        $success = DB::table('staffing_shiftoffer')
                            ->where([['requestID', $requestInfo->id],['userID', $user->id]])
                            ->update(['inWaitList' => $waitListStatus]);

                        return response()->json(['status'=>'1',
                           'msg'=>$msg]);      
                    }else{
              
                        return response()->json([
                            'status'=>0,
                            'msg'=>'You have not any offer.'    
                        ]); 
                    }
                 
                 
               }else{
                 return response()->json(['status'=>'0','message'=>'Request call not found.']);   
               }
            }else{
               return response()->json(['status'=>'0','message'=>'User not found.']); 
            }
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
            $pushMessage .= " ".$shiftTime;   
            
            return $pushMessage;
            
    }
    
    
    function acceptRequest(Request $request){//Full Shift/Partial Shift
        if(Auth::user()->role == '0' || Auth::user()->role == '4'){
           $requestPostID =  $request->requestID;
           $responseType =  $request->respondType?1:0;
           $overTimeShift = $request->overTime?1:0;
           $partialShiftTimeID = 0;//Full Shift
           
           $userID = Auth::user()->id;
           
           $post = Requestcall::find($requestPostID);
            
            $partialShiftStartTime = '';
            $partialShiftEndTime = '';
           
           if($responseType == 1)//Partial Shift
           {
                $partialShiftTimeID =   $request->partialShiftTimeID?$request->partialShiftTimeID:0;
                $partialShiftStartTime = date("Y-m-d H:i:s", strtotime($post->staffingStartDate." ".$request->partialShiftStartTime));  
                $partialShiftEndTime = date("Y-m-d H:i:s", strtotime($post->staffingStartDate." ".$request->partialShiftEndTime));  
                
                    if(strtotime($partialShiftStartTime) > strtotime($partialShiftEndTime)){
                        
                        $staffingEndDate = (date("Y-m-d",strtotime($post->staffingStartDate . " +1 day")));       
                        $partialShiftEndTime = date("Y-m-d H:i:s", strtotime($staffingEndDate." ".$request->partialShiftEndTime));
                    }
                
                

                 if($partialShiftTimeID > 0){
                     $savePartialShift = RequestPartialShift::find($partialShiftTimeID);
                     if($savePartialShift){
                         $savePartialShift->partialShiftStartTime = $partialShiftStartTime;
                         $savePartialShift->partialShiftEndTime = $partialShiftEndTime;
                         if($savePartialShift->save()){
                             $partialShiftTimeID = $savePartialShift->id;
                         }
                     }else{
                         $saveNewPartialShift = new RequestPartialShift;
                         $saveNewPartialShift->requestID = $requestPostID;
                         $saveNewPartialShift->partialShiftStartTime = $partialShiftStartTime;
                         $saveNewPartialShift->partialShiftEndTime = $partialShiftEndTime;
                         if($saveNewPartialShift->save()){
                             $partialShiftTimeID = $saveNewPartialShift->id;
                         }   
                     }
                 }else{
                     $savePartialShift = new RequestPartialShift;
                     $savePartialShift->requestID = $requestPostID;
                     $savePartialShift->partialShiftStartTime = $partialShiftStartTime;
                     $savePartialShift->partialShiftEndTime = $partialShiftEndTime;
                     if($savePartialShift->save()){
                         $partialShiftTimeID = $savePartialShift->id;
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
               $shiftOffer->partialShiftTimeID = $partialShiftTimeID;
               $shiftOffer->overTime = $overTimeShift;
                if($shiftOffer->save()){
                    $success = true;
                    
//                    return response()->json([
//                        'status'=>1,
//                        'msg'=>'Response sent.'    ,
//                        'msg2'=>$request->respondType
//                    ]);
                }else{
//                 return response()->json([
//                        'status'=>0,
//                        'msg'=>'Failed to send response.'    
//                    ]);     
                }  

            }else{
              $shiftOffer = new ShiftOffer;

               $shiftOffer->requestID = $requestPostID;
               $shiftOffer->userID = $userID;
               $shiftOffer->responseType = $responseType;
               $shiftOffer->partialShiftTimeID = $partialShiftTimeID;
               $shiftOffer->overTime = $overTimeShift;
                if($shiftOffer->save()){
                    $success = true;
//                    return response()->json([
//                        'status'=>1,
//                        'msg'=>'Response sent.'    ,
//                        'msg2'=>$request->respondType
//                    ]);
                }else{
//                 return response()->json([
//                        'status'=>0,
//                        'msg'=>'Failed to send response.'    
//                    ]);     
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

                     
                                        
                        $overTimeMsg = ' without OT.';
                        if($overTimeShift == 1){
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

                                         
                     
                      if($responseType == 1){
                         $pushMsg = Auth::user()->firstName." ".Auth::user()->lastName." accepted Partial Shift".$partialShiftTimingMsg.$overTimeMsg;//Partial Shift
                      }else {
                       
                         $pushMsg = Auth::user()->firstName." ".Auth::user()->lastName." accepted Full Shift".$overTimeMsg;//Full Shift  
                     }
                     
                     
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
                 $iosDevicesArray = array();
                $androidDevicesArray = array();    
//                 $getUserDevices = DB::table('staffing_devices')
//                     ->select('deviceID','deviceType')
//                     ->where([['userID','=',Auth::user()->id]])->get();
//
//                 if(count($getUserDevices) > 0){
//                     foreach($getUserDevices as $getUserDevice){
//                       $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
//                         if($getUserDevice->deviceType == '1'){
//                             if($deviceToken)
//                               $iosDevicesArray[] = $deviceToken;
//                         }elseif($getUserDevice->deviceType == '2'){
//                             if($deviceToken)
//                                $androidDevicesArray[] = $deviceToken;
//                         } 
//                     } 
//
//
//
//                 $pushmessage = "Thank you for your response, you will be notified shortly.";
//                      $msg_payload = array (
//                      'mtitle' => 'Staffing Call',
//                      'mdesc' => $pushmessage,
//                      'notificationStatus' => 0,
//                      'requestID' => $post->id
//                      );
//
//                      $msg_payloadAndroid = array (
//                      'mtitle' => 'Staffing Call',
//                      'mdesc' => $pushmessage,
//                      'notificationStatus' => 0,
//                      'requestID' => $post->id
//                      );   
//
//
//                      //if($androidDevicesArray){
//                          //if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
//                        //$androidPusStatus = true;
//                      //}
//
//                      if($iosDevicesArray){
//                          if(myHelper::iOS($msg_payload,$iosDevicesArray))
//                              $iosPusStatus = true;
//                      }
//
//                 }
                   /* Send Push To User */  
                
                
//                    return redirect(Config('constants.urlVar.shiftOfferDetail').$post->id)
//                         ->with('success','Thank you for your response.'); 
                
                $request->session()->flash('success', 'Thank you for your response.');
                
                return response()->json([
                        'status'=>1,
                        'msg'=>'Response sent.'    ,
                        'msg2'=>$request->respondType
                    ]);


             }else{
                 $request->session()->flash('error', 'Something is wrong. Please try again later.');
                 return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to send response.'    
                ]);  
                 
//                 return redirect(Config('constants.urlVar.shiftOfferDetail').$post->id)
//                         ->with('error','Something is wrong. Please try again later.'); 
             }
           
           
        }else{
                return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to send response.'    
                ]);  
//            return redirect(Config('constants.urlVar.shiftOfferDetail').$requestPostID)
//             ->with('error','Something is wrong. Please try again later.'); 
        }
        
         
    }
    
    
    
    function declineRequest(Request $request){
        if(Auth::user()->role == '0' || Auth::user()->role == '4'){
           $requestPostID =  $request->requestID;
           $responseType =  2;
           $overTimeShift = 0;
           $partialShiftTimeID = 0;
           
           $userID = Auth::user()->id;
           $post = Requestcall::find($requestPostID);
           
                    $success = false;
            $checkAlready = DB::table('staffing_shiftoffer')
                ->select(
                        'id'
                        )->where([['requestID','=',$requestPostID],
                            ['userID','=',$userID]])->first();
        
            if(count($checkAlready) > 0){
               $shiftOffer = ShiftOffer::find($checkAlready->id);
               $shiftOffer->requestID = $requestPostID;
               $shiftOffer->userID = $userID;
               $shiftOffer->responseType = $responseType;
               $shiftOffer->partialShiftTimeID = $partialShiftTimeID;
               $shiftOffer->overTime = $overTimeShift;
                if($shiftOffer->save()){
                    $success = true;
                    $request->session()->flash('success', 'Thank you for your response.');
                    return response()->json([
                        'status'=>1,
                        'msg'=>'Response sent.'    ,
                        'msg2'=>$request->respondType
                    ]);
                }else{
                 $request->session()->flash('error', 'Failed to send response. Please try again later.');   
                 return response()->json([
                        'status'=>0,
                        'msg'=>'Failed to send response.'    
                    ]);     
                }  

            }else{
              $shiftOffer = new ShiftOffer;

               $shiftOffer->requestID = $requestPostID;
               $shiftOffer->userID = $userID;
               $shiftOffer->responseType = $responseType;
               $shiftOffer->partialShiftTimeID = $partialShiftTimeID;
               $shiftOffer->overTime = $overTimeShift;
                if($shiftOffer->save()){
                    $success = true;
                    $request->session()->flash('success', 'Thank you for your response.');
                    return response()->json([
                        'status'=>1,
                        'msg'=>'Response sent.' 
                    ]);
                }else{
                 
                 $request->session()->flash('error', 'Failed to send response. Please try again later.');    
                 return response()->json([
                        'status'=>0,
                        'msg'=>'Failed to send response.'    
                    ]);     
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


                $pushMsg = Auth::user()->firstName." ".Auth::user()->lastName." declined request.";//Full Shift  
                    
                    
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
//                 $iosDevicesArray = array();
//                $androidDevicesArray = array();    
//                 $getUserDevices = DB::table('staffing_devices')
//                     ->select('deviceID','deviceType')
//                     ->where([['userID','=',Auth::user()->id]])->get();
//
//                 if(count($getUserDevices) > 0){
//                     foreach($getUserDevices as $getUserDevice){
//                       $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
//                         if($getUserDevice->deviceType == '1'){
//                             if($deviceToken)
//                               $iosDevicesArray[] = $deviceToken;
//                         }elseif($getUserDevice->deviceType == '2'){
//                             if($deviceToken)
//                                $androidDevicesArray[] = $deviceToken;
//                         } 
//                     } 
//
//
//
//                 $pushmessage = "Thank you for your response.";
//                      $msg_payload = array (
//                      'mtitle' => 'Staffing Call',
//                      'mdesc' => $pushmessage,
//                      'notificationStatus' => 0,
//                      'requestID' => $post->id
//                      );
//
//                      $msg_payloadAndroid = array (
//                      'mtitle' => 'Staffing Call',
//                      'mdesc' => $pushmessage,
//                      'notificationStatus' => 0,
//                      'requestID' => $post->id
//                      );   
//
//
//                      //if($androidDevicesArray){
//                          //if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
//                        //$androidPusStatus = true;
//                      //}
//
//                      if($iosDevicesArray){
//                          if(myHelper::iOS($msg_payload,$iosDevicesArray))
//                              $iosPusStatus = true;
//                      }
//
//                 }
                   /* Send Push To User */  


             } 
            
            
           
           
        }else{
                return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to send response.'    
                ]);  
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
    
    function acceptOffer(Request $request){
        
        if(Auth::user()->role == '0' || Auth::user()->role == '4'){
           $requestPostID =  $request->requestID;           
           $userID = Auth::user()->id;
           $checkAlready = DB::table('staffing_shiftoffer')
                ->select(
                        'id'
                )->where([['requestID','=',$requestPostID],
                    ['userID','=',$userID]])->first();
           
           
           $post = Requestcall::find($requestPostID);
        
            if(count($checkAlready) > 0){
                
               $checkOffer = DB::table('staffing_shiftconfirmation')
                ->select(
                        'id'
                )->where([['shiftOfferID','=',$checkAlready->id]])->first(); 
                
                if(count($checkOffer) > 0){
                    $shiftOffer = OfferConfirmation::find($checkOffer->id);
                    $shiftOffer->offerResponse = 1;
                     if($shiftOffer->save()){
                         
                         
                        /* Set Adjecent Shift As Unavailable For User As this shift is Sheduled for him. */
                    /* Only if Request has Pre-Shift Not Custom Shift */     
                if($post->staffingShiftID > 0 && $post->shiftType == 0) {        
                    $this->setUserAsUnAvailableOnAdjecentShifts($post->staffingStartDate, 
                        $post->staffingStartDate, $post->businessUnitID, $post->staffingShiftID,$userID);
                }
                        /* Set Adjecent Shift As Unavailable For User As this shift is Sheduled for him. */
                         
                         
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


                            
                $pushmessage = Auth::user()->firstName." ".Auth::user()->lastName." has accepted offer.";
                            
                 
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


                                               $pushmessage = "Would you like to remain AVAILABLE as per your response?";
                                               
                                               
                                                /* Get Push Message */
                                                 $pushMessage2 = $this->getRequestInformationForPush($post);
                                                 /* Get Push Message */
                                               
                                               
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
                        
                        
                        
                        /* Send Push To ADmin */  

                        /* Send Push To User */ 
//                        $iosDevicesArray = array();
//                       $androidDevicesArray = array();    
//                        $getUserDevices = DB::table('staffing_devices')
//                            ->select('deviceID','deviceType')
//                            ->where([['userID','=',Auth::user()->id]])->get();
//
//                        if(count($getUserDevices) > 0){
//                            foreach($getUserDevices as $getUserDevice){
//                              $deviceToken = $getUserDevice->deviceID?$getUserDevice->deviceID:''; 
//                                if($getUserDevice->deviceType == '1'){
//                                    if($deviceToken)
//                                      $iosDevicesArray[] = $deviceToken;
//                                }elseif($getUserDevice->deviceType == '2'){
//                                    if($deviceToken)
//                                       $androidDevicesArray[] = $deviceToken;
//                                } 
//                            } 
//
//
//                            
//                               $pushmessage = "Offer accepted. You are expected to come on Scheduled time.";
//                                $msg_payload = array (
//                                'mtitle' => 'Staffing Call',
//                                'mdesc' => $pushmessage,
//                                'notificationStatus' => 0,
//                                'requestID' => $post->id
//                                );
//
//                                $msg_payloadAndroid = array (
//                                'mtitle' => 'Staffing Call',
//                                'mdesc' => $pushmessage,
//                                'notificationStatus' => 0,
//                                'requestID' => $post->id
//                                );   
//
//
//                                //if($androidDevicesArray){
//                                    //if(myHelper::android($msg_payloadAndroid,$androidDevicesArray))
//                                  //$androidPusStatus = true;
//                                //}
//
//                                if($iosDevicesArray){
//                                    if(myHelper::iOS($msg_payload,$iosDevicesArray))
//                                        $iosPusStatus = true;
//                                }
//
//                        }
                          /* Send Push To User */  


                           /* Send Push Notifications */   
                         
                         
                         
                         return response()->json([
                             'status'=>1,
                             'msg'=>'Offer accepted. You are expected to come on Scheduled time.'    
                         ]);
                     }else{
                      return response()->json([
                             'status'=>0,
                             'msg'=>'Failed to confirm offer.'    
                         ]);     
                     }  
                }else{
                   return response()->json([
                        'status'=>0,
                        'msg'=>'You have not any offer.'    
                    ]);   
                }

            }else{
              
                 return response()->json([
                        'status'=>0,
                        'msg'=>'You have not any offer.'    
                    ]); 
            }
           
           
        }else{
                return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to send response.'    
                ]);  
        }
        
    }
    
    function declineOffer(Request $request){
       if(Auth::user()->role == '0' || Auth::user()->role == '4'){
           $requestPostID =  $request->requestID;           
           $userID = Auth::user()->id;           
           $post = Requestcall::find($requestPostID);
           $checkAlready = DB::table('staffing_shiftoffer')
                ->select(
                        'id'
                )->where([['requestID','=',$requestPostID],
                    ['userID','=',$userID]])->first();
        
            if(count($checkAlready) > 0){
                
               $checkOffer = DB::table('staffing_shiftconfirmation')
                ->select(
                        'id'
                )->where([['shiftOfferID','=',$checkAlready->id]])->first(); 
                
                if(count($checkOffer) > 0){
                    $shiftOffer = OfferConfirmation::find($checkOffer->id);
                    $shiftOffer->offerResponse = 2;
                     if($shiftOffer->save()){
                         
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


                    $pushmessage = Auth::user()->firstName." ".Auth::user()->lastName." has declined offer.";

                    
                    /* Get Push Message */
                     $pushMessage2 = $this->getRequestInformationForPush($post);
                     /* Get Push Message */
                                               
                                $msg_payload = array (
                                'mtitle' => $pushmessage,
                                'mdesc' => $pushMessage2,
                                'notificationStatus' => 0,
                                'requestID' => $post->id
                                );

                                $msg_payloadAndroid = array (
                                'mtitle' => $pushmessage,
                                'mdesc' => $pushMessage2,
                                'notificationStatus' => 0,
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
                         
                         
                         return response()->json([
                             'status'=>1,
                             'msg'=>'Offer declined.'    
                         ]);
                     }else{
                      return response()->json([
                             'status'=>0,
                             'msg'=>'Failed to decline offer.'    
                         ]);     
                     }  
                }else{
                   return response()->json([
                        'status'=>0,
                        'msg'=>'You have not any offer.'    
                    ]);   
                }

            }else{
              
                 return response()->json([
                        'status'=>0,
                        'msg'=>'You have not any offer.'    
                    ]); 
            }
           
           
        }else{
                return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to send response.'    
                ]);  
        }
    }
    
    
    
} 
                             