// Callback function for datatables to parse the API response
function onServerCall(sSource, aoData, fnCallback) 
{ // {{{
    $.ajax({
        dataType:'text',
        type:'POST',
        url:sSource,
        data:aoData,
        success:function(resp) {
            try {
                resp = resp.match(/JSON_DATA\>(.*)\<\/JSON_DATA/)[1];
                json = eval("(" + resp + ")");
            } catch(e) {}

            fnCallback(json);
        }
    });
} // }}}

$(document).ready(function() {
    index_page.initialize();
});
