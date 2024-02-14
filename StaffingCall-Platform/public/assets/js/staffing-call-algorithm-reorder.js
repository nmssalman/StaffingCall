$(document).ready(function() {
    
    $("tbody").sortable({

        items: 'tr:not(:first-child)',
        start: function (event, ui) {
            ui.item.css('border', '1px solid red');
        },
        axis: "y",
        placeholder: "sortable-placeholder",
        stop: function (event, ui) {
            var childrens = event.target.children;
            var ids = [];
            for (i = 0; i < childrens.length; ++i){
               ids[i] = childrens[i]["id"];
            }
            position = ids.join(); 
            $('#complexOrder').val(position);
            ui.item.css('border', '');
        }
    }).disableSelection();
    
});


$(function() {
    $("#offerAlgorithmID").change(function(){
        
        var option = $('option:selected', this).attr('itemid');
        if(option == 'complex'){
            $('#complexOrderDisplay').css('display', 'block');
        }else{
            $('#complexOrderDisplay').css('display', 'none');
        }
    });
});