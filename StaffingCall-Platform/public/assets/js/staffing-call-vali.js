$(document).ready(function() {
    $("#logo").change(function (e) {
        for (var i = 0; i < e.originalEvent.target.files.length; i++) {
            var file = e.originalEvent.target.files[i];

            var reader = new FileReader();
            reader.onloadend = function() {
                $("#logo_preview").attr("src", reader.result);
            }
            reader.readAsDataURL(file);
            //$("input").after(img);
        }
    });
    
    $("#removeLogo").click(function (){
        if(confirm('Are you sure? Remove Group Logo.')){
            $("#logo_preview").attr("src", defaultGroupIconAfterRemove);
            //$("#logo").val('');
            $.ajax({ 
                url: removeGroupIconUrl,  
                type: "GET",  
                success: function(response){ 
                },
                error:function(e){
                    console.log(e);
                }
            }); 
        }
    });
    
});


var BeFormValidation = function() {
   
    var initGroupFormValidationBootstrap = function(){
        
        $.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
    },
    'Password must contain at least one numeric and one alphabetic character.');
    
        $.validator.addMethod('phonecheck', function(value, element) {
            
        return this.optional(element) || (value.match(/^(\d+-?)+\d+$/));
    },
    'Please enter valid phone number.');
    
    
//        $.validator.addMethod('myloginid', function(value, element) {
//        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
//    },
//    'Login ID must contain at least one numeric and one alphabetic character.');
        
        
        jQuery('.js-group-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                'groupCode': {
                    required: true,
                    minlength: 3
                },
                'groupName': {
                    required: true,
                    minlength: 3
                },
                'firstName': {
                    required: true,
                    minlength: 3
                },
                'lastName': {
                    required: true,
                    minlength: 3
                },
                'userName': {
                    required: true,
                    minlength: 3
                },
                'email': {
                    required: true,
                    email: true
                },
                'phone': {
                    required: true,
                    phonecheck: true
                },
                'password': {
                    required: true,
                    minlength: 8,
                    mypassword: true
                },
                'password_confirmation': {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                'groupCode': {
                    required: 'Please enter Group Code',
                    minlength: 'Code must consist of at least 3 characters'
                },'groupName': {
                    required: 'Please enter Group name',
                    minlength: 'Group name must consist of at least 3 characters'
                },'firstName': {
                    required: 'Please enter First name',
                    minlength: 'First name must consist of at least 3 characters'
                },'lastName': {
                    required: 'Please enter Last name',
                    minlength: 'Last name must consist of at least 3 characters'
                },'userName': {
                    required: 'Please enter Login ID/Username',
                    minlength: 'Login ID must consist of at least 3 characters'
                },
                'val-email': 'Please enter a valid email address',
                
                'phone': {
                    required: 'Please enter phone number'
                },
                'password': {
                    required: 'Please enter password',
                    minlength: 'Your password must be at least 8 characters long'
                },
                'password_confirmation': {
                    required: 'Please enter password',
                    minlength: 'Your password must be at least 8 characters long',
                    equalTo: 'Please enter the same password as above'
                }
            }
        });  
        
    };
        
   
    var initUpdateGroupFormValidationBootstrap = function(){
        
        jQuery('.js-update-group-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'groupName': {
                    required: true,
                    minlength: 3
                }
            },
            messages: {
                'groupName': {
                    required: 'Please enter Group name',
                    minlength: 'Group name must consist of at least 3 characters'
                }
            }
        });  
        
    };
    
    
    
    
        
   
    var initCreateUnitFormValidationBootstrap = function(){
        
        
        $.validator.addMethod('storeNuberAlphaNumericCheck', function(value, element) {
        return this.optional(element) || (value.match(/^[a-z0-9]+$/i));
    },
    'Allowed only alpha-numeric.');
        
        jQuery('.js-unit-create-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'unitName': {
                    required: true,
                    minlength: 2
                },
                'storeNumber': {
                    required: true,
                    storeNuberAlphaNumericCheck: true
                },
                'offerAlgorithmID': {
                    required: true
                }
            },
            messages: {
                'unitName': {
                    required: 'Please enter Business unit name',
                    minlength: 'Group name must consist of at least 2 characters'
                },
                'storeNumber': {
                    required: 'Please enter Store number'
                },
                'offerAlgorithmID': {
                    required: 'Please select an offer algorithm.'
                }
            }
        });  
        
    };
    
        
   
    var initCreateSkillCategoryValidationBootstrap = function(){
        
        jQuery('.js-skill-create-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'skillName': {
                    required: true
                },
                'businessUnitID': {
                    required: true
                }
            },
            messages: {
                
                'skillName': {
                    required: 'Please enter Skill Category Name'
                },
                'businessUnitID': {
                    required: 'Please select Business Unit'
                }
            }
        });  
        
    };   
   
    var initRequestReasonFormValidationBootstrap = function(){
        
        jQuery('.js-request-reasons-form-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'reasonName': {
                    required: true
                }
            },
            messages: {
                
                'reasonName': {
                    required: 'Please enter Reason title'
                }
            }
        });  
        
    };  
   
    var initVacancyReasonFormValidationBootstrap = function(){
        
        jQuery('.js-vacancy-reasons-form-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'reasonName': {
                    required: true
                }
            },
            messages: {
                
                'reasonName': {
                    required: 'Please enter Reason title'
                }
            }
        });  
        
    }; 
    
    
    
    var initNewRequestCallFormValidationBootstrap = function(){
        
        jQuery('.js-staffing-new-request-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'businessUnitID': {
                    required: true
                },
                'requestReasonID': {
                    required: true
                },
                'requiredStaffCategoryID[]': {
                    required: true
                },
                'staffingStartDate': {
                    required: true
                }
            },
            messages: {
                
                'businessUnitID': {
                    required: 'Please select a Business Unit.'
                },
                'requestReasonID': {
                    required: 'Please select request reason.'
                },
                'requiredStaffCategoryID[]': {
                    required: 'Please select required skill category.'
                },
                'staffingStartDate': {
                    required: 'Please select Date of staff needed.'
                }
            }
        });  
        
    }; 
   
    var initShiftSetUPFormValidationBootstrap = function(){
        
        jQuery('.js-shift-setup-form-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                
                'businessUnitID': {
                    required: true
                },
                'startTime': {
                    required: true
                },
                'endTime': {
                    required: true
                }
            },
            messages: {
                
                'businessUnitID': {
                    required: 'Please select Business Unit'
                },
                'startTime': {
                    required: 'Please enter Shift start time.'
                },
                'endTime': {
                    required: 'Please enter Shift end time.'
                }
            }
        });  
        
    };

// ***********Change Password Validation Start********


   
    var initChangePasswordValidationBootstrap = function(){
        jQuery('.change-password-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                'old_password': {
                    required: true
                },
                'password': {
                    required: true,
                    minlength: 6
                },
                'password_confirmation': {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                'old_password': {
                    required: 'Please enter old password'
                },
                'password': {
                    required: 'Please enter password',
                    minlength: 'Your password must be at least 6 characters long'
                },
                'password_confirmation': {
                    required: 'Please enter password',
                    minlength: 'Your password must be at least 6 characters long',
                    equalTo: 'Please enter the same password as above'
                }
            }
        });  
        
    };
      
// ***********Change Password Validation End********
 

    /******************##Update Profile Validation##*************/
    
   
    var initUpdateProfileValidationBootstrap = function(){
        
        
    
        $.validator.addMethod('phonecheck', function(value, element) {
            
        return this.optional(element) || (value.match(/^(\d+-?)+\d+$/));
       },
        'Please enter valid phone number.');
        
        jQuery('.edit-profile-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                'name': {
                    required: true,
                    minlength: 3
                },
                'email': {
                    required: true,
                    email: true
                },
                'address': {
                    required: true,
                    minlength: 6
                },
                'phone': {
                    required: true,
                    phonecheck: true
                }
            },
            messages: {
                'name': {
                    required: 'Please enter a name',
                    minlength: 'Your name must consist of at least 3 characters'
                },
                
                'val-email': 'Please enter a valid email address',
                
                'address': {
                    required: 'Please provide office address',
                    minlength: 'Address must be at least 5 characters long'
                },
                'phone': {
                    required: 'Please enter phone number'
                }
            }
        });  
        
    };
        
/******************##Update Profile Validation##*************/


/************###*Create New User Form*###***********/


   
    var initCreateUserFormValidationBootstrap = function(){
        
        $.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
    },
    'Password must contain at least one numeric and one alphabetic character.');
    
        $.validator.addMethod('phonecheck', function(value, element) {
            
        return this.optional(element) || (value.match(/^(\d+-?)+\d+$/));
    },
    'Please enter valid phone number.');
    
    
//        $.validator.addMethod('myloginid', function(value, element) {
//        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
//    },
//    'Login ID must contain at least one numeric and one alphabetic character.');
        
        
        jQuery('.js-user-create-validation-bootstrap').validate({
            ignore: [],
            errorClass: 'invalid-feedback animated fadeInDown',
            errorElement: 'div',
            errorPlacement: function(error, e) {
                jQuery(e).parents('.form-group > div').append(error);
            },
            highlight: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid').addClass('is-invalid');
            },
            success: function(e) {
                jQuery(e).closest('.form-group').removeClass('is-invalid');
                jQuery(e).remove();
            },
            rules: {
                'businessUnitID[]': {
                    required: true
                },/*
                'skills[]': {
                    required: true
                },*/
                'firstName': {
                    required: true,
                    minlength: 3
                },
                'lastName': {
                    required: true,
                    minlength: 3
                },/*
                'userName': {
                    required: true,
                    minlength: 3
                },*/
                'email': {
                    required: true,
                    email: true
                },
                'phone': {
                    required: true,
                    phonecheck: true
                },
                'password': {
                    required: true,
                    minlength: 8,
                    mypassword: true
                },
                'password_confirmation': {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                'businessUnitID': {
                    required: 'Please assign Business Unit to user'
                },/*
                'skills': {
                    required: 'Please select skills of user'
                },*/'firstName': {
                    required: 'Please enter First name',
                    minlength: 'First name must consist of at least 2 characters'
                },'lastName': {
                    required: 'Please enter Last name',
                    minlength: 'Last name must consist of at least 2 characters'
                },/*'userName': {
                    required: 'Please enter Login ID/Username',
                    minlength: 'Login ID must consist of at least 3 characters'
                },*/
                'email':{
                 required: 'Please enter email address',   
                  'val-email': 'Please enter a valid email address',
                },
                
                'phone': {
                    required: 'Please enter phone number'
                },
                'password': {
                    required: 'Please enter a password',
                    minlength: 'Your password must be at least 8 characters long'
                },
                'password_confirmation': {
                    required: 'Please confirm password',
                    minlength: 'Your password must be at least 8 characters long',
                    equalTo: 'Please enter the same password as above'
                }
            }
        });  
        
    };
        

/************###*Create New User Form*###***********/



    return {
        init: function () {
            // Init Bootstrap Forms Validation
            initGroupFormValidationBootstrap();
            initUpdateGroupFormValidationBootstrap();
            initCreateUnitFormValidationBootstrap();
            initCreateUserFormValidationBootstrap();
            initChangePasswordValidationBootstrap();
            initUpdateProfileValidationBootstrap();
            initCreateSkillCategoryValidationBootstrap();
            initRequestReasonFormValidationBootstrap();
            initVacancyReasonFormValidationBootstrap();
            initShiftSetUPFormValidationBootstrap();
            
            initNewRequestCallFormValidationBootstrap();

        }
    };
}();

// Initialize when page loads
jQuery(function(){ BeFormValidation.init(); });


/* ALert Notification Validation Check Start */
function chkNotifyUser(){
    
    var chk = true;
    
    if($('#isNotification').prop('checked') == true){
       if($('#notifyUserAll').prop('checked') == true || $('#notifyUserSpecific').prop('checked') == true){
           
           $('#notify-error').html('');
            
            if($('#notifyUserSpecific').prop('checked') == true){
               if($('#radius').val() == ''){
                 $('#radius-error').html('Please enter radius.');  
                 chk = false;
               }else{
                 $('#radius-error').html('');   
               }
               
            }else{
                
               $('#radius-error').html('');   
            }
           
           
            if($('#notifyMsg').val() == ''){
               $('#notifyMsg-error').html('Please enter message.'); 
               chk = false;
            }else{
               $('#notifyMsg-error').html(''); 
            }
               
           
       } else{
           $('#notify-error').html('Please select an option given below.');
           chk = false;
       }
    }else{
      $('#notify-error').html(''); 
      $('#radius-error').html(''); 
      $('#notifyMsg-error').html(''); 
    }
    
    if(chk){
        return true;
    }else{
        return false;
    }
    
}
/* ALert Notification Validation Check End */
