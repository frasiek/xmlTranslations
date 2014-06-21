var controller = null;
$(document).ready(function(){
    controller = new main();
});

function main(){
    this.containter = null;
    this.tableName = null;
    this.init();
}

main.prototype.init = function(){
    var _th = this;
    this.containter = $("#dynamicContent");
    
    $("#setUpConnection").click(function(){
        _th.setUpConnection();
    });
    $("#fetchTables").click(function(){
        _th.fetchTables();
    });
    $(document).on("click",".showTable",function(e){
        e.preventDefault();
        var tableName = $(this).attr("data-table-name");
        _th.getTableData(tableName);
    });
}

main.prototype.setUpConnection = function(){
    var _th = this;
    $("#dynamicContent").addClass("container");
    $(this.containter).html("<div class='.col-xs-6'><form class='conf-form'>\
        <div class='form-group'><input class='form-control' type='text' placeholder='host' name='DB_HOST'/></div>\
        <div class='form-group'><input class='form-control' type='text' placeholder='login' name='DB_USER'/></div>\
        <div class='form-group'><input class='form-control' type='text' placeholder='password' name='DB_PASSWORD'/></div>\
        <div class='form-group'><input class='form-control' type='text' placeholder='database name' name='DB_DB'/></div>\
        <div class='form-group'><button type='submit' class='btn btn-success'>Connect</button></div>\
    </form></div>");
    
    $(this.containter).find("form").submit(function(e){
        e.preventDefault();
        _th.sendPost($(this).serialize()+"&a=connect");
    });
}

main.prototype.fetchTables = function(){
    var _th = this;
    $.ajax("index.php",{
        type : 'POST',
        data: {a: 'fetchTables'},
        success: function(data, textStatus, jqXHR ){
            data = $.parseJSON(data).response;
            $(".alert").alert("close");
            var html = '<h3>Select table too see data</h3><ul class="list-group">';
            $.each(data, function(tmp,tableName){
                for(var key in tableName){
                    html += '<li class="list-group-item"><a href="#" class="showTable" data-table-name="'+tableName[key]+'">'+tableName[key]+'</a>';
                }
            });
            html += "</ul>";
            $("#dynamicContent").addClass("container");
            $("#dynamicContent").html(html);
        },
        error: function(jqXHR, textStatus, errorThrown ){
            $(".alert").alert("close");
            $("#mainWrapper").prepend(_th.wrapAlert("<strong>ERROR: </strong> "+errorThrown, "danger"));
        }
    });
}

main.prototype.sendPost = function(data){
    var _th = this;
    $.ajax("index.php",{
        type : 'POST',
        data: data,
        success: function(data, textStatus, jqXHR ){
            data = $.parseJSON(data).response;
            var tmp = '';
            $(".alert").alert("close");
            $.each(data, function(name, value){
                $("#mainWrapper").prepend(_th.wrapAlert("<strong>"+name+": </strong> "+value, "info"));
            });
        },
        error: function(jqXHR, textStatus, errorThrown ){
            $(".alert").alert("close");
            $("#mainWrapper").prepend(_th.wrapAlert("<strong>ERROR: </strong> "+errorThrown, "danger"));
        }
    });
}

main.prototype.wrapAlert = function(text, type){
    var error = '<div class="alert alert-'+type+' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'+text+'</div>';;
    return error;
}

main.prototype.getTableData = function(tableName){
    this.tableName = tableName;
    $("#dynamicContent").removeClass("container");
    $("#dynamicContent").html("<iframe class='full-content' src='index.php?a=showContent&tableName="+tableName+"'></ifarame>");
    $("#dynamicContent").find("iframe").height($(window).height()-$(".navbar").outerHeight(true));
    $("#dynamicContent").find("iframe").load(function(){
        $($("#dynamicContent").find("iframe").contents()).find(".sorting").click(function(e){
            e.preventDefault();
            $.post("index.php",{a:"sorting",field:$(this).attr("data-field")})
            controller.getTableData(controller.tableName);
        });
        
        $($("#dynamicContent").find("iframe").contents()).find("input.xslt-filter").change(function(e){
            e.preventDefault();
            $.post("index.php",{a:"tests",field:$(this).attr("data-columnvalue"), test: $(this).val()});
            controller.getTableData(controller.tableName);
        });
    });
}