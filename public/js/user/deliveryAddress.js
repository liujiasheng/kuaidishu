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


var addDeliveryAddress = function(){

    if (!$(this).valid()) {
        return false;
    }
    var url = "/user/userinfo/userinfo/addDeliveryAddress";
    var Name = $("#inputReceiverName").val();
    var Domain = $("#Domain").val();
    var Domain2 = $("#Domain2").val();
    var Domain3 = $("#Domain3").val();
    var Street = $("#inputStreetAddress").val();
    var phone = $("#inputPhone").val();

    var    inputdata = {
        Domain:Domain,
        Domain2:Domain2,
        Domain3:Domain3,
        Address:Street,
        Phone:phone,
        Name:Name
    };
    $.post(url,
        inputdata
        , function (data) {
            if (data.state) {
//                    alert(data.message.message)
                var body = $('#deliveryAddressTable').find('> tbody');

                initDeliveryAddressTable();
                $('#deliverAddressModal').modal('hide')
                $("#addDeliveryAddress")[0].reset();
            } else {
                alert(data.message.message);

                //TODO replace data
            }
        }, 'json');
    return false;
};

function initDeliveryAddressTable() {
    var url = "/user/userinfo/userinfo/getDeliveryAddress";
    var data = {};
    $.post(url,
        data
        , function (data) {
            if (data.state) {

//                    alert(data.message.message)
                var list = data.message.list;
                var body = $('#deliveryAddressTable').find('> tbody');
                body.children().remove();
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
function getDomainList(){
    var url = "/user/userinfo/userinfo/getDeliveryLocation";
    var data = {};
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#Domain');
                var option =  getOptionHtml("请选择");
                select.prepend(option);
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

function getDomain2List(){
    var url = "/user/userinfo/userinfo/getDomain2List";


    var selected = $('#Domain').val();
    var data = {
        domain:selected
    };
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#Domain2');
                select.children().remove();
                var option =  getOptionHtml('请选择');
                select.prepend(option);
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

function getDomain3List(){
    var url = "/user/userinfo/userinfo/getDomain3List";


    var domain = $('#Domain').val();
    var domain2 = $('#Domain2').val();
    var data = {
        domain:domain,
        domain2:domain2
    };
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#Domain3');
                select.children().remove();
                var option =  getOptionHtml('请选择');
                select.prepend(option);
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
        "<td>"+data["Domain"]+data["Domain2"]+data["Domain3"]+"</td>" +
        "<td>"+data["Address"]+"</td>" +
        "<td>"+data["Phone"]+"</td>" +
        "<td><a href='#' onclick='removeAddress(" +data["ID"]+
        ")'>删除</a></td>" +
        "</tr>";
    return row;
}

function getOptionHtml(data){
    var option = "<option>" +data+
        "</option>";
    return option;
}

function removeAddress(id){
    var url = "/user/userinfo/userinfo/removeAddress";
    var data = {id:id};
    $.post(url,
        data
        , function (data) {
            if (data.state) {
//                alert(data.message.message)
                $('#deliveryAddressTable').find('> tbody').find('> #'+id).remove();
            } else {
                alert(data.message.message)

                //TODO replace data
            }
        }, 'json');
}

$().ready(function(){
    getDomainList();
    initDeliveryAddressTable();

    var select = $('#Domain2');
    select.children().remove();
    var option =  getOptionHtml('请选择');
    select.prepend(option);
    var select = $('#Domain3');
    select.children().remove();
    var option =  getOptionHtml('请选择');
    select.prepend(option);
    $('#Domain').on('change',getDomain2List);
    $('#Domain2').on('change',getDomain3List);
    var el = $("#addDeliveryAddress");
    el.submit(addDeliveryAddress);
    el.validate(addDeliveryAddressRules);
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
