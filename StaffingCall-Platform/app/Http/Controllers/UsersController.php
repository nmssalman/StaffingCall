<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Businessunit;
use App\Userunit;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{   
    
    
    
    
        public function index()
    {   
            
            
         if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }   
            
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
        
        
        return view('users.show', ['users' => $users]);
    }
    
    
    public function ajaxUserList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'staffing_users.userName', 
            1 => 'staffing_users.firstName',
            2=> 'staffing_users.role',
            3=> 'staffing_users.firstName',
            4=> 'staffing_users.skills',
            5=> 'staffing_users.email',
            6=> 'staffing_users.phone',
            7=> 'staffing_users.status'
        );
        
        $totalFiltered = DB::table('staffing_users')->select('id')
                ->where([['businessGroupID','=',Auth::user()
                ->businessGroupID],
                ['deleteStatus','=',0]])->count();
        
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
        
        $sql->where('deleteStatus','=',0);
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
                
                $query->orWhere('staffing_users.userName', 'LIKE', $requestData['search']['value'].'%');
                $query->orWhere('staffing_users.email', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.phone', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
            });
            
            
            
        } 
        
        
        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        $sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        $sql->orderBy('staffing_users.firstName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        if(Auth::user()->role == 3 && !$associatedUsers){//Super-Admin
           $results = array(); 
        }
        
        
        
        foreach($results as $result){
            
                
        /* Get User Assigned Business Unit */
                
                $primaryUserUnit = '';
                
                $userBusinessUnitsSql = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                 ->select(
                'staffing_businessunits.id',
                'staffing_businessunits.unitName');
                $userBusinessUnitsSql->where('staffing_usersunits.userID','=',$result->id);
                $userBusinessUnitsSql->where('staffing_usersunits.primaryUnit','=',0);
                $userBusinessUnits = $userBusinessUnitsSql->get();

                $usersUnits = array();
                if($userBusinessUnits){
                    foreach($userBusinessUnits as $userBusinessUnit){
                        $usersUnits[] = $userBusinessUnit->unitName;
                    }

                  $primaryUserUnit = $usersUnits?'<span style="color:#42a5f5;">'.(implode(', ',$usersUnits)).'</span>':'<span style="color:#f00;">Not assigned.</span>'; 

                }
                
                
                if($result->role == 0){
                  $userPrimaryBusinessUnitsSql = DB::table('staffing_usersunits')
                    ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                     ->select(
                    'staffing_businessunits.id',
                    'staffing_businessunits.unitName');
                    $userPrimaryBusinessUnitsSql->where('staffing_usersunits.userID','=',$result->id);
                    $userPrimaryBusinessUnitsSql->where('staffing_usersunits.primaryUnit','=',1);
                    $userPrimaryBusinessUnits = $userPrimaryBusinessUnitsSql->first();
                    if($userPrimaryBusinessUnits){
                    //$primaryUserUnit = "Primary - ".$userPrimaryBusinessUnits->unitName."<br> Secondary - ".$primaryUserUnit;
                    $primaryUserUnit = '<strong>Primary</strong> - <span style="color:#42a5f5;">'
                            . ''.$userPrimaryBusinessUnits->unitName.'</span>'
                            . '<br> <strong>Secondary</strong> - '.$primaryUserUnit;
                    }else{
                        $primaryUserUnit = '<strong>Primary</strong> - <span style="color:#42a5f5;">'
                            . $primaryUserUnit.'</span>'
                            . '<br> <strong>Secondary</strong> - '.$primaryUserUnit;
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
                
                $activeBtns = '';
                $url = url(Config('constants.urlVar.setUserActiveDeactive').$result->id.'/'.$result->status);
                if($result->status){
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to De-activate" href="javascript:void(0);" '
                          . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-success mb-10">Active</a>';  
                }else{
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to Activate" href="javascript:void(0);" '
                         . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-danger mb-10">Deactive</a>';  
                }

                $htmlAction = $activeBtns.' <a href="'.url(Config('constants.urlVar.editUser').$result->id).'" '
                        . 'class="btn btn-sm btn-outline-info mb-10"><i class="fa fa-edit"></i></a>'
                        . '<a title="Delete User" onclick="return confirm(\'Do you really want to delete User ?\')" '
                        . 'href="'.url(Config('constants.urlVar.deleteUser').$result->id).'" '
                        . 'class="btn btn-sm btn-danger mb-10"><i class="fa fa-trash"></i></a>'; 

                $nestedData[] = $result->userName;
                $nestedData[] = $primaryUserUnit;
                $nestedData[] = ($result->role == '3')?'<span class="badge badge-danger">Super Admin</span>':(($result->role == '4')?'<span class="badge badge-info">Admin</span>':'<span class="badge badge-warning">End User</span>');
                $nestedData[] = $result->name;
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
    
    
    
    
    
    public function deleteUser($id){ 
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $data=array('deleteStatus' => 1);
        $success = $this->updateUserInfoByID($id,$data);
        if($success)
        {
          return redirect(Config('constants.urlVar.userList'))->with('success','User deleted successfully.');   
        }else{
          return redirect(Config('constants.urlVar.userList').$id)->with('error','Failed to delete User.');  
        }

    }
    
	 
    public function setActiveDeactive($id, $status){ 	
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $data=array('status' => $status ? 0 : 1);
        $this->updateUserInfoByID($id,$data);
        
        $userInfo = User::find($id); 
        
        $data=array('id'=>$id,'status' => $userInfo->status);
        return view('users.toggle', ['results' => $data]);

    }
    
    public function updateUserInfoByID($id, $data = array()){
     if(DB::table('staffing_users')
        ->where('id', $id)
        ->update($data))
        return true;
     else
         return  false;
    } 
    
    

        
    public function createUser(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName');
        
        $businessUnitsSql->where('businessGroupID','=',Auth::user()->businessGroupID);
        
        $businessUnitsSql->where('deleteStatus','=',0);
        
        $businessUnitsSql->where('status','=',1);
        
        $businessUnitsSql->orderBy('unitName','ASC');
        
//        if(Auth::user()->role == 3){//Super-Admin
//            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//            $userUnitID = DB::table('staffing_usersunits')
//                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
//                ->select('staffing_businessunits.id AS businessUnitID')
//                ->where([
//                    ['staffing_usersunits.userID','=',Auth::user()->id]])
//                 ->first();   
//            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//          $businessUnitsSql->where('id','=',$userUnitID->businessUnitID);  
//            
//        }
        
        
        $businessUnits = $businessUnitsSql->get();
        
        
        $staffingCategory = DB::table('staffing_skillcategory')
                ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
        return view('users.new', ['units' => $businessUnits,'staffingCategory' => $staffingCategory]);
    }
    
    
    public function edit($id){
        
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $user = User::find($id); 
        
        
        $userPrimaryUnits = array();
        $userSecondaryUnits = array();
        if($user->role != 2){
            
            if($user->role == 0){
                
                $userAssociatedPrimaryUnitsRows = DB::table('staffing_usersunits')
                    ->select('businessUnitID','primaryUnit')->where([['userID', '=', $id], 
                        ['primaryUnit', '=', 1]])->get();
                
                $userAssociatedSecondaryUnitsRows = DB::table('staffing_usersunits')
                    ->select('businessUnitID','primaryUnit')->where([['userID', '=', $id], 
                        ['primaryUnit', '=', 0]])->get();
                if($userAssociatedSecondaryUnitsRows){
                    foreach($userAssociatedSecondaryUnitsRows as $userAssociatedSecondaryUnitsRow){   
                         $userSecondaryUnits[] = $userAssociatedSecondaryUnitsRow->businessUnitID;

                    } 
                }
            }else{
                $userAssociatedPrimaryUnitsRows = DB::table('staffing_usersunits')
                    ->select('businessUnitID','primaryUnit')->where([['userID', '=', $id], 
                        ['primaryUnit', '=', 0]])->get(); 
            }
            
            if($userAssociatedPrimaryUnitsRows){
               foreach($userAssociatedPrimaryUnitsRows as $userAssociatedPrimaryUnitsRow){                  
                    $userPrimaryUnits[] = $userAssociatedPrimaryUnitsRow->businessUnitID;
                  
               } 
            }
        
        }
        
        
        
        $businessUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName');        
        $businessUnitsSql->where('businessGroupID','=',Auth::user()->businessGroupID);  
        
        $businessUnitsSql->where('deleteStatus','=',0);
        
        $businessUnitsSql->where('status','=',1);      
        $businessUnitsSql->orderBy('unitName','ASC');
        $businessUnits = $businessUnitsSql->get();
        
        
       
        $businessUnitsSecondarySql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName');
        
         
         
        if($user->role == 0){
           if(count($userAssociatedPrimaryUnitsRows)){
              $businessUnitsSecondarySql->where('id','!=',$userAssociatedPrimaryUnitsRows[0]->businessUnitID); 
           } 
        }
       
        $businessUnitsSecondarySql->where('businessGroupID','=',Auth::user()->businessGroupID);
        
        $businessUnitsSecondarySql->where('deleteStatus','=',0);
        
        $businessUnitsSecondarySql->where('status','=',1);
        
        $businessUnitsSecondarySql->orderBy('unitName','ASC');
        $businessSecondaryUnits = $businessUnitsSecondarySql->get();
        
        
//        if(Auth::user()->role == 3){//Super-Admin
//                /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//                $userUnitID = DB::table('staffing_usersunits')
//                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
//                ->select('staffing_businessunits.id AS businessUnitID')
//                ->where([
//                    ['staffing_usersunits.userID','=',Auth::user()->id]])
//                 ->first();   
//                /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//            $businessUnitsSql->where('id','=',$userUnitID->businessUnitID);  
//            
//        }
        
        
        
        $staffingCategory = DB::table('staffing_skillcategory')
                ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
        
        return view('users.update', ['user' => $user, 
            'units' => $businessUnits, 
            'secondaryUnits' => $businessSecondaryUnits, 
            'staffingCategory' => $staffingCategory, 
            'userPrimaryUnits' => $userPrimaryUnits, 
            'userSecondaryUnits' => $userSecondaryUnits]);
    }
    
    /* For Groups & Unit Management For Group Manager Account*/
    
    
    public function updateUser(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
            $id = $request->id;
            $user = User::find($id);
            
            $groupID = Auth::user()->businessGroupID;
            $groupInfo = Group::find($groupID);
            
            if(!array_filter($request->businessUnitID)) {
                return redirect(Config('constants.urlVar.editUser').$id)->with('error','Business Unit can not be blank.');  
            }

      
            if($user->role == '0')
            $businessUnitIDs = $request->businessUnitID;
            else
            $businessUnitIDs = $request->businessUnitID;
            
            $firstName = $request->firstName;
            $lastName = $request->lastName;
            //$userName = $request->userName;
            $email = $request->email;
            $needApproval = $request->needApproval;
            $phone = $request->phone;
            $skills = $request->skills?serialize($request->skills):serialize(array());
            //1=>Junior,2=>Intermediate,3=>Experienced
            $experiencedLevel = $request->experiencedLevel?$request->experiencedLevel:0;
        
            if($user->role == '0'){
                $this->validate($request, [
                'businessUnitID' => 'required',
                'skills' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                /*'userName' => 'required|unique:staffing_users,userName,'.$user->id.',id,deleteStatus,0',*/ 
                'email' => 'required|email|unique:staffing_users,email,'.$user->id.',id,deleteStatus,0,businessGroupID,'.$groupID.'',      
                'phone' => 'required'
                ]);  
            }else if($user->role == '3'){
                
                $this->validate($request, [
                'businessUnitID' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                /*'userName' => 'required|unique:staffing_users,userName,'.$user->id.',id,deleteStatus,0',*/
                'email' => 'required|email|unique:staffing_users,email,'.$user->id.',id,deleteStatus,0,businessGroupID,'.$groupID.'',      
                'phone' => 'required'
                ]);  
                
            }else{

                $this->validate($request, [
                'businessUnitID' => 'required',
                'skills' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                /*'userName' => 'required|unique:staffing_users,userName,'.$user->id.',id,deleteStatus,0',*/
                'email' => 'required|email|unique:staffing_users,email,'.$user->id.',id,deleteStatus,0,businessGroupID,'.$groupID.'',      
                'phone' => 'required'
                ]);  
            }
        
        
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            //$user->userName = $userName;
            $user->email = $email;
            $user->needApproval = $needApproval;
            $user->phone = "+1".(str_replace(array("+1","+91"), "", $phone));
            $user->skills = $skills;
            $user->experiencedLevel = $experiencedLevel?$experiencedLevel:0;
            //$user->businessGroupID = Auth::user()->businessGroupID;
            
            
        
        if($user->save())
        {
          
            $lastUserId = $user->id; 
            $primaryUnit = 0;
            
            $deleteUnits = DB::table('staffing_usersunits')
            ->where([['userID','=',$user->id]])->delete();
            
            $businessUnitIDs = array_unique($businessUnitIDs);
             
            if($user->role == '0'){
                $primaryUnit = 1;
                
                $secondaryBusinessUnitIDs = $request->businessUnitIDs?array_unique($request->businessUnitIDs):array();
                
                if(count($secondaryBusinessUnitIDs) > 0){
                   $data2 = array();
                    foreach($secondaryBusinessUnitIDs as $k2=>$val2){
                         if($val2 !=''){
                        $data2[] = array(
                           'userID'=>$lastUserId, 
                           'businessUnitID'=> $val2, 
                           'primaryUnit'=> 0
                        );
                         }
                    }
                    if(count($data2) > 0)
                    Userunit::insert($data2); 
                }
                
                $data = array();
                
                foreach($businessUnitIDs as $k=>$val){
                     if($val !=''){
                       $data[] = array(
                          'userID'=>$lastUserId, 
                          'businessUnitID'=> $val, 
                          'primaryUnit'=> $primaryUnit
                       );
                     }
                    }
                if(count($data) > 0)    
                Userunit::insert($data);
                
            }else{
                
                $data = array();
                foreach($businessUnitIDs as $k=>$val){
                    if($val !=''){
                        $data[] = array(
                           'userID'=>$lastUserId, 
                           'businessUnitID'=> $val, 
                           'primaryUnit'=> $primaryUnit
                        );
                      }
                    }
                if(count($data) > 0)    
                Userunit::insert($data); 
            }
            
            
            /* Generate Token And Send Email To Create Username/LoginID And Password By User */
            if($user->userName == '' || $user->userName == NULL){
                $password = '';
                $name = $user->firstName;
                $useremail = $user->email;
                $userID = $user->id;
                $keyGenrate = $this->generateKey(70);
                $timestamp = time();
                $tokenKey = $timestamp."G#@T".$useremail."G#@T".$userID."G#@T".$keyGenrate;
                $token = base64_encode($tokenKey);
                $tokenUpdate = $this->updateToken($token, $userID);


                /* SEND EMAIL TO User */
                $this->sentMailToUser($user->id, $password, $token, $useremail, $name);
            }
                
            /* Generate Token And Send Email To Create Username/LoginID And Password By User */
            
            
          
            return redirect(Config('constants.urlVar.userList'))->with('success','User updated successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.editUser').$id)->with('error','Failed to create User.');  
        }
        
    }
    
    
    public function saveUser(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        if(!array_filter($request->businessUnitID)) {
            return redirect(Config('constants.urlVar.addNewUser'))->with('error','Business Unit can not be blank.');  
        }
        
        
        
        $groupID = Auth::user()->businessGroupID;
        $groupInfo = Group::find($groupID);
        
        
        
        if($groupInfo->maximumEmployee > 0){
            
            /* Check Total No of employess on Business-Unit */
            $assignedUnits = array();
            $businessUnitIDs = $request->businessUnitID;
            if(count($businessUnitIDs) > 0){
                foreach($businessUnitIDs as $k=>$val){
                    if($val !=''){
                       $assignedUnits[] = $val;
                    }
                }
            }
                
                $assignedSecondaryBusinessUnitIDs = array();
                if(isset($request->businessUnitIDs))
                $assignedSecondaryBusinessUnitIDs = array_unique($request->businessUnitIDs);
                
                if(count($assignedSecondaryBusinessUnitIDs) > 0){
                    foreach($assignedSecondaryBusinessUnitIDs as $ky=>$valu){
                        if($valu !=''){
                            $assignedUnits[] = $valu;
                        }
                    }
                }
                
                
            if(count($assignedUnits) > 0){
                   $perUnitEmployeesSql = DB::table('staffing_usersunits')
                   ->select(DB::raw('count(staffing_usersunits.businessUnitID) AS totalEmployees'),
                   'staffing_usersunits.businessUnitID',
                   'staffing_businessunits.unitName')
                   ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                   ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID');
                   $perUnitEmployeesSql->where('staffing_businessunits.deleteStatus', '=', 0);
                   $perUnitEmployeesSql->where('staffing_users.deleteStatus', '=', 0);
                   $perUnitEmployeesSql->where('staffing_users.businessGroupID', '=', $groupID);
                   $perUnitEmployeesSql->where('staffing_businessunits.businessGroupID', '=', $groupID);
                   $perUnitEmployeesSql->whereIn('staffing_usersunits.businessUnitID', $assignedUnits);

                   $perUnitEmployeesSql->groupBy('staffing_usersunits.businessUnitID');
                   $perUnitEmployeesSql->orderBy('totalEmployees', 'DESC');
                   $perUnitEmployees = $perUnitEmployeesSql->get();                    

            }   
            
            /* Check Total No of employess on Business-Unit */
            
//            $getNumberOfUsersSql = DB::table('staffing_users');            
//            $getNumberOfUsersSql->where('deleteStatus', '=', '0');
//            $getNumberOfUsersSql->where('businessGroupID', $groupID);
//            $getNumberOfUsersSql->whereNotIn('role', [1,2]);
//            $getNumberOfUsers = $getNumberOfUsersSql->count();
            
            if(count($perUnitEmployees) > 0){
                if($perUnitEmployees[0]->totalEmployees >= $groupInfo->maximumEmployee){
                   return redirect(Config('constants.urlVar.userList'))->with('error','Sorry! Maximum limit of Users creation has been exausted.');    
                }
            }
        }
        
      
        if($request->role == '0')
        $businessUnitIDs = $request->businessUnitID;
        else
        $businessUnitIDs = $request->businessUnitID;
            
        
        
        $firstName = $request->firstName;
        $lastName = $request->lastName;
        $userName = $request->userName?$request->userName:'';
        $email = $request->email;
        $password = $request->password?$request->password:'';
        $phone = $request->phone;
        $needApproval = $request->needApproval;
        $skills = $skills = $request->skills?serialize($request->skills):serialize(array());
        //1=>Junior,2=>Intermediate,3=>Experienced
        $experiencedLevel = $request->experiencedLevel?$request->experiencedLevel:0;
        $val = 1;
        if($request->role == '0'){
          $this->validate($request, [
            'businessUnitID' => 'required',
            'skills' => 'required',
            'firstName' => 'required',
            'lastName' => 'required',
            /*'userName' => 'required|unique:staffing_users,userName,'.$val.',deleteStatus',*/
            /*'email' => 'required|email|unique:staffing_users',*/
            'email' => 'required|email|unique:staffing_users,email,NULL,NULL,deleteStatus,0,businessGroupID,'.$groupID.'',  
            'phone' => 'required',/*
            'password' => 'required|alpha_num|min:8|confirmed',
            'password_confirmation' => 'required|alpha_num|min:8'*/
            ]);  
        }else if($request->role == '3'){
                
                $this->validate($request, [
                'businessUnitID' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                /*'userName' => 'required|unique:staffing_users,userName,'.$val.',deleteStatus',*/
                'email' => 'required|email|unique:staffing_users,email,NULL,NULL,deleteStatus,0,businessGroupID,'.$groupID.'',
                'phone' => 'required'
                ]);  
                
        }else{
            
          $this->validate($request, [
            'businessUnitID' => 'required',
            'skills' => 'required',
            'firstName' => 'required',
            'lastName' => 'required',
            /*'userName' => 'required|unique:staffing_users,userName,'.$val.',deleteStatus',*/
            'email' => 'required|email|unique:staffing_users,email,NULL,NULL,deleteStatus,0,businessGroupID,'.$groupID.'',
            'phone' => 'required'/*,
            'password' => 'required|alpha_num|min:8|confirmed',
            'password_confirmation' => 'required|alpha_num|min:8'*/
            ]);  
        }
        
        
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';  
            $password   = substr(str_shuffle($str),0,6);
        
        
            $user = new User;
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            $user->userName = $userName;
            $user->email = $email;
            $user->needApproval = $needApproval;
            $user->password = Hash::make($password);
            $user->phone = "+1".(str_replace(array("+1", "+91"), "", $phone));
            $user->skills = $skills;
            $user->experiencedLevel = $experiencedLevel?$experiencedLevel:0;
            $user->businessGroupID = Auth::user()->businessGroupID;
            $user->role = $request->role;
            $user->remember_token = $request->_token;
            
            
        
        if($user->save())
        {
          
            $lastUserId = $user->id; 
            
            $userInfo = User::find($lastUserId);
            $primaryUnit = 0;
            $businessUnitIDs = array_unique($businessUnitIDs);
             $secondaryBusinessUnitIDs = array();
            if($request->role == '0'){
                $primaryUnit = 1;
                if(isset($request->businessUnitIDs))
                $secondaryBusinessUnitIDs = array_unique($request->businessUnitIDs);
                
                
                
                if(count($secondaryBusinessUnitIDs) > 0){
                   $data2 = array();
                    foreach($secondaryBusinessUnitIDs as $k2=>$val2){
                         if($val2 !=''){
                        $data2[] = array(
                           'userID'=>$lastUserId, 
                           'businessUnitID'=> $val2, 
                           'primaryUnit'=> 0
                        );
                         }
                    }
                    
                   
                    
                    if(count($data2) > 0)
                    Userunit::insert($data2); 
                }
                
                $data = array();
                foreach($businessUnitIDs as $k=>$val){
                     if($val !=''){
                       $data[] = array(
                          'userID'=>$lastUserId, 
                          'businessUnitID'=> $val, 
                          'primaryUnit'=> $primaryUnit
                       );
                     }
                    }
                if(count($data) > 0)    
                Userunit::insert($data);
                
            }else{
                
                $data = array();
                foreach($businessUnitIDs as $k=>$val){
                    if($val !=''){
                        $data[] = array(
                           'userID'=>$lastUserId, 
                           'businessUnitID'=> $val, 
                           'primaryUnit'=> $primaryUnit
                        );
                      }
                    }
                if(count($data) > 0)    
                Userunit::insert($data); 
            }
            
            
            /* Generate Token And Send Email To Create Username/LoginID And Password By User */
            $name = $userInfo->firstName;
            $useremail = $userInfo->email;
            $userID = $userInfo->id;
            $keyGenrate = $this->generateKey(70);
            $timestamp = time();
            $tokenKey = $timestamp."G#@T".$useremail."G#@T".$userID."G#@T".$keyGenrate;
            $token = base64_encode($tokenKey);
            $tokenUpdate = $this->updateToken($token, $userID);
            
            
            /* SEND EMAIL TO User */
            $this->sentMailToUser($user->id, $password, $token, $useremail, $name);
                
            /* Generate Token And Send Email To Create Username/LoginID And Password By User */
            
          
            return redirect(Config('constants.urlVar.userList'))->with('success','User created successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewUser'))->with('error','Failed to create User.');  
        }
      
        
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
    
        
        public function updateToken($token, $userID){
            if(DB::table('staffing_users')
               ->where('id', $userID)
               ->where('status', 1)
               ->where('deleteStatus', 0)
               ->update(['remember_token' => $token]))
               return true;
            else
                return  false;
        }   
    
    
    public function sentMailToUser($userID, $password, $token, $useremail, $name){
        
        $userInfo = User::find($userID);
        
        if($userInfo){
            
            $groupInfo = Group::find($userInfo->businessGroupID);        
            $link = url(Config('constants.urlVar.GenerateLoginIDAndPassword').$token);
            $text_link = url(Config('constants.urlVar.GenerateLoginIDAndPassword').$token);
            //$logo = url('/assets/img/logo.png'); 
            $logo = url('/assets/img/logo_light.png'); 
            $to = $userInfo->email; 
            $name = $userInfo->firstName." ".$userInfo->lastName;
            $subject = 'StaffingCall - Account Information';
            
            
                $data = array(
                    'name' => $name,
                    'link' => $link,
                    'groupName' => $groupInfo->groupName,
                    'groupCode' => $groupInfo->groupCode,
                    'logo' => $logo,
                    'to' => $to,
                    'subject' => $subject
                ); 



            Mail::send('templates.usercreation', $data, function ($message) use ($to, $name, $subject)
            {

                $message->to($to, $name)->subject($subject);
                $message->from('contact@agidev-staffingcall.com', 'Staffing Call App');

            });
        }

        return true;
    } 
    
    
    
    public function myProfile(){
        
        $userSkills = array();
        
        if(Auth::user()->role != 1 && Auth::user()->role != 2){
        $userInfoSql = DB::table('staffing_users')
                ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID') 
                ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_users.businessGroupID')   
                ->select(
                        'staffing_users.*',
                        'staffing_businessunits.unitName',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode'
                        );
        
        $userInfoSql->where('staffing_users.id','=',Auth::user()->id);
        if(Auth::user()->role == 0)
        $userInfoSql->where('staffing_usersunits.primaryUnit','=',1);
        
        $userInfo = $userInfoSql->first();
        
        
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
        
        
        }else{
           if(Auth::user()->role == 1) {
            
           $userInfo = DB::table('staffing_users')
                   ->select('*')->where([
                                ['staffing_users.id','=',Auth::user()->id]
                            ])->first();
           }else{
              $userInfo = DB::table('staffing_users')
                   ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_users.businessGroupID')   
                ->select('staffing_users.*',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode')->where([
                                ['staffing_users.id','=',Auth::user()->id]
                            ])->first(); 
           }
        }
        
        return view('users.profile',['userInfo' => $userInfo,'userSkills' => $userSkills]);
    }
    
    
    
    
    
    
    
    public function editProfile(){
        
        $userSkills = array();
        
        if(Auth::user()->role != 1 && Auth::user()->role != 2){
        $userInfo = DB::table('staffing_users')
                ->join('staffing_usersunits', 'staffing_usersunits.userID', '=', 'staffing_users.id')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID') 
                ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_users.businessGroupID')   
                ->select(
                        'staffing_users.*',
                        'staffing_businessunits.unitName',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode'
                        )->where([
                                ['staffing_users.id','=',Auth::user()->id]
                            ])->first();
        
        
        }else{
           if(Auth::user()->role == 1) {
            
           $userInfo = DB::table('staffing_users')
                   ->select('*')->where([
                                ['staffing_users.id','=',Auth::user()->id]
                            ])->first();
           }else{
              $userInfo = DB::table('staffing_users')
                   ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_users.businessGroupID')   
                ->select('staffing_users.*',
                        'staffing_groups.groupName',
                        'staffing_groups.groupCode')->where([
                                ['staffing_users.id','=',Auth::user()->id]
                            ])->first(); 
           }
        }
        
        return view('users.editprofile',['userInfo' => $userInfo,'userSkills' => $userSkills]);
    }
    
    
    public function saveNotificationSettings(Request $request){
            $loginUser = User::find(Auth::user()->id);
            $loginUser->calendarView = $request->calendarView?$request->calendarView:0;
            $loginUser->emailNotification = $request->emailNotification?$request->emailNotification:0;
            $loginUser->pushNotification = $request->pushNotification?$request->pushNotification:0;
            $loginUser->smsNotification = $request->smsNotification?$request->smsNotification:0;
            
            if($loginUser->save()){            
                return  response()->json([
                    'success'=>'1'
                ]);
            }else{
                return  response()->json([
                    'success'=>'0'
                ]);
            }
    }
    
    public function updateProfile(Request $request){
            
            $userID = Auth::user()->id;
            
            $user = User::find($userID);
            
            $groupID = Auth::user()->businessGroupID;
            $groupInfo = Group::find($groupID);
        
            $email = $request->email;
            $phone = $request->phone;
            $firstName = $request->firstName;
            $lastName = $request->lastName;
            
            if($user->role == 1){        
                $this->validate($request, [
                    'email' => 'required|email|unique:staffing_users,email,'.$userID,
                    'phone' => 'required',
                    'firstName' => 'required',
                    'profilePic' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', 
                ]);
                
            }
            else if($user->role == 2){        
                $this->validate($request, [
                    'email' => 'required|email|unique:staffing_users,email,'.$userID.',id,deleteStatus,0,businessGroupID,'.$groupID.'',
                    'phone' => 'required',
                    'firstName' => 'required',
                    'profilePic' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', 
                ]);
                
            }
            else if($user->role == 3){        
                $this->validate($request, [
                    'email' => 'required|email|unique:staffing_users,email,'.$userID.',id,deleteStatus,0,businessGroupID,'.$groupID.'',
                    'phone' => 'required',
                    'firstName' => 'required',
                    'profilePic' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', 
                ]);
                
            }else{
                
                $this->validate($request, [
                    'email' => 'required|email|unique:staffing_users,email,'.$userID.',id,deleteStatus,0,businessGroupID,'.$groupID.'',
                    'phone' => 'required',
                    'profilePic' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', 
                ]);
                
            }
                
            
            
            $user->email = $email;
            if($firstName !='' && $lastName !=''){
                $user->firstName = $firstName;
                $user->lastName = $lastName;
            }
            $user->phone = "+1".(str_replace(array("+1","+91"), "", $phone));
            
            $user->remember_token = $request->_token;
            
            
             if ($request->hasFile('profilePic')) {
                $originalName = $request->profilePic->getClientOriginalName();
                $getimageName = time().'.'.$request->profilePic->getClientOriginalExtension();
                $request->profilePic->move(public_path('assets/uploads/users'), $getimageName);        
                $profilePicUrl = $getimageName;
                $user->profilePic = 'assets/uploads/users/'.$profilePicUrl;
             } 
             
             /* Upload Cropped Image If Any */
            if($request->croppedImage != ''){
                $baseFromJavascript = $request->croppedImage;
                // We need to remove the "data:image/png;base64,"
                $base_to_php = explode(',', $baseFromJavascript);
                // the 2nd item in the base_to_php array contains the content of the image
                $data = base64_decode($base_to_php[1]);
                if($base_to_php[1] != ''){
                    $getimageName = time().$userID.".png";
                    // here you can detect if type is png or jpg if you want
                    $filepath = public_path()."/assets/uploads/users/".$getimageName; // or image.jpg
                    // Save the image in a defined path
                    file_put_contents($filepath,$data);
                    $user->profilePic = 'assets/uploads/users/'.$getimageName;
                }
            }  
                /* Upload Cropped Image If Any */
             
            
            if($user->save()){
              return redirect()->intended(Config('constants.urlVar.myProfile'))->with('success','Profile updated successfully.');      
            }else{
              return redirect()->intended(Config('constants.urlVar.editProfile'))->with('error','Failed to update profile.');    
            }
    }
    
    
    /* To fetch Add New User When End-User Created Business-Unit */
    public function ajaxGetBusinessUnit(Request $request){
        
        $businessUnitID = $request->businessUnitID;
        $businessUnitsSql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName');
        
        $businessUnitsSql->where('businessGroupID','=',Auth::user()->businessGroupID);
        
        $businessUnitsSql->where('deleteStatus','=',0);
        
        $businessUnitsSql->where('status','=',1);
        
        $businessUnitsSql->orderBy('unitName','ASC');  
        
        $businessUnits = $businessUnitsSql->get();
        
        $staffingCategory = DB::table('staffing_skillcategory')
                ->select(
                        'id',
                        'skillName'
                        )->where([
                                ['businessGroupID','=',Auth::user()->businessGroupID]
                            ])->orderBy('skillName','ASC')->get();
        
                        return response()->json([
                        'status'=>'1',
                        'businessUnits'=>$businessUnits,
                        'skills'=>$staffingCategory
                        ]);
        
    }
    /* To fetch Add New User When End-User Created Business-Unit */
    
    
    
    
}
