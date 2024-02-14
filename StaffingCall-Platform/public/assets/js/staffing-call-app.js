$(document).ready(function() {
    var Datatableordering = false;
       if (typeof url !== 'undefined') {
            if(dataUrl == url+'/groups/ajaxGroupList' || dataUrl == url+'/users/ajaxUserList'){
               Datatableordering = true; 
            }
       }             
    var dataTable = $('#datatable').dataTable( {
            "processing": true,
            "serverSide": true,
            "order": [[ 0, "asc" ]],
             "ordering": Datatableordering,
            "ajax":{

                url :dataUrl, // json datasource
                type: "GET",
                error: function(e){// error handling
                    $(".datatable-grid-error").html("");
                    $("#datatable").append('<tbody class="datatable-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#datatable_processing").css("display","none");

                }
            },
            "rowCallback": function( row, data, index ) {
                
                if(data[4] == 'Medium'){
                     $('td:eq(4)', row).html('<span class="badge badge-info">Medium</span>');
                     $(row).addClass('tr-row-medium'); 
                 }
                else if(data[4] == 'High'){
                     $('td:eq(4)', row).html('<span class="badge badge-danger">High</span>');  
                     $(row).addClass('tr-row-high'); 
                 }
                else if(data[4] == 'Normal'){
                     $('td:eq(4)', row).html('<span class="badge badge-warning">Normal</span>');
                     $(row).addClass('tr-row-normal'); 
                 }
                   
            }
    } );
} );
                
                
    var published = { 
         toggle : function(id, url){ 
            obj = $('#'+id).parent(); 
            $.ajax({ 
                    url: url,  
                    type: "GET",  
                    success: function(response){ 
                          obj.html(response);    

                    },
                    error:function(e){
                        console.log(e);
                    }
            }); 
         }   

    };
                    
                    