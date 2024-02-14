<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Businessunit;
use App\Userunit;
use App\ShiftOffer;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;

class StaffProfileController extends Controller
{   
    
        public function index()
    {   

                $users = DB::table('staffing_users')
                    ->select(
                        'staffing_users.id',
                        'staffing_users.userName', 
                         DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS name"),
                        'staffing_users.role', 
                        'staffing_users.profilePic', 
                        'staffing_users.email', 
                        'staffing_users.phone', 
                        'staffing_users.skills', 
                        'staffing_users.status')->where([
                            ['businessGroupID','=',Auth::user()->businessGroupID],
                            ['id','!=',Auth::user()->id]])
                        ->whereIn('role',['0','3','4'])
                ->get();
                
                $groupInfo = DB::table('staffing_groups')
                        ->select(
                            'id',
                            'groupName',
                            'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
                $allBusinessUnitsOfGroup = DB::table('staffing_businessunits')
                        ->select(
                            'staffing_businessunits.id',
                            'staffing_businessunits.unitName'
                        )->where([['staffing_businessunits.businessGroupID','=',Auth::user()->businessGroupID]])->get();
        
            
                
                
                $unitInfo = array();
                if(Auth::user()->role == 3){
                        $unitInfoSql = DB::table('staffing_businessunits')
                        ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                        ->select(
                                'staffing_businessunits.id',
                                'staffing_businessunits.unitName'
                                );


                        $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id); 
                        //$unitInfoSql->where('staffing_usersunits.primaryUnit','=',0); 
                         $unitInfo = $unitInfoSql->first();

                }
           
        
        
        return view('staffprofiles.show', ['users' => $users,
            'unitInfo'=>$unitInfo,
            'allBusinessUnitsOfGroup'=> $allBusinessUnitsOfGroup,
            'groupInfo'=>$groupInfo]);
    }
    
    
    public function ajaxStaffProfileList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0=> 'name',
            1=> 'role',
            2=> 'units',
            3=> 'skills',
            4=> 'email',
            5=> 'phone',
            6=> 'id'
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
        
        if(Auth::user()->role == 2)
         $sql->whereIn('role',['0','3','4']);
        else
          $sql->whereIn('role',['0','4']); 
        
        if(Auth::user()->role == 3){//Super-Admin
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
            $userUnitID = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
                ->select('staffing_businessunits.id AS businessUnitID')
                ->where([
                    ['staffing_usersunits.userID','=',Auth::user()->id]])
                 ->first();   
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
            
            $unitUsers = DB::table('staffing_usersunits')
                ->select('userID')
                ->where([
                    ['businessUnitID','=',$userUnitID->businessUnitID]])
                 ->get(); 
            
            $associatedUsers = array();
            
            foreach($unitUsers as $unitUser){
                $associatedUsers[] = $unitUser->userID;
            }
            
            $sql->whereIn('id',$associatedUsers);
            
        }
        
        
       
        if( !empty($requestData['search']['value']) ) {
            $sql->where(function ($query) use ($requestData) {
                $searchStr = strtolower($requestData['search']['value']);
                if( (substr( $searchStr, 0, 3 ) === "end") ){
                  $query->orWhere('staffing_users.role', '=', 0);  
                }
                if( (substr( $searchStr, 0, 4 ) === "admin")){
                  $query->orWhere('staffing_users.role', '=', 4);  
                }
                if((substr( $searchStr, 0, 4 ) === "super") ){
                  $query->orWhere('staffing_users.role', '=', 3);  
                }
                
                $query->orWhere('staffing_users.email', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.phone', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
            });
            
            
            
        } 
        
        
        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        //$sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        $sql->orderBy('firstName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        if(Auth::user()->role == 3 && !$associatedUsers){//Super-Admin
           $results = array(); 
        }
        
        
        
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
               
                              
                $nestedData=array(); 

                $htmlAction = '<a href="'.url(Config('constants.urlVar.staffProfileDetail').$result->id).'" class="btn btn-sm btn-outline-info">Detail</a>';
                $nestedData[] = $result->name;
                $nestedData[] = ($result->role == '3')?'<span class="badge badge-danger">Super Admin</span>':(($result->role == '4')?'<span class="badge badge-info">Admin</span>':'<span class="badge badge-warning">End User</span>');
                $nestedData[] = $usersUnits?implode(', ',$usersUnits):'Not Assigned';
                $nestedData[] = $userSkills?implode(', ',$userSkills):'Not defined';
                $nestedData[] = $result->email;
                $nestedData[] = $result->phone;
                $nestedData[] = $htmlAction;
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
    
    

    
    public function staffProfileDetail($staffID){
        
       
        $userSkills = array();
        
        if(Auth::user()->role == 2 || Auth::user()->role == 3){
            $userInfo = DB::table('staffing_users')
                ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_users.businessGroupID')   
                ->select(
                        'staffing_users.*',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode'
                        )->where([
                                ['staffing_users.id','=',$staffID]
                            ])->first();
        
            if($userInfo){
                
                
                /* Get User's Business Unit */ 
                $userBusinessUnits = array();
                $usersUnits = DB::table('staffing_usersunits')
                 ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')       
                 ->select(
                     'staffing_usersunits.businessUnitID',
                     'staffing_businessunits.unitName'
                     )->where([
                             ['staffing_usersunits.userID', '=',$userInfo->id]])->orderBy('staffing_businessunits.unitName','ASC')->get();

                foreach($usersUnits as $usersUnit){
                    $userBusinessUnits[] = $usersUnit->unitName;
                }
                /* Get User's Business Unit */
            
            
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
            $shiftWillingToTake = 0;
            $shiftCovered = 0;
            $lastTimeShiftCancellationCount = 0;
            $lastMinuteShiftCancellations = array();
            $countVarMsg = '';
            $graphData2 = array();
             
            if($userInfo->role == 3){//Super Admin view
              
                $getCreatedRequestsSql = DB::table('staffing_staffrequest')->select(
                        DB::raw(
                                'YEAR(created_at) AS y,'
                                . ' MONTH(created_at) AS m, '
                                . 'COUNT(DISTINCT id) AS totalCount')
                       );

                $getCreatedRequestsSql->whereRaw('YEAR(created_at) = YEAR(CURDATE())');
                $getCreatedRequestsSql->where('ownerID','=',$userInfo->id);
                $getCreatedRequestsSql->groupBy(['y','m']);
                $getCreatedRequests = $getCreatedRequestsSql->get();

                $getApprovedRequestsSql = DB::table('staffing_staffrequest')->select(
                       DB::raw(
                               'YEAR(created_at) AS y,'
                               . ' MONTH(created_at) AS m, '
                               . 'COUNT(DISTINCT id) AS totalCount')
                      );

                $getApprovedRequestsSql->whereRaw('YEAR(created_at) = YEAR(CURDATE())');
                $getApprovedRequestsSql->where('approvedBy','=',$userInfo->id);
                $getApprovedRequestsSql->groupBy(['y','m']);
                 $getApprovedRequests = $getApprovedRequestsSql->get();

                $searchableUsrArr = array();
                $getCreatedRequestsCount = 0;
                foreach($getCreatedRequests as $getCreatedRequest){
                   $searchableUsrArr[$getCreatedRequest->m] = $getCreatedRequest->totalCount;
                   $getCreatedRequestsCount += $getCreatedRequest->totalCount;
                }


                $createdCalls = array();
                $graphData = array();
                for($u = 1;$u<=12;$u++){
                        if(array_key_exists($u, $searchableUsrArr)){
                            $graphData[] = (string)$searchableUsrArr[$u]; 
                        }else{
                            $graphData[] = (string)0; 
                        }
                }
                
                
                /*Get Users monthwise of current year Shift cancellation*/
                     
                $searchableUsrArr2 = array();
                $getApprovedRequestsCount = 0;
                foreach($getApprovedRequests as $getApprovedRequest){
                   $searchableUsrArr2[$getApprovedRequest->m] = $getApprovedRequest->totalCount;
                   $getApprovedRequestsCount += $getApprovedRequest->totalCount;
                }    
                        
                $graphData2 = array();        
                for($m=1;$m<=12;$m++){

                    if(array_key_exists($m, $searchableUsrArr2)){
                        $graphData2[] = (string)$searchableUsrArr2[$m]; 
                    }else{
                        $graphData2[] = (string)0; 
                    }
                }
                
                
                $lastTimeShiftCancellationCount = $getCreatedRequestsCount;
                $lastMinuteShiftCancellations = $graphData;
                $countVarMsg = $getApprovedRequestsCount;
                     
                       
            }else{
                
                /*Get Users monthwise of current year Shift cancellation*/
                $lastTimeShiftCancellationsSql = DB::table('staffing_staffrequest')->select(
                        DB::raw(
                                'YEAR(created_at) AS y,'
                                . ' MONTH(created_at) AS m, '
                                . 'COUNT(DISTINCT id) AS totalCount')
                       );
                
                $lastTimeShiftCancellationsSql->whereRaw('YEAR(created_at) = YEAR(CURDATE())');
                $lastTimeShiftCancellationsSql->where('lastMinuteStaffID','=',$userInfo->id);
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
                            $lastMinuteShiftCancellations[] = $searchableUsrArr[$u]; 
                        }else{
                            $lastMinuteShiftCancellations[] = 0; 
                        }
                }
                /*Get Users monthwise of current year Shift cancellation*/
                
                
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
                $avgTimeShiftCancellationsSql->where('staffing_staffrequest.lastMinuteStaffID','=',$userInfo->id);
                $avgTimeShiftCancellations = $avgTimeShiftCancellationsSql->get();
                        
                $yearVarCount = 0;
                $yearAvgTimeCount = 0;//In minutes
                $yearCancellationTime = 0;
                $graphData2 = array();
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

                        $graphData2[] = round($monthAvgTimeCount, 0);

                        $yearCancellationTime +=  $cancellationTime;
                        $yearVarCount += $monthVarCount;

                    }else{
                      $graphData2[] = 0;  
                    }
                }
                        
                        
                if($yearVarCount > 0)
                   $yearAvgTimeCount = $yearCancellationTime / $yearVarCount;
                /* Get Average Time Shift Cancellation */

                $countVarMsg = round($yearAvgTimeCount, 0).' min (+/- 30 min)';
                if(round($yearAvgTimeCount, 0) > 60)
                $countVarMsg = round($yearAvgTimeCount/60, 0).' Hr (+/- 30 min)';
                /* User's Shift willing to take */  
                 $shiftWillingToTake = ShiftOffer::where([
                     ['userID','=',$staffID],
                     ['responseType','!=',2]
                     ])->count();
                /* User's Shift willing to take */   
                /* User's Shift Covered */  
                $shiftCovered = ShiftOffer::select('staffing_shiftoffer.id')
                        ->join('staffing_shiftconfirmation', 
                                'staffing_shiftconfirmation.shiftOfferID', 
                                '=', 'staffing_shiftoffer.id')
                        ->where([
                    ['staffing_shiftoffer.userID','=',$staffID],
                    ['staffing_shiftoffer.responseType','!=',2],
                    ['staffing_shiftconfirmation.offerResponse','=',1]
                    ])->count();
               /* User's Shift Covered */ 
                
            }

                return view('staffprofiles.detail',['userInfo' => $userInfo,
                    'userSkills' => $userSkills,
                    'userBusinessUnits' => $userBusinessUnits,
                    'lastTimeShiftCancellationCount' => $lastTimeShiftCancellationCount,
                    'lastMinuteShiftCancellations' => json_encode($lastMinuteShiftCancellations),
                    'averageTimeShiftCancellationCount' => (string)$countVarMsg,
                    'averageTimeShiftCancellation' => json_encode($graphData2),
                    'shiftWillingToTake' => $shiftWillingToTake,
                    'shiftCovered' => $shiftCovered]);
            }else{
               return redirect(Config('constants.urlVar.staffProfileList'))->with('error','The page you are looking does not exist.');   
            }
        
        }else{
            return redirect(Config('constants.urlVar.staffProfileList'))->with('error','The page you are looking does not exist.');   
        }
        
    }
    
    
    
}
