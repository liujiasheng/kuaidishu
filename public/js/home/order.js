/**
 * Created by Administrator on 14-7-21.
 */

function showMsgBeforeSubmitOrder(content){
    $('#msgBeforeSubmitContent').html(content);
    $('#msgBeforeSubmit').modal('show');
}
$().ready(function(){
    //showMsgBeforeSubmitOrder();
});

function submitOrder(btn){
    var addrRadio = $("input[name='addressRadio']:checked");
    if(addrRadio.length < 1){
        //$.ambiance({message:"请选择收货地址！"});
        $('#wrongMsg').text('请选择收货地址！');
        $('#addressTable').addClass('alertDiv');
        alertFlashing($('#addressTable'));
        return;
    }
    var addrId = addrRadio[0].value;
    if(addrId == "0" || !jQuery.isNumeric(addrId)){
        //$.ambiance({message:"请填写收货地址！"});
        $('#wrongMsg').text('请填写收货地址！');
        $('#addressTable').addClass('alertDiv');
        alertFlashing($('#addressTable'));
        return;
    }
    var remark = $('#remarkText').val();
    btn.button('loading');
    var items = getCartItems();
    $.post("cart/cart/submitOrder",{
        items : items,
        addrId : addrId,
        remark : remark
    },function(data){
        if(data.state){
            var ids = data.ids;
            var idStr = ids[0];
            for(var i = 1; i<ids.length; i++){
                idStr += ","+ids[i];
            }
            location.href = "ordered?order=" + idStr;
            //clear cookie
            $.cookie("cart", "", {path:'/'});
        }else{
            $.ambiance({message:data.message});
            btn.button('reset');
        }
    },"json");

}

function editAddressOpen(id){
    $('#editAddressID').val(id);
    var data = {};
    $.post("/user/userinfo/userinfo/getDeliveryAddress",{
        id : id
    },function(data){
        if(data.state){
            var addr = data.message.list[0];
            $('#editInputReceiverName').val(addr["Name"]);
            $('#DomainEdit').val(addr["Domain"]);
            getEditDomain2List(addr["Domain"], function(){
                $('#Domain2Edit').val(addr["Domain2"]);
            });
            getEditDomain3List( addr["Domain"], addr["Domain2"], function(){
                $('#Domain3Edit').val(addr["Domain3"]);
            });

            $('#editInputStreetAddress').val(addr["Address"]);
            $('#editInputPhone').val(addr["Phone"]);

            $('#editDeliverAddressModal').modal('show');
        }
    },"json");

}

function editAddress(){
    var url = "/user/userinfo/userinfo/updateDeliveryAddress";
    var id = $('#editAddressID').val();
    var name = $('#editInputReceiverName').val();
    var domain = $('#DomainEdit').val();
    var domain2 = $('#Domain2Edit').val();
    var domain3 = $('#Domain3Edit').val();
    var address = $('#editInputStreetAddress').val();
    var phone = $('#editInputPhone').val();
    var data = {
        ID: id,
        Name : name,
        Domain : domain,
        Domain2 : domain2,
        Domain3 : domain3,
        Address : address,
        Phone : phone
    };
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var tr = $('#address-'+id);
                $(tr.children()[2]).text(name);
                $(tr.children()[3]).text(domain+" "+domain2+" "+domain3+" "+address);
                $(tr.children()[4]).text(phone);
                $('#editDeliverAddressModal').modal('hide');
            } else {
                alert(data.message.message);
            }
        }, 'json');
}

function getEditDomainList(){
    var url = "/user/userinfo/userinfo/getDeliveryLocation";
    var data = {};
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#DomainEdit');
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
getEditDomainList();

function getEditDomain2List(domain, callback){
    var url = "/user/userinfo/userinfo/getDomain2List";

    var data = {
        domain: domain
    };
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#Domain2Edit');
                select.children().remove();
                var option =  getOptionHtml('请选择');
                select.prepend(option);
                for(i=0;i<list.length;i++){
                    var option =  getOptionHtml(list[i]);
                    select.prepend(option);
                }
                if(callback) callback();
            } else {
                alert(data.message.message);
            }
        }, 'json');
}

function getEditDomain3List(domain, domain2, callback){
    var url = "/user/userinfo/userinfo/getDomain3List";

    var data = {
        domain:domain,
        domain2:domain2
    };
    $.post(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var select = $('#Domain3Edit');
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


function alertFlashing(obj){
    var colorA = "#ddd";
    var opt = 1.0;
    var time = 100;
    obj.animate({
        backgroundColor: colorA,
        opacity: opt
    },time)
        .animate({
            backgroundColor: "#fff",
            opacity: opt
        },time)
        .animate({
            backgroundColor: colorA,
            opacity: opt
        },time)
        .animate({
            backgroundColor: "#fff",
            opacity: opt
        },time);
//        .animate({
//            backgroundColor: colorA,
//            opacity: opt
//        },50)
//        .animate({
//            backgroundColor: "#fff",
//            opacity: opt
//        },50)
}

