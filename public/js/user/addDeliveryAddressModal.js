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
                var trHtml = getRowHtml({
                    ID:data.id,
                    Name: Name,
                    Domain:Domain,
                    Domain2:Domain2,
                    Domain3:Domain3,
                    Address:Street,
                    Phone : phone
                });
                $(trHtml).insertBefore( $('#address-0') );
//                $('#address-'+data.id).children().first().next().children().first()[0].checked=true;
                $('#sendtothis-'+data.id).click();
                $('#deliverAddressModal').modal('hide');
                $("#addDeliveryAddress")[0].reset();
            } else {
                alert(data.message.message);

                //TODO replace data
            }
        }, 'json');
    return false;
};

function getDomainList(callback){
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
                if(callback) callback();
//                    alert(data.message.message)
            } else {
                alert(data.message.message)

            }
        }, 'json');
}

function getDomain2List(callback){
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
                if(callback) callback();
//                    alert(data.message.message)
            } else {
                alert(data.message.message)

            }
        }, 'json');
}

function getDomain3List(callback){
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
                if(callback) callback();
//                    alert(data.message.message)
            } else {
                alert(data.message.message)

            }
        }, 'json');
}

//<tr id="address-36" onclick="$(this).children().first().children().first()[0].checked=true">
//    <td><input type="radio" name="addressRadio" value="36"></td>
//        <td>刘家升</td>
//        <td>广州大学 梅苑 B4 242</td>
//        <td>15926098303</td>
//    </tr>

function getRowHtml(data){
    var row = '<tr id="address-'+data["ID"]+'" onclick="sendToThisEffect($(this))">'+
        '<td style="color: #ff0000">'+
        '<div class="sendtothismarker" id="sendtothis-'+data["ID"]+'" style="display: none;">'+
        '<span style="color: #ff0000 !important" class="glyphicon glyphicon-map-marker"></span> 寄送至'+
        '</div>'+
        '</td>'+
        '<td><input type="radio" name="addressRadio" value="'+data["ID"]+'"></td>'+
        '<td>'+data["Name"]+'</td>'+
        '<td>'+ data["Domain"] + ' ' + data["Domain2"] + ' ' + data["Domain3"] + ' '+ data["Address"] +'</td>' +
        '<td>'+ data["Phone"]+'</td>'+
        '<td><a href="###" onclick="editAddressOpen('+ data["ID"] +')">修改</a></td>'+
        '</tr>';
    return row;
}

function getOptionHtml(data){
    var option = "<option>" +data+
        "</option>";
    return option;
}


$().ready(function(){
    getDomainList(function(){
        getDomain2List(function(){
            getDomain3List();
        });
    });

    var select = $('#Domain2');
    select.children().remove();
    var option =  getOptionHtml('请选择');
    select.prepend(option);
    var select = $('#Domain3');
    select.children().remove();
    var option =  getOptionHtml('请选择');
    select.prepend(option);
    $('#Domain').on('change',function(){
        getDomain2List(function(){
            getDomain3List();
        });
    });
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
