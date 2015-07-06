
function checkAddWorker(
    username,
    password,
    repassword,
    name,
    sex,
    certNumber,
    phone,
    selfPhone
    ){
    var msgObj = $("#reg-message");
    if(!checkWorkerUsername(username)){
        showMessage(msgObj, "* 工号格式错误");
        return false;
    }
    if(!checkUserPwd(password)){
        showMessage(msgObj, "* 密码格式错误");
        return false;
    }
    if(password != repassword){
        showMessage(msgObj, "* 输入的密码不一致");
        return false;
    }
    if(!checkRealName(name)){
        showMessage(msgObj, "* 姓名格式错误");
        return false;
    }
    if(sex!="男" && sex!="女"){
        showMessage(msgObj, "* 性别格式错误");
        return false;
    }
    if(!checkCertNumber(certNumber)){
        showMessage(msgObj, "* 证件号格式错误");
        return false;
    }
    if(!checkUserPhone(phone)){
        showMessage(msgObj, "* 联系电话格式错误");
        return false;
    }
    if(selfPhone!="" && !checkUserPhone(selfPhone)){
        showMessage(msgObj, "* 个人电话格式错误");
        return false;
    }

    showMessage(msgObj,"");
    return true;
}
function addWorker(){
    //get front data
    var username = $("#reg-username").val();
    var password = $("#reg-password").val();
    var repassword = $("#reg-repassword").val();
    var name = $("#reg-name").val();
    var sex = "";
    if($("input[name='reg-sex']")[0].checked) sex = "男";
    if($("input[name='reg-sex']")[1].checked) sex = "女";
    var certNumber = $("#reg-certNumber").val();
    var phone = $("#reg-phone").val();
    var selfPhone = $("#reg-selfPhone").val();

    if(!checkAddWorker(
        username,
        password,
        repassword,
        name,
        sex,
        certNumber,
        phone,
        selfPhone)) return;

    //post
    $.post("worker/worker/add",{
        "username" : username,
        "password" : hex_md5(password),
        "name" : name,
        "sex" :sex,
        "certNumber" : certNumber,
        "phone" : phone,
        "selfPhone" : selfPhone
    },function(data){
        if(data.state){
            var row = getWorkerRowHtml({
                "id" : data.id,
                "username" : username,
                "name" : name,
                "sex" : sex,
                "certNumber" : certNumber,
                "phone" : phone,
                "state" : 1
            });
            $("tbody").prepend(row);
            showMessage($("#reg-message"),"");
            $("#addWorker").modal("hide");
        }else{
            showMessage($("#reg-message"),data.message);
        }
    },"json");

}

function getWorkerRowHtml(worker){
    var row = '<tr id="tr-' + worker["id"] + '">'+
        '<td><input id="check-' + worker["id"] + '" type="checkbox"></td>' +
        '<td>' + worker["username"] + '</td>' +
        '<td>' + worker["name"] + '</td>' +
        '<td>' + worker["sex"] + '</td>' +
        '<td>' + worker["certNumber"] + '</td>' +
        '<td>' + worker["phone"] + '</td>' +
        '<td>' + getWorkerStateByNum(worker["state"]) + '</td>' +
        '<td><a href="#" onclick="viewWorker(' + worker["id"] + ')">查看</a> ' +
        '<a href="#" onclick="editWorkerOpen(' + worker["id"] + ')">编辑</a></td></tr>';
    return row;
}
function getWorkerStateByNum(num){
    num = num.toString();
    var state = "";
    switch (num){
        case "1" : state = "正常"; break;
        case "8" : state = "解约"; break;
        case "9" : state = "禁用"; break;
        default  : state = "未知";break;
    }
    return state;
}

function viewWorker(id){
    displayLoading(true);
    $.post("worker/worker/view",{"id":id},function(data){
        if(data.state){
            var worker = data.worker;
            $("#view-username").text(worker.username);
            $("#view-name").text(worker.name);
            $("#view-sex").text(worker.sex);
            $("#view-certNumber").text(worker.certNumber);
            $("#view-phone").text(worker.phone);
            $("#view-selfPhone").text(worker.selfPhone);
            $("#view-loginTime").text(worker.loginTime);
            $("#view-loginIP").text(worker.loginIP);
            $("#view-state").text(getStateByNum(worker.state));
            $("#viewWorker").modal("show");
        }else{
            // print error message
        }
    },"json").always(function(){
        displayLoading(false);
    });
}

function getStateByNum(num){
    num = num.toString();
    var state = "";
    switch (num){
        case "1": state = "正常";break;
        case "8": state = "解约";break;
        case "9": state = "禁用";break;
        default : state = "未知";break;
    }
    return state;
}

function editWorkerOpen(workerId){
    flushFormValues();
    displayLoading(true);
    $.post("worker/worker/view", {"id":workerId}, function(data){
        if(data.state){
            var worker = data.worker;
            $("#edit-id").val(worker.id);
            $("#edit-username").val(worker.username);
            $("#edit-name").val(worker.name);
            if(worker.sex == "男") $("input[name='edit-sex']")[0].checked = true;
            if(worker.sex == "女") $("input[name='edit-sex']")[1].checked = true;
            $("#edit-certNumber").val(worker.certNumber);
            $("#edit-phone").val(worker.phone);
            $("#edit-selfPhone").val(worker.selfPhone);
            $("#edit-state").val(worker.state);
            $("#editWorker").modal("show");
        }
    },"json").always(function(){
            displayLoading(false);
    });
}

function checkEditWorker(
    id,
    password,
    repassword,
    name,
    sex,
    certNumber,
    phone,
    selfPhone,
    state
    ){
    var msgObj = $("#edit-message");
    if(password!=repassword){
        showMessage(msgObj,"* 输入的密码不一致");
        return false;
    }
    if(password!="" && !checkUserPwd(password)){
        showMessage(msgObj,"* 密码格式错误");
        return false;
    }
    if(!checkRealName(name)){
        showMessage(msgObj,"* 姓名格式错误");
        return false;
    }
    if(sex!="男" && sex!="女"){
        showMessage(msgObj,"* 性别格式错误");
        return false;
    }
    if(!checkCertNumber(certNumber)){
        showMessage(msgObj,"* 证件号格式错误");
        return false;
    }
    if(!checkUserPhone(phone)){
        showMessage(msgObj,"* 联系电话格式错误");
        return false;
    }
    if(selfPhone!="" && !checkUserPhone(selfPhone)){
        showMessage(msgObj,"* 个人电话格式错误");
        return false;
    }
    showMessage(msgObj,"");
    return true;
}

function editWorker(){

    var id = $("#edit-id").val();
    var username = $("#edit-username").val();
    var password = $("#edit-password").val();
    var repassword = $("#edit-repassword").val();
    var name = $("#edit-name").val();
    var sex = "";
    if($("input[name='edit-sex']")[0].checked) sex = "男";
    if($("input[name='edit-sex']")[1].checked) sex = "女";
    var certNumber = $("#edit-certNumber").val();
    var phone = $("#edit-phone").val();
    var selfPhone = $("#edit-selfPhone").val();
    var state = $("#edit-state").val();

    //chcek worker
    if(!checkEditWorker(
        id,
        password,
        repassword,
        name,
        sex,
        certNumber,
        phone,
        selfPhone,
        state
    )) return;

    var aimWorker = {
        "id" : id,
        "username" : username,
        "name" : name,
        "sex" : sex,
        "certNumber" :certNumber,
        "phone" : phone,
        "state" : state
    };

    $.post("worker/worker/edit",{
        "id" : id,
        "username" : username,
        "password" : password==""?"":hex_md5(password),
        "name" : name,
        "sex" : sex,
        "certNumber" : certNumber,
        "phone" : phone,
        "selfPhone" : selfPhone,
        "state" : state
    },function(data){
        if(data.state){
            $("#tr-"+id).replaceWith(getWorkerRowHtml(aimWorker));
            $("#editWorker").modal("hide");
            showMessage($("#edit-message"),"");
        }else{
            showMessage($("#edit-message"),data.message);
        }
    },"json");


}

var adminUserSelect = {
    "state" : 0,
    "searchText" : "",
    "curPage" : 1,
    "pageCount" : 10
};
function changeTable(){
    displayLoading(true);
    $.post("worker/worker/search",adminUserSelect,function(data){
        if(data.state){
            if(data.count > 0){
                $("#workerTable").children().remove();
                var newTable = "";
                var userTable = data.workerTable;
                for(var k = 0; k<userTable.length; k++){
                    newTable += getWorkerRowHtml(userTable[k]);
                }
                $("#workerTable").prepend(newTable);
                var pages = Math.ceil(data.count/10);
                $("#allPages").text(pages.toString());
                flushEmptyResultHtml();
            }else{
                $("#workerTable").children().remove();
                $("#allPages").text("1");
                $("table").after(getEmptyResultHtml());
            }
        }else{

        }
    },"json").always(function(){
            displayLoading(false);
        });
}





function flushFormValues(){
    //reg Form
    $("#reg-username").val("");
    $("#reg-password").val("");
    $("#reg-repassword").val("");
    $("#reg-name").val("");
    $("#reg-certNumber").val("");
    $("#reg-phone").val("");
    $("#reg-selfPhone").val("");
    $("#reg-message").val("");

    //edit Form
    $("#edit-password").val("");
    $("#edit-repassword").val("");
    $("#edit-message").val("");
}