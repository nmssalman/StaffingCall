<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Illuminate\Support\Facades\Mail;

class GroupsController extends Controller
{   
    
        public function index()
    {   

        $groups = DB::table('staffing_groups')
                ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id')
                ->select(
                        'staffing_groups.id',
                        'staffing_groups.groupName', 
                        'staffing_groups.groupCode', 
                        'staffing_groups.maximumUnits', 
                        'staffing_groups.maximumEmployee', 
                        'staffing_groups.created_at', 
                        'staffing_groups.status')->where([['staffing_users.role','=',2]])
                ->get();
        
        
        return view('groups.show', ['groups' => $groups]);
    }
    
                            
                        
                         
                         
    public function ajaxGroupList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'staffing_groups.groupCode', 
            1 => 'staffing_groups.groupName', 
            2 => 'staffing_groups.groupCode',
            3=> 'staffing_groups.maximumUnits',
            4=> 'staffing_groups.maximumEmployee',
            5=> 'staffing_users.firstName',
            6=> 'staffing_groups.id'
        );
        
        $totalFiltered = DB::table('staffing_groups')->select('id')
                ->where([['deleteStatus','=',0]])->count();
        
        $sql = DB::table('staffing_groups')
                ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id')
                ->select(
                        'staffing_groups.id',
                        'staffing_groups.groupName', 
                        'staffing_groups.groupCode', 
                        'staffing_groups.maximumUnits', 
                        'staffing_groups.maximumEmployee',
                        'staffing_groups.status',
                        'staffing_groups.deleteStatus', 
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS managerName"),
                        'staffing_users.email', 
                        'staffing_users.phone');
        
        $sql->where('staffing_users.role', '=', 2);
        $sql->where('staffing_groups.deleteStatus', '=', 0);
       
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where(function ($query) use ($requestData) {
                $query->orWhere('staffing_groups.groupName', 'LIKE', $requestData['search']['value'].'%');
                $query->orWhere('staffing_users.email', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_groups.groupCode', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
            });
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        $sql->orderBy('staffing_groups.updated_at','DESC');
        $sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        //$sql->orderBy('staffing_users.firstName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
       
        $results = $sql->get();  
        $data = array();
        foreach($results as $result){
                              
                $nestedData=array();  
                
                $activeBtns = '';
                $url = url(Config('constants.urlVar.setGroupActiveDeactive').$result->id.'/'.$result->status);
                if($result->status){
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to De-activate" href="javascript:void(0);" '
                          . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-success mb-10">Active</a>';  
                }else{
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to Activate" href="javascript:void(0);" '
                         . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-danger mb-10">Deactive</a>';  
                }

                $htmlAction = $activeBtns.' <a href="'.url(Config('constants.urlVar.editGroup').$result->id).'" '
                        . 'class="btn btn-sm btn-outline-info mb-10"><i class="fa fa-edit"></i></a>'
                        . '<a title="Delete Group" onclick="return confirm(\'Do you really want to delete Group- '.$result->groupName.' ?\')" '
                        . 'href="'.url(Config('constants.urlVar.deleteGroup').$result->id).'" '
                        . 'class="btn btn-sm btn-danger mb-10"><i class="fa fa-trash"></i></a>';
                
                $nestedData[] = $result->groupCode;
                $nestedData[] = $result->groupName;
                $nestedData[] = '<a href="'.url(Config('constants.urlVar.login').$result->groupCode).'">'.url(Config('constants.urlVar.login').$result->groupCode).'</a>';
                $nestedData[] = $result->maximumUnits?$result->maximumUnits:'Unlimited';
                $nestedData[] = $result->maximumEmployee?$result->maximumEmployee:'Unlimited';
                $nestedData[] = $result->managerName;
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
    
    
    public function removeGroupLogo($groupID){
        if($groupID){
            $groupInfo = Group::find($groupID);
            if($groupInfo){
              $groupInfo->logo = 'assets/img/group-logo.png';
              $groupInfo->save();
            }
        }
        
        return true;
    }
    
    
    public function deleteGroup($id){ 		
        $data=array('deleteStatus' => 1);
        $success = $this->updateGroupInfoByID($id,$data);
        if($success)
        {
            
          /* Delete All Users Of This Group */ 
            $this->updateUserInfoByID($id,$data);
          /* Delete All Users Of This Group */ 
            
          return redirect(Config('constants.urlVar.groupList'))->with('success','Group deleted successfully.');   
        }else{
          return redirect(Config('constants.urlVar.groupList').$id)->with('error','Failed to delete Group.');  
        }

    }
    
    public function updateUserInfoByID($groupID, $data = array()){
     if(DB::table('staffing_users')
        ->where('businessGroupID', $groupID)
        ->update($data))
        return true;
     else
         return  false;
    } 
    
	 
    public function setActiveDeactive($id, $status){ 		
        $data=array('status' => $status ? 0 : 1);
        $this->updateGroupInfoByID($id,$data);
        
        $groupInfo = Group::find($id); 
        
        $data=array('id'=>$id,'status' => $groupInfo->status);
        return view('groups.toggle', ['results' => $data]);

    }
    
    public function updateGroupInfoByID($id, $data = array()){
     if(DB::table('staffing_groups')
        ->where('id', $id)
        ->update($data))
        return true;
     else
         return  false;
    } 

        
    public function createGroup(){
        return view('groups.new');
    } 
    
    
    public function saveGroup(Request $request){
        
        $groupName = $request->groupName;
        $groupCode = $request->groupCode;
        $maximumUnits = $request->maximumUnits;
        $maximumEmployee = $request->maximumEmployee;
        $firstName = $request->firstName;
        $lastName = $request->lastName;
        $userName = $request->userName;
        $email = $request->email;
        //$password = $request->password;
        $phone = $request->phone;
        $whiteLabelOption = $request->whiteLabelOption;
        $val = 1;
        $this->validate($request, [
            'groupCode' => 'required|unique:staffing_groups,groupCode,'.$val.',deleteStatus',
            'groupName' => 'required',//unique:staffing_groups
            'firstName' => 'required',
            'lastName' => 'required',
            'userName' => 'required|unique:staffing_users,userName,'.$val.',deleteStatus',
            /*'email' => 'required|email|unique:staffing_users',*/
            'email' => 'required|email',
            'phone' => 'required'/*,
            'password' => 'required|alpha_num|min:8|confirmed',
            'password_confirmation' => 'required|alpha_num|min:8'*/
        ]);
        
        
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';  
        $password   = substr(str_shuffle($str),0,6);
        
        
        
        $group = new Group;
        $group->groupCode = $groupCode;
        $group->groupName = $groupName;
        $group->maximumUnits = $maximumUnits?$maximumUnits:0;
        $group->maximumEmployee = $maximumEmployee?$maximumEmployee:0;
        $group->whiteLabelOption = $whiteLabelOption?$whiteLabelOption:0;
        
        if($group->save())
        {
            $lastGroupId = $group->id; 
            
            $user = new User;
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            $user->userName = $userName;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->phone = $phone?("+1".(str_replace("+1", "", $phone))):'';
            $user->businessGroupID = $lastGroupId;
            $user->role = 2;
            $user->passwordAlertNotice = 1;//Show alert first time when manager logged in
            $user->remember_token = $request->_token;
            $user->save();
            
            /* SEND EMAIL TO GROUP MANAGER */
            $this->sentMailToGroupManager($user->id, $password);
            /* SEND EMAIL TO GROUP MANAGER */
            
            $insertedData = array(
                ['businessGroupID' => $lastGroupId,
                  'name' => 'Simple',
                  'type' => 'simple' ,
                  'notes' => 'For simple algorithm , follow the below order to display and suggest responded candidates
                            1. Full shift available , not overtime 
                            2. Full shift available, overtime
                            3. Partial shift available, not overtime 
                            4. Partial shift available, overtime'  ],
                ['businessGroupID' => $lastGroupId,
                  'name' => 'Open',
                  'notes' => 'Offer Algorithm',  
                  'type' => 'open' ]  ,
                ['businessGroupID' => $lastGroupId,
                  'name' => 'Complex',
                  'notes' => 'Offer Algorithm - Complex',  
                  'type' => 'complex' ]  
            );
            
            $success = DB::table('staffing_offeralgorithm')->insert($insertedData); 
            
          return redirect(Config('constants.urlVar.groupList'))->with('success','Group created successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewGroup'))->with('error','Failed to create Group.');  
        }
        
        
    }
    
    public function sentMailToGroupManager($userID, $password){
        
        $userInfo = User::find($userID);
        
        if($userInfo){
            
            $groupInfo = Group::find($userInfo->businessGroupID);
        
            $link = url(Config('constants.urlVar.login').$groupInfo->groupCode);
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
                    'loginID' => $userInfo->userName,
                    'password' => $password,
                    'logo' => $logo,
                    'to' => $to,
                    'subject' => $subject
                ); 



            Mail::send('templates.managercreation', $data, function ($message) use ($to, $name, $subject)
            {

                $message->to($to, $name)->subject($subject);
                $message->from('contact@agidev-staffingcall.com', 'Staffing Call App');

            });
        }

        return true;
    } 
    
    
    public function edit($id){
        $group = Group::find($id); 
        $manager = DB::table('staffing_users')->select('*')
                ->where([
                    ['businessGroupID','=',$group->id],
                    ['role','=',2]
                    ])->first();
        return view('manager.groups.update', ['group' => $group,'manager' => $manager]);
    }
    
    /* For Groups & Unit Management For Group Manager Account*/
    
    
    public function updateGroup(Request $request){
        
        $id = $request->id;
        $managerID = $request->managerID?$request->managerID:0;
        
        $group = Group::find($id); 
        $groupID = $group->id;
        
        $groupName = $request->groupName;
        
        if(Auth::user()->role == '1')//Group Admin
        {
            $user = User::find($managerID); 
            
            $groupCode = $request->groupCode;
            $maximumUnits = $request->maximumUnits;
            $maximumEmployee = $request->maximumEmployee;
            $firstName = $request->firstName;
            $lastName = $request->lastName;
            $userName = $request->userName;
            $email = $request->email;
            $password = $request->password;
            $phone = $request->phone;
            $whiteLabelOption = $request->whiteLabelOption;
            
            if($maximumEmployee > 0){
                
                /* Check per business Units Employees count */
                $perUnitEmployees = DB::table('staffing_usersunits')
                    ->select(DB::raw('count(staffing_usersunits.businessUnitID) AS totalEmployees'),
                        'staffing_usersunits.businessUnitID',
                        'staffing_businessunits.unitName')
                    ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')
                    ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                    ->where([['staffing_businessunits.deleteStatus', '=', 0],
                        ['staffing_users.deleteStatus', '=', 0],
                        ['staffing_users.businessGroupID', '=', $groupID],
                        ['staffing_businessunits.businessGroupID', '=', $groupID]])
                    ->groupBy('staffing_usersunits.businessUnitID')
                    ->orderBy('totalEmployees', 'DESC')->get();
                
               
               
                /* Check per business Units Employees count */
                
//                $getNumberOfUsersSql = DB::table('staffing_users');
//                $getNumberOfUsersSql->where('deleteStatus', '=', '0');
//                $getNumberOfUsersSql->where('businessGroupID', $groupID);
//                $getNumberOfUsersSql->whereNotIn('role', [1,2]);
//                $getNumberOfUsers = $getNumberOfUsersSql->count();
                
                 if(count($perUnitEmployees) > 0){
                
                    if($perUnitEmployees[0]->totalEmployees > $maximumEmployee){
                       return redirect(Config('constants.urlVar.editGroup').$id)
                               ->with('error','You already have '.$perUnitEmployees[0]->totalEmployees.' employees in "'.$perUnitEmployees[0]->unitName.'" Business Unit');    
                    }
                 }
            }
            
            if($maximumUnits > 0){
                $getNumberOfUnitsSql = DB::table('staffing_businessunits');
                $getNumberOfUnitsSql->where('deleteStatus', '=', '0');
                $getNumberOfUnitsSql->where('businessGroupID', $groupID);
                $getNumberOfUnits = $getNumberOfUnitsSql->count();
                if($getNumberOfUnits > $maximumUnits){
                   return redirect(Config('constants.urlVar.editGroup').$id)
                        ->with('error','You already have '.$getNumberOfUnits.' Business Units in your Group.');    
                }
            }
            
            
        //'groupCode' => 'required|unique:staffing_groups,groupCode,'.$val.',deleteStatus',
            $this->validate($request, [
                'groupCode' => 'required|unique:staffing_groups,groupCode,'.$id.',id,deleteStatus,0',
                'groupName' => 'required',
                'firstName' => 'required',
                'lastName' => 'required',
                'userName' => 'required|unique:staffing_users,userName,'.$managerID.',id,deleteStatus,0',
                'email' => 'required|email|unique:staffing_users,email,'.$managerID.',id,deleteStatus,0,businessGroupID,'.$groupID.'',  
                'phone' => 'required'
            ]); 
            
            $group->groupCode = $groupCode;
            $group->groupName = $groupName;
            $group->maximumUnits = $maximumUnits?$maximumUnits:0;
            $group->maximumEmployee = $maximumEmployee?$maximumEmployee:0;
            $group->whiteLabelOption = $whiteLabelOption?$whiteLabelOption:0;
            
            if ($request->hasFile('logo')) {
                $originalName = $request->logo->getClientOriginalName();
                $getimageName = time().'.'.$request->logo->getClientOriginalExtension();
                $request->logo->move(public_path('assets/uploads/groups'), $getimageName);        
                $logoUrl = $getimageName;
                $group->logo = 'assets/uploads/groups/'.$logoUrl;
             }  
             
             
            /* Upload Cropped Image If Any */
            if($request->croppedImage != ''){
                $baseFromJavascript = $request->croppedImage;
                // We need to remove the "data:image/png;base64,"
                $base_to_php = explode(',', $baseFromJavascript);
                // the 2nd item in the base_to_php array contains the content of the image
                $data = base64_decode($base_to_php[1]);
                if($base_to_php[1] != ''){
                    $getimageName = time().$groupID.".png";
                    // here you can detect if type is png or jpg if you want
                    $filepath = public_path()."/assets/uploads/groups/".$getimageName; // or image.jpg
                    // Save the image in a defined path
                    file_put_contents($filepath,$data);
                    $group->logo = 'assets/uploads/groups/'.$getimageName;
                }
            }  
                /* Upload Cropped Image If Any */
              
            
            
        }else{
            $this->validate($request, [
                  'groupName' => 'required',//'required|unique:staffing_groups,groupName,'.$id,
                  'logo' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048', 
              ]);


             if ($request->hasFile('logo')) {
                $originalName = $request->logo->getClientOriginalName();
                $getimageName = time().'.'.$request->logo->getClientOriginalExtension();
                $request->logo->move(public_path('assets/uploads/groups'), $getimageName);        
                $logoUrl = $getimageName;
                $group->logo = 'assets/uploads/groups/'.$logoUrl;
             } 
             
                /* Upload Cropped Image If Any */
            if($request->croppedImage != ''){
                $baseFromJavascript = $request->croppedImage;
                // We need to remove the "data:image/png;base64,"
                $base_to_php = explode(',', $baseFromJavascript);
                // the 2nd item in the base_to_php array contains the content of the image
                $data = base64_decode($base_to_php[1]);
                if($base_to_php[1] != ''){
                    $getimageName = time().$groupID.".png";
                    // here you can detect if type is png or jpg if you want
                    $filepath = public_path()."/assets/uploads/groups/".$getimageName; // or image.jpg
                    // Save the image in a defined path
                    file_put_contents($filepath,$data);
                    $group->logo = 'assets/uploads/groups/'.$getimageName;
                }
            }  
                /* Upload Cropped Image If Any */
             
             
             
        }
        
        $group->groupName = $groupName;
        
        if($group->save())
        {
           if(Auth::user()->role == '1')//Group Admin
        { 
            $user->firstName = $firstName;
            $user->lastName = $lastName;
            $user->userName = $userName;
            $user->email = $email;
            $user->phone = $phone?("+1".(str_replace("+1", "", $phone))):'';
            $user->businessGroupID = $group->id;
            $user->role = 2;
            $user->remember_token = $request->_token;
            $user->save();  
          
           
          return redirect()->intended(Config('constants.urlVar.groupList'))->with('success','Group information updated successfully.');    
        }else{
          return redirect()->intended(Config('constants.urlVar.managerGroupList'))->with('success','Group information updated successfully.');      
        }
          
        }else{
          return redirect()->intended(Config('constants.urlVar.editGroup').$id)->with('error','Failed to update group.');  
        }
        
    }
    
     public function managerGroupIndex()
    {   

        $groups = DB::table('staffing_groups')
                ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id')
                ->select(
                        'staffing_groups.id',
                        'staffing_groups.groupName', 
                        'staffing_groups.groupCode', 
                        'staffing_groups.maximumUnits', 
                        'staffing_groups.maximumEmployee', 
                        'staffing_groups.created_at', 
                        'staffing_groups.status')
                ->where([['staffing_groups.id','=',Auth::user()->businessGroupID],
                    ['staffing_users.id','=',Auth::user()->id]])->get();
        
        
        return view('manager.groups.show', ['groups' => $groups]);
    }
    
    
    public function ajaxManagerGroupInfo(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'groupCode', 
            1 => 'groupName',
            2=> 'logo',
            3=> 'maximumUnits',
            4=> 'maximumEmployee',
            5=> 'unit',
            6=> 'id'
        );
        
        $totalFiltered = DB::table('staffing_groups')->select('id')
                ->where([['staffing_groups.id','=',Auth::user()->businessGroupID]])
                ->count();
        
        $sql = DB::table('staffing_groups')
                ->join('staffing_users', 'staffing_users.businessGroupID', '=', 'staffing_groups.id')
                ->select(
                        'staffing_groups.id',
                        'staffing_groups.groupName', 
                        'staffing_groups.groupCode', 
                        'staffing_groups.maximumUnits', 
                        'staffing_groups.maximumEmployee', 
                        'staffing_groups.logo', 
                        DB::raw("CONCAT(staffing_users.firstName, ' ', staffing_users.lastName) AS managerName"),
                        'staffing_users.email', 
                        'staffing_users.phone');
        
        $sql->where('staffing_groups.id','=',Auth::user()->businessGroupID);
        $sql->where('staffing_users.id','=',Auth::user()->id);
        
       
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where(function ($query) use ($requestData) {
                $query->orWhere('staffing_groups.groupName', 'LIKE', $requestData['search']['value'].'%');
                $query->orWhere('staffing_users.email', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_groups.groupCode', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.firstName', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('staffing_users.lastName', 'LIKE', $requestData['search']['value'].'%'); 
            });
            
            
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData; 
        $results = $sql->get();  
        $data = array();
        foreach($results as $result){
            
                $htmlAction = '<a href="'.url(Config('constants.urlVar.editGroup').$result->id).'" class="btn btn-sm btn-outline-info">Edit</a>';
                              
                $nestedData=array(); 

                $nestedData[] = $result->groupCode;
                $nestedData[] = $result->groupName;
                $nestedData[] = '<img class="img_style" width="70" height="70" src="'
                                .($result->logo?url('public/'.$result->logo):
                                url('/assets/img/img_preview.png')).'" />';
                $nestedData[] = $result->maximumUnits?$result->maximumUnits:'Unlimited';
                $nestedData[] = $result->maximumEmployee?$result->maximumEmployee:'Unlimited';
                $nestedData[] = '<a href="'.url(Config('constants.urlVar.unitList')).'">Click to view</a>';
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
    /* For Groups & Unit Management For Group Manager Account*/
    	
    
}
