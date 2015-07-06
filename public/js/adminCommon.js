
function showMessage(object, message){
    if(message == null) message="";
    object.text(message);
}

function getEmptyResultHtml(){
    flushEmptyResultHtml();
    var html = '<div class="emptyMsg text-center" class="text-center">列表为空</div>';
    return html;
}
function flushEmptyResultHtml(){
    $(".emptyMsg").remove();
}

//一波分页搜索函数
function nextPage(){
    var allPages = parseInt($("#allPages").text());
    if(adminUserSelect["curPage"]+1 <= allPages){
        adminUserSelect["curPage"]++;
        changeTable();
        $("#curPage").text(adminUserSelect["curPage"]);
    }
}
function prevPage(){
    if(adminUserSelect["curPage"]-1 >= 1){
        adminUserSelect["curPage"]--;
        changeTable();
        $("#curPage").text(adminUserSelect["curPage"]);
    }
}
function changeState(){
    var state = parseInt($("#state-select").val());
    adminUserSelect["state"] = state;
    adminUserSelect["curPage"] = 1;
    adminUserSelect["pageCount"] = 10;
    changeTable();
    $("#curPage").text("1");
}
function searchTextChange(){
    var text = $("#searchText").val();
    adminUserSelect["searchText"] = text;
    adminUserSelect["curPage"] = 1;
    adminUserSelect["pageCount"] = 10;
    changeTable();
    $("#curPage").text("1");
}

function displayLoading(switcher){
    if(switcher==true){
        $("#loading").show();
    }else{
        $("#loading").hide();
    }
}

//操作checkbox函数
function checkAllBoxes(){
    var grouper = $("#check-all")[0].checked;
    $("tbody :checkbox").each(function(){
        $(this)[0].checked = grouper;
    });
}
//清空checkbox
function flushAllBoxes(checked){
    $('tbody :checkbox').each(function(){
        $(this)[0].checked = checked;
    });
}

function getSelectedIdArray(){
    var ids = new Array(0);
    $("tbody :checkbox").each(function(){
        if($(this)[0].checked == true)
            ids.push(($(this)[0].id).split("-")[1]);
    });
    return ids;
}

function getSellerLogoLink(){

}