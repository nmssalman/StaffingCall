var businessUnitID = '';

$('#businessUnitID').change(function (){ 
    
        businessUnitID = this.value;
        setDataDynamicByBusinessUnit(businessUnitID);
});

function setDataDynamicByBusinessUnit(businessUnitID){
    $.ajax({ 
            url: requestUrl,  
            type: "POST",
            data: {_token: CSRF_TOKEN,businessUnitID:businessUnitID}  ,
            success: function(response){ 
                
                var staffSelect = $("#staff");
               staffSelect.html($("<option />").val('').text(''));
                $.each(response.staffs, function(k, v) {
                    staffSelect.append($("<option />").val(v.id).text(v.firstName+" "+v.lastName));
                });
                
//                var skillCatSelect = $("#requiredStaffCategoryID");
//               skillCatSelect.html($("<option />").val('').text(''));
//                $.each(response.skills, function(k2, v2) {
//                    skillCatSelect.append($("<option />").val(v2.id).text(v2.skillName));
//                });
                
                
                $('#dayShiftData').html('');
                var i = 1;
                $.each(response.shifts, function(k3, v3) {
                    if(i == 1){
                        $('#dayShiftData').append('' +
                        '<label class="css-control css-control-secondary css-radio">' +
                        '&nbsp;&nbsp;&nbsp;<input checked="checked" value="'+v3.id+'" type="radio" class="css-control-input" '+
                        'id="shiftID_'+v3.id+'" name="staffingShiftID">'+
                        '<span class="css-control-indicator"></span> '+
                        v3.startTimes+' - '+v3.endTimes +
                        '</label>');
                     }else{
                        $('#dayShiftData').append('' +
                        '<label class="css-control css-control-secondary css-radio">' +
                        '<input value="'+v3.id+'" type="radio" class="css-control-input" '+
                        'id="shiftID_'+v3.id+'" name="staffingShiftID">'+
                        '<span class="css-control-indicator"></span> '+
                        v3.startTimes+' - '+v3.endTimes +
                        '</label>');
                     }
                    i++;
                });
            },
            error:function(e){
                console.log(e);
            }
        });
}

$('#dayShiftClick').click(function (){
  $('#dayShiftStyle').css('display','block');  
  $('#nightShiftStyle').css('display','none');  
  $('#customShiftStyle').css('display','none');  
});

$('#nightShiftClick').click(function (){
  $('#nightShiftStyle').css('display','block');  
  $('#dayShiftStyle').css('display','none');  
  $('#customShiftStyle').css('display','none');   
});

$('#customShiftClick').click(function (){
  $('#customShiftStyle').css('display','block'); 
  $('#nightShiftStyle').css('display','none');  
  $('#dayShiftStyle').css('display','none');  
    
});


function reasonRequestCheck(requestID){
    var defaultOf = $('#requestReasonID option:selected').attr('defaultOf');
    if(requestID == 1 || defaultOf == 1){
        $('#quesStepOne').css('display','block');
    }else{
        $('#quesStepOne').css('display','none');
    }
}


$(document).ready(function (){
    
    var defaultOf = $('#requestReasonID option:selected').attr('defaultOf');
    //requestID = $('input[name=requestReasonID]:checked').val();
    requestID = $('#requestReasonID').val();
    
    if(requestID == 1 || defaultOf == 1){
        $('#quesStepOne').css('display','block');
    }else{
        $('#quesStepOne').css('display','none');
    }
    
});


$(document).ready(function (){
    businessUnitID = $('#businessUnitID').val();
    setDataDynamicByBusinessUnit(businessUnitID);
    //console.log(multiSkills);
});