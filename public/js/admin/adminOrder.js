/**
 * Created by Administrator on 14-7-24.
 */
function initFunctions(){
    tableHighlight();

    $(".tr-detail").unbind().click(function(){
        var trInfo = $(this).parent().parent().next();
        trInfo.removeClass();
        trInfo.addClass ($(this).parent().parent().attr("class") );
        trInfo.fadeToggle(300);
    });

    bindPhonePopover();
}
//ready to do
$(function(){
    initFunctions();
    refreshTimer();
});

//bind phonePopover
function bindPhonePopover(){
    $('html').unbind().click(function(e) {
        $('.phonePopover').popover('hide');
    });
    $('.phonePopover').unbind().popover({
        trigger: 'manual'
    }).click(function(e) {
            var phone = $(this).text().trim();
            var outThis = $(this);
            var phoneStr = getPhoneState(phone, function(data){
                outThis.attr('data-content',data);
                outThis.popover('toggle');
            });
            e.stopPropagation();
        });
}

function tableHighlight() {
    var check = $("#highlight")[0].checked;

    if(check){
    $("tr").each(function () {
        var state = $(this).children(".state").text().trim();
        if (state) {
            var c = "";
            switch (state) {
                case "待确认":
                    c = "success";
                    break;
                case "已确认":
                    c = "info";
                    break;
                case "已退回":
                    c = "danger";
                    break;
                default :
                    c = "";
                    break;
            }
            $(this).removeClass();
            $(this).addClass(c);
        }
    });
    }else{
        $("tr").each(function () {
            $(this).removeClass();
        });
    }
}

function readyConfirm(id){
    $('#cfm-id').val(id);
}

function confirmOrder(){
    var id = $('#cfm-id').val();
    var workerId = $('#cfm-worker').val();
    var ids;
    if(id == 0) ids = getSelectedIdArray();
    else ids = [id];

    $.post("order/order/confirmOrders",{
        workerId : workerId,
        ids : ids
    },function(data){
        if(data.state){
            var arr = data.changedArr;
            for(var i=0;i<arr.length;i++){
                var rowHtml = $('#order-'+arr[i]);
                rowHtml.children('td.state').text("已确认");
                rowHtml.children('td.operator').html(getOperatorHtml("已确认",arr[i]));
            }
            initFunctions();
            $('#confirmOrder').modal('hide');
            flushAllBoxes(false);

            //show send to worker err order
            var msg = "";var j = 0;
            var errArr = data.sendErrArr;
            if(errArr.length > 0){
                msg = "";
                for(j = 0 ; j<errArr.length; j++){
                    msg += "订单:"+errArr[j]["orderid"]+" 发送到员工失败("+errArr[j]["message"]+")\n";
                }
                alert(msg);
            }

            //show send buyer err msg
            var buyerErrArr = data.sendBuyerErrArr;
            if(buyerErrArr.length > 0){
                msg = "";
                for(j = 0 ; j<buyerErrArr.length; j++){
                    msg += "订单:"+buyerErrArr[j]["orderid"]+" 发送到派送员失败("+buyerErrArr[j]["message"]+")\n";
                }
                alert(msg);
            }
        }else{
            alert(data.message);
        }
    },"json");
}

function readyReject(id){
    $('#rej-id').val(id);
}

function rejectOrder(){
    var id = $('#rej-id').val();
    var text = $('#rej-text').val();
    if(!jQuery.isNumeric(id)){
        return;
    }
    if(!checkRejectReason(text)){
        alert("退回原因内容格式错误");
        return;
    }
    var ids;
    if(id == 0) ids = getSelectedIdArray();
    else ids = [id];
    var rowHtml = $('#order-'+id);

    $.post("order/order/rejectOrders",{
        text : text,
        ids : ids
    },function(data){
        if(data.state){
            var arr = data.changedArr;
            for(var i=0;i<arr.length;i++){
                var rowHtml = $('#order-'+arr[i]);
                rowHtml.children('td.state').text("已退回");
                rowHtml.children('td.operator').html(getOperatorHtml("已退回",arr[i]));
            }
            initFunctions();
            $('#rejectOrder').modal('hide');
            flushAllBoxes(false);
        }else{
            alert(data.message);
        }
    },"json");
}

function readyFinish(id){
    $('#finish-id').val(id);
}

function finishOrder(){
    var id = $('#finish-id').val();
    var ids;
    if(id == 0) ids = getSelectedIdArray();
    else ids = [id];
    $.post("order/order/finishOrders",{
        ids : ids
    },function(data){
        if(data.state){
            var arr = data.changedArr;
            for(var i=0;i<arr.length;i++){
                var rowHtml = $('#order-'+arr[i]);
                rowHtml.children('td.state').text("已完成");
                rowHtml.children('td.operator').html(getOperatorHtml("已完成",arr[i]));
            }
            initFunctions();
            $('#finishOrder').modal('hide');
            flushAllBoxes(false);
        }else{
            alert(data.message);
        }
    },"json");
}

function readyResendOrder(id){

}
function resendOrder(){
    var workerid = $('#resend-worker').val();
    var ids = getSelectedIdArray();
    $.post("order/order/sendOrdersToWorker",{
        workerid : workerid,
        ids : ids
    },function(data){
        if(data.state){
            $('#resendOrder').modal('hide');
            if(data.message!="") alert(data.message);
            else alert("发送成功");
        }else{
            alert(data.message);
        }
    },"json");
}

//<tr id="order-1000000027" class="success">
//    <td><input id="check-1000000027" type="checkbox"></td>
//        <td>1000000027</td>
//        <td>2014-07-28 14:16:39</td>
//        <td>刘家升</td>
//        <td>广州大学 梅苑 B4 242</td>
//        <td>15926098303</td>
//        <td class="state">待确认                    </td>
//        <td class="operator">
//            <a href="#" data-toggle="modal" data-target="#confirmOrder" onclick="readyConfirm(1000000027)">确认</a>
//            <a href="#" data-toggle="modal" data-target="#rejectOrder" onclick="readyReject(1000000027)">退回</a>
//            <a href="###" class="tr-detail">详细</a>
//        </td>
//    </tr>

//requires: id, orderTime, name, address, phone, state(chinese)
function getRowHtml(data){
    var html = '<tr id="order-'+data.id+'">'+
        '<td><input id="check-'+data.id+'" type="checkbox"></td>'+
        '<td><a href="###" onclick="viewOrder('+data.id+')">'+data.id+'</a></td>'+
        '<td>'+data.orderTime+'</td>'+
        '<td>'+data.sellerName+'</td>'+
        '<td>'+data.name+'</td>'+
        '<td>'+data.address+'</td>'+
        '<td><a class="phonePopover" href="###" data-container="body" data-toggle="popover" data-placement="left"  data-original-title="" title="">'+ data.phone +'</a></td>' +
        '<td class="state">'+ getStateByNum(data.state) +'</td>'+
        '<td class="operator">'+getOperatorHtml( getStateByNum(data.state), data.id)+'</td>';
    return html;
}

//<tr id="orderInfo-1000000026" style="height: 70px;" class="tr-orderInfo success">
//    <td colspan="8">
//    订单号：1000000026
//    下单用户：kesonlau   <br>
//    订单内容：
//    [喜事多便利店]
//    PRETZ*2; 旺旺小小酥*3;    <br>
//        总额：34.5
//    </td> </tr>

//requires id, username, details, total
function getRowInfoHtml(data){
    var html = '<tr id="orderInfo-'+data.id+'" style="height: 70px; display:none;" class="tr-orderInfo">'+
        '<td colspan="9">'+
        '订单号：'+data.id+ ' '+
        '下单用户：'+data.username+'<br>'+
        '订单内容：';
    var details = data.details;
    for(var index in details){
        html += '[' + details[index][0]["SellerName"] + '] ';
        for(var i=0;i<details[index].length;i++){
            var goods = details[index][i];
            html += goods["Name"] + "*" + goods["Count"] + "; "
        }
    }
    html += "<br/>";
    html += "备注："+ (data.remark==""?"空":data.remark) + " 总额：" + data.total;
    html += '</td></tr>';
    return html;
}

function getOperatorHtml(state, id){
    var html = '';
    switch(state.trim()){
        case "待确认":
            html = '<a href="#" data-toggle="modal" data-target="#confirmOrder" onclick="readyConfirm('+id+')">确认</a> '+
                   '<a href="#" data-toggle="modal" data-target="#rejectOrder" onclick="readyReject('+id+')">退回</a> '+
                   '<a href="###" class="tr-detail">详细</a>';
            break;
        case "已确认":
            html = '<a href="#" data-toggle="modal" data-target="#finishOrder" onclick="readyFinish('+id+')">完成</a>  '+
                '<a href="#" data-toggle="modal" data-target="#rejectOrder" onclick="readyReject('+id+')">退回</a> '+
                '<a href="###" class="tr-detail">详细</a>';
            break;
        case "已退回":
            html = '<a href="###" class="tr-detail">详细</a>';
            break;
        case "已完成":
            html = '<a href="###" class="tr-detail">详细</a>';
            break;
    }
    return html;
}

function getStateByNum(state){
    var s = '';
    switch(parseInt(state)){
        case 1:s="待确认";break;
        case 2:s="已确认";break;
        case 3:s="已退回";break;
        case 9:s="已完成";break;
        default: s="未知状态";break;
    }
    return s;
}

var adminUserSelect = {
    "state" : 0,
    "searchText" : "",
    "curPage" : 1,
    "pageCount" : 10,
    "domain" : "",
    "domain2" : "",
    "domain3" : "",
    "address" : ""
};

function changeTable(){
    displayLoading(true);
    $.post("order/order/search", adminUserSelect, function(data){
        if(data.state){
            $("#orderTable").children().remove();
            var newTable = '';
            var orders = data.orders;
            var orderDetails = data.orderDetails;
            for(var index in orders){
                newTable += getRowHtml({
                    id : orders[index]["ID"],
                    orderTime : orders[index]["OrderTime"],
                    sellerName : getFirstElement(orderDetails[orders[index]["ID"]])[0]["SellerName"],
                    name : orders[index]["Name"],
                    address : orders[index]["Domain"] + " " +
                               orders[index]["Domain2"] + " " +
                               (orders[index]["Domain3"]==null?"":orders[index]["Domain3"]) + " " +
                               orders[index]["Address"],
                    phone : orders[index]["Phone"],
                    state : orders[index]["State"]
                });
                newTable += getRowInfoHtml({
                    id : orders[index]["ID"],
                    username : orders[index]["UserName"],
                    details : orderDetails[orders[index]["ID"]],
                    total : orders[index]["Total"],
                    remark : orders[index]["Remark"]
                });
            }
            $("#orderTable").prepend(newTable);
            initFunctions();
            bindPhonePopover();
            var pages = Math.ceil(data.count/10);
            $("#allPages").text(pages.toString());
            flushEmptyResultHtml();
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function getPhoneState(phone, callback){
    var url = "/admin/phoneList/phoneList/getPhoneState";
    $.post(url,{phone : phone},function(data){
        if(data.state){
            if(callback) callback(data.phoneStr);
            return data.phoneStr;
        }else{
            return null;
        }
    },"json");
}

function showOnce(object,text,delay){
//    object.text(text);
//    object.show();
//    setTimeout(function(){
//        object.hide();
//    },delay);
}


function getFirstElement(p){for(var i in p)return p[i];}


function viewOrder(orderid){
    var url = '/admin/order/order/view';
    $.post(url, {id:orderid},function(data){
        if(data.state){
            $('#view-orderMsg').val(data.orderMsg);
            $('#viewOrder').modal('show');
        }else{
            alert(data.message);
        }
    },"json");
}

//自动更新
var refreshTimeout = 60; //seconds
var refreshTimeouter = null;
function refreshTimer(){
    clearTimeout(refreshTimeouter);
    refreshTimeouter = setTimeout(function(){
        changeTable();
        refreshTimer();
    },refreshTimeout*1000);
}

function changeRefreshing(){
    var check = $("#refresh")[0].checked;
    if(check){
        refreshTimer();
    }else{
        clearTimeout(refreshTimeouter);
    }
}