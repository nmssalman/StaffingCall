<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use App\Group;
use Illuminate\Support\Facades\Auth;

class StaffingCommonActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        
        
        if(Auth::user()->role == 2){//Group Manager
            
            $userGroupID = Auth::user()->businessGroupID;
            $groupInfo = Group::find($userGroupID);
            if($groupInfo->deleteStatus == 1){
                    Auth::logout();        
                    return redirect(Config('constants.urlVar.login'))
                                ->with('msg','Your Organization account is deleted. Please contact management.'); 

            } else if($groupInfo->status == 0){
                Auth::logout();   
               return redirect(Config('constants.urlVar.login'))
                       ->with('msg','Your Organization account is deactivated. Please contact management.'); 
            } 
        
            
            
            if(Auth::user()->deleteStatus == 1){
                Auth::logout();        
                return redirect(Config('constants.urlVar.login'))
                            ->with('msg','Your account is deleted. Please contact management.'); 
            } else if(Auth::user()->status == 0 ){  
                
                Auth::logout();                    
               return redirect(Config('constants.urlVar.login'))
                       ->with('msg','Your account is deactivated. Please contact management.'); 
            }
            
        }
        
        if(Auth::user()->role == 3 || Auth::user()->role == 4 || Auth::user()->role == 0){
            
            $userGroupID = Auth::user()->businessGroupID;
            $groupInfo = Group::find($userGroupID);
            if($groupInfo->deleteStatus == 1){
                    Auth::logout();        
                    return redirect(Config('constants.urlVar.login'))
                                ->with('msg','Your Organization account is deleted. Please contact management.'); 

            } else if($groupInfo->status == 0){
                Auth::logout();   
               return redirect(Config('constants.urlVar.login'))
                       ->with('msg','Your Organization account is deactivated. Please contact management.'); 
            } 
            
            
            if(Auth::user()->deleteStatus == 1){
                Auth::logout();        
                return redirect(Config('constants.urlVar.login'))
                            ->with('msg','Your account is deleted. Please contact management.'); 
            } else if(Auth::user()->status == 0 ){  
                Auth::logout();                    
               return redirect(Config('constants.urlVar.login'))
                       ->with('msg','Your account is deactivated. Please contact management.'); 
            }
            
            
                $unitInfoSql = DB::table('staffing_businessunits')
                 ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', 
                     '=', 'staffing_businessunits.id')
                 ->select(
                     'staffing_businessunits.id',
                     'staffing_businessunits.status',
                     'staffing_businessunits.deleteStatus'
                );

                if(Auth::user()->role == 0){
                    $unitInfoSql->where([['staffing_usersunits.userID','=',Auth::user()->id]])->first();
                    $unitInfoSql->where([['staffing_usersunits.primaryUnit','=',1]])->first();
                }else{
                   $unitInfoSql->where([['staffing_usersunits.userID','=',Auth::user()->id]])->first(); 
                }

                $unitInfo = $unitInfoSql->first();
                
                if($unitInfo){

                    if($unitInfo->deleteStatus == 1){
                        Auth::logout();        
                        return redirect(Config('constants.urlVar.login'))
                                ->with('msg','Your Business Unit account is deleted. Please contact management.'); 

                    } else if($unitInfo->status == 0){
                        Auth::logout();        
                        return redirect(Config('constants.urlVar.login'))
                                ->with('msg','Your Business Unit account is deactivated. Please contact management.'); 

                    }
                }else{
                   Auth::logout();        
                    return redirect(Config('constants.urlVar.login'))
                    ->with('msg','Your Business Unit account is either deleted or deactivated. Please contact management.'); 
 
                }
            
            
        }
        
        
        return $next($request);
    }
}
