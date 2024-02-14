
/*Ajax Paging for Requests Post OF Each Unit */

function performPaging(pageNo){
     var url = requestPagingUrl+'/'+pageNo;
     var obj = $('#basic-info');
        $('#loadingDiv').css('display','block');
        $.ajax({ 
                url: url,  
                type: "GET",  
                success: function(response){  
                      obj.html(response);
                      
                        $('#loadingDiv').css('display','none');
                },
                error:function(e){
                    console.log(e);
                }
        }); 
}

/*Ajax Paging for Requests Post OF Each Unit */  


/*Ajax Paging for Units List */

function performPagingForUnit(pageNo){
     var url = requestPagingForUnitUrl+'/'+pageNo;
     var obj = $('#all-units');
        $('#loadingDiv').css('display','block');
        $.ajax({ 
                url: url,  
                type: "GET",  
                success: function(response){  
                      obj.html(response);
                      $('#loadingDiv').css('display','none');
                },
                error:function(e){
                    console.log(e);
                }
        }); 
}

/*Ajax Paging for Units List */  

/*Ajax Paging for Groups List */

function performPagingForGroups(pageNo){
     var url = requestPagingForGroupsUrl+'/'+pageNo;
     var obj = $('#all-groups');
        $('#loadingDiv').css('display','block');
        $.ajax({ 
                url: url,  
                type: "GET",  
                success: function(response){  
                      obj.html(response);
                      
                        $('#loadingDiv').css('display','none');
                },
                error:function(e){
                    console.log(e);
                }
        }); 
}

/*Ajax Paging for Groups List */ 

/*Ajax Paging for Groups List */

function performPagingForGroupDetail(pageNo){
     var url = requestPagingForGroupsDetailUrl+'/'+pageNo;
     var obj = $('#group-detail');
        $('#loadingDiv').css('display','block');
        $.ajax({ 
                url: url,  
                type: "GET",  
                success: function(response){  
                      obj.html(response);
                      
                        $('#loadingDiv').css('display','none');
                },
                error:function(e){
                    console.log(e);
                }
        }); 
}

/*Ajax Paging for Groups List */