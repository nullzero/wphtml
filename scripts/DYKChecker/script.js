$(document).ready(function(){
    $("#openWiki").click(function(){
        var title = $('#inputTitle').val();
        if(title){
            window.open("http://th.wikipedia.org/wiki/" + title, "child_window");
            return false;
        }
    });
    $("#inputTitle").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "http://th.wikipedia.org/w/api.php",
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
