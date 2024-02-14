<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Group;
use App\Businessunit;
use App\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;

class ShiftController extends Controller
{   
    
        public function index()
    {   

        $allShifts = DB::table('staffing_shiftsetup')
            ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_shiftsetup.businessUnitID')
            ->select(
            'staffing_shiftsetup.id',
            'staffing_shiftsetup.shiftTitle',
            'staffing_shiftsetup.shiftType',
            'staffing_shiftsetup.startTime',
            'staffing_shiftsetup.endTime',
            'staffing_businessunits.unitName',
            'staffing_shiftsetup.status'       
            )->where([['staffing_shiftsetup.businessGroupID','=',Auth::user()->businessGroupID],
                ['staffing_businessunits.deleteStatus','=',0],
                ['staffing_businessunits.status','=',1]])
            ->get();
        
        return view('units.shiftSetUp.show', ['shifts' => $allShifts]);
    }
    
    
    
    public function ajaxList(){
        $requestData= $_REQUEST;
        $columns = array( 
            // datatable column index  => database column name
            0 =>'startTime',
            1 =>'unitName',
            2 =>'status'
        );
        
        $totalFiltered = DB::table('staffing_shiftsetup')
            ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_shiftsetup.businessUnitID')
            ->select('id')
                ->where([['staffing_shiftsetup.businessGroupID','=',Auth::user()
                ->businessGroupID],
                ['staffing_businessunits.deleteStatus','=',0],
                ['staffing_businessunits.status','=',1]])->count();
        
            $sql = DB::table('staffing_shiftsetup')
            ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_shiftsetup.businessUnitID')
            ->select(
            'staffing_shiftsetup.id',
            'staffing_shiftsetup.shiftTitle',
            'staffing_shiftsetup.shiftType',
            'staffing_shiftsetup.startTime',
            'staffing_shiftsetup.endTime',
            'staffing_businessunits.unitName',
            'staffing_shiftsetup.status'       
            );
        
        $sql->where('staffing_shiftsetup.businessGroupID','=',Auth::user()->businessGroupID);
        $sql->where('staffing_businessunits.deleteStatus','=',0);
        $sql->where('staffing_businessunits.status','=',1);
        
        if(Auth::user()->role == 3){//Super-Admin
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
            $userUnitID = DB::table('staffing_usersunits')
                ->join('staffing_businessunits', 'staffing_businessunits.id', '=', 'staffing_usersunits.businessUnitID')    
                ->select('staffing_businessunits.id AS businessUnitID')
                ->where([
                    ['staffing_usersunits.userID','=',Auth::user()->id]])
                 ->first();   
            /* GET HIS BUSINESS UNIT INFO TO FETCH ONLY THOSE USERS WHO ARE ASSOCIATED WITH HIS UNIT */
            if($userUnitID)
            $sql->where('staffing_businessunits.id','=',$userUnitID->businessUnitID);
            
        }
        
        if( !empty($requestData['search']['value']) ) {
            
            $sql->where('staffing_businessunits.unitName', 'LIKE', $requestData['search']['value'].'%');
           
        }        
        $totalData = $sql->count();
        $totalFiltered = $totalData; 
        $sql->orderBy('staffing_businessunits.unitName','ASC');
        $sql->orderBy('staffing_shiftsetup.startTime','ASC');
        $sql->limit($requestData['length'])->offset($requestData['start']);
        $results = $sql->get();  
        $data = array();
        
        foreach($results as $result){
                              
                $nestedData=array(); 
                $nestedData[] = date("g:i A",strtotime($result->startTime)). " - ".date("g:i A",strtotime($result->endTime));
                $nestedData[] = $result->unitName;
                $nestedData[] = '<a href="'.url(Config('constants.urlVar.editShiftSetUp').$result->id).'" class="btn btn-outline-info mb-10">Edit</a>';
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
    
      
    public function addNew(){
        
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
        
        if(Auth::user()->role == 3){//Super-Admin
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
        
        
        return view('units.shiftSetUp.new', 
                [
                    'groups' => $businessGroup,
                    'units' => $businessUnits
                ]);
    } 
    
      
    public function edit($id){
        
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
        
        $businessUnitsSql->where('staffing_businessunits.businessGroupID','=',Auth::user()->businessGroupID);
        $businessUnitsSql->where('staffing_businessunits.deleteStatus','=',0);
        $businessUnitsSql->where('staffing_businessunits.status','=',1);
        
        $businessUnitsSql->orderBy('unitName','ASC');
        
        if(Auth::user()->role == 3){//Super-Admin
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
        
        
        $shifts = DB::table('staffing_shiftsetup')
            ->select(
            'id',
            'shiftTitle',
            'shiftType',
            'startTime',
            'endTime',
            'businessUnitID'
        )->where([['id','=',$id]])->first();
        
        if($shifts){
        
        
            return view('units.shiftSetUp.update', 
               [
                'groups' => $businessGroup,
                'shifts' => $shifts,
                'units' => $businessUnits
                ]);
        }else{ 
            
            return redirect(Config('constants.urlVar.unitShiftSetUpList'))
                ->with('error', 'Shift not found.'); 
        }
    } 
    
    
    
    
    
    
    public function updation(Request $request){
        
        $id = $request->id;
        
        $shift = DB::table('staffing_shiftsetup')
            ->select(
            'id'
        )->where([['id','=',$id]])->first();
        
        if($shift){ 
        
            $startTime = $request->startTime;
            $endTime = $request->endTime;
            $businessUnitID = $request->businessUnitID;
            
            /* Check Unique Shift Timing per Business-Unit */            
            $uniqueShiftCheck = DB::table('staffing_shiftsetup')
            ->select(
            'id'
            )->where([
                
                ['businessUnitID','=',$businessUnitID],
                ['businessGroupID','=',Auth::user()->businessGroupID],
                ['startTime','=', (date('H:i:s',strtotime($startTime)))],
                ['endTime','=', (date('H:i:s',strtotime($endTime)))],
                ['id','!=', $id]
                    
            ])->count();
        
            if($uniqueShiftCheck > 0){
                return redirect(Config('constants.urlVar.editShiftSetUp').$id)
                    ->with('error', 'Shift already created.');   
            }            
            /* Check Unique Shift Timing per Business-Unit */

            $this->validate($request, [
                'businessUnitID' => 'required',
                'startTime' => 'required',
                'endTime' => 'required'
            ]);

            $updationData = [
                'businessUnitID' => $businessUnitID ,  
                'startTime' => date('H:i:s',strtotime($startTime)) ,  
                'endTime' => date('H:i:s',strtotime($endTime)) 
            ];


            $success = DB::table('staffing_shiftsetup')
            ->where('id', $id)
            ->update($updationData);        
          
            return redirect(Config('constants.urlVar.unitShiftSetUpList'))
                ->with('success','Shift updated successfully.'); 
            
        }else{            
            return redirect(Config('constants.urlVar.unitShiftSetUpList'))
                ->with('error', 'Shift not found.'); 
        }
        
    }
    
    
    
    public function save(Request $request){
        
        
        $startTime = $request->startTime;
        $endTime = $request->endTime;
        $shiftType = $request->shiftType;
        $businessUnitID = $request->businessUnitID;
        
        /* Check Unique Shift Timing per Business-Unit */
            $uniqueShiftCheck = DB::table('staffing_shiftsetup')
            ->select(
            'id'
            )->where([
                ['businessUnitID','=',$businessUnitID],
                ['businessGroupID','=',Auth::user()->businessGroupID],
                ['startTime','=', (date('H:i:s',strtotime($startTime)))],
                ['endTime','=', (date('H:i:s',strtotime($endTime)))]
            ])->count();
        
            if($uniqueShiftCheck > 0){
                return redirect(Config('constants.urlVar.addNewShiftSetUp'))
                    ->with('error', 'Shift already created.');   
            }
        /* Check Unique Shift Timing per Business-Unit */
        
        
        /*Checking for Not To Save more tham 3 Shift On a Business Unit */
        
        $shiftCheck = DB::table('staffing_shiftsetup')
            ->select(
            'id'
        )->where([
            ['businessUnitID','=',$businessUnitID],
            ['businessGroupID','=',Auth::user()->businessGroupID]
                ])->count();
        
        /*Checking for Not To Save more tham 3 Shift On a Business Unit */
        
            $this->validate($request, [
                'businessUnitID' => 'required',
                'startTime' => 'required',
                'endTime' => 'required'
            ]);
            
        if($shiftCheck < 3){
        

            $insertData = array(
              'businessGroupID' => Auth::user()->businessGroupID,
              'businessUnitID' => $businessUnitID ,  
              'startTime' => date('H:i:s',strtotime($startTime)) ,  
              'endTime' => date('H:i:s',strtotime($endTime)) ,
              'status' => 1 
            );

            $success = DB::table('staffing_shiftsetup')->insert([$insertData]);

            if($success)
            {
              return redirect(Config('constants.urlVar.unitShiftSetUpList'))->with('success','New shift added successfully.');   

            }else{
              return redirect(Config('constants.urlVar.addNewShiftSetUp'))->with('error','Failed to add shift.');  
            }
            
        }else{
           return redirect(Config('constants.urlVar.addNewShiftSetUp'))->with('error','Already 3 Shifts. You can not add more than 3 Shifts in a Day on a Business Unit');   
        }
        
        
    }
    
    
    
}