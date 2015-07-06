/**
 * Created by james on 14-7-13.
 */
jQuery(function ($) {

    var g_menuActivedId = "userinfo";


    //个人信息
    var userinfoFormValidRules = {
        rules: {
            inputNickName: {
                required: true,
                minlength: 2,
                maxlength: 8,
                isValidateUsername: true
            },
            inputEmail: {
                required: true,
                email: true
            },
            inputPhone: {
                minlength: 6,
                maxlength: 16,
                number: true
            }
        },
        messages: {
            inputNickName: {
                required: "请输入用户名",
                minlength: "用户名不得少于6位",
                maxlength: "用户名不得长于16位",
                isValidateUsername: "用户名只能是字母、数字、_和中文组成"
            },
            inputEmail: {
                required: "请输入Email地址",
                email: "请输入正确的Email地址"
            },
            inputPhone: {
                minlength: "电话号码不能少于6位",
                maxlength: "电话号码不能长于16位",
                number: "电话号码只能是数字"
            }
        }
    };

    var passwordFormValidRules = {
        rules: {
            oripwd: {
                required: true,
                minlength: 6,
                maxlength: 16,
                isValidatePwd: true
            },
            newpwd: {
                required: true,
                minlength: 6,
                maxlength: 16,
                isValidatePwd: true
            },
            confirmPwd: {
                equalTo: "#newpwd"
            }
        },
        messages: {
            oripwd: {
                required: "请输入密码",
                minlength: "密码不得少于6位",
                maxlength: "密码不得长于16位",
                isValidatePwd: "密码只能是字母、数字、符号和中文组成"
            },
            newpwd: {
                required: "请输入密码",
                minlength: "密码不得少于6位",
                maxlength: "密码不得长于16位",
                isValidatePwd: "密码只能是字母、数字、符号和中文组成"
            },
            confirmPwd: {
                equalTo: "请输入正确的确认密码"
            }
        }
    }

    var addDeliveryAddressRules = {
        rules: {
            inputReceiverName: {
                required: true,
                minlength: 2,
                maxlength: 8,
                isValidateUsername: true
            },
            Domain:{
                required:true
            },
            Domain2:{
                required:true
            },
            inputStreetAddress:{
                required:true,
                maxlength:20
            },
            inputPhone:{
                required:true,
                number:true
            }
        },
        messages: {
            inputReceiverName: {
                required: "必须填写收货人姓名",
                minlength: "请填写完整的姓名",
                maxlength: "请填写正确的姓名",
                isValidateUsername: true
            },
            Domain:{
                required:"请选择区域"
            },
            Domain2:{
                required:"请填写地址"
            },
            inputStreetAddress:{
                required:"请填写具体位置",
                maxlength:"地址不能多于20个字符"

            },
            inputPhone:{
                required:"请填写你的联系电话",
                number:"电话只能是数字"
            }
        }
    };
    var solveFormFunc = function () {
        if (!$(this).valid()) {
            return false;
        }
        var nickname = $('#inputNickName').val();
        var email = $("#inputEmail").val();
        var phone = $("#inputPhone").val();
        var url = "userinfo/userinfo/updateuserinfo";
        var data = {
            nickName: nickname,
            email: email,
            phone: phone
        };
        $.post(url,
            data
            , function (data) {
                if (data.state) {
                    //TODO to enable redirect
                    alert(data.message.message)
                } else {
                    alert(data.message.message)
                }
            }, 'json');
        return false;
    }
    var updatePassword = function () {
        if (!$(this).valid()) {
            return false;
        }

        var oripwd = $("#oripwd").val();
        var newpwd = $('#newpwd').val();

        var url = '/user/userinfo/userinfo/updatePassword';
        var data = {
            oripwd: hex_md5(oripwd),
            newpwd: hex_md5(newpwd)
        }

        $.post(url,
            data
            , function (data) {
                if (data.state) {
                    //TODO to enable redirect
                    alert(data.message.message)
                } else {
                    alert(data.message.message)
                }
            }, 'json');
        return false;
    };
    var addDeliveryAddress = function(){
        var url = "userinfo/userinfo/addDeliveryAddress";
        var Name = $("#inputReceiverName").val();
        var Domain = $("#Domain").val();
        var Domain2 = $("#Domain2").val();
        var Domain3 = $("#Domain3").val();
        var Street = $("#inputStreetAddress").val();
        var phone = $("#inputPhone").val();

        var data ={
            Domain:Domain,
            Domain2:Domain2,
            Address:Street,
            Phone:phone,
            Name:Name
        };
        $.post(url,
            data
            , function (data) {
                if (data.state) {
//                    alert(data.message.message)
                    var body = $('#deliveryAddressTable').find('> tbody');
                    for(i=0;i<list.length;i++){
                        var row = getRowHtml(list[i]);
                        body.prepend(row);
                    }
                } else {
                    alert(data.message.message)

                    //TODO replace data
                }
            }, 'json');
        return false;
    }
    $('#userinfo').click(function () {
        var url = "userinfo/userinfo/userinfo";
        var data = {};
        $.ajax({
            type: "post",
            async: false,  //同步请求
            url: url,
            data: data,
            timeout: 1000,
            success: function (dates) {
                //alert(dates);
                replaceContent(dates, g_menuActivedId, "userinfo");

                var el = $("#userinfoForm");
                el.submit(solveFormFunc);
                el.validate(userinfoFormValidRules);
            },
            error: function () {
                alert("失败，请稍后再试！");
            }
        });
    });

    //修改密码
    $('#modify_password').click(function () {
        var url = "userinfo/userinfo/modifypwd";
        var data = {};
        $.ajax({
            type: "post",
            async: false,  //同步请求
            url: url,
            data: data,
            timeout: 1000,
            success: function (dates) {
                //alert(dates);
                replaceContent(dates, g_menuActivedId, "modify_password");
                var el = $("#passwordForm");
                el.submit(updatePassword);
                el.validate(passwordFormValidRules);
            },
            error: function () {
                alert("失败，请稍后再试！");
            }
        });
    });

    //收货地址
    $('#delivery_address').click(function () {
        var url = "userinfo/userinfo/delivery";
        var data = {type: 1};
        $.ajax({
            type: "post",
            async: false,  //同步请求
            url: url,
            data: data,
            timeout: 1000,
            success: function (dates) {
                //alert(dates);

                replaceContent(dates, g_menuActivedId, "delivery_address");
                initLocationList();
                initDeliveryAddressTable();
                var el = $("#addDeliveryAddress");
                el.submit(addDeliveryAddress);
                el.validate(addDeliveryAddressRules);
            },
            error: function () {
                alert("失败，请稍后再试！");
            }
        });
    });

    function initDeliveryAddressTable() {
        var url = "userinfo/userinfo/getDeliveryAddress";
        var data = {};
        $.post(url,
            data
            , function (data) {
                if (data.state) {

//                    alert(data.message.message)
                    var list = data.message.list;
                    var body = $('#deliveryAddressTable').find('> tbody');
                    for(i=0;i<list.length;i++){
                        var row = getRowHtml(list[i]);
                        body.prepend(row);
                    }
                    //TODO check the result

                } else {
                    alert(data.message.message)

                }
            }, 'json');
    }
    function initLocationList(){
        var url = "userinfo/userinfo/getDeliveryLocation";
        var data = {};
        $.post(url,
            data
            , function (data) {
                if (data.state) {
                    var list = data.message.list;
                    var select = $('#Domain');
                    for(i=0;i<list.length;i++){
                        var option =  getOptionHtml(list[i]);
                        select.prepend(option);
                    }

//                    alert(data.message.message)
                } else {
                    alert(data.message.message)

                }
            }, 'json');
    }
    function getRowHtml(data){
        var row = "<tr id='" +data["ID"]+
            "'>" +
            //TODO combine the domains
            "<td>"+data["Name"]+"</td>" +
            "<td>"+data["Domain"]+"</td>" +
            "<td>"+data["Address"]+"</td>" +
            "<td>"+data["Phone"]+"</td>" +
            "<td><a onclick='removeAddress(" +data["ID"]+
            ")'>删除</a></td>" +
            "</tr>";
        return row;
    }

    function getOptionHtml(data){
        var option = "<option>" +data+
            "</option>";
        return option;
    }

    function init() {
        var url = "userinfo/userinfo/userinfo";
        var data = {};
        $.ajax({
            type: "post",
            async: false,  //同步请求
            url: url,
            data: data,
            timeout: 1000,
            success: function (dates) {
                //alert(dates);
                replaceContent(dates, g_menuActivedId, "userinfo");
                var el = $("#userinfoForm");
                el.submit(solveFormFunc);
                el.validate(userinfoFormValidRules);
            },
            error: function () {
                alert("失败，请稍后再试！");
            }
        });
    }

    init();
    function replaceContent(dates, menuActivedId, nextActiveId) {

        $("#mainContent").html(dates);//要刷新的div
        $("#" + menuActivedId).removeClass("active");
        $('#' + nextActiveId).addClass("active");
        g_menuActivedId = nextActiveId;
    }


});

function removeAddress(id){
    var url = "userinfo/userinfo/removeAddress";
    var data = {id:id};
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                alert(data.message.message)
                $('#deliveryAddressTable').find('> tbody').find('> #'+id).remove();
            } else {
                alert(data.message.message)

                //TODO replace data
            }
        }, 'json');
}
$().ready(function () {
    jQuery.validator.addMethod("isValidatePwd", function (value, element) {
        if (value == "") {
            return true;
        }
        return this.optional(element) || checkUserPwd(value);
    }, "密码只能是英文、数字和符号组成");

    jQuery.validator.addMethod("isValidateUsername", function (value, element) {
        return this.optional(element) || checkRealName(value);
    }, "用户名只能是中文、英文、数字和_组成");

});