$(document).ready(function (){
    
    
    
    var role = $('input[name=role]:checked').val();
    
    if(role == '0'){
        
        $('#userSkillsForm').show();
        $('#skills').attr('required', true);
        
        $('.primaryUnitForEndUser').attr('name', 'businessUnitID[]');
        $('#forEndUser').css('display','block');
        $('#forHigherUser').css('display','none');
        $('#forAdminUser').css('display','none');
    }else if(role == '4'){
        
        $('#userSkillsForm').show();
        $('#skills').attr('required', true);
        
        
        $('.primaryUnitForEndUser').attr('name', 'businessUnitIDEnd');
        $('#forHigherUser').css('display','block');
        $('#forEndUser').css('display','none');
        $('#forAdminUser').css('display','block');
    }else { 
        
        $('#userSkillsForm').hide();
        $('#skills').attr('required', false);
        
        $('.primaryUnitForEndUser').attr('name', 'businessUnitIDEnd');
        $('#forHigherUser').css('display','block');
        $('#forEndUser').css('display','none');
        $('#forAdminUser').css('display','none');
    }
    
    $('#businessUnitID').change(function (){ 
        role = $('input[name=role]:checked').val();
        var businessUnitID = this.value;
        if(role == '0'){
            $.ajax({ 
                url: requestUrl,  
                type: "POST",
                data: {_token: CSRF_TOKEN,businessUnitID:businessUnitID},
                success: function(response){ 
                    var unitSelect = $("#businessUnitIDs");
                   unitSelect.html($("<option />").val('').text(''));
                    $.each(response.businessUnits, function(k, v) {
                        unitSelect.append($("<option />").val(v.id).text(v.unitName));
                    });
                    
                    $("#businessUnitIDs option[value="+businessUnitID+"]").remove();
                    
                    
                
//                        var skillCatSelect = $("#skills");
//                       skillCatSelect.html($("<option />").val('').text(''));
//                        $.each(response.skills, function(k2, v2) {
//                            skillCatSelect.append($("<option />").val(v2.id).text(v2.skillName));
//                        });
                    
                },
                error:function(e){
                    console.log(e);
                }
            }); 
        }

    });
    
    
    
    

    
});


function checkUserType(role){
    if(role == '0'){
        
        $('#userSkillsForm').show();
        $('#skills').attr('required', true);
        
        
        $('.primaryUnitForEndUser').attr('name', 'businessUnitID[]');
        $('#forEndUser').css('display','block');
        $('#forHigherUser').css('display','none');
        $('#forAdminUser').css('display','none');
    }else if(role == '4'){
        
        $('#userSkillsForm').show();
        $('#skills').attr('required', true);
        
        
        $('.primaryUnitForEndUser').attr('name', 'businessUnitIDEnd');
        $('#forHigherUser').css('display','block');
        $('#forEndUser').css('display','none');
        $('#forAdminUser').css('display','block');
    }else {   
        
        $('#userSkillsForm').hide();
        $('#skills').attr('required', false);
        
             
        $('.primaryUnitForEndUser').attr('name', 'businessUnitIDEnd');
        $('#forHigherUser').css('display','block');
        $('#forEndUser').css('display','none');
        $('#forAdminUser').css('display','none');
    }
}