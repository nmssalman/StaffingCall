<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>StaffingCall</title>
</head>

<body>

<div class="latter" style="background-color: #fff;width:98%; border:1px solid #00aff0;display:inline-block; padding:5px;">  
<div class="w_logo" style="width:30%;background-position:center top;height:135px;display:inline-block;margin-left:20px;"><img src="{!! $logo !!}" alt="Staffing Call" style="width:140px;"/></div>  
<hr class="hr" style="border:2px solid #00aff0;" />  
<div class="w_frame" style="width:98%;margin:0 auto;font-family:arial, helvetica, sans-serif;font-size:9pt;color:#000;line-height:20px;margin-bottom:10px;">    
<div class="w_detail" style="padding-left:20px;text-align:justify;">      
<p><br />
       
      </p>      Dear <span style="font-weight: bold; font-style: italic;">{!! $name !!}</span><em><strong>,</strong></em><br />
      <br />
      Your account is created in StaffingCall with following details.
      <ol>
          <li><strong>Business Group :</strong> {!! $groupName !!}</li>
          <li><strong>Group Code:</strong> {!! $groupCode !!}</li>
          <li><strong>Login-Id:</strong> {!! $loginID !!}</li>
          <li><strong>Password:</strong> {!! $password !!}</li>
      </ol>
      Please click <a style="color: #4ab7e4;" href="{!! $link !!}">here</a> to login.<br />

    </div>    
<p>    </p>
<div class="w_detail" style="padding-left:20px;text-align:justify;"> <strong>Best Regards</strong><br />
      <strong>StaffingCall Team</strong><br />
      
    </div>    
<p></p>  </div>  <br />
</div>

</body>
</html>