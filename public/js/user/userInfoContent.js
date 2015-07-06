//个人信息
var userinfoFormValidRules = {
    rules: {
        inputNickName: {
            required: true,
            minlength: 2,
            maxlength: 16,
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
            minlength: "用户名不得少于2位",
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

            var el = $("#userinfoForm");
            el.submit(solveFormFunc);
            el.validate(userinfoFormValidRules);

$().ready(function () {
    jQuery.validator.addMethod("isValidatePwd", function (value, element) {
        if (value == "") {
            return true;
        }
        return this.optional(element) || checkUserPwd(value);
    }, "密码只能是英文、数字和符号组成");

    jQuery.validator.addMethod("isValidateUsername", function (value, element) {
        return this.optional(element) || checkNickName(value);
    }, "昵称只能是中文、英文、数字和_组成");

});