/**
 * Created with JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-2
 * Time: 上午10:00
 * To change this template use File | Settings | File Templates.
 */

function checkAddUser(username, password, repassword, email){
    var msgObj = $("#reg-message");
    if(username=="" || password=="" || repassword=="" || email==""){
        showMessage(msgObj,"* 输入不能为空");
        return false;
    }
    if(!checkUsername(username)){
        showMessage(msgObj,"* 用户名格式错误");
        return false;
    }
    if(!checkUserPwd(password)){
        showMessage(msgObj,"* 密码格式错误");
        return false;
    }
    if(password != repassword){
        showMessage(msgObj,"* 输入的密码不一致");
        return false;
    }
    if(!checkUserEmail(email) || email.length>45 ){
        showMessage(msgObj,"* 邮箱格式错误");
        return false;
    }
    showMessage(msgObj,null);
    return true;
}
function addUser()
{
    //get input data
    var un = $("#reg-username").val();
    var pw = $("#reg-password").val();
    var reps = $("#reg-repassword").val();
    var em = $("#reg-email").val();

    if(!checkAddUser(un,pw,reps,em)) return;
    //post
    $.post( "user/user/add", {
        "username":un,
        "password":hex_md5(pw),
        "email":em
    }, function( data ) {
        if(data.state == true){
            var row = getRowHtml({
                "id":data.id,
                "username":un,
                "nickname":un,
                "email":em,
                "registerTime":data.regTime,
                "state":1
            });
            $('tbody').prepend(row);

            showMessage($("#reg-message"),"");
            flushFormValues();
            //dismiss modal
            $("#addUser").modal("hide");
        }else{
            //print error message
            showMessage($("#reg-message"),data.message);
        }
    },"json");
}

function getRowHtml(user){
    var row = "<tr id='tr-"+ user["id"] +"'> <td><input id='check-"+ user["id"] +"' type='checkbox'/></td>" +
        "<td>"+ user["username"] +"</td> <td>"+ user["nickname"] +"</td> <td>"+ user["email"] +"</td> " +
        " <td>" + user["registerTime"] + "</td> <td>" + getStateByNum(user["state"].toString()) + "</td> <td> " +
        "<a href=\"#\" onclick='viewUser("+ user["id"] +")'>查看</a> " +
        "<a href=\"#\" onclick='editUserOpen("+user["id"] +")'>编辑</a> </td> </tr>";
    return row;
}

function viewUser(userid)
{
    displayLoading(true);
    $.post("user/user/view", { "id" : userid } ,function(data){
        if(data.state == true){
            var user = data.user;
            $("#view-username").text(user.username);
            $("#view-nickname").text(user.nickname);
            $("#view-email").text(user.email);
            $("#view-phone").text(user.phone);
            $("#view-registerTime").text(user.registerTime);
            $("#view-loginIP").text(user.loginIP);
            $("#view-loginTime").text(user.loginTime);
            $("#view-state").text(getStateByNum(user.state));
            $("#viewUser").modal("show");
        }else{
            //print error message
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function getStateByNum(num){
    num = num.toString();
    var state = "";
    switch (num){
        case "1" : state = "正常"; break;
        case "2" : state = "未激活"; break;
        case "9" : state = "禁用"; break;
        default  : state = "未知";break;
    }
    return state;
}

function editUserOpen(userid){
    flushFormValues();
    displayLoading(true);
    $.post("user/user/view", { "id" : userid } ,function(data){
        if(data.state == true){
            var user = data.user;
            $("#edit-id").val(user.id);
            $("#edit-username").val(user.username);
            $("#edit-nickname").val(user.nickname);
            $("#edit-email").val(user.email);
            $("#edit-phone").val(user.phone);
            $("#edit-state").val(user.state);

            $("#editUser").modal("show");
        }else{
            //print error message
        }
    },"json").always(function(){
            displayLoading(false);
        });

}

function checkEditUser(
    password,
    repassword,
    nickname,
    email,
    phone,
    state){
    var msgObj = $("#edit-message");
    if(email=="" || nickname==""){
        showMessage(msgObj,"* Email或昵称不能为空");
        return false;
    }
    if(password!=repassword){
        showMessage(msgObj,"* 输入的密码不一致");
        return false;
    }
    if(password!="" && !checkUserPwd(password)){
        showMessage(msgObj,"* 密码格式错误");
        return false;
    }
    if(!checkUsername(nickname)){
        showMessage(msgObj,"* 昵称格式错误");
        return false;
    }
    if(!checkUserEmail(email)){
        showMessage(msgObj,"* Email格式错误");
        return false;
    }
    if(!checkUserPhone(phone)){
        showMessage(msgObj,"* 联系电话格式错误");
        return false;
    }
    showMessage(msgObj,"");
    return true;
}
function editUser(){
    var id = $("#edit-id").val();
    var password = $("#edit-password").val();
    var repassword = $("#edit-repassword").val();
    var nickname = $("#edit-nickname").val();
    var email = $("#edit-email").val();
    var phone = $("#edit-phone").val();
    var state = $("#edit-state").val();
    //check
    if(!checkEditUser(password,repassword,nickname,email,phone,state)){
        return;
    }
    var aimTr = {
        "id":id,
        "username": $("#edit-username").val(),
        "nickname":nickname,
        "email":email,
        "registerTime":$("#tr-"+id+" :nth-child(5)").text(),
        "state":state
    };
    $.post("user/user/edit", {
        "id": id ,
        "password":password==""?"":hex_md5(password) ,
        "nickname":nickname ,
        "email":email,
        "phone":phone,
        "state":state
    },function(data){
        if(data.state == true){
            $("#tr-"+id).replaceWith(getRowHtml(aimTr));
            $("#editUser").modal("hide");
            showMessage($("#edit-message"),"");
            flushFormValues();
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
    $.post("user/user/search",adminUserSelect,function(data){
        if(data.state){
            if(data.count > 0){
                $("#userTable").children().remove();
                var newTable = "";
                var userTable = data.userTable;
                for(var k = 0; k<userTable.length; k++){
                    newTable += getRowHtml(userTable[k]);
                }
                $("#userTable").prepend(newTable);
                var pages = Math.ceil(data.count/10);
                $("#allPages").text(pages.toString());
                flushEmptyResultHtml();
            }else{
                $("#userTable").children().remove();
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
    $("#reg-email").val("");
    $("#reg-message").text("");
    //edit Form
    $("#edit-password").val("");
    $("#edit-repassword").val("");
    $("#edit-message").text("");
}




