<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Group;
use App\Requestcall;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Session;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    
    public function registration(){
        return view('secure.register');
    }
    
    
    public function DynamicGlobalURL(Request $request){
        $postInfo = Requestcall::where([
            ['id', '=', $request->requestID],
            ['closingTime', '>', date("Y-m-d H:i:s")]])->first();
        if($postInfo){
          $businessUnitID =  $postInfo->businessUnitID;
          $businessGroupID =  $postInfo->businessGroupID; 
          $requestID = $postInfo->id;
          return redirect(Config('constants.urlVar.shiftOfferDetail').$requestID); 
        }else{
           return redirect(Config('constants.urlVar.home'))
                ->with('error','404 Page your are requested is no longer available.');  
        }
    }
    
    public function login($groupCode = NULL) 
    {   
        if($groupCode){
            $groupInfo = Group::where([['groupCode', '=', $groupCode],
                ['deleteStatus', '=', 0],
                ['status', '=', 1]])->first();
            if($groupInfo){
                $groupLogo = $groupInfo->logo?url('public/'.$groupInfo->logo):'';
            }else{
               return redirect(Config('constants.urlVar.login'))->with('msg','404 page not found.'); 
            }
        }else{
            $requestedUrl =  explode('.', $_SERVER['HTTP_HOST']);
            $groupCode = $requestedUrl[0];
            $groupInfo = Group::where([['groupCode', '=', $groupCode],
                ['deleteStatus', '=', 0],
                ['status', '=', 1]])->first();
            if($groupInfo){
                $groupLogo = $groupInfo->logo?url('public/'.$groupInfo->logo):'';
            }else{
                $groupLogo = '';
            } 
        }
        
        
        if(Auth::check()){
          return redirect(Config('constants.urlVar.home'));  
        }
        
        
        //if($groupInfo)
            return view('secure.login', ['groupLogo' => $groupLogo]);
        //else
            //return view('secure.notFound');    
    }
    
    
    
    
    public function doLogin(Request $request) 
    {
        $userID = $request->userID;
        $password = $request->password;
        
        $this->validate($request, [
        'userID' => 'required',
        'password' => 'required',
        ]);
        
        
        $userInfo = User::where([['userName', '=', $userID],['deleteStatus', '=', 0]])->first();
        
        if($userInfo){
            if($userInfo->role != 1){
                $userGroup = $userInfo->businessGroupID;
                $groupInfo = Group::find($userGroup);

                if($userInfo->deleteStatus == 1){
                    return redirect(Config('constants.urlVar.login'))
                            ->with('msg','Your account is deleted. Please contact management.'); 
                } else if($userInfo->status == 0 ){              
                   return redirect(Config('constants.urlVar.login'))
                           ->with('msg','Your account is deactivated. Please contact management.'); 
                }

                if($groupInfo->deleteStatus == 1){
                    return redirect(Config('constants.urlVar.login'))
                            ->with('msg','Your Organization account is deleted. Please contact management.'); 

                } else if($groupInfo->status == 0){

                   return redirect(Config('constants.urlVar.login'))
                           ->with('msg','Your Organization account is deactivated. Please contact management.'); 
                } 
            
                /* Check Active And Delete */
                if($userInfo->role == 3 || $userInfo->role == 4 || $userInfo->role == 0) {
                    $unitInfoSql = DB::table('staffing_businessunits')
                     ->join('staffing_usersunits', 'staffing_usersunits.businessUnitID', 
                             '=', 'staffing_businessunits.id')
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
                            return redirect(Config('constants.urlVar.login'))
                                    ->with('msg','Your Business Unit account is deleted. Please contact management.'); 

                        } else if($unitInfo->status == 0){
                            return redirect(Config('constants.urlVar.login'))
                                    ->with('msg','Your Business Unit account is deactivated. Please contact management.'); 

                        }
                    }else{
                      return redirect(Config('constants.urlVar.login'))
                                    ->with('msg','Your Business Unit account is deactivated. Please contact management.'); 
  
                    }

                }
                /* Check Active And Delete */
            
            }
        
            if(Auth::attempt(['userName'=>$userID, 'password'=>$password, 'deleteStatus'=> '0']))
            {
                
                /* Update Default home view of user as calendar */
                $loginUserInfo = User::find(Auth::user()->id);
                
                if($loginUserInfo && Auth::user()->role != 1){
                    $loginUserInfo->calendarView = 1;
                    $loginUserInfo->save();
                }
                /* Update Default home view of user as calendar */
                
                if(Auth::user()->role == 1 || Auth::user()->role == 2)
                return redirect()->intended(Config('constants.urlVar.home'));
                else
                return redirect()->intended(Config('constants.urlVar.userCalendarView'));    
            }
            else 
            {  
                return redirect(Config('constants.urlVar.login'))->with('msg','Login failed! Incorrect login-id or password.');

            }
            
        }else{
            return redirect(Config('constants.urlVar.login'))->with('msg','Login failed! Incorrect login-id or password.');
        }
        
    }
    
    
    public function logout() 
    {
        session()->flush();
        Auth::logout();
        return redirect()->intended(Config('constants.urlVar.login'))->with('success', 'Successfully logged out.');;
    }
    
    
    
    /* Forgot Password Code */
    
        public function getUserInfoByEmail($email){
            $rows = DB::table('staffing_users')->select('id','userName','firstName','email','businessGroupID')
                    ->where([['email', '=', $email],['status', '=', 1],['deleteStatus', '=', 0]])->get();
            
            return $rows;
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
            
            if ($request->isMethod('post')) {
                $email = $request->email;
                
                $this->validate($request, [
                'email' => 'required|email'    
                ]);
                
                
                
                $userInfo = $this->getUserInfoByEmail($email);
                $totalUsers = $userInfo->count();
                if($totalUsers > 1){
                  
                    $keyGenrate = $this->generateKey(70);
                    $timestamp = time();  
                    foreach($userInfo as $user){
                        $name = $user->firstName;
                        $useremail = $user->email;
                        $userID = $user->id;
                    }
                    
                    $name = 'User';
                    $tokenKey = $timestamp."G#@T".$useremail."G#@T".$userID."G#@T".$keyGenrate;
                    $token = base64_encode($tokenKey);
                    $tokenUpdate = $this->updateToken($token, $useremail);
                    
                    if($tokenUpdate){
                        $sentMail = true;  
                        $sentMail = $this->sentMailForForgotPassword($token, $useremail, $name);
                        if($sentMail){
                          return redirect(Config('constants.urlVar.forgotpassword'))->with('success','Password reset link has been sent to your Email');    
                        }else{
                         return redirect(Config('constants.urlVar.forgotpassword'))->with('msg','Failed to sent reset password link.'); 
                        }
                    }else{
                       return redirect(Config('constants.urlVar.forgotpassword'))->with('msg','Failed to sent reset password link.'); 
                    }
                    
                    
                    
                }else{
                    
                  if ($totalUsers > 0) {
                    // output data of each row
                    $name = $userInfo[0]->firstName;
                    $useremail = $userInfo[0]->email;
                    $userID = $userInfo[0]->id;
                    $keyGenrate = $this->generateKey(70);
                    $timestamp = time();
                    $tokenKey = $timestamp."G#@T".$useremail."G#@T".$userID."G#@T".$keyGenrate;
                    $token = base64_encode($tokenKey);
                    $tokenUpdate = $this->updateToken($token, $useremail);
                    if($tokenUpdate){
                        $sentMail = true;  
                        $sentMail = $this->sentMailForForgotPassword($token, $useremail, $name);
                        if($sentMail){
                          return redirect(Config('constants.urlVar.forgotpassword'))->with('success','Password reset link has been sent to your Email');    
                        }else{
                         return redirect(Config('constants.urlVar.forgotpassword'))->with('msg','Failed to sent reset password link.'); 
                        }
                    }else{
                       return redirect(Config('constants.urlVar.forgotpassword'))->with('msg','Failed to sent reset password link.'); 
                    }

                  } else {
                    return redirect(Config('constants.urlVar.forgotpassword'))->with('msg','Email is not registered with us.');   
                    
                  }
                  
                }
                
                
            }
            
            return view('secure.forgotpassword');
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
           	
            
            
            Mail::send('templates.forgotemail', $data, function ($message) use ($to, $name, $subject)
        {
            
            $message->to($to, $name)->subject($subject);
            $message->from('contacts.nmssalman@gmail.com', 'Staffing Call App');

        });

            return true;
        }
 
        
        
    
     /*****######Reset Password Link######*****/

    public function updateUserInfoByEmail($email,$groupID, $data = array()){
     if(DB::table('staffing_users')
        ->where([['email', $email],['businessGroupID', $groupID],['deleteStatus', 0],['status', 1]])
        ->update($data)){
         $data2['token'] = '';
         DB::table('staffing_users')
        ->where([['email', $email]])
        ->update($data2);
        return true;
        }
     else{
         return  false;
     }
    } 
    
    
    public function tokenCheck($token){
        $rows = DB::table('staffing_users')->select('id')->where([
            ['token', '=', $token],
            ['status', '=', 1],
            ['deleteStatus', '=', 0]
                ])->count();
        if($rows > 0)
        return $rows;
        else
        return false;    
    }
     
    
    
    public function tokenCheckForNewUser($token){
        $rows = DB::table('staffing_users')->select('id')->where([
            ['remember_token', '=', $token],
            ['status', '=', 1],
            ['deleteStatus', '=', 0]
                ])->count();
        if($rows > 0)
        return $rows;
        else
        return false;    
    }
    
    
    
        public function getUserInfoByID($userID){
            $rows = DB::table('staffing_users')->select('id',
                    'userName',
                    'firstName',
                    'email',
                    'businessGroupID')
                    ->where([['id', '=', $userID],
                            ['status', '=', 1],
                            ['deleteStatus', '=', 0]
                        ])->first();
            
            return $rows;
	}
        
        

    public function updateUserInfoByID($userID, $data = array()){
        if(DB::table('staffing_users')
           ->where([['id', $userID],['deleteStatus', 0],['status', 1]])
           ->update($data)){
               return true;
           }
        else{
            return  false;
        }
    } 
    
    
    
    public function generateUserNameAndPassword($token = NULL, Request $request){
        $token = $token?$token:$request->token;
         $groupLogo = '';
        if($token){
            $data=$this->tokenCheckForNewUser($token);
            
            if($data){
                $tokenDecode = base64_decode($token);
                $tokenSplit = explode('G#@T',$tokenDecode);
                $tokenTime = $tokenSplit[0];
                $userEmail = $tokenSplit[1];
                $userId = $tokenSplit[2];
                
                
                $userInfo = array();    
                if($data > 0){
                  /* Get User Business Groups In Which User is associated (When user is associated with multiple group) */
                    $userInfo = $this->getUserInfoByID($userId);
                    $groupID = $userInfo->businessGroupID;

                }                
                
                if($userInfo){  
                    
                    $groupInfo = Group::where([['id', '=', $groupID],
                        ['deleteStatus', '=', 0],
                        ['status', '=', 1]])->first();
                    if($groupInfo){
                        $groupLogo = $groupInfo->logo?url('public/'.$groupInfo->logo):'';
                    }else{
                        $groupLogo = '';
                    } 
                    
                    if ($request->isMethod('post')) {
                        $val = 1;
                        
                        $customMessages = [
                            'userName.required' => 'Login-ID is required.',
                            'userName.unique' => 'Login-ID has already been taken.',
                        ];
                        
                         $this->validate($request, [
                            'userName' => 'required|unique:staffing_users,userName,'.$val.',deleteStatus',
                            'password' => 'required|regex:/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/|min:8|confirmed',
                            'password_confirmation' => 'required|regex:/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/|min:8'
                         ], $customMessages);  
                        $updateRows['userName'] = $request->userName;
                        $updateRows['password'] = bcrypt($request->password);
                        $updateRows['remember_token'] = '';
                        $email=$userEmail;
                        $updateInfo = $this->updateUserInfoByID($userId, $updateRows);
                        if($updateInfo){
                            return redirect(Config('constants.urlVar.resetsuccess'))->with('msg','Your Login-ID and password created successfully!'); 
                        }else{
                            return redirect(Config('constants.urlVar.resetsuccess').$token)->with('msg','Failed to create Login-ID and password, please try again.');     
                        }
                    }
                    
                }else{
                    return redirect(Config('constants.urlVar.pageNotFound'))->with('msg',"It looks like this link has already been used to create an account. Please check your email again.");
                }
                
            }else{
            return redirect(Config('constants.urlVar.pageNotFound'))->with('msg','It looks like this link has already been used to create an account. Please check your email again.');
            }
        }else{
         return redirect(Config('constants.urlVar.pageNotFound'))->with('msg','It looks like this link has already been used to create an account. Please check your email again.');
        }

        return view('secure.generate-username-password', ['token' => $token, 'groupLogo' => $groupLogo]);
    }
    
    
    
    public function resetPassword($token = NULL, Request $request){	
         
        $token = $token?$token:$request->token;	
        $groups = array();
        
        if($token){
            $data=$this->tokenCheck($token);
            
            if($data){
                $tokenDecode = base64_decode($token);
                $tokenSplit = explode('G#@T',$tokenDecode);
                $tokenTime = $tokenSplit[0];
                $userEmail = $tokenSplit[1];
                $userId = $tokenSplit[2];
                
                
            

                if($tokenTime + 86400 > time()){
                    
                    if($data > 0){
                      /* Get User Business Groups In Which User is associated (When user is associated with multiple group) */
                        $userInfo = $this->getUserInfoByEmail($userEmail);
                        foreach($userInfo as $user){
                            $groupInfo = Group::where([
                                ['deleteStatus', '=', 0],
                                ['status', '=', 1],
                                ['id', '=', $user->businessGroupID]
                                    ])->first();
                            if($groupInfo)
                            $groups[] = $groupInfo;
                        }
                    }
                    
                    
                    if(count($groups) > 0) {   
                        if ($request->isMethod('post')) {
                            
                            $customMessages = [
                            'businessGroupID.required' => 'Please select your Business Group.'
                             ];
                            
                            $this->validate($request, [
                                'businessGroupID' => 'required',
                                'password' => 'required|regex:/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/|min:8|confirmed',
                                'password_confirmation' => 'required|regex:/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/|min:8'
                             ], $customMessages);  

                            $groupID = $request->businessGroupID;  
                            $updateRows['password'] = bcrypt($request->password);
                            $updateRows['token'] = '';

                            $email=$userEmail;
                            $updateInfo = $this->updateUserInfoByEmail($email,$groupID, $updateRows);

                            if($updateInfo){
                            return redirect(Config('constants.urlVar.resetsuccess'))->with('msg','Your password changed successfully!'); 
                            }else{
                            return redirect(Config('constants.urlVar.resetsuccess').$token)->with('msg','Failed to  reset password, please try again.');     
                            }

                        }
                    }else{
                       return redirect(Config('constants.urlVar.tokenexpired'))->with('msg','Something wrong with your account. Please contact administrator.'); 
                    }

                }else{
                 return redirect(Config('constants.urlVar.tokenexpired'))->with('msg','Token Expired!');   
                }
            }else{
            return redirect(Config('constants.urlVar.tokenexpired'))->with('msg','Requested link has expired.');
            }
        }else{
         return redirect(Config('constants.urlVar.tokenexpired'))->with('msg','Requested link has expired.');
        }

        return view('secure.resetpassword', ['token' => $token, 'groups' => $groups]);

    }
    
    
        public function tokenExpired(){
		return view('secure.tokenexpired');
	}
    
        public function pageNotFound(){
		return view('secure.notFound');
	}
	
	
	
	public function resetSuccess(){
		return view('secure.resetsuccess');
	}
    
        /*****######Reset Password Link######*****/
    
    
    /* Forgot Password Code */
        
        
        /* Change Password */
        public function changePassword(Request $request){
            
            if ($request->isMethod('post')) {
                $oldPassword = $request->old_password;
                $newPassword = $request->password;
                
                $this->validate($request, [
                'old_password' => 'required',
                'password' => 'required|min:6|confirmed',
                'password_confirmation' => 'required|min:6',  
                ]);
                
                /* ####Check Old Password#### */
                $checkInfo = DB::table('staffing_users')
                        ->select('id','password')
                        ->where([['id', '=', Auth::user()->id]])->first();
                
                //$hashed_password = Auth::user()->password;
                $hashed_password = $checkInfo->password;            
                $current_password = $oldPassword;      
                if (Hash::check($current_password, $hashed_password)) {     
                    $updatePassword = DB::table('staffing_users')
                        ->where('id', Auth::user()->id)->update(['password' => Hash::make($newPassword)]);                
                    return redirect(Config('constants.urlVar.changePassword'))->with('success','Password changed successfully.');   
                }
                else{
                    return redirect(Config('constants.urlVar.changePassword'))->with('error','Current password did not match.');   
                }
                /* ####Check Old Password#### */
                
            }
            
            return view('secure.changepassword'); 
        }
        /* Change Password */
    
    
    
    public function doRegister(Request $request) 
    {
//        $firstName = $request->firstName;
//        $lastName = $request->lastName;
//        $email = $request->email;
//        $password = $request->password;
//        
//        $user = new User;
//        $user->userName = 1;
//        $user->firstName = $firstName;
//        $user->lastName = $lastName;
//        $user->email = $email;
//        $user->password = Hash::make($password);
//        $user->role = 1;
//        $user->remember_token = $request->_token;
//        if($user->save())
//        {
//            echo "save";
//        }
//        else 
//        {
            echo "error";
        //}
    }
    
    
    
    
}
