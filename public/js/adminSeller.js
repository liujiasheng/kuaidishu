/**
 * Created by Administrator on 14-7-8.
 */
function checkAddSeller(
    username,
    password,
    repassword,
    name,
    logo,
    address,
    email,
    phone,
    contactPhone,
    comment
    ){
    var msgObj = $("#reg-message");
    if(!checkUsername(username)){
        showMessage(msgObj, "* 用户名格式错误");
        return false;
    }
    if(password!==repassword){
        showMessage(msgObj, "* 输入的密码不一致");
        return false;
    }
    if(!checkUserPwd(password)){
        showMessage(msgObj, "* 密码格式错误");
        return false;
    }
    if(!checkSellerName(name)){
        showMessage(msgObj, "* 商家名称格式错误");
        return false;
    }
    if(!checkSellerComment(comment)){
        showMessage(msgObj, "* 商家介绍格式错误");
        return false;
    }
    if(logo==null){
        showMessage(msgObj, "* 图片不能为空");
        return false;
    }
    if(logo["size"]>1024*1024*8){
        showMessage(msgObj, "* 图片最大为8M");
        return false;
    }
    if(!checkAddress(address)){
        showMessage(msgObj, "* 商家地址格式错误");
        return false;
    }
    if(!checkUserEmail(email)){
        showMessage(msgObj, "* Email格式错误");
        return false;
    }
    if(!checkUserPhone(phone)){
        showMessage(msgObj, "* 商家电话格式错误");
        return false;
    }
    if(!checkUserPhone(contactPhone)){
        showMessage(msgObj, "* 联系人电话格式错误");
        return false;
    }
    showMessage(msgObj, "");
    return true;
}

function checkEditSeller(
    password,
    repassword,
    name,
    logo,
    address,
    email,
    phone,
    contactPhone,
    comment
    ){
    var msgObj = $("#edit-message");
    if(password!==repassword){
        showMessage(msgObj, "* 输入的密码不一致");
        return false;
    }
    if(password!="" && !checkUserPwd(password)){
        showMessage(msgObj, "* 密码格式错误");
        return false;
    }
    if(!checkSellerName(name)){
        showMessage(msgObj, "* 商家名称格式错误");
        return false;
    }
    if(!checkSellerComment(comment)){
        showMessage(msgObj, "* 商家简介格式错误");
        return false;
    }
    if(logo!=null && logo["size"]>1024*1024*8){
        showMessage(msgObj, "* 图片最大为8M");
        return false;
    }
    if(!checkAddress(address)){
        showMessage(msgObj, "* 商家地址格式错误");
        return false;
    }
    if(!checkUserEmail(email)){
        showMessage(msgObj, "* Email格式错误");
        return false;
    }
    if(!checkUserPhone(phone)){
        showMessage(msgObj, "* 商家电话格式错误");
        return false;
    }
    if(!checkUserPhone(contactPhone)){
        showMessage(msgObj, "* 联系人电话格式错误");
        return false;
    }
    showMessage(msgObj, "");
    return true;
}

function addSeller(){
    //get front data
    var username = $("#reg-username").val();
    var password = $("#reg-password").val();
    var repassword = $("#reg-repassword").val();
    var name = $("#reg-name").val();
    var logo = $("#reg-logo")[0].files[0];
    var address = $("#reg-address").val();
    var email = $("#reg-email").val();
    var phone = $("#reg-phone").val();
    var contactPhone = $("#reg-contactPhone").val();
    var comment = $("#reg-comment").val();
    if(!checkAddSeller(
        username,
        password,
        repassword,
        name,
        logo,
        address,
        email,
        phone,
        contactPhone,
        comment
    )) return;

    var pd = {
        "username":username,
        "password":hex_md5(password),
        "name":name,
        "address":address,
        "email":email,
        "phone":phone,
        "contactPhone":contactPhone
    };

    var fd = new FormData();
    fd.append("username",username);
    fd.append("password",hex_md5(password));
    fd.append("name",name);
    fd.append("address",address);
    fd.append("email",email);
    fd.append("phone",phone);
    fd.append("contactPhone",contactPhone);
    fd.append("logo",logo);
    fd.append("comment",comment);
    //post
    $.ajax({
        url: 'seller/seller/add',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: "json",
        success: function(data){
            if(data.state){
                var row = getSellerRowHtml({
                    "id":data.id,
                    "username":username,
                    "name":name,
                    "phone":phone,
                    "contactPhone":contactPhone,
                    "state":1
                });
                $("tbody").prepend(row);
                showMessage($("#reg-message"),"");
                $("#addSeller").modal("hide");
            }else{
                showMessage($("#reg-message"),data.message);
            }
        }
    });
}

function getSellerRowHtml(seller){
    return '<tr id="tr-' + seller["id"] + '">'+
        '<td><input id="check-' + seller["id"] + '" type="checkbox"></td>' +
        '<td>' + seller["username"] + '</td>' +
        '<td>' + seller["name"] + '</td>' +
        '<td>' + seller["phone"] + '</td>' +
        '<td>' + seller["contactPhone"] + '</td>' +
        '<td>' + getSellerStateByNum(seller["state"]) + '</td>' +
        '<td><a href="#" onclick="viewSeller(' + seller["id"] + ')">查看</a> ' +
        '<a href="#" onclick="editSellerOpen(' + seller["id"] + ')">编辑</a></td></tr>';
}

function getSellerStateByNum(num){
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

function getLogoAddress(filename){
    if(filename=="") return  "http://static.kuaidishu.com/sellerlogo/logo.jpg";
    return "http://static.kuaidishu.com/sellerlogo/" + filename;
}

function viewSeller(id){
    displayLoading(true);
    $.post("seller/seller/view",{"id":id},function(data){
        if(data.state){
            var seller = data.seller;
            $("#view-username").text(seller.username);
            $("#view-name").text(seller.name);
            $("#view-comment").text(seller.comment);
            $("#view-address").text(seller.address);
            $("#view-phone").text(seller.phone);
            $("#view-contactPhone").text(seller.contactPhone);
            $("#view-email").text(seller.email);
            $("#view-loginTime").text(seller.loginTime);
            $("#view-loginIP").text(seller.loginIP);
            $("#view-logo").attr("src", getLogoAddress(seller.logo));
            $("#view-state").text(getStateByNum(seller.state));
            $("#viewSeller").modal("show");
        }else{
            // print error message
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function editSellerOpen(sellerId){
    flushFormValues();
    displayLoading(true);
    $.post("seller/seller/view", {"id":sellerId}, function(data){
        if(data.state){
            var seller = data.seller;
            $("#edit-id").val(sellerId);
            $("#edit-username").val(seller.username);
            $("#edit-name").val(seller.name);
            $("#edit-comment").val(seller.comment);
//            $("#edit-logo").val("");
            $("#edit-img").attr("src",getLogoAddress(seller.logo));
            $("#edit-address").val(seller.address);
            $("#edit-email").val(seller.email);
            $("#edit-phone").val(seller.phone);
            $("#edit-contactPhone").val(seller.contactPhone);
            $("#edit-state").val(seller.state);

            $("#editSeller").modal("show");
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function editSeller(){
    //get front data
    var id = $("#edit-id").val();
    var username = $("#edit-username").val();
    var password = $("#edit-password").val();
    var repassword = $("#edit-repassword").val();
    var name = $("#edit-name").val();
    var comment = $("#edit-comment").val();
    var logo = $("#edit-logo")[0].files[0];
    var address = $("#edit-address").val();
    var email = $("#edit-email").val();
    var phone = $("#edit-phone").val();
    var contactPhone = $("#edit-contactPhone").val();
    var state = $("#edit-state").val();
    if(!checkEditSeller(
        password,
        repassword,
        name,
        logo,
        address,
        email,
        phone,
        contactPhone,
        comment
    )) return;

    var aimSeller = {
        "id":id,
        "username":username,
        "name":name,
        "phone":phone,
        "contactPhone":contactPhone,
        "state":state
    };
    var fd = new FormData();
    fd.append("id",id);
    fd.append("password",password==""?"":hex_md5(password));
    fd.append("name",name);
    fd.append("comment",comment);
    fd.append("logo",logo);
    fd.append("address",address);
    fd.append("email",email);
    fd.append("phone",phone);
    fd.append("contactPhone",contactPhone);
    fd.append("state",state);
    //post
    $.ajax({
        url: 'seller/seller/edit',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: "json",
        success: function(data){
            if(data.state){
                $("#tr-"+id).replaceWith(getSellerRowHtml(aimSeller));
                $("#editSeller").modal("hide");
            }else{
                showMessage($("#edit-message"),data.message);
            }
        }
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

function flushFormValues(){
    $("#reg-username").val("");
    $("#reg-password").val("");
    $("#reg-repassword").val("");
    $("#reg-name").val("");
    $("#reg-comment").val("");
    $("#reg-logo").val("");
    $("#reg-address").val("");
    $("#reg-email").val("");
    $("#reg-phone").val("");
    $("#reg-contactPhone").val("");
    $("#reg-showImg").hide();
    $("#reg-message").text("");

    $("#edit-password").val("");
    $("#edit-repassword").val("");
    $("#edit-logo").val("");
    $("#edit-message").text("");
}



var adminUserSelect = {
    "state" : 0,
    "searchText" : "",
    "curPage" : 1,
    "pageCount" : 10
};
function changeTable(){
    displayLoading(true);
    $.post("seller/seller/search",adminUserSelect,function(data){
        if(data.state){
            if(data.count > 0){
                $("#sellerTable").children().remove();
                var newTable = "";
                var sellerTable = data.sellerTable;
                for(var k = 0; k<sellerTable.length; k++){
                    newTable += getSellerRowHtml(sellerTable[k]);
                }
                $("#sellerTable").prepend(newTable);
                var pages = Math.ceil(data.count/10);
                $("#allPages").text(pages.toString());
                flushEmptyResultHtml();
            }else{
                $("#sellerTable").children().remove();
                $("#allPages").text("1");
                $("table").after(getEmptyResultHtml());
            }
        }else{

        }
    },"json").always(function(){
            displayLoading(false);
        });
}


function test(){

}


function logoChanged(input, imgObj){
    if (input.files && input.files[0])
    {
        var reader = new FileReader();
        reader.onload = function (e)
        {
            imgObj.parent().parent().show();
            imgObj.attr("src", e.target.result,50);
        };
        reader.readAsDataURL(input.files[0]);
    }
}






