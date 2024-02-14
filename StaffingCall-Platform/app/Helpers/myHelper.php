<?php

namespace App\Helpers;
use DB;
use File;

class myHelper
{
    
    private static  $API_ACCESS_KEY = 'AIzaSyA3sSIU7oT0SlQJgBQ1zuXQoQ3ywUJVkpE';
    // (iOS) Private key's passphrase.
     private static  $gatewayUrl = 'gateway.push.apple.com';
//   private static  $gatewayUrl = 'gateway.sandbox.push.apple.com';
    // (iOS) Private key's passphrase.
    private static  $gatewayPort = '2195';
    // (iOS) Private key's passphrase.
    private static  $passphrase = 'AppGuys123';
    // (iOS) Private key's PEM FILE.
//    private static $pemFile = 'StaffingCallSandboxAPNS.pem';
//    private static $pemFile = 'StaffingCallAlphaAPNS.pem';
    private static $pemFile = 'StaffingCallBetaAPNS.pem';
    // (Windows Phone 8) The name of our push channel.
    private static $channelName = "joashp";
    
    /* For Text Communication */
    private static $accountSID = "ACd69db0c077c7f35071be137d9dcc85aa";
    
    private static $authToken = "c7ca0d605007e48371e56617a968ebc7";
    
    private static $fromPhone = "+18313465491";
    
    private static $dynamicUrlForSMSKEY = "AIzaSyAhOWKp-29bLW9tta7k1SFwXddOFZuGH98";
    
    private static $dynamicURL = "https://r4348.app.goo.gl/?";
    
    private static $dynamicUrlForSMS = 'https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=';
    
    
//    private static $androidPkgName = "ca.appguys.staffingCallSandbox";
//    private static $iOSBundleIdentifier = "ca.appguys.staffingCallSandbox";
    
//    private static $androidPkgName = "ca.appguys.staffingCallAlpha";
//    private static $iOSBundleIdentifier = "ca.appguys.staffingCallAlpha";
    
    private static $androidPkgName = "a.appguys.staffingCallBeta";
    private static $iOSBundleIdentifier = "ca.appguys.staffingCallBeta";
//    
    /* For Text Communication */
    
    
    
    public static function getKeysAndURLs(){
        $keysAndURLs = array();
        $keysAndURLs['dynamicURL'] = self::$dynamicURL;
        $keysAndURLs['dynamicUrlForSMSKEY'] = self::$dynamicUrlForSMSKEY;
        $keysAndURLs['dynamicUrlForSMS'] = self::$dynamicUrlForSMS;
        $keysAndURLs['androidPkgName'] = self::$androidPkgName;
        $keysAndURLs['iOSBundleIdentifier'] = self::$iOSBundleIdentifier;
        
        return $keysAndURLs;
        
    }  
    
//    Time Ago Function 
    
    public static function get_time_ago( $time )
{
    $time_difference = time() - $time;

    if( $time_difference < 1 ) { return 'less than 1 second ago'; }
    $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                30 * 24 * 60 * 60       =>  'month',
                24 * 60 * 60            =>  'day',
                60 * 60                 =>  'hour',
                60                      =>  'min',
                1                       =>  'sec'
    );

    foreach( $condition as $secs => $str )
    {
        $d = $time_difference / $secs;

        if( $d >= 1 )
        {
            $t = round( $d );
            return '' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
        }
    }
}
    


    public static function sendSMS($to, $message){
        
            $id = self::$accountSID;
            $token = self::$authToken;
            //$url = "https://api.twilio.com/2010-04-01/Accounts/$id/SMS/Messages";
            $url = "https://api.twilio.com/2010-04-01/Accounts/$id/Messages.json";
            $from = self::$fromPhone;
            $to = str_replace("-", "", $to); //"+918318159112";// twilio trial verified number
            $body = $message;
            $data = array (
                'From' => $from,
                'To' => $to,
                'Body' => $body,
            );
            $post = http_build_query($data);
            $x = curl_init($url );
            curl_setopt($x, CURLOPT_POST, true);
            curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($x, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($x, CURLOPT_USERPWD, "$id:$token");
            curl_setopt($x, CURLOPT_POSTFIELDS, $post);
            $y = curl_exec($x);
            curl_close($x);
            
            return true;
    }

    
    public static function testPushiOS()
    {
                // Provide the Host Information.
		//$tHost = self::$gatewayUrl;
		$tHost = self::$gatewayUrl;
		//$tHost = 'gateway.push.apple.com';
		$tPort = 2195;
		// Provide the Certificate and Key Data.
			
		$tCert = public_path('/').self::$pemFile;
                
		// Provide the Private Key Passphrase (alternatively you can keep this secrete
		// and enter the key manually on the terminal -> remove relevant line from code).
		// Replace XXXXX with your Passphrase
		$tPassphrase = self::$passphrase;
		// Provide the Device Identifier (Ensure that the Identifier does not have spaces in it).
		// Replace this token with the token of the iOS device that is to receive the notification.
		//$tToken = 'b3d7a96d5bfc73f96d5bfc73f96d5bfc73f7a06c3b0101296d5bfc73f38311b4';
		$tToken = '3FD5DE37F4484DA239DFB6F20186D7712B36158CD41361D390E19490FF502DC5';
		//0a32cbcc8464ec05ac3389429813119b6febca1cd567939b2f54892cd1dcb134
		// The message that is to appear on the dialog.
		$tAlert = 'You have a LiveCode APNS Message';
		// The Badge Number for the Application Icon (integer >=0).
		$tBadge = 0;
		// Audible Notification Option.
		$tSound = 'default';
		// The content that is returned by the LiveCode "pushNotificationReceived" message.
		$tPayload = 'APNS Message Handled by LiveCode';
		// Create the message content that is to be sent to the device.
		$tBody['aps'] = array (
		'alert' => $tAlert,
		'badge' => $tBadge,
		'sound' => $tSound,
		);
		$tBody ['payload'] = $tPayload;
		// Encode the body to JSON.
		$tBody = json_encode ($tBody);
		// Create the Socket Stream.
		$tContext = stream_context_create ();
		stream_context_set_option ($tContext, 'ssl', 'local_cert', $tCert);
		// Remove this line if you would like to enter the Private Key Passphrase manually.
		stream_context_set_option ($tContext, 'ssl', 'passphrase', $tPassphrase);
		// Open the Connection to the APNS Server.
		$tSocket = stream_socket_client ('ssl://'.$tHost.':'.$tPort, $error, $errstr, 30, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $tContext);
		// Check if we were able to open a socket.
		if (!$tSocket)
		exit ("APNS Connection Failed: $error $errstr" . PHP_EOL);
		// Build the Binary Notification.
		$tMsg = chr (0) . chr (0) . chr (32) . pack ('H*', $tToken) . pack ('n', strlen ($tBody)) . $tBody;
		// Send the Notification to the Server.
		$tResult = fwrite ($tSocket, $tMsg, strlen ($tMsg));
		if ($tResult)
		echo 'Delivered Message to APNS Successfully' . PHP_EOL;
		else
		echo 'Could not Deliver Message to APNS' . PHP_EOL;
		// Close the Connection to the Server.
		fclose ($tSocket);
		return true;
		die;
    }
    
    
    
	public static function androidTest($data, $reg_id) {
            
	        $url = 'https://fcm.googleapis.com/fcm/send';
			
			$payload['aps'] = array(
			    'title' => $data['mtitle'],
                            'body' => $data['mdesc'],
                            'sound' => 'default'
				
		    );
			
		$payload['data'] = array(
                    'title' => $data['mtitle']?(string)$data['mtitle']:"",
                    'message' => $data['mdesc']?(string)$data['mdesc']:"",
                    'notificationStatus' => $data['notificationStatus']?$data['notificationStatus']:0,
                    'requestID' => $data['requestID']?$data['requestID']:0,
                    'sound' => 'default'	
		 );
	       
	        
	        $headers = array(
	        	'Authorization: key=' .self::$API_ACCESS_KEY,
	        	'Content-Type: application/json'
	        );
	

	        $fields = array(
	            'registration_ids' => $reg_id,
                    'data' => $payload['data']
	        );
	
	    	return self::useCurl($url, $headers, json_encode($fields));
    	}
	
    
     // Sends Push notification for Android users
	public static function android($data, $reg_id) {
            
                
	        $url = 'https://fcm.googleapis.com/fcm/send';
			
			$payload['aps'] = array(
			    'title' => $data['mtitle'],
                            'body' => $data['mdesc'],
                            'sound' => 'default'
				
		    );
                        
                   $title = $data['mtitle']?(string)$data['mtitle']." \n":"";     
			
		$payload['data'] = array(
                    'title' => "StaffingCall",
                    'message' => $data['mdesc']?(string)$title.$data['mdesc']:"",
                    'notificationStatus' => (string)($data['notificationStatus']?$data['notificationStatus']:0),
                    'requestID' => (string)($data['requestID']?$data['requestID']:0),
                    'sound' => 'default'	
		 );
	       
	        
	        $headers = array(
	        	'Authorization: key=' .self::$API_ACCESS_KEY,
	        	'Content-Type: application/json'
	        );
	

	        $fields = array(
	            'registration_ids' => $reg_id,
                    'data' => $payload['data']
	        );
                
                //return true;
	
	    	return self::useCurl($url, $headers, json_encode($fields));
    	}
	
	
	
	// Curl 
	public static function useCurl($url, $headers=array(), $fields = null) {
		
		if(!is_array($headers)){die('headers MUST be an array!');}
	        // Open connection
                
                
	        $ch = curl_init();
	        if ($url) {
	            // Set the url, number of POST vars, POST data
	            curl_setopt($ch, CURLOPT_URL, $url);
	            curl_setopt($ch, CURLOPT_POST, true);
	            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	     
	            // Disabling SSL Certificate support temporarly
	            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	            if ($fields) {
	                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	            }
	     
	            // Execute post
	            $result = curl_exec($ch);
	            if ($result === FALSE) {
	                die('Curl failed: ' . curl_error($ch));
	            }
	     
	            // Close connection
	            curl_close($ch);
	
	            return true;//$result;
                }
         }
	
        // Sends Push notification for iOS users
	public static function iOS($data, $devicetokens) {
            foreach($devicetokens as $k => $dToken){
		$deviceToken = $devicetokens;
		$ctx = stream_context_create();
		// ck.pem is your certificate file
		stream_context_set_option($ctx, 'ssl', 'local_cert', public_path('/').self::$pemFile);
		stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
		// Open a connection to the APNS server
		
		$fp = stream_socket_client(
			'ssl://'.self::$gatewayUrl.':'.self::$gatewayPort, $err,
			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
		if (!$fp)
			return false;
			//exit("Failed to connect: $err $errstr" . PHP_EOL);
		// Create the payload body
                
                 $title = $data['mtitle']?(string)$data['mtitle']."\n":"";     
                
		$body['aps'] = array(
			'alert' => array(
			    'title' => "",
                'body' => $title.$data['mdesc'],
			 ),
			'sound' => 'default'
		);
		
		
		$body['notificationStatus'] = (string)($data['notificationStatus']?$data['notificationStatus']:0);
		$body['requestID'] = (string)($data['requestID']?$data['requestID']:0);
		// Encode the payload as JSON
		$payload = json_encode($body);
		// Build the binary notification
		
		
			$msg = chr(0) . pack('n', 32) . pack('H*', $dToken) . pack('n', strlen($payload)) . $payload;
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
		
		// Close the connection to the server
		fclose($fp);
//		if (!$result)
//			$resp =  false;//'Message not delivered' . PHP_EOL;
//		else
//			$resp =  true;//'Message successfully delivered' . PHP_EOL;
//			
//			return $resp;
            }
            
            return true;
	}
	
    
    public static function getPartialShiftsTimeOfRequestOffer($requestID){
        $getPartialShifts = DB::table('staffing_requestpartialshifts')->select('id',
                'partialShiftStartTime',
                'partialShiftEndTime'
                )->where([['requestID','=',$requestID]])->get();
        return $getPartialShifts;
    }
    
    
	
    
    public static function getPartialShiftsTimeOfUser($partialTimeID, $requestID){
        $getPartialShifts = DB::table('staffing_requestpartialshifts')->select('id',
                'partialShiftStartTime',
                'partialShiftEndTime'
                )->where([['id', '=', $partialTimeID], ['requestID','=',$requestID]])->first();
        return $getPartialShifts;
    }
    
    
    
    public static function getCountOfRespondedPeople($requestID){
        $respondedPeopleCount = array();
        /* Get Total Responded People */  
                      $respondedPeopleLists = DB::table('staffing_shiftoffer')
                ->select('id')->where([['requestID','=',$requestID]])->count();      
                    /* Get Total Responded People */        
                                  
                    /* Get Full Shift Responded People */  
                      $respondedFullShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestID],
                    ['responseType','=',0]
                        ])->count();      
                    /* Get Full Shift Responded People */         
                                  
                    /* Get Partial Shift Responded People */  
                      $respondedPartialShiftPeopleCount = DB::table('staffing_shiftoffer')
                ->select('id')->where([
                    ['requestID','=',$requestID],
                    ['responseType','=',1]
                        ])->count();      
                    /* Get Partial Shift Responded People */ 
               
                $respondedPeopleCount['totalResponded']  =  $respondedPeopleLists; 
                $respondedPeopleCount['fullResponded']  =  $respondedFullShiftPeopleCount; 
                $respondedPeopleCount['partialResponded']  =  $respondedPartialShiftPeopleCount;   
                      
               return   $respondedPeopleCount;     
                      
    }
    
    
    public static function getBusinessUnitInformation($businessUnitID){
            $unitInfoDetail = array();
            /* Get Open Staffing Request */  
            $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id'); 
            
            $openPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
            
            $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));
            
            $openPostingCount = $openPostingCountSQL->count();              
            /* Get Open Staffing Request */
            
            /* Get Past Staffing Request */  
            $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id'); 
            
            $pastPostingCountSQL->where('staffing_staffrequest.businessUnitID','=', $businessUnitID);
            
            //$pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d"));
            
            $pastPostingCount = $pastPostingCountSQL->count();              
            /* Get Past Staffing Request */       
            
            /* Get Past Staffing Request */  
            $totalUsersSQL = DB::table('staffing_usersunits')
                ->join('staffing_users', 'staffing_users.id', '=', 'staffing_usersunits.userID')
                ->select(
                        'staffing_usersunits.id'
                        );
            $totalUsersSQL->where('staffing_usersunits.businessUnitID','=', $businessUnitID);            
            $totalUsersSQL->whereIn('staffing_users.role', [0,3,4]); 
            $totalUsers = $totalUsersSQL->count();              
            /* Get Past Staffing Request */        

           

        $unitInfoDetail['openPostingCount']  =  $openPostingCount; 
        $unitInfoDetail['pastPostingCount']  =  $pastPostingCount; 
        $unitInfoDetail['totalUsers']  =  $totalUsers;   

       return   $unitInfoDetail;     
                      
    }
    
    
    
    
    public static function getGroupInformation($groupID){
            $groupDetail = array();
            /* Get Open Staffing Request */  
            $openPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id'); 
            
            $openPostingCountSQL->where('staffing_staffrequest.businessGroupID','=', $groupID);
            
            $openPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            $openPostingCountSQL->where('staffing_staffrequest.staffingStartDate','>=', date("Y-m-d"));
            
            $openPostingCount = $openPostingCountSQL->count();              
            /* Get Open Staffing Request */
            
            /* Get Past Staffing Request */  
            $pastPostingCountSQL = DB::table('staffing_staffrequest')
                    ->select('id'); 
            
            $pastPostingCountSQL->where('staffing_staffrequest.businessGroupID','=', $groupID);
            
            //$pastPostingCountSQL->whereIn('staffing_staffrequest.postingStatus', [1,3]); 
            $pastPostingCountSQL->where('staffing_staffrequest.staffingStartDate','<', date("Y-m-d"));
            
            $pastPostingCount = $pastPostingCountSQL->count();              
            /* Get Past Staffing Request */       
            
            /* Get Past Staffing Request */  
            $totalUsersSQL = DB::table('staffing_users')
                ->select(
                        'staffing_users.id'
                        );
            $totalUsersSQL->where('staffing_users.businessGroupID','=', $groupID);            
            $totalUsersSQL->whereIn('staffing_users.role', [0,3,4]); 
            $totalUsers = $totalUsersSQL->count();              
            /* Get Past Staffing Request */        

           

        $groupDetail['openPostingCount']  =  $openPostingCount; 
        $groupDetail['pastPostingCount']  =  $pastPostingCount; 
        $groupDetail['totalUsers']  =  $totalUsers;   

       return   $groupDetail;     
                      
    }
    
    
    public static function getCalenderData($year,$month,$userBusinessUnitID,$loginUserID,$userRole, $isUser){
        
        $shiftsArray = array();
        
        /* Get Business Unit All Shifts */
        $getShifts = DB::table('staffing_shiftsetup')
                    ->select('id','startTime','endTime','shiftType')
                    ->where([['businessUnitID','=',$userBusinessUnitID]])->orderBy('startTime', 'ASC')->get();
        /* Get Business Unit All Shifts */
        $allDatesInAMonth = myHelper::getAllDatesOfAYear($year,$month); 
        foreach($allDatesInAMonth as $k=>$displayMonth){
            if($getShifts){
                $shiftCount = 1;
                foreach($getShifts as $getShift){
                    $shiftType = "green";  //green
                    
                    $userAvailability = 0;
                    $userWorking = 0;
                    if($userRole == 0 || ($userRole == 4 && $isUser == 1)){
                        /* Check If User is Available To Take Offers */
                        
                        $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                        ->select('id');
                        $userAvailabilitySql->where('userID','=',$loginUserID);
                        $userAvailabilitySql->where('onDate','=',$displayMonth);
                        $userAvailabilitySql->where('availabilityStatus', '=', 0);//Unavailable or working somewhere else
                        $userAvailabilitySql->where(function ($query) use ($getShift) {
                            $query->orWhere('shiftID', '=', $getShift->id);
                            $query->orWhere('shiftID', '=', 0); 

                        });
                        $userAvailability = $userAvailabilitySql->count();
                        
                        if(($userAvailability) > 0){
                            
                           $shiftType = "red"; //red 
                        }
                        
                        $userWorkingSql = DB::table('staffing_usercalendarsettings')
                        ->select('id');
                        $userWorkingSql->where('userID','=',$loginUserID);
                        $userWorkingSql->where('onDate','=',$displayMonth);
                        $userWorkingSql->where('availabilityStatus', '=', 2);//Unavailable or working somewhere else
                        $userWorkingSql->where(function ($query2) use ($getShift) {
                            $query2->orWhere('shiftID', '=', $getShift->id);
                            $query2->orWhere('shiftID', '=', 0); 

                        });

                        $userWorking = $userWorkingSql->count();

                        if(($userWorking) > 0){
                            
                            if($displayMonth < date("Y-m-d")){
                                $shiftType = "darkblue";  //dark-blue
                            }else{  
                                $shiftType = "blue"; //blue 
                            }
                        }
                        
                        /* Check If User is Available To Take Offers */
                    }
                    
                    
                    if($userRole == 0 || ($userRole == 4 && $isUser == 1)){
                      
                            $getConfirmedPostsSql = DB::table('staffing_staffrequest')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftoffer', 
                                   'staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id')     
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id');
                            
                                $getConfirmedPostsSql->where('staffing_shiftoffer.userID','=',$loginUserID);
                               $getConfirmedPostsSql->where('staffing_shiftconfirmation.offerResponse','=',1);
                               $getConfirmedPostsSql->where('staffing_staffrequest.staffingStartDate','=',$displayMonth);
                               $getConfirmedPostsSql->where('staffing_staffrequest.staffingShiftID','=', $getShift->id);
                               $getConfirmedPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
                                   $getConfirmedPosts = $getConfirmedPostsSql->get();
                            
                            if($getConfirmedPosts->count() > 0){
                               if($displayMonth < date("Y-m-d")){
                                    $shiftType = "darkblue";  //dark-blue
                                }else{  
                                    $shiftType = "blue"; //blue 
                                } 
                            }else if($shiftType != 'red'){
                                if($displayMonth < date("Y-m-d")){
                                    $shiftType = "grey";  //grey
                                }
//                                }else{  
//                                    $shiftType = "green";  //green 
//                                } 
                            }
                        
                    }else{
                       $getStaffingCallsSql = DB::table('staffing_staffrequest')
                        ->select('id','staffingShiftID','lastMinuteStaffID');
                        $getStaffingCallsSql->where('staffingStartDate','=', $displayMonth);
                        $getStaffingCallsSql->where('staffingShiftID','=', $getShift->id);
                        $getStaffingCalls = $getStaffingCallsSql->get();
                        if($getStaffingCalls->count() > 0){
                           $shiftType = "red"; //red 
                        }else{
                           if($displayMonth < date("Y-m-d")){
                                $shiftType = "grey";  //grey
                            }else{  
                                $shiftType = "green";  //green 
                            }    
                        }
                    }
                     
                    
                    
                    //$title = date("g:iA",strtotime($getShift->startTime))."-".date("g:iA",strtotime($getShift->endTime));                 
                    $title = "Shift ".$shiftCount;
                    $shiftIcon = "day";
                    $shiftsArray[] = array(
                      'title'=> $title,
                      'start'=> date('Y-m-d',strtotime($displayMonth)),
                      'shiftType'=> $shiftType,
                      'shiftIcon'=> $shiftIcon,
                      'userAvailability'=> ($userAvailability)
                    );    
                  $shiftCount++;  
                }
                
            }
        }
        
        return $shiftsArray;
       
    }
    
    
    
    public static function getAllDatesOfAYear($year,$month){
        $list=array();
        //for($m = 1;$m<=12;$m++){
            //$month = $m; 
            $totalDaysInAMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
            for($d=1; $d<=$totalDaysInAMonth; $d++)
            {
                $time=mktime(12, 0, 0, $month, $d, $year);          
                if (date('m', $time)==$month)       
                    $list[]=date('Y-m-d', $time);
            }
        //}
        
        return $list;
        
    }
    
    
    /* Calender SET-UP For APIS */ 
    
    public static function getCalenderDataForAPI($startDate,$endDate,$userBusinessUnitID,$loginUserID,$userRole,$isUser){
       
        /* Get Business Unit All Shifts */
        $getShifts = DB::table('staffing_shiftsetup')
                    ->select('id','startTime','endTime','shiftType')
                    ->where([['businessUnitID','=',$userBusinessUnitID]])->orderBy('startTime', 'ASC')->get();
        /* Get Business Unit All Shifts */
        $allDatesInAMonth = myHelper::getAllDatesOfAPI($startDate,$endDate);        
        $fullCalenderData = array(); 
        
        foreach($allDatesInAMonth as $k=>$displayMonth){
            $shiftsArray = array();
            if($getShifts){
                $cnt = 1;
                foreach($getShifts as $getShift){
                    $shiftType = "1";  //green   
                    
                    if($userRole == 0 || ($userRole == 4 && $isUser == 1)){
                        /* Check If User is Available To Take Offers */
                        $userAvailabilitySql = DB::table('staffing_usercalendarsettings')
                        ->select('id');
                        $userAvailabilitySql->where('userID','=',$loginUserID);
                        $userAvailabilitySql->where('onDate','=',$displayMonth);
                        $userAvailabilitySql->whereIn('availabilityStatus', [0]);//Unavailable or working somewhere else
                        $userAvailabilitySql->where(function ($query) use ($getShift) {
                            $query->orWhere('shiftID', '=', $getShift->id);
                            $query->orWhere('shiftID', '=', 0); 

                        });
                        $userAvailability = $userAvailabilitySql->get();
                        
                        $userWorkingSql = DB::table('staffing_usercalendarsettings')
                        ->select('id');
                        $userWorkingSql->where('userID','=',$loginUserID);
                        $userWorkingSql->where('onDate','=',$displayMonth);
                        $userWorkingSql->whereIn('availabilityStatus', [2]);//Unavailable or working somewhere else
                        $userWorkingSql->where(function ($query2) use ($getShift) {
                            $query2->orWhere('shiftID', '=', $getShift->id);
                            $query2->orWhere('shiftID', '=', 0); 

                        });

                        $userWorking = $userWorkingSql->get();

                        if(count($userWorking) > 0){
                            
                            if($displayMonth < date("Y-m-d")){
                                $shiftType = "4";  //dark-blue
                            }else{  
                                $shiftType = "3"; //blue 
                            }
                        }

                        if(count($userAvailability) > 0){
                            
                           $shiftType = "2"; //red 
                        }
                        /* Check If User is Available To Take Offers */
                    }
                    
                    if($userRole == 0 || ($userRole == 4 && $isUser == 1)){
                      
                            $getConfirmedPostsSql = DB::table('staffing_staffrequest')
                           ->select('staffing_shiftoffer.requestID')
                           ->join('staffing_shiftoffer', 
                                   'staffing_shiftoffer.requestID', '=', 'staffing_staffrequest.id')     
                           ->join('staffing_shiftconfirmation', 'staffing_shiftconfirmation.shiftOfferID', '=', 'staffing_shiftoffer.id');
                                
                               $getConfirmedPostsSql->where('staffing_shiftoffer.userID','=',$loginUserID);
                               $getConfirmedPostsSql->where('staffing_shiftconfirmation.offerResponse','=',1);
                               $getConfirmedPostsSql->where('staffing_staffrequest.staffingStartDate','=',$displayMonth);
                               $getConfirmedPostsSql->where('staffing_staffrequest.staffingShiftID','=', $getShift->id);
                               $getConfirmedPostsSql->whereIn('staffing_staffrequest.postingStatus', [1,3]);
                                   $getConfirmedPosts = $getConfirmedPostsSql->get();
                                    
                            if($getConfirmedPosts->count() > 0){
                               if($displayMonth < date("Y-m-d")){
                                    $shiftType = "4";  //dark-blue
                                }else{  
                                    $shiftType = "3"; //blue 
                                } 
                            }else if($shiftType != '2'){
                                if($displayMonth < date("Y-m-d")){
                                    $shiftType = "5";  //grey   
                                }
                                
//                                else{  
//                                    $shiftType = "1";  //green   
//                                } 
                            }
                        
                    }else{
                       $getStaffingCallsSql = DB::table('staffing_staffrequest')
                        ->select('id','staffingShiftID','lastMinuteStaffID');
                        $getStaffingCallsSql->where('staffingStartDate','=', $displayMonth);
                        $getStaffingCallsSql->where('staffingShiftID','=', $getShift->id);
                        $getStaffingCalls = $getStaffingCallsSql->get();
                        if($getStaffingCalls->count() > 0){
                           $shiftType = "2"; //red  
                        }else{
                           if($displayMonth < date("Y-m-d")){
                                    $shiftType = "5";  //grey   
                            }else{  
                                $shiftType = "1";  //green   
                            }    
                        }
                    }
                     
                    
                    
                    $title = date("g:iA",strtotime($getShift->startTime))."-".date("g:iA",strtotime($getShift->endTime));                 
                    
                    $shiftsArray[] = array(
                      'id'=> (string)$getShift->id,
                      'name'=> 'Shift '.$cnt,//$title,
                      'shiftStatus'=> $shiftType
                    ); 
                  $cnt++;  
                }
                
            }
            
                    $fullCalenderData[] = array(
                        'Day' => (string)$displayMonth,
                        'shifts' => $shiftsArray
                     );
                    
            
            
        }
        
        return $fullCalenderData;
       
    }
    
    
    
    public static function getAllDatesOfAPI($startDate,$endDate){
        $list=array();
            $date_from = strtotime(date("Y-m-d",strtotime($startDate . " -1 day")));
            $date_to = strtotime(date("Y-m-d",strtotime($endDate . " +1 day")));
                $arr_days = array();
                $day_passed = ($date_to - $date_from); //seconds
                $day_passed = ($day_passed/86400); //days

                $counter = 1;
                $day_to_display = $date_from;
                while($counter < $day_passed){
                    $day_to_display += 86400;
                    $list[] = date('Y-m-d',$day_to_display);
                    $counter++;
                }

                
        
        
        return $list;
        
    }
    
    
    public static function getRequiredSkills($requiredSkillIDs){
        
        $requiredTypeOfStaffSkills = '';
                            
        if($requiredSkillIDs != ''){
           $requiredStaffCategoryIDs = explode(",", $requiredSkillIDs);
           $getSkills = DB::table('staffing_skillcategory')
             ->select('skillName')->whereIn('id', $requiredStaffCategoryIDs)->get();
           $skillsName = array();
           foreach($getSkills as $getSkill){
              $skillsName[] = $getSkill->skillName; 
           }

           if($skillsName)
             $requiredTypeOfStaffSkills = implode(', ', $skillsName);      
        } 
        
        return $requiredTypeOfStaffSkills;
        
    }
    
    
    /* Calender SET-UP For APIS */
    
    
}