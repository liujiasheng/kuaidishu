/**
 * Created with JetBrains PhpStorm.
 * User: james
 * Date: 14-7-2
 * Time: 下午9:02
 * To change this template use File | Settings | File Templates.
 */
function checkUserPwd(value){


    // match only numbers
    var numberRegex = /^[a-zA-Z0-9`~!@#$%^&*()_+<>?:"|=,./;'\\\{\}\-\[\]]{6,16}$/;

    // if the phone number doesn't match the regex
    return numberRegex.test(value);
};
function checkNickName(value){
    //字母数字中文下划线
    var usernameRegex = /^[a-zA-Z0-9_\u4e00-\u9fa5]{2,16}$/;
    return usernameRegex.test(value);
}
function checkUsername(value){
    //字母数字中文下划线
    var usernameRegex = /^[a-zA-Z0-9_\u4e00-\u9fa5]{6,16}$/;
    return usernameRegex.test(value);
}

function checkUserEmail(value){
    //email
    var emailRegex = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    return emailRegex.test(value);
}

function checkUserPhone(value){
    //phone
    var phoneRegex = /^[0-9]{6,16}$/;
    return phoneRegex.test(value);
}

function checkWorkerUsername(value){
    //工号 字母数字
    var workerNumRegex = /^[a-zA-Z0-9]{6,16}$/;
    return workerNumRegex.test(value);
}

function checkRealName(value){
    //真实姓名  中文
    var nameRegex = /^[\u4e00-\u9fa5]{2,8}$/;
    return nameRegex.test(value);
}

function checkCertNumber(value){
    //证件号 6-18数字
    var certRegex = /^[0-9]{6,18}$/;
    return certRegex.test(value);
}

function checkSellerName(value){
    var regex =  /^[a-zA-Z0-9_\u0000-\uffff]{2,32}$/;
    return regex.test(value);
}

function checkAddress(value){
    var regex =  /^[a-zA-Z0-9_\u4e00-\u9fa5]{6,32}$/;
    return regex.test(value);
}

function checkGoodsName(value){
    var regex =  /^[a-zA-Z0-9_\u0000-\uffff]{1,32}$/;
    return regex.test(value);
}

function checkSellerComment(value){
    var regex =  /^[a-zA-Z0-9_\u4e00-\u9fa5\u0000-\uffff]{1,120}$/;
    return regex.test(value);
}

function checkRejectReason(value){
    var regex =  /^[a-zA-Z0-9_\u4e00-\u9fa5\u0000-\uffff]{1,120}$/;
    return regex.test(value);
}

function checkGoodsStandard(value){
    var regex =  /^[a-zA-Z0-9\u4e00-\u9fa5\u0000-\uffff]{1,12}$/;
    return regex.test(value);
}
