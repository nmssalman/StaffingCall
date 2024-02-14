/*
 *  Document   : op_auth_signin.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Sign In Page
 */

var OpAuthSignIn = function() {
    // Init Sign In Form Validation, for more examples you can check out https://github.com/jzaefferer/jquery-validation
    var initValidationSignIn = function(){
        
        $.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
    },
    'Password must contain at least one numeric and one alphabetic character.');
    
    
        
        jQuery('.js-validation-resetpass').validate({
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
                
                'password': {
                    required: true,
                    minlength: 8,
                    mypassword: true
                },
                'password_confirmation': {
                    required: true,
                    equalTo: '#password'
                },
            },
            messages: {
                'password': {
                    required: 'Please provide a password',
                    minlength: 'Your password must be at least 8 characters long'
                },
                'password_confirmation': {
                    required: 'Please provide a password',
                    equalTo: 'Please enter the same password as above'
                },
            }
        });
    };
    
    
    
    var initValidationGenerateLoginIDAndPassword = function(){
        
        $.validator.addMethod('mypassword', function(value, element) {
        return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
    },
    'Password must contain at least one numeric and one alphabetic character.');
    
    
        
        jQuery('.js-validation-generate-username-pass').validate({
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
                
                'userName': {
                    required: true,
                    minlength: 3
                },
                'password': {
                    required: true,
                    minlength: 8,
                    mypassword: true
                },
                'password_confirmation': {
                    required: true,
                    equalTo: '#password'
                },
            },
            messages: {
                'userName': {
                    required: 'Please enter Login ID',
                    minlength: 'Login ID must consist of at least 3 characters'
                },
                'password': {
                    required: 'Please provide a password',
                    minlength: 'Your password must be at least 8 characters long'
                },
                'password_confirmation': {
                    required: 'Please confirm password',
                    equalTo: 'Please enter the same password as above'
                },
            }
        });
    };
    
    

    return {
        init: function () {
            // Init Sign In Form Validation
            initValidationSignIn();
            initValidationGenerateLoginIDAndPassword();
        }
    };
}();

// Initialize when page loads
jQuery(function(){ OpAuthSignIn.init(); });