<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Businessunit;
use App\Userunit;
use App\User;
use App\Requestcall;
use App\RequestLog;
use App\RequestPartialShift;
use App\OfferConfirmation;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use myHelper;
use Illuminate\Support\Facades\Mail;

class CallsController extends Controller
{   
    
        public function index()
    {   
        
        return view('calls.show');
    }
    
    
    
    public function sendMessageCommunication($requestID = 0, $userData = array()){
            if($requestID > 0 && $userData){
                $mobiles = array();
              foreach($userData as $userInfo)  {
                  $name = $userInfo['firstName']." ".$userInfo['lastName'];
                  $this->emailCommunication($requestID, $name, $userInfo['email']);
                  if($userInfo['phone'] != '')
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
                        $message->from('contact@agidev-staffingcall.com', 'StaffingCall App');

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
    
    
    public function ajaxStaffingRequestsList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'userName', 
            1 => 'businessUnit',
            2=> 'role',
            3=> 'name',
            4=> 'skills',
            6=> 'email',
            5=> 'phone'
        );
        
        $totalFiltered = DB::table('staffing_users')->select('id')->count();
        
        $sql = DB::table('staffing_users')
                ->select(
                        'staffing_users.id',
                        'staffing_users.userName', 
                         DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                        'staffing_users.role', 
                        'staffing_users.profilePic', 
                        'staffing_users.email', 
                        'staffing_users.phone', 
                        'staffing_users.skills', 
                        'staffing_users.status');
        
        $sql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where('id','!=',Auth::user()->id);
       
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where('staffing_users.userName', 'LIKE', $requestData['search']['value'].'%');
            $sql->orWhere('staffing_users.email', 'LIKE', $requestData['search']['value'].'%'); 
            $sql->orWhere('staffing_users.phone', 'LIKE', $requestData['search']['value'].'%'); 
            $sql->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
            $sql->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        //$sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        $sql->orderBy('firstName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        
        
        foreach($results as $result){
            
                
        /* Get User Assigned Business Unit */
                $userBusinessUnits = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                 ->select(
                'staffing_businessunits.id',
                'staffing_businessunits.unitName')->where([['staffing_usersunits.userID','=',$result->id]])
                ->get();
              $usersUnits = array();
                if($userBusinessUnits){
                    foreach($userBusinessUnits as $userBusinessUnit){
                        $usersUnits[] = $userBusinessUnit->unitName;
                    }
                }
                
        /* Get User Assigned Business Unit */
                              
                $nestedData=array(); 

                $nestedData[] = $result->userName;
                $nestedData[] = $usersUnits?'<span style="color:#42a5f5;">'.(implode(', ',$usersUnits)).'</span>':'<span style="color:#f00;">Not alloted yet</span>';
                $nestedData[] = ($result->role == '3')?'<span class="badge badge-danger">Super Admin</span>':(($result->role == '4')?'<span class="badge badge-info">Admin</span>':'<span class="badge badge-warning">End User</span>');
                $nestedData[] = $result->name;
                $nestedData[] = $result->skills?implode(', ',unserialize($result->skills)):'Not defined';
                $nestedData[] = $result->email;
                $nestedData[] = $result->phone;
                $data[] = $nestedData; 
        }
        
        $json_data = array(
        "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
        "recordsTotal"    => intval( $totalData ),  // total number of records
        "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
        "data"            => $data   // total data array
        );
        
       return response()->json($json_data); 
    }
    
    

        
    public function newRequest(){
        
        if(Auth::user()->role == 0){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
                        ->select(
                        'id',
                        'groupCode',
                        'groupName'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        
        $businessUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName');
        
        $businessUnitsSql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $businessUnitsSql->where('staffing_businessunits.deleteStatus','=',0);
        $businessUnitsSql->where('staffing_businessunits.status','=',1);
        
        $businessUnitsSql->orderBy('unitName','ASC');
        
        if(Auth::user()->role == 3 || Auth::user()->role == 4){//Super-Admin
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
            $userUnitID = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
                ->select('staffing_businessunits.id AS businessUnitID')
                ->where([
                    ['staffing_usersunits.userID','=',Auth::user()->id]])
                 ->first();   
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
          $businessUnitsSql->where('id','=',$userUnitID->businessUnitID);  
            
        }
        
        
        $businessUnits = $businessUnitsSql->get();
        
        
//        $requestReasons = DB::table('staffing_requestreasons')
//                ->select(
//                        'id',
//                        'reasonName'
//                        )->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//                                ->orWhere('businessGroupID','=',0)->get();
        
        $requestReasons = DB::table('staffing_requestreasons')
                ->select(
                        'id',
                        'reasonName',
                        'defaultOf'
                        )->where('businessGroupID','=',Auth::user()->businessGroupID)
                                ->where('status','=',1)->get();
        
//        $vacancyReasons = DB::table('staffing_vacancyreasons')
//                ->select(
//                        'id',
//                        'reasonName'
//                        )->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//                                ->orWhere('businessGroupID','=',0)->get();
        
        $vacancyReasons = DB::table('staffing_vacancyreasons')
                ->select(
                        'id',
                        'reasonName'
                        )->where('businessGroupID','=',Auth::user()->businessGroupID)
                                ->where('status','=',1)->get();
        
        
        
        $staffingCategory = DB::table('staffing_skillcategory')
                ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
        $staffSql = DB::table('staffing_users')
                ->select(
                        'id',
                        'firstName',
                        'lastName'
                        );
        
        $staffSql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $staffSql->whereIn('role',[0,4]);
        $staffSql->where('staffing_users.deleteStatus','=',0);
        $staffSql->where('staffing_users.status','=',1);
        $staffSql->orderBy('firstName','ASC');
        $staffs = $staffSql->get();
        
        
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
        
        $algorithmsSql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $algorithmsSql->where(function ($query){
                $query->orWhere('type', '=', 'simple');
                $query->orWhere('type', '=', 'open'); 
        });
        
        $algorithmsSql->orderBy('id','ASC');
        
        $algorithms = $algorithmsSql->get();
        /* Offer Algorithm */
        
        
        
        return view('calls.new', [
            'units' => $businessUnits,
            'groups' => $businessGroup,
            'requestReasons' => $requestReasons,
            'vacancyReasons' => $vacancyReasons,
            'staffingCategory' => $staffingCategory,
            'staffs' => $staffs,
            'algorithms' => $algorithms
                ]);
    }
    
    /* To fetch Staffs & their Categories By Changing Drop-down of Business-Unit */
    public function ajaxNewRequestFormSetting(Request $request){
        $businessUnitID = $request->businessUnitID;
        $staffSql = DB::table('staffing_users')
                ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id')
                ->select(
                        'staffing_users.id',
                        'staffing_users.firstName',
                        'staffing_users.lastName'
                );
        
        
        $staffSql->where('staffing_users.businessGroupID','=',Auth::user()->businessGroupID);
        $staffSql->whereIn('staffing_users.role',[0,4]);
        $staffSql->where('staffing_usersunits.businessUnitID','=',$businessUnitID);
        $staffSql->where('staffing_users.deleteStatus','=',0);
        $staffSql->where('staffing_users.status','=',1);
        $staffSql->orderBy('staffing_users.firstName','ASC');
        $staffs = $staffSql->get();
        
        
        $staffingCategory = DB::table('staffing_skillcategory')
                ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
        
        $shifts = DB::table('staffing_shiftsetup')
                ->select(
                        'id',
                        'shiftType',
                        DB::raw('DATE_FORMAT(startTime, "%h:%i %p") as startTimes'),
                        DB::raw('DATE_FORMAT(endTime, "%h:%i %p") as endTimes')
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID],
                                ['businessUnitID','=',$businessUnitID]
                            ])->orderBy('startTime','ASC')->get();
        
//        $nightShifts = DB::table('staffing_shiftsetup')
//                ->select(
//                        'id',
//                        'shiftTitle',
//                        DB::raw('DATE_FORMAT(startTime, "%h:%i %p") as startTimes'),
//                        DB::raw('DATE_FORMAT(endTime, "%h:%i %p") as endTimes')
//                        )->where([
//                                ['businessGroupID','=',Auth::user()->businessGroupID],
//                                ['businessUnitID','=',$businessUnitID],
//                                ['shiftType','=',1]
//                            ])->orderBy('startTime','ASC')->get();
        
        
        return response()->json([
                        'status'=>'1',
                        'staffs'=>$staffs,
                        'skills'=>$staffingCategory,
                        'shifts'=>$shifts
                        ]);
        
    }
    /* To fetch Staffs & their Categories By Changing Drop-down of Business-Unit */
    
    public function getPartialShiftsFiftyPercente($shiftStartDate,$shiftEndDate,$shiftStartTime,$shiftEndTime){
        $staffingStartDate = date("Y-m-d",strtotime($shiftStartDate));
        $staffingEndDate = date("Y-m-d",strtotime($shiftEndDate));
        
        $customShiftStartTime = date("H:i:s",strtotime($shiftStartTime));
        $customShiftEndTime = date("H:i:s",strtotime($shiftEndTime));
        
        $time1 = date("Y-m-d H:i:s",strtotime($staffingStartDate." ".$customShiftStartTime));
        $time2 = date("Y-m-d H:i:s",strtotime($staffingEndDate." ".$customShiftEndTime));
        
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
    
    public function saveRequest(Request $request){
        
        if(Auth::user()->role == 0){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroupID = Auth::user()->businessGroupID;
        
        $businessUnitID = $request->businessUnitID;
        $requestReasonID = $request->requestReasonID;
        
        
        $lastMinuteStaffID = $request->lastMinuteStaffID;
        $timeOfCallMade = date("Y-m-d H:i:s",strtotime($request->timeOfCallMade));
        $vacancyReasonID = $request->vacancyReasonID;
        
        
        
        $requiredStaffCategoryID = $request->requiredStaffCategoryID;
        $requiredExperiencedLevel = $request->requiredExperiencedLevel?$request->requiredExperiencedLevel:0;
        
        
        
        $numberOfOffers = $request->numberOfOffers;
        $staffingStartDate = date("Y-m-d",strtotime($request->staffingStartDate));
        $staffingEndDate = $staffingStartDate;//date("Y-m-d",strtotime($request->staffingEndDate));
        $shiftType = $request->shiftType;
        $staffingShiftID = $request->staffingShiftID;
        $customShiftStartTime = date("H:i:s",strtotime($request->customShiftStartTime));
        $customShiftEndTime = date("H:i:s",strtotime($request->customShiftEndTime));
        $notes = $request->notes;
        
        $ownerID = Auth::user()->id;
        $updatedBy = Auth::user()->id;
        
        if(Auth::user()->role == 4 && Auth::user()->needApproval != 1){
            $approvedBy = Auth::user()->id;
            $postingStatus = 1;  
        }else{
            $approvedBy = (Auth::user()->role == 2 || Auth::user()->role == 3)?Auth::user()->id:0;
            $postingStatus = (Auth::user()->role == 2 || Auth::user()->role == 3)?3:0;
        }
        
        /* Check Request Reason ID default Of value */
        $requestReasonDefaultOf = DB::table('staffing_requestreasons')
                ->select('id','defaultOf')->where([['id', '=', $requestReasonID]]);
        /* Check Request Reason ID default Of value */
        $changable = 0;
        if($requestReasonDefaultOf->count() > 0){
            $requestReasonDefaultOf = $requestReasonDefaultOf->first();
            if($requestReasonDefaultOf->defaultOf == 1)
            $changable = 1;
        }
        if($requestReasonID == '1' || $changable == '1'){
            
            if($shiftType == '1'){
                
                $requestValidation = [
                    'businessUnitID' => 'required',
                    'lastMinuteStaffID' => 'required',
                    'timeOfCallMade' => 'required',
                    'vacancyReasonID' => 'required',
                    'requestReasonID' => 'required',
                    'requiredStaffCategoryID' => 'required',
                    'numberOfOffers' => 'required',
                    'staffingStartDate' => 'required',
                    'customShiftStartTime' => 'required',
                    'customShiftEndTime' => 'required'
                ];
                
                
                
            }else{
                
                $requestValidation = [
                    'businessUnitID' => 'required',
                    'lastMinuteStaffID' => 'required',
                    'timeOfCallMade' => 'required',
                    'vacancyReasonID' => 'required',
                    'requestReasonID' => 'required',
                    'requiredStaffCategoryID' => 'required',
                    'numberOfOffers' => 'required',
                    'staffingStartDate' => 'required',
                    'staffingShiftID' => 'required'
                ];
            }
            
            
        }else{
            if($shiftType == '1'){
            
                
                $requestValidation = [
                    'businessUnitID' => 'required',
                    'requestReasonID' => 'required',
                    'requiredStaffCategoryID' => 'required',
                    'numberOfOffers' => 'required',
                    'staffingStartDate' => 'required',
                    'customShiftStartTime' => 'required',
                    'customShiftEndTime' => 'required'
                ];
                
                
                
            }else{
                                
                $requestValidation = [
                    'businessUnitID' => 'required',
                    'requestReasonID' => 'required',
                    'requiredStaffCategoryID' => 'required',
                    'numberOfOffers' => 'required',
                    'staffingStartDate' => 'required',
                    'staffingShiftID' => 'required'
                ];
                
              }
        }
       
        
            $this->validate($request, $requestValidation);        
        
            $post = new Requestcall;
            $post->businessGroupID = $businessGroupID;
            $post->businessUnitID = $businessUnitID;
            $post->requestReasonID = $requestReasonID;
            
            if($requestReasonID == 1 || $changable == '1'){
                $post->lastMinuteStaffID = $lastMinuteStaffID;
                $post->timeOfCallMade = $timeOfCallMade;
                $post->vacancyReasonID = $vacancyReasonID; 
            }
           
            if(count($requiredStaffCategoryID) > 0){
              $requiredStaffCategoryIDs = implode(',', $requiredStaffCategoryID);  
              $post->requiredStaffCategoryID = $requiredStaffCategoryIDs;  
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
            
            
            if($staffingShiftID > 0 && $shiftType != '1'){
                $getShiftTiming = DB::table('staffing_shiftsetup')
                        ->select('startTime','endTime')->where([['id', '=', $staffingShiftID]])->first();

                $shiftStartTimeForPartial = $getShiftTiming->startTime;
                $shiftEndTimeForPartial = $getShiftTiming->endTime;
                
            } else{
                $shiftStartTimeForPartial = $request->customShiftStartTime;
                $shiftEndTimeForPartial = $request->customShiftEndTime;
            }
            
            
            $shiftStartTime = $staffingStartDate." ".(date("g:i A",strtotime($shiftStartTimeForPartial)));
            $shiftEndTime = $staffingEndDate." ".(date("g:i A",strtotime($shiftEndTimeForPartial)));


            if(strtotime($shiftStartTime) > strtotime($shiftEndTime)){
                $staffingEndDate = (date("Y-m-d",strtotime($staffingEndDate . " +1 day"))); 
            }
                    
            
            $post->staffingEndDate = $staffingEndDate;
            
            $post->staffingShiftID = $staffingShiftID?$staffingShiftID:0;
            $post->notes = $notes;
            $post->ownerID = $ownerID;
            $post->updatedBy = $updatedBy;
            $post->approvedBy = $approvedBy;
            $post->postingStatus = $postingStatus;
            
            
            if($request->staffingCloseTime > 0){
                $startTimeOfShiftForClosing = date("Y-m-d H:i:s",strtotime($staffingStartDate." ".$shiftStartTimeForPartial));
                $minuteToAdd = (string)$request->staffingCloseTime;
                $minutes_to_add = $minuteToAdd;

                $time = new \DateTime($startTimeOfShiftForClosing);
                $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));

                $stamp = $time->format('Y-m-d H:i:s');


                $post->closingTime = $stamp;
                        
            }else{
                $post->closingTime = date("Y-m-d H:i:s",strtotime($staffingStartDate." ".$shiftStartTimeForPartial));
            }
            
            
            
            /* Offer Algorithm */
            $post->offerAlgorithmID = $request->offerAlgorithmID?$request->offerAlgorithmID:0;
            /* Offer Algorithm */
            
            
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
            
            
            
          $androidDevicesArray = array();
          $iosDevicesArray = array();
          if(Auth::user()->role == 4 && Auth::user()->needApproval == 1 && $post->approvedBy == 0){
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
                            $pushMessage = $this->getRequestInformationForPush($post);
                            /* Get Push Message */

                            $msg_payload = array (
                            'mtitle' => Auth::user()->firstName." ".Auth::user()->lastName." created New staffing request.",
                            'mdesc' => $pushMessage,
                            'notificationStatus' => 1,
                            'requestID' => $post->id
                            );

                            $msg_payloadAndroid = array (
                            'mtitle' => Auth::user()->firstName." ".Auth::user()->lastName." created New staffing request.",
                            'mdesc' => $pushMessage,
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
                            
                            $this->sendPushNotificationToEndUsers(Auth::user(), 
                                $businessUnitID, 
                                $businessGroupID, 
                                $post, 
                                $requiredStaffCategoryIDs, 
                                $staffingStartDate, 
                                $staffingShiftID, 
                                $requiredExperiencedLevel);
                            
                            $this->createLogForNewRequest($post);
                        } 
            
          return redirect(Config('constants.urlVar.home'))->with('success','Staffing Request created successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewStaffingRequest'))->with('error','Failed to create Staffing request.');  
        }
        
        
    }
    
    
    public function createLogForNewRequest($post){ /*To send notification using CRON */
            
            $requestLog = new RequestLog;
            $requestLog->requestID = $post->id;
            $requestLog->notificationStatus = 0;
            $requestLog->save();
            
    }
    
      
    public function postDetail($id){
        
        if(Auth::user()->role == 0){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $post = Requestcall::find($id); 
        
        $requestPosts = DB::table('staffing_staffrequest')
                ->select(
                        'staffing_staffrequest.id AS postID',
                        'staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.closingTime',
                        'staffing_staffrequest.cancelReason',
                        'staffing_staffrequest.cancelledBy',
                        'staffing_staffrequest.approvedBy',
                        'staffing_staffrequest.postingStatus',
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
                ->leftJoin('staffing_users AS u', 'u.id', '=', 'staffing_staffrequest.lastMinuteStaffID')
                ->leftJoin('staffing_offeralgorithm', 'staffing_offeralgorithm.id', '=', 'staffing_businessunits.offerAlgorithmID')
                ->where([
                            ['staffing_staffrequest.id','=', $post->id]
                                ])
                ->first();
        
        
                $requestPosts->cancelledByUserName = '';
                if($requestPosts->postingStatus == 4){//Manually Cancelled
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


                           $requestPosts->cancelledByUserName =  $cancelledUser->firstName." ".$cancelledUser->lastName." (".$cancelledUserRole.")";
                        }
                    }
                }
                
                
                if($requestPosts->postingStatus == 2){//Disapproved/Declined
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


                           $requestPosts->cancelledByUserName =  $cancelledUser->firstName." ".$cancelledUser->lastName." (".$cancelledUserRole.")";
                        }
                    }
                }
        
        
                if($requestPosts->closingTime == NULL || $requestPosts->closingTime == '')
                    $requestPosts->closingTime = $requestPosts->staffingStartDate;
        
        
                /* Get Total Responded People */  
                      $respondedPeopleLists = DB::table('staffing_shiftoffer')
                ->select('*')->where([['requestID','=',$requestPosts->postID]])->get();      
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
                      
                   /* Check Admin Has Offer to End-User And Is Someone Decline/Accept */ 
//                      $offerConfirmation = DB::table('staffing_shiftoffer')
//                        ->select('')
//                        ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
//                        ->where([
//                            ['staffing_shiftoffer.requestID','=',$requestPosts->postID]
//                            ])->get();      
                   /* Check Admin Has Offer to End-User And Is Someone Decline/Accept */  
                      
        
        
        if(!$post){
           return redirect(Config('constants.urlVar.home'))->with('error','The page you are requested, not found.');   
        }
        
        
        $respondedUsers = $this->respondedUsers(Auth::user()->id, $requestPosts->postID);
        //echo '<pre>';print_r($respondedUsers);die;
        return view('calls.detail', [
            'requestPost' => $requestPosts,
            'respondedPeopleLists' => $respondedPeopleLists,
            'respondedFullShiftPeopleCount' => $respondedFullShiftPeopleCount,
            'respondedPartialShiftPeopleCount' => $respondedPartialShiftPeopleCount,
            'respondedUsers' => $respondedUsers
                ]);
    } 
    
    
    
    public function respondedUsers($userID, $requestID){
        $responseJson = array();
        $requestType = 0;//1=>Full,2=>Partial,0=>All
        if($userID){

            $id = $userID;
            $user = User::find($id);
            $userID = $user->id;

            if ($user) {

                $requestPostID = $requestID;
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
                        
                    if($requestType == 1){//FULL SHIFT
                        if($algorithmName == 'simple'){
                            
                            
                            
                            /* Full Shift No-OverTime */
                            $fullShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                1, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
                                $isOfferAccepted,
                                $offerConfirmedUser
                            );
                     
                            $responseJson['data'][0] = array(
                               'name' => "Open"  ,
                               'data' => $allUsers  
                            );  
                        } 
                        
                    }elseif($requestType == 2){//PARTIAL SHIFT
                        
                         /* Partial Shift No-OverTime */
                        if($algorithmName == 'simple'){
                            $partialShiftNoOvertimeUsers = $this->getRespondedUsersFollowedByAlgorithm(
                                3, 
                                $post,  
                                $algorithmName,  
                                $complexPoolOrder, 
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                                $requestType, 
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
                        return $responseJson;   



                    }else{
                      return array();   
                    }

                } else{
                  //return response()->json(['status'=>'0','message'=>'Request call not found.'], 500);   
                  return array();       
                }  


                }else{
                  return array();     
                }

            } else {
              return array();      
            }
        }else{
            return array();       

        }
    } 
        
        
    public function getRespondedUsersFollowedByAlgorithm($sorting = 0, 
                                $post, 
                                $algorithmName,  
                                $complexPoolOrder, 
                                $requestType, 
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
            if($requestType == 1){ 
                 $respondUsersSql->where('staffing_shiftoffer.responseType','=', 0);  
                 $respondUsersSql->orderBy('staffing_shiftoffer.id', 'ASC');
                 $respondUsersSql->orderBy('staffing_shiftoffer.responseType', 'DESC');  
            }else if($requestType == 2){
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
    
    
    
    public function ajaxRespondedPeopleList($requestID){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'userName', 
            1 => 'businessUnit',
            2=> 'role',
            3=> 'name',
            4=> 'skills',
            6=> 'email',
            5=> 'phone'
        );
        
        $postInfo = DB::table('staffing_staffrequest')
           ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
            ->select('staffing_staffrequest.staffingStartDate',
                        'staffing_staffrequest.staffingEndDate',
                        'staffing_staffrequest.shiftType',
                        'staffing_staffrequest.postingStatus',
                        'staffing_shiftsetup.startTime',
                        'staffing_shiftsetup.endTime',
                        'staffing_staffrequest.customShiftStartTime',
                        'staffing_staffrequest.customShiftEndTime')->where([
                            ['staffing_staffrequest.id','=',$requestID]
                            ])->first();
                
        
        
        $totalFiltered = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestID]])->count();
        
        $sql = DB::table('staffing_users')
                                  
            ->join('staffing_shiftoffer','staffing_shiftoffer.userID', '=', 'staffing_users.id')
          ->leftJoin('staffing_shiftconfirmation','staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')  
                ->leftJoin('staffing_requestpartialshifts', 
                        'staffing_requestpartialshifts.id', 
                        '=', 'staffing_shiftoffer.partialShiftTimeID')   
          ->select(
              'staffing_shiftoffer.userID AS responseUserID',
              'staffing_shiftoffer.responseType',
              'staffing_shiftoffer.overTime',
              'staffing_shiftoffer.partialShiftTimeID',
              'staffing_users.profilePic',
              'staffing_users.skills',
              'staffing_shiftconfirmation.offerResponse',
              'staffing_shiftconfirmation.id AS offerID',
              'staffing_requestpartialshifts.partialShiftStartTime',
              'staffing_requestpartialshifts.partialShiftEndTime',
          DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS userName")
          );

          $sql->where('staffing_shiftoffer.requestID','=', $requestID); 

          
          /* Active Requests */
          
       
        if( !empty($requestData['search']['value']) ) {
            $sql->where(function ($query) use ($requestData) {
                $query->orWhere('staffing_users.userName', 'LIKE', $requestData['search']['value'].'%');
                $query->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
            });
            
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        //$sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        $sql->orderBy('staffing_users.userName', 'ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        $waitList = false;
        
        foreach($results as $result){
            
            
            
            $profilePic = $result->profilePic?url('public/'.$result->profilePic):url('/assets/img/avatar1.jpg');
                            
            /* Get User Skills */
            $skillsArr = array();
            $skillsArr = $result->skills?unserialize($result->skills):array();

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
            
                
        /* Get User Assigned Business Unit */
                $userBusinessUnits = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                 ->select(
                'staffing_businessunits.id',
                'staffing_businessunits.unitName')->where([['staffing_usersunits.userID','=',$result->responseUserID]])
                ->get();
              $usersUnits = array();
                if($userBusinessUnits){
                    foreach($userBusinessUnits as $userBusinessUnit){
                        $usersUnits[] = $userBusinessUnit->unitName;
                    }
                }
                
        /* Get User Assigned Business Unit */
                
                
                if($result->responseType == 0){
                    if($postInfo->shiftType == 1){
                    $shiftTimeForUser =  date("g:i A",strtotime($postInfo->customShiftStartTime))." - "
                            .date("g:i A",strtotime($postInfo->customShiftEndTime));
                    
                    
                    $shiftTiming = date("l M d, Y",strtotime($postInfo->staffingStartDate))." - ".$shiftTimeForUser;
                    
                    }else{
                     $shiftTimeForUser =  date("g:i A",strtotime($postInfo->startTime))." - " 
                        .date("g:i A",strtotime($postInfo->endTime)) ;
                    
                    
                    $shiftTiming = date("l M d, Y",strtotime($postInfo->staffingStartDate))." - ".$shiftTimeForUser;
                    
                    
                    }

               
                }elseif($result->responseType == 1){
               
                 $shiftTimeForUser =  date("g:i A",strtotime($result->partialShiftStartTime))." - " 
                   .date("g:i A",strtotime($result->partialShiftEndTime)) ;
                 
                    
                    $shiftTiming = date("l M d, Y",strtotime($postInfo->staffingStartDate))." - ".$shiftTimeForUser;
                }
                
                
                /* Check Any Offer is Already Running Or Not Any OF User */
                if($result->offerResponse == '0')
                    $waitList = false;//true;//No need To Show Waitlist As admin can send another Offer
                /* Check Any Offer is Already Running Or Not Any OF User */
                
                              
                $nestedData=array(); 
                
                if($result->responseType == 0)
                    $responseStatus = "Full Shift</br>(".$shiftTimeForUser.")";
                if($result->responseType == 1)
                    $responseStatus = "Partial Shift</br>(".$shiftTimeForUser.")";
                if($result->responseType == 2)
                    $responseStatus = "Decline";

                $nestedData[] = $result->userName;
                $nestedData[] = $usersUnits?'<span style="color:#42a5f5;">'.(implode(', ',$usersUnits)).'</span>':'<span style="color:#f00;">Not alloted yet</span>';
                $nestedData[] = '<img class="img_style" src="'.$profilePic.'" width="120" height="120"/>';
                $nestedData[] = $userSkills?implode(', ',$userSkills):'Not defined';
                $nestedData[] = $responseStatus;
                
                
                if($postInfo->postingStatus == '2' || 
                        $postInfo->postingStatus == '4' || $postInfo->staffingStartDate < date("Y-m-d")){
                    $alertMsg = "Posting is closed, You can not perform any action.";
                    $nestedData[] = ($result->offerResponse == '0')?
                        '<span style="color:#008000;"><i class="fa fa-check"></i>Offer sent</span>':(($result->offerResponse == '2')?
                        '<span style="color:#f00;">Declined</span>':(($result->offerResponse == '1')?
                        '<span style="color:#008000;"><i class="fa fa-check"></i>Offer Accepted</span>':($waitList?'<span style="color:#f00;">Waitlist</span>':
                        '<button onclick="javascript:alert(\''.$alertMsg.'\')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">Make Offer</button>'))); 
                }else{
                
                    $nestedData[] = ($result->offerResponse == '0')?
                    '<span style="color:#008000;"><i class="fa fa-check"></i>Offer sent</span>':(($result->offerResponse == '2')?
                    '<span style="color:#f00;">Declined</span>':(($result->offerResponse == '1')?
                    '<span style="color:#008000;"><i class="fa fa-check"></i>Offer Accepted</span>':($waitList?'<span style="color:#f00;">Waitlist</span>':
                    '<button onclick="makeOffer(\''.$result->responseUserID.'\',\''.$requestID.'\',\''.$result->userName.'\',\''.$shiftTiming.'\')" style="cursor: pointer;" type="button" class="btn btn-sm btn-outline-info mb-10">Make Offer</button>')));
                }
                
                $data[] = $nestedData; 
                
                
        }
        
        $json_data = array(
        "draw"            => intval( $requestData['draw'] ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
        "recordsTotal"    => intval( $totalData ),  // total number of records
        "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
        "data"            => $data   // total data array
        );
        
       return response()->json($json_data); 
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
    
    
    public function sendPushNotificationToEndUsers($user, 
                        $businessUnitID, 
                        $businessGroupID, 
                        $post, 
                        $requiredStaffCategoryID, 
                        $staffingStartDate, 
                        $staffingShiftID, 
                        $requiredExperiencedLevel){
        
        $androidDevicesArray = array();
        $iosDevicesArray = array();
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
    
    public function approvePost($id, $approveStatus = 4, $returnUrl = 'home', $cancelReason = NULL){
        //4 => Closed/Cancelled
        //1 => Open/Approved
        
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $success = false;
        $post = Requestcall::find($id);
        if(!$post){
           return redirect(Config('constants.urlVar.home'))->with('error','The request can not be completed.');   
        }
        
        
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
                }
          }
          /* Send Push To ADmin */  
        
        
        if($approveStatus == '1' && ($post->postingStatus != 1 || $post->postingStatus != 3)){
           $post->postingStatus = 1; //Approved
           $post->approvedBy = Auth::user()->id; //Approved By
           $post->save();
           $success = true;
           
           
           
            /* Get Push Message */
            $pushMessage = $this->getRequestInformationForPush($post);
            /* Get Push Message */
           
           /* Send Push To ADmin */      
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
            
            /* Send Push Notification To Staff Members that Meet Criteria *//* Send Push Notification To Staff Members that Meet Criteria */
            $businessUnitID = $post->businessUnitID;
            $businessGroupID = $post->businessGroupID;
            $requiredStaffCategoryID = $post->requiredStaffCategoryID;
            $staffingStartDate = $post->staffingStartDate;
            $staffingShiftID = $post->staffingShiftID;
            $requiredExperiencedLevel = $post->requiredExperiencedLevel;

            $this->sendPushNotificationToEndUsers($postOwnerAdminInfo, 
                $businessUnitID, 
                $businessGroupID, 
                $post, 
                $requiredStaffCategoryID, 
                $staffingStartDate, 
                $staffingShiftID, 
                $requiredExperiencedLevel);  
          
            $this->createLogForNewRequest($post);  
            /* Send Push Notification To Staff Members that Meet Criteria */
           
           
           
        }
        
        if($approveStatus == '4'){
           $post->postingStatus = 2;  //Disaaproved/Cancelled
           $post->approvedBy = Auth::user()->id; //Approved/Cancelled By
           $post->save();
           $success = true;
           
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
           
        }
        
        
        if($approveStatus == '5'){
           $post->postingStatus = 4;  //Closed/Cancelled
           $post->cancelReason = $cancelReason;  //Cancel Reason
           $post->cancelledBy = Auth::user()->id; //Approved/Cancelled By
           $post->save();
           $success = true;
           
           /* Get Push Message */
            $pushMessage = $this->getRequestInformationForPush($post);
            /* Get Push Message */
           
           /* Send Push To ADmin */      
            $msg_payload = array (
             'mtitle' => 'Staffing request cancelled.',
             'mdesc' => $pushMessage,
             'notificationStatus' => 7,//cancelled
             'requestID' => $post->id
             );

            $msg_payloadAndroid = array (
             'mtitle' => 'Staffing request cancelled.',
             'mdesc' => $pushMessage,
             'notificationStatus' => 7,//cancelled
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
            
            /* Send Push Notification To Whom End-Users who already working or scheduled */
            $getConfirmedPostsUsers = DB::table('staffing_shiftoffer')
                ->select('staffing_shiftoffer.userID','staffing_users.pushNotification')
                ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_shiftoffer.userID')
                ->where([
                    ['staffing_shiftoffer.requestID','=',$post->id],
                    ['staffing_shiftconfirmation.offerResponse','=',1]
                        ])->get();
            
            if(count($getConfirmedPostsUsers) > 0){
                         $iosDevicesArray = array();
                         $androidDevicesArray = array();
                         
                foreach($getConfirmedPostsUsers as $getConfirmedPostsUser){         
                       /* Send Push Notifications */
                        if($getConfirmedPostsUser->pushNotification == '1'){
                            $getDevices = DB::table('staffing_devices')
                                ->select('deviceID','deviceType')
                                ->where([['userID','=',$getConfirmedPostsUser->userID]])->get();

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

                            }
                        }
                       /* Send Push Notifications */
                }
                
                
                    /* Get Push Message */
                    $pushMessage = $this->getRequestInformationForPush($post);
                    /* Get Push Message */


                    $msg_payload = array (
                    'mtitle' => "Staffing request cancelled which is scheduled to you.",
                    'mdesc' => $pushMessage,
                    'notificationStatus' => 7,
                    'requestID' => $post->id
                    );

                    $msg_payloadAndroid = array (
                    'mtitle' => "Staffing request cancelled which is scheduled to you.",
                    'mdesc' => $pushMessage,
                    'notificationStatus' => 7,
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
            
            /* Send Push Notification To Whom End-Users who already working or scheduled */
            
            
            
            
        }
        
        if($success && $approveStatus == '1'){
            if($returnUrl == 'home')
           return redirect(Config('constants.urlVar.home'))
                ->with('success','staffing request approved successfully.'); 
            else
           return redirect(Config('constants.urlVar.staffingPostDetail').$post->id)
                    ->with('success','staffing request approved successfully.');      
        }elseif($success && $approveStatus == '4'){
             if($returnUrl == 'home')
           return redirect(Config('constants.urlVar.home'))
                 ->with('success','staffing request disapproved.'); 
            else
           return redirect(Config('constants.urlVar.staffingPostDetail').$post->id)
                    ->with('success','staffing request disapproved.');   
        }elseif($success && $approveStatus == '5'){
             if($returnUrl == 'home')
           return redirect(Config('constants.urlVar.home'))
                 ->with('success','staffing request cancelled successfully.'); 
            else
           return redirect(Config('constants.urlVar.staffingPostDetail').$post->id)
                    ->with('success','staffing request cancelled successfully.');   
        }else{
             if($returnUrl == 'home')
           return redirect(Config('constants.urlVar.home'))
                 ->with('error','The request can not be completed.'); 
            else
           return redirect(Config('constants.urlVar.staffingPostDetail').$post->id)
                    ->with('error','The request can not be completed.');   
        }
        
          
        
    }
    
        
        
        
        
    function makeOfferToUser(Request $request){
        if(Auth::user()->role == '2' || Auth::user()->role == '3' || Auth::user()->role == '4'){
            $request->session()->flash('success', 'Offer successfully sent to user.');
            
           $requestPostID =  $request->requestID;           
           $toUserID =  $request->toUserID;           
           $userID = Auth::user()->id;
           
           $toUserInfo = User::find($toUserID);
           $post = Requestcall::find($requestPostID);
           
           $checkAlready = DB::table('staffing_shiftoffer')
                ->select(
                        'id','userID','inWaitList'
                )->where([['requestID','=',$requestPostID],
                    ['userID','=',$toUserID]])->first();
        
            if(count($checkAlready) > 0){
                
               $checkOffer = DB::table('staffing_shiftconfirmation')
                ->select(
                        'id','shiftOfferID','offerResponse','created_at'
                )->where([['shiftOfferID','=',$checkAlready->id]])->first(); 
                
                if(count($checkOffer) > 0){
                    
                        if($checkOffer->offerResponse == 0){

                            /* If User was on waitlist then Update status for second offer */
                            if($checkAlready->inWaitList == 1){
                                $updateWaitlistStatus = DB::table('staffing_shiftoffer')
                                ->where([['id', $checkAlready->id],['userID', $toUserID]])
                                ->update(['inWaitList' => 3]);
                            }
                            /* If User was on waitlist then Update status for second offer */

                            return response()->json([
                             'status'=>1,
                             'msg'=>'Offer sent.'    
                             ]);                                          

                        }
                        if($checkOffer->offerResponse == 1){

                           return response()->json([
                             'status'=>1,
                             'msg'=>'Offer accepted by user.'    
                         ]);                                            

                        }
                        if($checkOffer->offerResponse == 2){

                            return response()->json([
                             'status'=>1,
                             'msg'=>'Offer declined by user.'    
                         ]);    


                        }
                    
                   
                }else{
                   $shiftOffer = new OfferConfirmation();
                    $shiftOffer->shiftOfferID = $checkAlready->id;
                    $shiftOffer->offerResponse = 0;
                     if($shiftOffer->save()){ 
                         
                        /* If User was on waitlist then Update status for second offer */
                        if($checkAlready->inWaitList == 1){
                            $updateWaitlistStatus = DB::table('staffing_shiftoffer')
                            ->where([['id', $checkAlready->id],['userID', $toUserID]])
                            ->update(['inWaitList' => 3]);
                        }
                        /* If User was on waitlist then Update status for second offer */

                                             
                         
                         
                         $iosDevicesArray = array();
                         $androidDevicesArray = array();
                       /* Send Push Notifications */
                        if($toUserInfo->pushNotification == '1'){
                            $getDevices = DB::table('staffing_devices')
                                ->select('deviceID','deviceType')
                                ->where([['userID','=',$toUserID]])->get();

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
                         
                         
                         return response()->json([
                             'status'=>1,
                             'msg'=>'Offer sent.'    
                         ]);
                     }else{
                      return response()->json([
                             'status'=>0,
                             'msg'=>'Failed to make offer.'    
                         ]);     
                     }  
                }

            }else{
              
                 return response()->json([
                        'status'=>0,
                        'msg'=>'You can not sent offer to this user.'    
                    ]); 
            }
           
           
        }else{
                return response()->json([
                    'status'=>0,
                    'msg'=>'Failed to make offer.'    
                ]);  
        }
        
    }
    
    
    
    
    /* Staffing History */
    
    public function staffingHistory(){
        
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
            
            
            if(Auth::user()->role != 2){
        
                $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                    'staffing_businessunits.id',
                    'staffing_businessunits.unitName'
                );
            
                
                if(Auth::user()->role == 0){
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
                    $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                }
                else {
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
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
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
        
            $requestPostsSql->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d")); 
            if(Auth::user()->role != 2){
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            }
            else{
              $requestPostsSql->where('staffing_staffrequest.businessGroupID','=', Auth::user()->businessGroupID);   
            }
            
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            
            //$requestPostsSql->limit(12)->offset(0);
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            
            $requestPosts = $requestPostsSql->get();
            
            /* Active Requests */
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            if(Auth::user()->role != 2){
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            }
            else{
              $totalOpenRequestsCountSql->where('staffing_staffrequest.businessGroupID','=', Auth::user()->businessGroupID);   
            }
            
            
            
            
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count(); 
            
            if(Auth::user()->role == 2){
               return view('staffingHistory.staffing-history', 
                ['requestPosts' => $requestPosts,
                'groupInfo' => $groupInfo  ,
                'totalOpenRequestsCount' => $totalOpenRequestsCount  
                ]); 
            }else{
            
            return view('staffingHistory.staffing-history', 
                ['requestPosts' => $requestPosts,
                'groupInfo' => $groupInfo  ,
                'unitInfo' => $unitInfo  ,
                'totalOpenRequestsCount' => $totalOpenRequestsCount  
                ]);
            }
            
    }
        
    
    /* Staffing History */  
    
    
    
    /* Cancelled Requests */  
    
    public function cancelledRequests(){
        
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
            
            
            if(Auth::user()->role != 2){
        
                $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                    'staffing_businessunits.id',
                    'staffing_businessunits.unitName'
                );
            
                
                if(Auth::user()->role == 0){
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
                    $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
                }
                else {
                    $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
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
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
        
            //$requestPostsSql->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d")); 
            if(Auth::user()->role != 2){
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            }
            else{
              $requestPostsSql->where('staffing_staffrequest.businessGroupID','=', Auth::user()->businessGroupID);   
            }
            
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [2,4]);
            
            //$requestPostsSql->limit(12)->offset(0);
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            
            $requestPosts = $requestPostsSql->get();
            
            /* Active Requests */
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            if(Auth::user()->role != 2){
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            }
            else{
              $totalOpenRequestsCountSql->where('staffing_staffrequest.businessGroupID','=', Auth::user()->businessGroupID);   
            }
            
            
            
            
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count(); 
            
            if(Auth::user()->role == 2){
               return view('cancelledRequests.cancelled-requests', 
                ['requestPosts' => $requestPosts,
                'groupInfo' => $groupInfo  ,
                'totalOpenRequestsCount' => $totalOpenRequestsCount  
                ]); 
            }else{
            
            return view('cancelledRequests.cancelled-requests', 
                ['requestPosts' => $requestPosts,
                'groupInfo' => $groupInfo  ,
                'unitInfo' => $unitInfo  ,
                'totalOpenRequestsCount' => $totalOpenRequestsCount  
                ]);
            }
            
    }
        
    
    /* Cancelled Requests */ 
    
    
   /* Canceled Shifts For End User*/  
    
    public function usersCancelledRequests(){
        
        if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
            $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                    'staffing_businessunits.id',
                    'staffing_businessunits.unitName'
                );
        
            if(Auth::user()->role == 0){
                $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
                $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
            }
            else {
                $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
            }
            $unitInfo = $unitInfoSql->first();
            
                        $requestPosts = array();
                        $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',Auth::user()->id],
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
                        'staffing_shiftoffer.userID AS responseUserID',
                        'staffing_shiftoffer.id AS userResponseID',
                        'staffing_shiftoffer.responseType',
                        'staffing_shiftoffer.overTime',
                        'staffing_shiftoffer.partialShiftTimeID',
                        'staffing_requestpartialshifts.partialShiftStartTime',
                        'staffing_requestpartialshifts.partialShiftEndTime',
                        'staffing_shiftconfirmation.id AS confirmOfferID',
                        'staffing_shiftconfirmation.offerResponse AS confirmationOfferStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
                            
                            

                                $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                                if(count($postIDs) > 0){
                                $requestPostsSql->whereIn(
                                   'staffing_staffrequest.id',$postIDs);
                                }


                                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 

                                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));

                                $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                                /* Active Requests */
                                $requestPosts = $offers = $requestPostsSql->get(); 
                        }
                   }
            
        
            
            
                return view('cancelledRequests.user-cancelled-shifts', 
                    ['requestPosts' => $requestPosts,
                    'groupInfo' => $groupInfo  ,
                    'unitInfo' => $unitInfo   
                    ]);
            
    }
        
    
    /* Canceled Shifts For End User*/  
    
    
    
    
    /* Staffing History For End User*/
    
    public function userArchivedShifts(){
        
        if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
            $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                    'staffing_businessunits.id',
                    'staffing_businessunits.unitName'
                );
        
            if(Auth::user()->role == 0){
                $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
                $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1); 
            }
            else {
                $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
            }
            $unitInfo = $unitInfoSql->first();
            
                        $requestPosts = array();
                        $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',Auth::user()->id],
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
                        'staffing_shiftoffer.userID AS responseUserID',
                        'staffing_shiftoffer.id AS userResponseID',
                        'staffing_shiftoffer.responseType',
                        'staffing_shiftoffer.overTime',
                        'staffing_shiftoffer.partialShiftTimeID',
                        'staffing_requestpartialshifts.partialShiftStartTime',
                        'staffing_requestpartialshifts.partialShiftEndTime',
                        'staffing_shiftconfirmation.id AS confirmOfferID',
                        'staffing_shiftconfirmation.offerResponse AS confirmationOfferStatus',
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS staffOwner")
                        );
                            
                            

                                $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                                if(count($postIDs) > 0){
                                $requestPostsSql->whereIn(
                                   'staffing_staffrequest.id',$postIDs);
                                }


                                $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 

                                $requestPostsSql->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));

                                $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                                /* Active Requests */
                                $requestPosts = $offers = $requestPostsSql->get(); 
                        }
                   }
            
        
            
            
                return view('staffingHistory.user-archived', 
                    ['requestPosts' => $requestPosts,
                    'groupInfo' => $groupInfo  ,
                    'unitInfo' => $unitInfo   
                    ]);
            
    }
        
    
    /* Staffing History For End User*/  
    
    
    
    
}