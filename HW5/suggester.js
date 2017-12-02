
$(function() {
            
            $("#q").autocomplete({
                source : function(request, response) {
                    var query=$("#q").val();
                    var last = query.toLowerCase().split(" ");
                        last = last[last.length-1]; 
                    var URL = "http://localhost:8983/solr/myexample/suggest?q="+last+"&wt=json";
                    $.ajax({
                        url : URL,
                        dataType : 'jsonp',
                        jsonp : 'json.wrf',
                    
                        success : function(data) {

                            var suggestions = data.suggest.suggest[last].suggestions;
                            response($.map(suggestions, function (item) {
                                
                                var queries = query.split(" ");
                                
                                var before=queries.slice(0,queries.length-1).join(" ")+" ";

                                if (item.term.length>20) return null;
                                return before+ item.term;
                            }));
                            
                        },
                    });
                },
                minLength : 2,
                max : 5,
              
                // focus: function( event, ui ) {  
                //    // $(".rph").val( ui.item.label );  
                //    $("#q").val( ui.item.value );  
                //      // return false;  
                //    },
                // select: function( event, ui ) {   
                //    $("#q").val( ui.item.value );  
                //    return false;  
                // },
                open: function() {
                    $( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
                },
                close: function() {
                    $( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
                }
          // formatItem: function(row) {
          //   return "<span style='width:40%' class='col-1'>" + row[0] + "</span> " ;
          // },
          // formatMatch: function(row) {
          //   return row[0];
          // },
          // formatResult: function(row) {
          //   return row[0];
          // }   
        

                // scrollHeight: 220
                
                

            });
        });
