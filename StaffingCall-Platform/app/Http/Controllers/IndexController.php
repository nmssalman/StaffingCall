<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Page;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Session;
use myHelper;

class IndexController extends Controller
{   
    
    
    public function changeAdminView(Request $request){
        
        if($request->defaultView > 0){
            Session::put('defaultView','admin');
        }else{
            Session::put('defaultView','end-user');
        }
        return response()->json([
                        'success'=>'1'
                        ]);
    }
    
    
    public function calenderView($businessUnitID = NULL){
        
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
    
     if(Auth::user()->role == 4 || Auth::user()->role == 0 || Auth::user()->role == 3){   
         
         /* For Admin Only */
         
         if(Session::has('defaultView')){
            
         }else{
             Session::put('defaultView',"admin");
         }
         
         
        
        /* For Admin Only */
        
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
                        
            if(Auth::user()->role == 0 || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)){
                
                
                
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
        
                    $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            }else{
                
              
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
        
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            }
            
                
              $postIDs = array();  
              if(Auth::user()->role == 0 || Session::get('defaultView') == 'end-user'){
                  
                  
                  
                  $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',Auth::user()->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                       if(count($postIDs) > 0){
                        $requestPostsSql->whereIn(
                            'staffing_staffrequest.id',$postIDs);
                       }
                   }
                  
              }
                
                
                
            
            $requestPostsSql->orderBy('staffing_staffrequest.created_at', 'DESC');
            /* Active Requests */
            $requestPosts = $requestPostsSql->get();
            
            if((Auth::user()->role == 0  || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)) && count($postIDs) == 0){
                $requestPosts = array();
            }
            
            /* Pending Requests */
            
            $pendingRequestPostsSql = DB::table('staffing_staffrequest')
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
        
            $pendingRequestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
        
            
                $pendingRequestPostsSql->where('staffing_staffrequest.postingStatus', '=', 0); 
            
            $pendingRequestPostsSql->orderBy('staffing_staffrequest.created_at', 'DESC');
            
            $pendingRequestPosts = $pendingRequestPostsSql->get();
            /* Pending Requests */
            
            
            $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            
             $openPostingCount = $openPostingCountSQL->count();    
            
            $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
            
             $pendingPostingCount = $pendingPostingCountSQL->count();       
        
                $unitUsersCount = DB::table('staffing_usersunits')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                ->select(
                        'staffing_usersunits.id'
                        )->where([
                            ['staffing_usersunits.businessUnitID','=',$unitInfo->id],
                            ['staffing_users.role','=',0]
                                
                                ])->count();
        
        
        
        
        
        /* Get Business Unit All Shifts */
        $unitShifts = DB::table('staffing_shiftsetup')
                    ->select('id','startTime','endTime','shiftType')
                    ->where([['businessUnitID','=',$unitInfo->id]])->orderBy('startTime', 'ASC')->get();
        /* Get Business Unit All Shifts */
        
        
     }
        
        if(Auth::user()->role == 1)
            return view('dashboard.godadmin.home');
        if(Auth::user()->role == 2){
            
            
            $currentViewOfCalendar = 1;//1=>Monthly,2=>Weekly,3=>Bi-Weekly 
            
            $defaultMonth = date("Y-m-d");
            
            if(Session::has('defaultMonth')){
                $defaultMonth = Session::get('defaultMonth');
            }else{
                Session::put('defaultMonth',$defaultMonth);
            }
            
            if(Session::has('currentViewOfCalendar')){
                $currentViewOfCalendar = Session::get('currentViewOfCalendar');
            }else{
                Session::put('currentViewOfCalendar',$currentViewOfCalendar);
            }
            
            
            $unitInfoSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        );
            $unitInfoSql->where('staffing_businessunits.businessGroupID','=',Auth::user()->businessGroupID); 
            $unitInfo = $unitInfoSql->get();
            
            $currentYear = date("Y",strtotime($defaultMonth));
            $currentMonth = date("m",strtotime($defaultMonth));
            $userBusinessUnitID = $businessUnitID?$businessUnitID:$unitInfo[0]->id;
            
            /* Get Business Unit All Shifts */
                $unitShifts = DB::table('staffing_shiftsetup')
                ->select('id','startTime','endTime','shiftType')
                ->where([['businessUnitID','=',$userBusinessUnitID]])->orderBy('startTime', 'ASC')->get();
            /* Get Business Unit All Shifts */
            
            $loginUserID = Auth::user()->id;
            $shiftsArray = myHelper::getCalenderData($currentYear,$currentMonth,$userBusinessUnitID,$loginUserID,Auth::user()->role, 0);
                        
            return view('dashboard.manager.calendar',[
                'groupInfo' => $groupInfo,
                'unitShifts' => $unitShifts,
                'shiftArray' => json_encode($shiftsArray),
                'defaultDate' => $defaultMonth,
                'userBusinessUnitID' => $userBusinessUnitID,
                'currentViewOfCalendar' => $currentViewOfCalendar
                    ]);
            
        }
        
        if(Auth::user()->role == 3){
            
            $currentViewOfCalendar = 1;//1=>Monthly,2=>Weekly,3=>Bi-Weekly 
            
            $defaultMonth = date("Y-m-d");
            
            if(Session::has('defaultMonth')){
                $defaultMonth = Session::get('defaultMonth');
            }else{
                Session::put('defaultMonth',$defaultMonth);
            }
            
            if(Session::has('currentViewOfCalendar')){
                $currentViewOfCalendar = Session::get('currentViewOfCalendar');
            }else{
                Session::put('currentViewOfCalendar',$currentViewOfCalendar);
            }
            
            
            $currentYear = date("Y",strtotime($defaultMonth));
            $currentMonth = date("m",strtotime($defaultMonth));
            $userBusinessUnitID = $unitInfo->id;
            $loginUserID = Auth::user()->id;
            $shiftsArray = myHelper::getCalenderData($currentYear,$currentMonth,$userBusinessUnitID,$loginUserID,Auth::user()->role, 0);
                        
            return view('dashboard.superadmin.calendar',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'pendingRequestPosts' => $pendingRequestPosts,
                'unitUsersCount' => $unitUsersCount,
                'pendingPostingCount' => $pendingPostingCount,
                'openPostingCount' => $openPostingCount,
                'unitShifts' => $unitShifts,
                'shiftArray' => json_encode($shiftsArray),
                'defaultDate' => $defaultMonth,
                'currentViewOfCalendar' => $currentViewOfCalendar
                    ]);
        }
        if(Auth::user()->role == 4){
            
            
            $currentViewOfCalendar = 1;//1=>Monthly,2=>Weekly,3=>Bi-Weekly 
            
            $defaultMonth = date("Y-m-d");
            
            if(Session::has('defaultMonth')){
                $defaultMonth = Session::get('defaultMonth');
            }else{
                Session::put('defaultMonth',$defaultMonth);
            }
            
            if(Session::has('currentViewOfCalendar')){
                $currentViewOfCalendar = Session::get('currentViewOfCalendar');
            }else{
                Session::put('currentViewOfCalendar',$currentViewOfCalendar);
            }
            
            
            $currentYear = date("Y",strtotime($defaultMonth));
            $currentMonth = date("m",strtotime($defaultMonth));
            $userBusinessUnitID = $unitInfo->id;
            $loginUserID = Auth::user()->id;
            if(Session::get('defaultView') == 'end-user')
            $shiftsArray = myHelper::getCalenderData($currentYear,$currentMonth,$userBusinessUnitID,$loginUserID,Auth::user()->role, 1);
            else
            $shiftsArray = myHelper::getCalenderData($currentYear,$currentMonth,$userBusinessUnitID,$loginUserID,Auth::user()->role, 0);   
            
            if(Session::get('defaultView') == 'admin'){
                return view('dashboard.admin.calendar',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'pendingRequestPosts' => $pendingRequestPosts,
                'unitUsersCount' => $unitUsersCount,
                'pendingPostingCount' => $pendingPostingCount,
                'openPostingCount' => $openPostingCount,
                'unitShifts' => $unitShifts,
                'shiftArray' => json_encode($shiftsArray),
                'defaultDate' => $defaultMonth,
                'currentViewOfCalendar' => $currentViewOfCalendar
                    ]);
             }else{
                 return view('dashboard.admin.calendar-userview',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'unitUsersCount' => $unitUsersCount,
                'unitShifts' => $unitShifts,
                'shiftArray' => json_encode($shiftsArray),
                'defaultDate' => $defaultMonth,
                'currentViewOfCalendar' => $currentViewOfCalendar
                    ]);
             }
        }
          
        if(Auth::user()->role == 0){
            
            
            $currentViewOfCalendar = 1;//1=>Monthly,2=>Weekly,3=>Bi-Weekly 
            
            $defaultMonth = date("Y-m-d");
            
            if(Session::has('defaultMonth')){
                $defaultMonth = Session::get('defaultMonth');
            }else{
                Session::put('defaultMonth',$defaultMonth);
            }
            
            if(Session::has('currentViewOfCalendar')){
                $currentViewOfCalendar = Session::get('currentViewOfCalendar');
            }else{
                Session::put('currentViewOfCalendar',$currentViewOfCalendar);
            }
            
            
            $currentYear = date("Y",strtotime($defaultMonth));
            $currentMonth = date("m",strtotime($defaultMonth));
            $userBusinessUnitID = $unitInfo->id;
            $loginUserID = Auth::user()->id;
            $shiftsArray = myHelper::getCalenderData($currentYear,$currentMonth,$userBusinessUnitID,$loginUserID,Auth::user()->role, 0);
            
            return view('dashboard.enduser.calendar',[
                'groupInfo' => $groupInfo,
                'requestPosts' => $requestPosts,
                'unitInfo' => $unitInfo,
                'unitShifts' => $unitShifts,
                'shiftArray' => json_encode($shiftsArray),
                'defaultDate' => $defaultMonth,
                'currentViewOfCalendar' => $currentViewOfCalendar]);
        }
        
    }
    
    
    public function home(Request $request){
       
        $searchStartDateValue = '';
        $searchEndDateValue = '';
      
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
   
     if(Auth::user()->role == 4 || Auth::user()->role == 0 || Auth::user()->role == 3){   
         
         /* For Admin Only */
         
         if(Session::has('defaultView')){
            
         }else{
             Session::put('defaultView',"admin");
         }
         
                 
        /* For Admin Only */
        
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
                        
            if(Auth::user()->role == 0 || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)){
                
                
                
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
        
                    $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                    $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                    
                    
                    if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                        $searchStartDateValue = $request->fromDate;
                        $searchEndDateValue = $request->toDate;
                        $requestPostsSql->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                    } 
                    
                    //$requestPostsSql->limit(2)->offset(0);
            
                 /* Active Requests */
            
                $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                    ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                    ->select(
                            'staffing_staffrequest.id AS postID');

                $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
                $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
                $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                $totalOpenRequestsCount = $totalOpenRequestsCountSql->count();  
                    
                    
            }else{
                
              
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
        
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
                
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $requestPostsSql->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
            //$requestPostsSql->limit(2)->offset(0);
            
            /* Active Requests */
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count();  
            
            
            }
            
                
              $postIDs = array();  
              if(Auth::user()->role == 0 || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)){
                  
                  
                  
                  $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',Auth::user()->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                       if(count($postIDs) > 0){
                        $requestPostsSql->whereIn(
                            'staffing_staffrequest.id',$postIDs);
                       }
                   }
                  
              }
                
                
                
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC'); 
            /* Active Requests */
            $requestPosts = $requestPostsSql->get();
            
            if((Auth::user()->role == 0  || (Session::get('defaultView') == 'end-user' && Auth::user()->role == 4)) && count($postIDs) == 0){
                $requestPosts = array();
            }
            
            /* Pending Requests */
            
            $pendingRequestPostsSql = DB::table('staffing_staffrequest')
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
        
            $pendingRequestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
        
            
                $pendingRequestPostsSql->where('staffing_staffrequest.postingStatus', '=', 0); 
                
            $pendingRequestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
            
            if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                $searchStartDateValue = $request->fromDate;
                $searchEndDateValue = $request->toDate;
                $pendingRequestPostsSql->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
            } 
            
            //$pendingRequestPostsSql->orderBy('staffing_staffrequest.created_at', 'DESC');
            $pendingRequestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC'); 
            
            $pendingRequestPosts = $pendingRequestPostsSql->get();
            /* Pending Requests */
            
            
            $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
             $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
             
             
            if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                $searchStartDateValue = $request->fromDate;
                $searchEndDateValue = $request->toDate;
                $openPostingCountSQL->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
            } 
            
             $openPostingCount = $openPostingCountSQL->count(); 
             
            $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
             $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
            
             $pastPostingCount = $pastPostingCountSQL->count(); 
             
             
            
            $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
            $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
            if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                $searchStartDateValue = $request->fromDate;
                $searchEndDateValue = $request->toDate;
                $pendingPostingCountSQL->whereBetween('staffing_staffrequest.staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
            } 
            
            $pendingPostingCount = $pendingPostingCountSQL->count();       
        
            $unitUsersCountSql = DB::table('staffing_usersunits')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                ->select(
                        'staffing_usersunits.id'
                    );
            
            $unitUsersCountSql->where('staffing_usersunits.businessUnitID','=',$unitInfo->id);
            $unitUsersCountSql->where('staffing_users.deleteStatus','=',0);
            
            if(Auth::user()->role == 2){//Group Manager
                  $unitUsersCountSql->whereIn('staffing_users.role',[3,4,0]);
            }

            if(Auth::user()->role == 3){//Super Admin
               $unitUsersCountSql->whereIn('staffing_users.role',[4,0]);
            }

            if(Auth::user()->role == 4){//Admin
               $unitUsersCountSql->whereIn('staffing_users.role',[0,4]);
            }

            if(Auth::user()->role == 0){//Admin
               $unitUsersCountSql->whereIn('staffing_users.role',[0]);
            }

                $unitUsersCount = $unitUsersCountSql->count();
        
        
        
        
        
                /* Get Business Unit All Shifts */
                $unitShifts = DB::table('staffing_shiftsetup')
                    ->select('id','startTime','endTime','shiftType')
                    ->where([['businessUnitID','=',$unitInfo->id]])->orderBy('startTime', 'ASC')->get();
                /* Get Business Unit All Shifts */
        
        
     }
        
        if(Auth::user()->role == 1){
            $searchValue = '';
            $allGroupsSql = DB::table('staffing_groups')
                    ->select('staffing_groups.*')
                    ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id');
            
                    $allGroupsSql->where('staffing_groups.status','=',1);
                    $allGroupsSql->where('staffing_groups.deleteStatus','=',0);
                    $allGroupsSql->where('staffing_users.role','=',2);
                    
                    if(isset($request->search) && $request->search != ''){
                        $searchValue = $searchKey = $request->search;
                        $allGroupsSql->where(function ($q) use ($searchKey){
                            $q->orWhere('groupCode', 'LIKE', $searchKey."%");
                            $q->orWhere('groupName', 'LIKE', $searchKey."%");
                         }); 
                    }
                    
                    $allGroupsSql->orderBy('staffing_groups.updated_at', 'DESC');
            
            //$allGroupsSql->limit(4)->offset(0);            
            
            $allGroups = $allGroupsSql->get();
            
            $totalGroupsCountSql = DB::table('staffing_groups')
                ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id')
                    ->select(
                        'staffing_groups.id');
        
            $totalGroupsCountSql->where('staffing_groups.status','=', 1); 
            $totalGroupsCountSql->where('staffing_groups.deleteStatus','=', 0); 
            $totalGroupsCountSql->where('staffing_users.role','=', 2); 
            
            
            
            $totalGroupsCount = $totalGroupsCountSql->count(); 
            
            return view('dashboard.godadmin.home',[
                'allGroups' => $allGroups,
                'totalGroupsCount' => $totalGroupsCount,
                'searchValue' => $searchValue]);
        }
        
        if(Auth::user()->role == 2){
            
            $managerUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        )->where([
               ['staffing_businessunits.businessGroupID','=',Auth::user()->businessGroupID],
                            ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]
                                ]);
        
            
                $searchValue = '';
                if(isset($request->search) && $request->search != ''){
                    $searchValue = $searchKey = $request->search;
                    $managerUnitsSql->where(function ($q) use ($searchKey){
                        $q->orWhere('staffing_businessunits.unitName', 'LIKE', $searchKey."%");
                        $q->orWhere('staffing_businessunits.storeNumber', 'LIKE', $searchKey."%");
                     }); 
                } 
                
            $managerUnitsSql->orderBy('staffing_businessunits.updated_at', 'DESC');
            
            
            //$managerUnitsSql->limit(4)->offset(0);            
            
            $managerUnits = $managerUnitsSql->get();
            
            $totalUnitsCountSql = DB::table('staffing_businessunits')
                ->select(
                        'id');
        
            $totalUnitsCountSql->where('staffing_businessunits.businessGroupID','=', Auth::user()->businessGroupID); 
            $totalUnitsCountSql->where('staffing_businessunits.deleteStatus','=',0);
            $totalUnitsCountSql->where('staffing_businessunits.status','=',1);
            
            $totalUnitsCount = $totalUnitsCountSql->count();
            
            if(Auth::user()->passwordAlertNotice == '1'){
                $request->session()->flash('passwordPrompt', '1');
                /* Update flag for alert (do not show alert to change password.) */
                $editUser = User::find(Auth::user()->id);
                $editUser->passwordAlertNotice = 0;
                $editUser->save();
                /* Update flag for alert (do not show alert to change password.) */
            }
            
            
            return view('dashboard.manager.home',[
                'groupInfo' => $groupInfo,
                'managerUnits' => $managerUnits,
                'totalUnitsCount' => $totalUnitsCount,
                'searchValue' => $searchValue]);
        }
        
        if(Auth::user()->role == 3)
            return view('dashboard.superadmin.home',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'pendingRequestPosts' => $pendingRequestPosts,
                'unitUsersCount' => $unitUsersCount,
                'pendingPostingCount' => $pendingPostingCount,
                'openPostingCount' => $openPostingCount,
                'totalOpenRequestsCount' => $totalOpenRequestsCount,
                    'pastPostingCount' => $pastPostingCount,
                'searchStartDateValue' => $searchStartDateValue,
                    'searchEndDateValue' => $searchEndDateValue
                    ]);
        if(Auth::user()->role == 4){
            
            if(Session::get('defaultView') == 'admin'){
                return view('dashboard.admin.home',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'pendingRequestPosts' => $pendingRequestPosts,
                'unitUsersCount' => $unitUsersCount,
                'pendingPostingCount' => $pendingPostingCount,
                'openPostingCount' => $openPostingCount,
                'totalOpenRequestsCount' => $totalOpenRequestsCount,
                    'pastPostingCount' => $pastPostingCount,
                'searchStartDateValue' => $searchStartDateValue,
                    'searchEndDateValue' => $searchEndDateValue
                    ]);
             }else{
                 return view('dashboard.admin.userview',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts,
                'unitUsersCount' => $unitUsersCount,
                'searchStartDateValue' => $searchStartDateValue,
                    'searchEndDateValue' => $searchEndDateValue
                    ]);
             }
        }
          
        if(Auth::user()->role == 0){
            
            return view('dashboard.enduser.home',[
                'groupInfo' => $groupInfo,
                'requestPosts' => $requestPosts,
                'searchStartDateValue' => $searchStartDateValue,
                'searchEndDateValue' => $searchEndDateValue]);
            
        }
    }
    
    
    public function ajaxCalendarViewOnNextPrevious(Request $request){
        $defaultMonth = $request->defaultMonth;
        $currentViewOfCalendar = $request->currentViewOfCalendar; 
        Session::put('defaultMonth',$defaultMonth);
        Session::put('currentViewOfCalendar',$currentViewOfCalendar);
        return response()->json(['status'=>'1']);
    }
    
    
    public function groupDetail($groupID, Request $request){
        
        
        if(Auth::user()->role == 0 || Auth::user()->role == 3 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
        }  
        
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',$groupID],
                            ['staffing_groups.deleteStatus','=',0],
                                ['staffing_groups.status','=',1]])->first();
        if($groupInfo){
                $managerUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        )->where([
                            ['staffing_businessunits.businessGroupID','=',$groupID],
                            ['staffing_businessunits.deleteStatus','=',0],
                                ['staffing_businessunits.status','=',1]
                                ]);
                
                
                
            
                $searchValue = '';
                if(isset($request->search) && $request->search != ''){
                    $searchValue = $searchKey = $request->search;
                    $managerUnitsSql->where(function ($q) use ($searchKey){
                        $q->orWhere('staffing_businessunits.unitName', 'LIKE', $searchKey."%");
                        $q->orWhere('staffing_businessunits.storeNumber', 'LIKE', $searchKey."%");
                     }); 
                } 
                
                $managerUnitsSql->orderBy('staffing_businessunits.updated_at', 'DESC');
        
        
            //$managerUnitsSql->limit(4)->offset(0);            

            $managerUnits = $managerUnitsSql->get();

            $totalUnitsCountSql = DB::table('staffing_businessunits')
                ->select(
                        'id');

            $totalUnitsCountSql->where('staffing_businessunits.businessGroupID','=', $groupID); 
            $totalUnitsCount = $totalUnitsCountSql->count(); 



            return view('groups.detail',[
                'groupInfo' => $groupInfo,
                'managerUnits' => $managerUnits,
                'totalUnitsCount' => $totalUnitsCount,
                'searchValue' => $searchValue]); 
        }else{
            return redirect(Config('constants.urlVar.home'))->with('success','The page you are looking for is no longer available.');   
        }
    }
    
    
    
    public function scheduling(){  
        
        $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
    
     if(Auth::user()->role == 4 || Auth::user()->role == 0){   
         
         /* For Admin Only */
         
         if(Session::has('defaultView')){
            
         }else{
             Session::put('defaultView',"admin");
         }
         
         
        
        /* For Admin Only */
        
        $unitInfoSql = DB::table('staffing_businessunits')
                ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', '=', 'staffing_businessunits.id')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        );
        
        
        $unitInfoSql->where('staffing_usersunits.userID','=',Auth::user()->id);
        
        if(Auth::user()->role == 0)
          $unitInfoSql->where('staffing_usersunits.primaryUnit','=',1);  
            
        
        $unitInfo = $unitInfoSql->first();
        
                        
            if(Auth::user()->role == 0 || Session::get('defaultView') == 'end-user'){
                
                
                
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
                     
                        if($unitInfo)
                        $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
        
                        $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                    
                    
                        $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                    
            }
            
                
              $postIDs = array();  
              if(Auth::user()->role == 0 || Session::get('defaultView') == 'end-user'){
                  
                  
                  
                  $getConfirmedPosts = DB::table('staffing_shiftoffer')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id')
                           ->where([
                               ['staffing_shiftoffer.userID','=',Auth::user()->id],
                               ['staffing_shiftconfirmation.offerResponse','=',1]
                                   ])->get();
                   
                   
                   if(count($getConfirmedPosts) > 0){
                       $postIDs = array();
                       foreach($getConfirmedPosts as $getConfirmedPost){
                           $postIDs[] = $getConfirmedPost->requestID;
                       }
                   
                        
                       if(count($postIDs) > 0){
                        $requestPostsSql->whereIn(
                            'staffing_staffrequest.id',$postIDs);
                       }
                   }
                  
              }
                
                
                
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            /* Active Requests */
            $requestPosts = $requestPostsSql->get();
            
            if((Auth::user()->role == 0  || Session::get('defaultView') == 'end-user') && count($postIDs) == 0){
                $requestPosts = array();
            }
        
     }
        
        
        if(Auth::user()->role == 4){
            
            if(Session::get('defaultView') == 'end-user'){
                return view('dashboard.scheduling',[
                'groupInfo' => $groupInfo,
                'unitInfo' => $unitInfo,
                'requestPosts' => $requestPosts
                    ]);
             }
        }
          
        if(Auth::user()->role == 0)
            return view('dashboard.scheduling',[
                'groupInfo' => $groupInfo,
                'requestPosts' => $requestPosts]);
    }
    
    
    
    public function userCalendarAvailability(Request $request){
            $loginUserID = Auth::user()->id;
            $user = User::find($loginUserID);
            $loginUserID = $userID = $user->id;
            $insertData = array();
            $fromDate = date("Y-m-d",strtotime($request->startDate));
            //$toDate = date("Y-m-d", strtotime("-1 day", strtotime($request->endDate)));
            $toDate = date("Y-m-d", strtotime($request->endDate));
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
                    
                    if(count($insertData) > 0){
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
                           $responseJson['message'] = 'Your availability has been saved..';
                        }else{
                            $responseJson['status'] = '0';//'YES';
                           $responseJson['message'] = 'No.';
                        }
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
    
    
    public function userCalendarAvailabilityOLD(Request $request){
            $loginUserID = Auth::user()->id;
            $user = User::find($loginUserID);
            $loginUserID = $userID = $user->id;

            $fromDate = date("Y-m-d",strtotime($request->startDate));
            $toDate = date("Y-m-d", strtotime("-1 day", strtotime($request->endDate)));
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
                            ->where([['businessUnitID','=',$unitInfo->id]])->get();
                            }
                        /* Get Business Unit All Shifts */
                   
                   
                   
                   if ($user->role == 0 || $user->role == 4) {
                        $availabilityStatus = $request->availabilityStatus;
                        if($availabilityStatus == '1'){
                            $availabilityStatus = '1';//Available
                        }else{
                            $availabilityStatus = '0';//Unavailable
                        }
                     
                    $success = false;   
                    if($shiftID == 0){
                        if($getShifts){
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
                                      $insertData[] = array(
                                       'userID' => $loginUserID,
                                       'onDate' => $v,   
                                       'shiftID' => $getShift->id ,
                                       'availabilityStatus' => $availabilityStatus 
                                      );  
                                   }

                                } 
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
                        $responseJson['message'] = 'Success.';
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
            
        
        
        public function businessUnitDetail($businessUnitID, Request $request){
            
            
            $unitInfoSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName',
                        'staffing_businessunits.businessGroupID'
                        );
        
            $unitInfoSql->where('staffing_businessunits.id','=',$businessUnitID); 
            
            $unitInfo = $unitInfoSql->first();
            
            if(Auth::user()->role == 1){
               $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',$unitInfo->businessGroupID]])->first(); 
            }else{
            
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
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
        
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
            
                $searchStartDateValue = '';
                $searchEndDateValue = '';
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $requestPostsSql->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
            
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            
            
            
            
            //$requestPostsSql->limit(2)->offset(0);
            
            /* Active Requests */
            $requestPosts = $requestPostsSql->get();
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            
            $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
            
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count();            
            
            /* Pending Requests */
            
            $pendingRequestPostsSql = DB::table('staffing_staffrequest')
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
        
                $pendingRequestPostsSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
        
            
                $pendingRequestPostsSql->where('staffing_staffrequest.postingStatus', '=', 0); 
                $pendingRequestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                
                
                
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $pendingRequestPostsSql->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
                
                
                $pendingRequestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
                
                

                $pendingRequestPosts = $pendingRequestPostsSql->get();
            /* Pending Requests */
            
            
                $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
                $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
                $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                 $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                 
                
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $openPostingCountSQL->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
                $openPostingCount = $openPostingCountSQL->count();    
            
                $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
                $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
                $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
                 $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                 
                 
                
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $pendingPostingCountSQL->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
                $pendingPostingCount = $pendingPostingCountSQL->count(); 
                 
             
            $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
             $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
            
             $pastPostingCount = $pastPostingCountSQL->count(); 
        
                $unitUsersCount = DB::table('staffing_usersunits')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                ->select(
                        'staffing_usersunits.id'
                        )->where([
                            ['staffing_usersunits.businessUnitID','=',$businessUnitID],
                            ['staffing_users.role','=',0]
                                
                                ])->count();
        
             
                return view('units.detail',[
                    'groupInfo' => $groupInfo,
                    'unitInfo' => $unitInfo,
                    'requestPosts' => $requestPosts,
                    'pendingRequestPosts' => $pendingRequestPosts,
                    'unitUsersCount' => $unitUsersCount,
                    'pendingPostingCount' => $pendingPostingCount,
                    'openPostingCount' => $openPostingCount,
                    'totalOpenRequestsCount' => $totalOpenRequestsCount,
                    'pastPostingCount' => $pastPostingCount,
                    'searchStartDateValue' => $searchStartDateValue,
                    'searchEndDateValue' => $searchEndDateValue
                    ]);     
        }
        
        public function businessUnitDetailPending($businessUnitID, Request $request){
            
            
            $unitInfoSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName',
                        'staffing_businessunits.businessGroupID'
                        );
        
            $unitInfoSql->where('staffing_businessunits.id','=',$businessUnitID); 
            
            $unitInfo = $unitInfoSql->first();
            
            if(Auth::user()->role == 1){
               $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',$unitInfo->businessGroupID]])->first(); 
            }else{
            
            $groupInfo = DB::table('staffing_groups')
                ->select(
                        'id',
                        'groupName',
                        'groupCode'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
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
        
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            
                $searchStartDateValue = '';
                $searchEndDateValue = '';
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $requestPostsSql->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            
            
            //$requestPostsSql->limit(2)->offset(0);
            
            /* Active Requests */
            $requestPosts = $requestPostsSql->get();
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
            
            $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count();            
            
            /* Pending Requests */
            
            $pendingRequestPostsSql = DB::table('staffing_staffrequest')
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
        
                $pendingRequestPostsSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
        
            
                $pendingRequestPostsSql->where('staffing_staffrequest.postingStatus', '=', 0); 
                $pendingRequestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $pendingRequestPostsSql->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
                
                
                
                $pendingRequestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');

                $pendingRequestPosts = $pendingRequestPostsSql->get();
            /* Pending Requests */
            
            
                $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
                $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
                $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
                 $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                 
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $openPostingCountSQL->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
                $openPostingCount = $openPostingCountSQL->count();    
            
                $pendingPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
                $pendingPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
                $pendingPostingCountSQL->where('staffing_staffrequest.postingStatus', '=', 0); 
                 $pendingPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
                 
                if(isset($request->fromDate) && isset($request->toDate) && $request->fromDate != '' && $request->toDate != ''){
                    $searchStartDateValue = $request->fromDate;
                    $searchEndDateValue = $request->toDate;
                    $pendingPostingCountSQL->whereBetween('staffingStartDate', [$searchStartDateValue, $searchEndDateValue]); 
                } 
            
                $pendingPostingCount = $pendingPostingCountSQL->count(); 
                 
             
            $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id');
            
            $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $unitInfo->id); 
            $pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
             $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<',date("Y-m-d"));
            
             $pastPostingCount = $pastPostingCountSQL->count(); 
        
                $unitUsersCount = DB::table('staffing_usersunits')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                ->select(
                        'staffing_usersunits.id'
                        )->where([
                            ['staffing_usersunits.businessUnitID','=',$businessUnitID],
                            ['staffing_users.role','=',0]
                                
                                ])->count();
        
             
                return view('units.pending-detail',[
                    'groupInfo' => $groupInfo,
                    'unitInfo' => $unitInfo,
                    'requestPosts' => $requestPosts,
                    'pendingRequestPosts' => $pendingRequestPosts,
                    'unitUsersCount' => $unitUsersCount,
                    'pendingPostingCount' => $pendingPostingCount,
                    'openPostingCount' => $openPostingCount,
                    'totalOpenRequestsCount' => $totalOpenRequestsCount,
                    'pastPostingCount' => $pastPostingCount,
                    'searchStartDateValue' => $searchStartDateValue,
                    'searchEndDateValue' => $searchEndDateValue
                    ]);     
        }
    /* Ajax Groups Paging */  
        public function ajaxGroupsHomeList($pageNo = 1){
            
            $allGroupsSql = DB::table('staffing_groups')
                        ->where([
                            ['status','=',1],
                            ['deleteStatus','=',0]
                                ]);            
            
            
            $offset = (($pageNo-1) * 4);
        
            $allGroupsSql->limit(4)->offset($offset);    
            
            $allGroups = $allGroupsSql->get();
            
            $totalGroupsCountSql = DB::table('staffing_groups')
                ->select(
                        'id');
        
            $totalGroupsCountSql->where('status','=', 1); 
            $totalGroupsCountSql->where('deleteStatus','=', 0); 
            $totalGroupsCount = $totalGroupsCountSql->count(); 
            
            return view('dashboard.godadmin.ajax-home',[
                'allGroups' => $allGroups,
                'activePage' => $pageNo,
                'totalGroupsCount' => $totalGroupsCount]);
        }
    /* Ajax Groups Paging */
        
        
     
        
    /* Ajax Group Detail Business Unit Paging */  
        public function ajaxGroupDetailBusinessUnitsPaging($groupID, $pageNo = 1){
            $managerUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        )->where([
               ['staffing_businessunits.businessGroupID','=',$groupID]
                                ]);
            
            $offset = (($pageNo-1) * 4);
        
            $managerUnitsSql->limit(4)->offset($offset);    
            
            $managerUnits = $managerUnitsSql->get();
            
            $totalUnitsCountSql = DB::table('staffing_businessunits')
                ->select(
                        'id');
        
            $totalUnitsCountSql->where('staffing_businessunits.businessGroupID','=', $groupID); 
            $totalUnitsCount = $totalUnitsCountSql->count(); 
            
            return view('groups.ajax-detail',[
                'managerUnits' => $managerUnits,
                'activePage' => $pageNo,
                'totalUnitsCount' => $totalUnitsCount]);
        }
    /* Ajax Group Detail Business Unit Paging */     
        
        
        
    /* Ajax Business Unit Paging */  
        public function ajaxBusinessUnitPaging($pageNo = 1){
            $managerUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'staffing_businessunits.id',
                        'staffing_businessunits.unitName'
                        )->where([
               ['staffing_businessunits.businessGroupID','=',Auth::user()->businessGroupID]
                                ]);
            
            $offset = (($pageNo-1) * 4);
        
            $managerUnitsSql->limit(4)->offset($offset);    
            
            $managerUnits = $managerUnitsSql->get();
            
            $totalUnitsCountSql = DB::table('staffing_businessunits')
                ->select(
                        'id');
        
            $totalUnitsCountSql->where('staffing_businessunits.businessGroupID','=', Auth::user()->businessGroupID); 
            $totalUnitsCount = $totalUnitsCountSql->count(); 
            
            return view('dashboard.manager.ajax-home',[
                'managerUnits' => $managerUnits,
                'activePage' => $pageNo,
                'totalUnitsCount' => $totalUnitsCount]);
        }
    /* Ajax Business Unit Paging */ 
        
    /* Ajax Paging List of Open Requests */
    public function ajaxOpenRequestsList($businessUnitID, $pageNo = 1){
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
        
            $requestPostsSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $requestPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
             $requestPostsSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $requestPostsSql->orderBy('staffing_staffrequest.staffingStartDate', 'ASC');
            
        
        
            
            $totalOpenRequestsCountSql = DB::table('staffing_staffrequest')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_staffrequest.ownerID')
                ->leftJoin('staffing_shiftsetup', 'staffing_shiftsetup.id', '=', 'staffing_staffrequest.staffingShiftID')
                ->select(
                        'staffing_staffrequest.id AS postID');
        
            $totalOpenRequestsCountSql->where('staffing_staffrequest.businessUnitID','=', $businessUnitID); 
            $totalOpenRequestsCountSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
             $totalOpenRequestsCountSql->where('staffing_staffrequest.staffingStartDate','>=',date("Y-m-d"));
            $totalOpenRequestsCount = $totalOpenRequestsCountSql->count();
            
        
        $offset = (($pageNo-1) * 2);
        
        $requestPostsSql->limit(2)->offset($offset);
        $requestPosts = $requestPostsSql->get();
        
        return view('units.ajaxUnitDetail', 
                ['requestPosts' => $requestPosts,
                'activePage' => $pageNo,
                'totalOpenRequestsCount' => $totalOpenRequestsCount    
                ]);
    }       
    /* Ajax Paging List of Open Requests */





        public function privacyPolicy() {
            if(Auth::user()->role == 1){
               $businessGroupID = 0;  
            }else{
                $businessGroupID = Auth::user()->businessGroupID;
            }
            $page = DB::table('staffing_staticpages')
                    ->select('id','title','content','businessGroupID')
                    ->where([['businessGroupID','=',$businessGroupID],
                        ['type','=','privacy']])
                    ->first();
            
            
            if($page){
                
            }else{
              $saveNewForThisGroup = $this->saveNewPageInformation('privacy'); 
              $page = DB::table('staffing_staticpages')
                    ->select('id','title','content')
                    ->where([['businessGroupID','=',$businessGroupID],
                        ['type','=','privacy']])
                    ->first();
            }
            
            return view('pages.view',['page' => $page]);                
        }
        
        
        public function termsOfService() {
            if(Auth::user()->role == 1){
               $businessGroupID = 0;  
            }else{
                $businessGroupID = Auth::user()->businessGroupID;
            }
            $page = DB::table('staffing_staticpages')
                    ->select('id','title','content')
                    ->where([['businessGroupID','=',$businessGroupID],
                        ['type','=','terms']])
                    ->first();
            
            if($page){
                
            }else{
              $saveNewForThisGroup = $this->saveNewPageInformation('terms'); 
              $page = DB::table('staffing_staticpages')
                    ->select('id','title','content')
                    ->where([['businessGroupID','=',$businessGroupID],
                        ['type','=','terms']])
                    ->first();
            }
            
            return view('pages.view',['page' => $page]);
        }
        
        public function editPage($id){
            $pageID = $id;
            $pageInfo = Page::find($pageID);
            if($pageInfo){
              return view('pages.edit',['page' => $pageInfo]);  
            }else{
                return redirect(Config('constants.urlVar.dashboard'))
                        ->with('error','Page not found.');   
            }
        }
        
        
        public function saveNewPageInformation($pageType = 'privacy'){
            
            /* GET DEFAULT TOS & PP */
            $page = DB::table('staffing_staticpages')
                    ->select('id','title','content')
                    ->where([['businessGroupID','=',0],
                        ['type','=',$pageType]])
                    ->first();
            /* GET DEFAULT TOS & PP */
            
            if($pageType == 'terms')
                $title = 'Terms of Service';
            else
                $title = 'Privacy Policy';    
            
            $success = false;
            
            if($page){
            
                $insertedData = array(
                    ['businessGroupID' => Auth::user()->businessGroupID,
                      'title' => $page->title,
                      'type' => $pageType,
                     'content' => $page->content]                
                );
                
                $success = DB::table('staffing_staticpages')->insert($insertedData); 
            }
            
            if($success){
              return true;  
            }else{
               return false; 
            }
            
        }
        
        
        public function updatePage(Request $request){
            
            $pageID = $request->id;
            $pageInfo = Page::find($pageID);

            if($pageInfo){      
                $title = $request->title;
                $content = $request->content;        

                $this->validate($request, [
                    'title' => 'required',
                    'content' => 'required'
                ]);

                $pageInfo->title = $title;
                $pageInfo->content = $content;


                if($pageInfo->save())
                {
                  if($pageInfo->type == 'terms')  
                    return redirect()->intended(Config('constants.urlVar.termsOfService'))
                          ->with('success','Page information updated successfully.'); 
                  else
                    return redirect()->intended(Config('constants.urlVar.privacyPolicy'))
                          ->with('success','Page information updated successfully.'); 
                  
                }else{
                  return redirect()->intended(Config('constants.urlVar.editPage').$pageID)
                          ->with('error','Failed to update page information.');  
                }
            }else{
                return redirect()->intended(Config('constants.urlVar.dashboard'))
                    ->with('error','Page not found.');  
            }
        
        }
        
            
    
}