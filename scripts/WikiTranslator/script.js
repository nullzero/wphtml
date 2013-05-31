$(document).ready(function(){
    $("#ausepage").click(function(){
        usepage = true;
        usecontent = false;
    });
    $("#ausecontent").click(function(){
        usecontent = true;
        usepage = false;
    });
    $("#btnClear").click(function(){
        $('#inputTitle').val("");
        $('#inputSite').val("");
        $('#inputSiteDest').val("");
        $('#inputContent').val("");
    });
    $("#btnSubmit").click(function(){
        var site = $('#inputSite').val();
        if(!site){
            bootstrap_alert.warning('โปรดใส่รหัสไซต์ต้นทาง');
            return false;
        }
        var siteDest = $('#inputSiteDest').val();
        if(!siteDest){
            bootstrap_alert.warning('โปรดใส่รหัสไซต์ปลายทาง');
            return false;
        }
        var title = $('#inputTitle').val();
        var content = $('#inputContent').val();
        if((title && usepage) || (content && usecontent)){
            var tmp = {"site": site,
                       "siteDest": siteDest};
            if(title && usepage){
                tmp["title"] = title;
                $('#inputContent').val("");
            }
            $('#form').attr('action', '?' + $.param(tmp));
            return true;
        }
        bootstrap_alert.warning('โปรดใส่ชื่อหน้าต้นทางหรือข้อความที่จะให้แปล');
        return false;
    });
    $("#inputTitle").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "http://" + $('#inputSite').val() + ".wikipedia.org/w/api.php",
                dataType: "jsonp",
                data: {
                    'action': "query",
                    'list'  : "allpages",
                    'format': "json",
                    'apprefix': request.term,
                    'limit' : 10
                },
                success: function(data) {
                    var tmp = new Array();
                    data = data["query"]["allpages"];
                    for(var x in data) tmp.push(data[x]["title"]);
                    response(tmp);
                }
            });
        }
    });
    var icons = {
        header: "ui-icon-triangle-1-e",
        activeHeader: "ui-icon-triangle-1-s"
    };
    $('#statPanel').accordion({heightStyle: "content", 
                               icons: icons, 
                               active: false,
                               collapsible: true});
});
