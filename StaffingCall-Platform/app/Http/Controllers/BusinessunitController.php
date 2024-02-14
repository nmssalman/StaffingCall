<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Businessunit;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;

class BusinessunitController extends Controller
{   
    
        public function index()
    {   
         
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }      
            
        $units = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName', 
                        'storeNumber', 
                        'information',  
                        'businessGroupID', 
                        'created_at', 
                        'status')->where([
                            ['businessGroupID','=',Auth::user()->businessGroupID],
                            ['deleteStatus','=',0]
                                ])
                ->get();
        
        
        return view('units.show', ['units' => $units]);
    }
    
    
    public function ajaxUnitList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'unitName', 
            1 => 'storeNumber',
            2=> 'information',
            3=> 'status'
        );
        
        $totalFiltered = DB::table('staffing_businessunits')->select('id')
                ->where([['businessGroupID','=',Auth::user()
                ->businessGroupID],
                ['deleteStatus','=',0]])->count();
        
        $sql = DB::table('staffing_businessunits')
                ->select(
                        'id',
                        'unitName', 
                        'storeNumber', 
                        'information', 
                        'status'
                        );
        
        $sql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where('deleteStatus','=',0);
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where(function ($query) use ($requestData) {
                $query->orWhere('unitName', 'LIKE', $requestData['search']['value'].'%');
                $query->orWhere('storeNumber', 'LIKE', $requestData['search']['value'].'%'); 
                $query->orWhere('information', 'LIKE', '%'.$requestData['search']['value'].'%'); 
            });
            
           
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData;        
        //$sql->orderBy($columns[$requestData['order'][0]['column']],$requestData['order'][0]['dir']);
        $sql->orderBy('unitName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        foreach($results as $result){
            
           
                              
                $nestedData=array(); 
                
                $activeBtns = '';
                $url = url(Config('constants.urlVar.setUnitActiveDeactive').$result->id.'/'.$result->status);
                if($result->status){
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to De-activate" href="javascript:void(0);" '
                          . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-success mb-10">Active</a>';  
                }else{
                  $activeBtns = '<a id="A-'.$result->id.'" title="Click to Activate" href="javascript:void(0);" '
                         . ' onclick="published.toggle(\'A-'.$result->id.'\',\''.$url.'\')"'
                        . 'class="btn btn-sm btn-danger mb-10">Deactive</a>';  
                }

                $nestedData[] = $result->unitName;
                $nestedData[] = $result->storeNumber;
                $nestedData[] = $result->information;
                $nestedData[] = $activeBtns.' <a href="'.url(Config('constants.urlVar.editUnit').$result->id).'" '
                        . 'class="btn btn-sm btn-outline-info mb-10">Edit</a>'
                        . '<a title="View detail" href="'.url(Config('constants.urlVar.businessUnitDetail').$result->id).'" '
                        . 'class="btn btn-sm btn-outline-default mb-10"><i class="fa fa-eye"></i></a>'
                        . '<a title="Delete Business Unit" onclick="return confirm(\'Do you really want to delete Business Unit- '.$result->unitName.' ?\')" '
                        . 'href="'.url(Config('constants.urlVar.deleteBusinessUnit').$result->id).'" '
                        . 'class="btn btn-sm btn-danger mb-10"><i class="fa fa-trash"></i></a>';
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
    
    
	 
    public function deleteBusinessUnit($id){ 
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $data=array('deleteStatus' => 1);
        $success = $this->updateUnitInfoByID($id,$data);
        if($success)
        {
          return redirect(Config('constants.urlVar.unitList'))->with('success','Business unit deleted successfully.');   
        }else{
          return redirect(Config('constants.urlVar.unitList').$id)->with('error','Failed to delete Business unit.');  
        }

    }
    
	 
    public function setActiveDeactive($id, $status){ 
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $data=array('status' => $status ? 0 : 1);
        $this->updateUnitInfoByID($id,$data);
        
        $unitInfo = Businessunit::find($id); 
        
        $data=array('id'=>$id,'status' => $unitInfo->status);
        return view('units.toggle', ['results' => $data]);

    }
    
    public function updateUnitInfoByID($id, $data = array()){
     if(DB::table('staffing_businessunits')
        ->where('id', $id)
        ->update($data))
        return true;
     else
         return  false;
    } 
    
    
    

        
    public function createUnit(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
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
                $query->orWhere('type', '=', 'complex'); 
        });
        
        $algorithmsSql->orderBy('id','ASC');
        
        $algorithms = $algorithmsSql->get();
        /* Get Complex-Algorithm Ordering Data */
        $complexOrders = DB::table('staffing_complexalgorithmordering')
            ->select('id', 'name')->orderBy('id','ASC')->get();
        /* Get Complex-Algorithm Ordering Data */
        return view('units.new', ['algorithms' => $algorithms, 'complexOrders' => $complexOrders]);
    } 
    
    public function editUnit($id){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $units = Businessunit::find($id);
        
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
                $query->orWhere('type', '=', 'complex'); 
        });
        
        $algorithmsSql->orderBy('id','ASC');
        
        $algorithms = $algorithmsSql->get();
        /* Get Complex-Algorithm Ordering Data */
        if($units->offerAlgorithmID > 0 && !empty($units->complexPoolOrder)){
            //echo $units->complexPoolOrder;
            $complexPoolOrderArr = explode(',', $units->complexPoolOrder);
            //echo '<pre>'; print_r($complexPoolOrderArr);
            $complexOrders = DB::table('staffing_complexalgorithmordering')
            ->select('id', 'name')->whereIn('id', $complexPoolOrderArr)
                    ->orderBy(DB::raw("FIELD(`id`, ".$units->complexPoolOrder.")"))
                    ->get();
            
        }else{
            $complexOrders = DB::table('staffing_complexalgorithmordering')
            ->select('id', 'name')->orderBy('id','ASC')->get();
        }
        /* Get Complex-Algorithm Ordering Data */
        return view('units.update',[
            'units' => $units, 
            'algorithms' => $algorithms, 
            'complexOrders' => $complexOrders]);
    } 
    
    
    public function updateBusinessUnit(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        
        $groupID = Auth::user()->businessGroupID;
        $id = $request->id;
        
        $unitName = $request->unitName;
        $storeNumber = $request->storeNumber;
        $information = $request->information;
        $offerAlgorithmID = $request->offerAlgorithmID;
        $val = 0;//where deleteStatus = 0
        $this->validate($request, [
            'unitName' => 'required|unique:staffing_businessunits,unitName,'.$id.',id,deleteStatus,'.$val.',businessGroupID,'.$groupID,
            'storeNumber' => 'required'
        ]);
        
        
        $unit = Businessunit::find($id);
        
        $unit->unitName = $unitName;
        $unit->storeNumber = $storeNumber;
        $unit->information = $information;
        $unit->businessGroupID = Auth::user()->businessGroupID;
        
            if($offerAlgorithmID > 0){
                $algorithms = DB::table('staffing_offeralgorithm')
                ->select('id', 'type')->where([['id', '=', $offerAlgorithmID]])->first();
                if($algorithms->id > 0){
                  $unit->offerAlgorithmID = $offerAlgorithmID?$offerAlgorithmID:0;
                  if($algorithms->type == 'complex'){
                    $unit->complexPoolOrder = $request->complexOrder;
                  }
                }
            }
        
        if($unit->save())
        {
          return redirect(Config('constants.urlVar.unitList'))->with('success','Business unit updated successfully.');   
        }else{
          return redirect(Config('constants.urlVar.editUnit').$id)->with('error','Failed to update Business unit.');  
        }
    } 
    
    
    public function saveUnit(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        
        $groupID = Auth::user()->businessGroupID;
        $groupInfo = Group::find($groupID);
        
        if($groupInfo->maximumUnits > 0){
            $getNumberOfUnitsSql = DB::table('staffing_businessunits');
            $getNumberOfUnitsSql->where('deleteStatus', '=', '0');
            $getNumberOfUnitsSql->where('businessGroupID', $groupID);
            $getNumberOfUnits = $getNumberOfUnitsSql->count();
            if($getNumberOfUnits >= $groupInfo->maximumUnits){
               return redirect(Config('constants.urlVar.unitList'))->with('error','Sorry! Maximum limit of Business Unit creation has been finished.');    
            }
        }
        
        $unitName = $request->unitName;
        $storeNumber = $request->storeNumber;
        $information = $request->information;
        $offerAlgorithmID = $request->offerAlgorithmID;
        $val = 1;
        $this->validate($request, [
            'unitName' => 'required|unique:staffing_businessunits,unitName,'.$val.',deleteStatus,businessGroupID,'.$groupID,
            'storeNumber' => 'required'
        ]);
        
        $unit = new Businessunit;
        $unit->unitName = $unitName;
        $unit->storeNumber = $storeNumber;
        $unit->information = $information;
        $unit->businessGroupID = Auth::user()->businessGroupID;
        
            if($offerAlgorithmID > 0){
               
                $algorithms = DB::table('staffing_offeralgorithm')
                ->select('id', 'type')->where([['id', '=', $offerAlgorithmID]])->first();
                
                if($algorithms->id > 0){
                  $unit->offerAlgorithmID = $offerAlgorithmID?$offerAlgorithmID:0;
                  if($algorithms->type == 'complex'){
                    $unit->complexPoolOrder = $request->complexOrder;
                  }
                }
            }
        
        if($unit->save())
        {
          return redirect(Config('constants.urlVar.unitList'))->with('success','Business unit created successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewUnit'))->with('error','Failed to create Business unit.');  
        }
        
        
    }
    
    
    
    
    /* Business Unit Management For Super Admin to manage End-User Skills Category & New Request Reasons etc  */
    
      
    public function unitSkillsCategoryList(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $skills = DB::table('staffing_skillcategory')
            ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_skillcategory.businessGroupID')
            ->select(
            'staffing_skillcategory.id',
            'staffing_skillcategory.skillName'
            )->where([['staffing_skillcategory.businessGroupID','=',Auth::user()->businessGroupID]])
            ->get();
        
        return view('units.skillscategorylist', ['skills' => $skills]);
    } 
    
    
    public function ajaxSkillCategory(){
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'staffing_skillcategory.skillName'
        );
        
        $totalFiltered = DB::table('staffing_skillcategory')
            ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_skillcategory.businessGroupID')
            ->select('id')
                ->where([['staffing_skillcategory.businessGroupID','=',Auth::user()
                ->businessGroupID]])->count();
        
        $sql = DB::table('staffing_skillcategory')
            ->join('staffing_groups', 'staffing_groups.id', '=', 'staffing_skillcategory.businessGroupID')
            ->select(
                    'staffing_skillcategory.id',
                    'staffing_skillcategory.skillName',
                    'staffing_groups.groupName'
                    );
        
        $sql->where('staffing_skillcategory.businessGroupID','=',Auth::user()->businessGroupID);
        
//        if(Auth::user()->role == 3){//Super-Admin
//            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//            $userUnitID = DB::table('staffing_usersunits')
//                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
//                ->select('staffing_businessunits.id AS businessUnitID')
//                ->where([
//                    ['staffing_usersunits.userID','=',Auth::user()->id]])
//                 ->first();   
//            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
//          
//            $sql->where('staffing_businessunits.id','=',$userUnitID->businessUnitID);
//            
//        }
        
        if( !empty($requestData['search']['value']) ) {
            
            
            $sql->where('staffing_skillcategory.skillName', 'LIKE', '%'.$requestData['search']['value'].'%'); 
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData; 
        $sql->orderBy('staffing_skillcategory.skillName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        foreach($results as $result){
                              
            $nestedData=array(); 
            $nestedData[] = $result->skillName;
            $nestedData[] = '<a href="'.url(Config('constants.urlVar.editSkillCategory').$result->id).'" class="btn btn-outline-info mb-10">Edit</a>';
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
      
    public function addNewSkillCategory(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
                        ->select(
                        'id',
                        'groupCode',
                        'groupName'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        return view('units.newskillcategory', ['groups' => $businessGroup]);
    } 
    
    
     
    
    
    public function saveSkillCategory(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $groupID = Auth::user()->businessGroupID;
        $groupInfo = Group::find($groupID);
        $skillName = $request->skillName;
        $businessUnitID = 0;
        // //unique: table, column, exception, exceptionValue, where, whereValue
        $this->validate($request, [
            'skillName' => 'required|unique:staffing_skillcategory,skillName,NULL,NULL,businessGroupID,'.$groupID.'',
        ]);
        
        $insertData = array(
          'businessGroupID' => Auth::user()->businessGroupID,
          'businessUnitID' => $businessUnitID,  
          'skillName' => $skillName ,  
          'status' => 1 
        );
        
        $success = DB::table('staffing_skillcategory')->insert([$insertData]);
        
        if($success)
        {
          return redirect(Config('constants.urlVar.unitSkillsCategoryList'))->with('success','Skill category created successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewSkillCategory'))->with('error','Failed to create Skill Category.');  
        }
        
        
    }
    
    
    
    
      
    public function editSkillCategory($id){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
                        ->select(
                        'id',
                        'groupCode',
                        'groupName'
                        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $skills = DB::table('staffing_skillcategory')
            ->select(
            'id',
            'skillName',
            'businessGroupID',
            'businessUnitID'
        )->where([['id','=',$id]])->first();
        
        
        
        return view('units.updateskillcategory', ['groups' => $businessGroup,
                    'skills' => $skills]);
    }
    
    
    
    
    
    public function updateSkills(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $groupID = Auth::user()->businessGroupID;
        $groupInfo = Group::find($groupID);
        
        $id = $request->id;
        
        $skills = DB::table('staffing_skillcategory')
            ->select(
            'id'
        )->where([['id','=',$id]])->first();
        
        $skillName = $request->skillName;
        $businessUnitID = 0;
        
        // //unique: table, column, exception, exceptionValue, where, whereValue
        $this->validate($request, [
            'skillName' => 'required|unique:staffing_skillcategory,skillName,'.$id.',id,businessGroupID,'.$groupID.'',
        ]);
        
//        $this->validate($request, [
//            'skillName' => 'required|unique:staffing_skillcategory,skillName,'.'id,'.$id.''
//        ]);
        
        $updationData = [
            'businessUnitID' => $businessUnitID ,  
            'skillName' => $skillName
        ];
        
        
        $success = DB::table('staffing_skillcategory')
        ->where('id', $id)
        ->update($updationData);
        
          
        return redirect(Config('constants.urlVar.unitSkillsCategoryList'))
                ->with('success','Skill category updated successfully.'); 
        
    }
    
    
    
    
    /* Staffing Request Reasons Questions management By SuperAdmin */
      
    public function requestReasonsList(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
//        $reasons = DB::table('staffing_requestreasons')
//            ->select(
//            'id',
//            'reasonName',
//            'status'        
//            )->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//               ->orWhere('businessGroupID','=',0)
//            ->orderBy('id','ASC')    
//            ->get();
        
         
        $reasons = DB::table('staffing_requestreasons')
            ->select(
            'id',
            'reasonName',
            'status'        
            )->where('businessGroupID','=',Auth::user()->businessGroupID)
               ->where('status','=',1)
            ->orderBy('id','ASC')    
            ->get(); 
         
         
        $defaultReasons = DB::table('staffing_requestreasons')
            ->select(
            'id',
            'reasonName',
            'status'        
            )->where([['businessGroupID','=',0]])
            ->get();
        
        return view('units.requestReasons.show', ['reasons' => $reasons, 'defaultReasons' => $defaultReasons]);
    } 
    
    
    public function ajaxRequestReasonsList(){
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'reasonName',
            1 =>'status'
        );
        
//        $totalFiltered = DB::table('staffing_requestreasons')->select('id')
//                ->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//                                ->orWhere('businessGroupID','=',0)->count();
        
        $totalFiltered = DB::table('staffing_requestreasons')->select('id')
                ->where('businessGroupID','=',Auth::user()->businessGroupID)
                                ->where('status','=',1)->count();
        
        $sql = DB::table('staffing_requestreasons')
            ->select(
                    'id',
                    'reasonName',
                    'isDefault',
                    'defaultOf',
                    'status'
                    );
        
//        $sql->orWhere('businessGroupID','=',Auth::user()->businessGroupID);
//        $sql->orWhere('businessGroupID','=',0);
        
        $sql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where('status','=',1);
        
        
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where('reasonName', 'LIKE', $requestData['search']['value'].'%');
           
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData; 
        $sql->orderBy('isDefault','DESC');
        $sql->orderBy('reasonName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        foreach($results as $result){
                              
                $nestedData=array(); 
                if($result->isDefault == '1'){
                   $htmlAction = '<a href="javascript:void(0)" class="btn btn-success mb-10">Default</a>'; 
                }else{
                   $htmlAction = '<a href="'.url(Config('constants.urlVar.editRequestReason').$result->id).'" class="btn btn-outline-info mb-10">Edit</a>'; 
                }
                
                $htmlAction .= '&nbsp;&nbsp;<a title="Delete Reason" onclick="return confirm(\'Do you really want to delete - '.$result->reasonName.' ?\')" href="'.url(Config('constants.urlVar.removeRequestReasons').$result->id).'" class="btn btn-outline-danger mb-10">Delete</a>'; 
                
                $infoText = '';
                if($result->defaultOf == 1):   
                      $infoText = '<br /><small>(Staff information will be required)</small>';
                endif;
                
                $nestedData[] = $result->reasonName.$infoText;
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
    
      
    public function addNewRequestReason(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
            ->select(
            'id',
            'groupCode',
            'groupName'
        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        return view('units.requestReasons.new', ['groups' => $businessGroup]);
    } 
    
      
    public function editRequestReason($id){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
            ->select(
            'id',
            'groupCode',
            'groupName'
        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $requestReason = DB::table('staffing_requestreasons')
            ->select(
            'id',
            'reasonName'
        )->where([['id','=',$id]])->first();
        
        
        return view('units.requestReasons.update', 
           [
            'groups' => $businessGroup,
            'requestReason' => $requestReason
            ]);
    } 
    
    
    
    
    
    
    public function updateRequestReason(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $id = $request->id;
        
        $requestReason = DB::table('staffing_requestreasons')
            ->select(
            'id',
            'reasonName'
        )->where([['id','=',$id]])->first();
        
        $reasonName = $request->reasonName;
        
        $this->validate($request, [
            'reasonName' => 'required|unique:staffing_requestreasons,reasonName,'.$id.',id,status,1,isDefault,0,businessGroupID,'.Auth::user()->businessGroupID.'', 
               
        ]);
        
        
            $success = DB::table('staffing_requestreasons')
            ->where('id', $id)
            ->update(['reasonName' => $reasonName]);
        
        if($requestReason->reasonName != $reasonName){
            
            if($success)
            {
              return redirect()->intended(Config('constants.urlVar.requestReasonsList'))->with('success','Reason updated successfully.');    
            }else{
              return redirect()->intended(Config('constants.urlVar.editRequestReason').$id)->with('error','Failed to update reason.');  
            }
        }else{
          return redirect()->intended(Config('constants.urlVar.requestReasonsList'))->with('success','Reason updated successfully.');      
        }
        
    }
    
    
    
    public function saveRequestReason(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $reasonName = $request->reasonName;
        
        $this->validate($request, [
            'reasonName' => 'required|unique:staffing_requestreasons,reasonName,NULL,NULL,status,1,isDefault,0,businessGroupID,'.Auth::user()->businessGroupID.''
            ]);
        
        $insertData = array(
          'businessGroupID' => Auth::user()->businessGroupID,
          'reasonName' => $reasonName ,  
          'status' => 1 
        );
        
        $success = DB::table('staffing_requestreasons')->insert([$insertData]);
        
        if($success)
        {
          return redirect(Config('constants.urlVar.requestReasonsList'))->with('success','New reason added to list successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewRequestReason'))->with('error','Failed to add new reason.');  
        }
        
        
    }
    
    /* Staffing Request Reasons Questions management By SuperAdmin */
    
    
    
    
    
    
    
    /* Staffing Vacancy Reasons Questions management By SuperAdmin */
      
    public function vacancyReasonsList(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
//        $reasons = DB::table('staffing_vacancyreasons')
//            ->select(
//            'id',
//            'reasonName',
//            'status'        
//            )->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//               ->orWhere('businessGroupID','=',0)
//            ->get();  
        
        $reasons = DB::table('staffing_vacancyreasons')
            ->select(
            'id',
            'reasonName',
            'status'        
            )->where([['businessGroupID','=',Auth::user()->businessGroupID],
                    ['status','=',1]])
            ->get();
        
        $defaultReasons = DB::table('staffing_vacancyreasons')
            ->select(
            'id',
            'reasonName',
            'status'        
            )->where([['businessGroupID','=',0]])
            ->get();
        
        return view('units.vacancyReasons.show', ['reasons' => $reasons, 'defaultReasons' => $defaultReasons]);
    } 
    
    
    public function ajaxVacancyReasonsList(){
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'reasonName',
            1 =>'status'
        );
        
//        $totalFiltered = DB::table('staffing_vacancyreasons')->select('id')
//                ->orWhere('businessGroupID','=',Auth::user()->businessGroupID)
//                                ->orWhere('businessGroupID','=',0)->count();
        $totalFiltered = DB::table('staffing_vacancyreasons')->select('id')
                ->where([
                    ['businessGroupID','=',Auth::user()->businessGroupID],
                    ['status','=',1]
                        ])->count();
        
        $sql = DB::table('staffing_vacancyreasons')
            ->select(
                    'id',
                    'reasonName',
                    'isDefault',
                    'status'
                    );
        
        $sql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where('status','=',1);
        //$sql->orWhere('businessGroupID','=',0);
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where('reasonName', 'LIKE', $requestData['search']['value'].'%');
           
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData;
        $sql->orderBy('isDefault','DESC'); 
        $sql->orderBy('reasonName','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        foreach($results as $result){
                              
                $nestedData=array(); 
                
                if($result->isDefault == '1'){
                   $htmlAction = '<a href="javascript:void(0)" class="btn btn-success mb-10">Default</a>'; 
                }else{
                   $htmlAction = '<a href="'.url(Config('constants.urlVar.editVacancyReason').$result->id).'" class="btn btn-outline-info mb-10">Edit</a>'; 
                }
                
                $htmlAction .= '&nbsp;&nbsp;<a title="Delete Reason" onclick="return confirm(\'Do you really want to delete - '.$result->reasonName.' ?\')" href="'.url(Config('constants.urlVar.removeVacancyReasons').$result->id).'" class="btn btn-outline-danger mb-10">Delete</a>'; 
                
                $nestedData[] = $result->reasonName;
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
    
      
    public function addNewVacancyReason(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
            ->select(
            'id',
            'groupCode',
            'groupName'
        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        return view('units.vacancyReasons.new', ['groups' => $businessGroup]);
    } 
    
      
    public function editVacancyReason($id){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
            ->select(
            'id',
            'groupCode',
            'groupName'
        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $vacancyReason = DB::table('staffing_vacancyreasons')
            ->select(
            'id',
            'reasonName'
        )->where([['id','=',$id]])->first();
        
        
        return view('units.vacancyReasons.update', 
           [
            'groups' => $businessGroup,
            'vacancyReason' => $vacancyReason
            ]);
    } 
    
    
    public function removeVacancyReasons($id){
       
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
         return redirect('/')->with('error','404 page not found.');  
        }  
        
        $data=array('status' => 0);
        
        $success = $this->updateVacancyReasonByID($id,$data);
        if($success)
        {
          return redirect(Config('constants.urlVar.vacancyReasonsList'))->with('success','Vacancy reason deleted successfully.');   
        }else{
          return redirect(Config('constants.urlVar.vacancyReasonsList'))->with('error','Failed to delete Vacancy reason.');  
        }
    }
    
        
    public function updateVacancyReasonByID($id, $data = array()){
     if(DB::table('staffing_vacancyreasons')
        ->where('id', $id)
        ->update($data))
        return true;
     else
         return  false;
    } 
    
    
    
    public function saveDefaultVacancyReasons(Request $request){
       if(Auth::user()->role == '2' || Auth::user()->role == '3') {
           
        $defaultVacancyReasonsIDs = $request->defaultVacancyIDs;
            if(is_array($defaultVacancyReasonsIDs) && count($defaultVacancyReasonsIDs)){
                
                $defaultReasons = DB::table('staffing_vacancyreasons')
                    ->select(
                    'id',
                    'reasonName',
                    'status'        
                    )->where('businessGroupID','=',0)
                     ->whereIn('id', $defaultVacancyReasonsIDs)   
                    ->get();
                
                if($defaultReasons->count() > 0){
                    $copyDefaultReasons = array();
                    foreach($defaultReasons as $defaultReason){
                        
                        $uniqueReasonsExistance = DB::table('staffing_vacancyreasons')
                            ->select(
                            'reasonName',
                            'status'        
                            )->where('businessGroupID','=',Auth::user()->businessGroupID)
                             ->where('reasonName', $defaultReason->reasonName)   
                             ->where('status', 1)     
                             ->where('isDefault', 0)   
                            ->count();
                        
                        if($uniqueReasonsExistance > 0){
                            $request->session()->flash('error', 'Duplicate entry not allowed.');
                            return response()->json([
                              'status'=>0,
                              'msg'=>'Duplicate entry not allowed.'    
                              ]);   
                            break;
                        }else{
                        
                            $copyDefaultReasons[] = array(
                                'businessGroupID' => Auth::user()->businessGroupID,
                                'reasonName' => $defaultReason->reasonName,  
                                'status' => 1 ,
                                'isDefault' => 0 ,
                                'defaultOf' => $defaultReason->id 
                            );
                        }
                        
                    }
                    $success = false;
                    if($copyDefaultReasons){
                        $success = DB::table('staffing_vacancyreasons')->insert($copyDefaultReasons);
                    }
                    
                    if($success){
                        $request->session()->flash('success', 'Vacancy reasons successfully copied.');
                        return response()->json([
                            'status'=>1,
                            'msg'=>'Success.'  
                        ]);
                    }else{
                        $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                        'status'=>0,
                        'msg'=>'Something went wrong please try again.'    
                        ]);   
                    }
                }else{
                     $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                    'status'=>0,
                    'msg'=>'Something went wrong please try again.'     
                    ]); 
                }
                
            }else{
                $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                'status'=>0,
                'msg'=>'Something went wrong please try again.'    
                ]);   
            }
            
       }else{
              $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                'status'=>0,
                'msg'=>'Something went wrong please try again.'       
            ]); 
            
       }
    }
    
    
    public function removeRequestReasons($id){
       
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
         return redirect('/')->with('error','404 page not found.');  
        }  
        
        $data=array('status' => 0);
        
        $success = $this->updateRequestReasonByID($id,$data);
        if($success)
        {
          return redirect(Config('constants.urlVar.requestReasonsList'))->with('success','Request reason deleted successfully.');   
        }else{
          return redirect(Config('constants.urlVar.requestReasonsList'))->with('error','Failed to delete Request reason.');  
        }
    }
    
        
    public function updateRequestReasonByID($id, $data = array()){
     if(DB::table('staffing_requestreasons')
        ->where('id', $id)
        ->update($data))
        return true;
     else
         return  false;
    } 
    
    
    
    public function saveDefaultRequestReasons(Request $request){
       if(Auth::user()->role == '2' || Auth::user()->role == '3') {
           
        $defaultRequestReasonsIDs = $request->defaultRequestIDs;
            if(is_array($defaultRequestReasonsIDs) && count($defaultRequestReasonsIDs)){
                
                $defaultReasons = DB::table('staffing_requestreasons')
                    ->select(
                    'id',
                    'reasonName',
                    'status'        
                    )->where('businessGroupID','=',0)
                     ->whereIn('id', $defaultRequestReasonsIDs)   
                    ->get();
                
                if($defaultReasons->count() > 0){
                    $copyDefaultReasons = array();
                    foreach($defaultReasons as $defaultReason){
                        
                        $uniqueReasonsExistance = DB::table('staffing_requestreasons')
                            ->select(
                            'reasonName',
                            'status'        
                            )->where('businessGroupID','=',Auth::user()->businessGroupID)
                             ->where('reasonName', $defaultReason->reasonName)   
                             ->where('status', 1)     
                             ->where('isDefault', 0)   
                            ->count();
                        
                        if($uniqueReasonsExistance > 0){
                            $request->session()->flash('error', 'Duplicate entry not allowed.');
                            return response()->json([
                              'status'=>0,
                              'msg'=>'Duplicate entry not allowed.'    
                              ]);   
                            break;
                        }else{
                        
                            $copyDefaultReasons[] = array(
                                'businessGroupID' => Auth::user()->businessGroupID,
                                'reasonName' => $defaultReason->reasonName,  
                                'status' => 1 ,
                                'isDefault' => 0 ,
                                'defaultOf' => $defaultReason->id 
                            );
                        }
                        
                    }
                    $success = false;
                    if($copyDefaultReasons){
                        $success = DB::table('staffing_requestreasons')->insert($copyDefaultReasons);
                    }
                    
                    if($success){
                        $request->session()->flash('success', 'Request reasons successfully copied.');
                        return response()->json([
                            'status'=>1,
                            'msg'=>'Success.'  
                        ]);
                    }else{
                        $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                        'status'=>0,
                        'msg'=>'Something went wrong please try again.'    
                        ]);   
                    }
                }else{
                     $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                    'status'=>0,
                    'msg'=>'Something went wrong please try again.'     
                    ]); 
                }
                
            }else{
                $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                'status'=>0,
                'msg'=>'Something went wrong please try again.'    
                ]);   
            }
            
       }else{
              $request->session()->flash('error', 'Something went wrong please try again.');
                      return response()->json([
                'status'=>0,
                'msg'=>'Something went wrong please try again.'       
            ]); 
            
       }
    }
    
    
    
    public function updateVacancyReason(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $id = $request->id;
        
        $vacancyReason = DB::table('staffing_vacancyreasons')
            ->select(
            'id',
            'reasonName'
        )->where([['id','=',$id]])->first();
        
        $reasonName = $request->reasonName;
        
        $this->validate($request, [
            'reasonName' => 'required|unique:staffing_vacancyreasons,reasonName,'.$id.',id,status,1,isDefault,0,businessGroupID,'.Auth::user()->businessGroupID.'', 
            
        ]);
        
        
            $success = DB::table('staffing_vacancyreasons')
            ->where('id', $id)
            ->update(['reasonName' => $reasonName]);
        
        if($vacancyReason->reasonName != $reasonName){
            
            if($success)
            {
              return redirect()->intended(Config('constants.urlVar.vacancyReasonsList'))->with('success','Reason updated successfully.');    
            }else{
              return redirect()->intended(Config('constants.urlVar.editVacancyReason').$id)->with('error','Failed to update reason.');  
            }
        }else{
          return redirect()->intended(Config('constants.urlVar.vacancyReasonsList'))->with('success','Reason updated successfully.');      
        }
        
    }
    
    
    
    public function saveVacancyReason(Request $request){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        
        $reasonName = $request->reasonName;
        
        $this->validate($request, [
            'reasonName' => 'required|unique:staffing_vacancyreasons,reasonName,NULL,NULL,status,1,isDefault,0,businessGroupID,'.Auth::user()->businessGroupID.''
        ]);
        
        $insertData = array(
          'businessGroupID' => Auth::user()->businessGroupID,
          'reasonName' => $reasonName ,  
          'isDefault' => 0 ,  
          'status' => 1 
        );
        
        $success = DB::table('staffing_vacancyreasons')->insert([$insertData]);
        
        if($success)
        {
          return redirect(Config('constants.urlVar.vacancyReasonsList'))->with('success','New reason added to list successfully.');   
          
        }else{
          return redirect(Config('constants.urlVar.addNewVacancyReason'))->with('error','Failed to add new reason.');  
        }
        
        
    }
    
    /* Staffing Vacancy Reasons Questions management By SuperAdmin */
    
    
    
    /* Business Unit Management For Super Admin to manage End-User Skills Category & New Request Reasons etc  */
    
    
    
    /* Offer Algorithm for BusinessManager Update & List */
      
    public function algorithmList(){
        
        if(Auth::user()->role == 0 || Auth::user()->role == 3 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
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
                $query->orWhere('type', '=', 'complex'); 
        });
        
        $algorithmsSql->orderBy('id','ASC');
        
        $algorithms = $algorithmsSql->get();
        
        return view('units.offerAlgorithm.show', ['algorithms' => $algorithms]);
    } 
    
    
    public function ajaxAlgorithmList(){
        
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'name',
            2 =>'notes',
            1 =>'status'
        );
        
        $totalFilteredSql = DB::table('staffing_offeralgorithm')->select('id');
        
        $totalFilteredSql->where('businessGroupID','=',Auth::user()
                ->businessGroupID);
        $totalFilteredSql->where(function ($query2){
                $query2->orWhere('type', '=', 'simple');
                $query2->orWhere('type', '=', 'open'); 
                $query2->orWhere('type', '=', 'complex'); 
        });
        
        
        $totalFiltered = $totalFilteredSql->count();
        
        $sql = DB::table('staffing_offeralgorithm')
            ->select(
                    'id',
                    'businessGroupID',
                    'name',
                    'notes',
                    'type',
                    'status'
                    );
        
        $sql->where('businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where(function ($query){
                $query->orWhere('type', '=', 'simple');
                $query->orWhere('type', '=', 'open'); 
                $query->orWhere('type', '=', 'complex'); 
        });
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where('name', 'LIKE', $requestData['search']['value'].'%');
           
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData; 
        $sql->orderBy('id','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        foreach($results as $result){
                              
                $nestedData=array(); 
                $htmlAction = '<a title="Update information" href="'.url(Config('constants.urlVar.editAlgorithm').$result->id).'" '
                        . 'class="btn-sm btn-default mb-10"><i class="fa fa-edit"></i></a> '
                        . '<a title="View detail information" href="'.url(Config('constants.urlVar.algorithmDetail').$result->id).'" '
                        . 'class="btn-sm btn-default mb-10"><i class="fa fa-eye"></i></a>';
                
                $nestedData[] = $result->name;
                $nestedData[] = $result->notes;
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
    
      
      
    public function editAlgorithm($id){
        
        
        if(Auth::user()->role == 0 || Auth::user()->role == 3 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $businessGroup = DB::table('staffing_groups')
            ->select(
            'id',
            'groupCode',
            'groupName'
        )->where([['id','=',Auth::user()->businessGroupID]])->first();
        
        
        $algorithms = DB::table('staffing_offeralgorithm')
            ->select(
            'id',
            'name',
            'notes' ,
            'type'        
        )->where([['id','=',$id]])->first();
        
        
        return view('units.offerAlgorithm.update', 
           [
            'groups' => $businessGroup,
            'algorithms' => $algorithms
            ]);
    } 
    
    
    public function updateAlgorithm(Request $request){
        
        
        if(Auth::user()->role == 0 || Auth::user()->role == 3 || Auth::user()->role == 4){
          return redirect('/')->with('error','404 page not found.');  
         }  
        
        $id = $request->id;
        
        $algorithms = DB::table('staffing_offeralgorithm')
            ->select(
            'id',
            'name',
            'notes'        
        )->where([['id','=',$id]])->first();
        
        $name = $request->name;
        $notes = $request->notes;
        
        $this->validate($request, [
            'name' => 'required'
        ]);
        
        
            $success = DB::table('staffing_offeralgorithm')
            ->where('id', $id)
            ->update(['name' => $name, 'notes' => $notes]);
        
        if($algorithms->name != $name && $algorithms->notes != $notes){
            
            if($success)
            {
              return redirect()->intended(Config('constants.urlVar.algorithmList'))->with('success','Offer algorithm updated successfully.');    
            }else{
              return redirect()->intended(Config('constants.urlVar.editAlgorithm').$id)->with('error','Failed to update Offer algorithm.');  
            }
            
        }else{
          return redirect()->intended(Config('constants.urlVar.algorithmList'))->with('success','Offer algorithm updated successfully.');      
        }
        
    }
    
    
    public function algorithmDetail($id) {
        
            
        
            $algorithm = DB::table('staffing_offeralgorithm')
                    ->select('id','name','notes','type')
                    ->where([['id','=',$id]])
                    ->first();
            
            if($algorithm){
               return view('units.offerAlgorithm.view',['algorithm' => $algorithm]);     
            }else{
             return redirect()->intended(Config('constants.urlVar.algorithmList'))
                     ->with('error','Page is you are looking for no longer available.');      
            }
            
                        
        }
    
    
    /* Offer Algorithm for BusinessManager Update & List */
    
    
    	
    
}
