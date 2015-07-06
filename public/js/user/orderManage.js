
function getOrderList(page){
    var url = "/user/orderManage/getUserOrderList";
    var data = {
        page:page
    };
    $.get(url,
        data
        , function (data) {
            if (data.state) {
                var list = data.message.list;
                var body = $('#userOrderMGRtable').find('> tbody');
                body.children().remove();
                for(i=0;i<list.length;i++){
                    var row = getRowHtml(list[i]);
                    body.append(row);
                }
                $('#currentPage').html(String(data.message.page));
                $('#totalPage').html(String(data.message.pageCount));
                if(String(data.message.page)=="1"){
                    $('.pager li:first-child').html('<span >上一页</span>')


                }else{
                    $('.pager li:first-child').html('<a id="preLink" href="#" >上一页</a>');
                    $('#preLink').on('click',function(){
                        var page = $('#currentPage').html();
                        getOrderList(parseInt(page)-1)
                    });
                }

                if(String(data.message.page)==String(data.message.pageCount)){
                    $('.pager li:last-child').html('<span>下一页</span>')
                }else{
                    $('.pager li:last-child').html('<a id="nextLink" href="#" >下一页</a>');

                    $('#nextLink').on('click',function(){
                        var page = $('#currentPage').html();
                        getOrderList(parseInt(page)+1)
                    });
                }
            } else {
                alert(data.message.message)
            }
        }, 'json');
}

function getRowHtml(data){
    var state = "";
    switch (data["State"]){
        case "1":
            state = "等待处理";
            break;
        case "2":
            state = "已确认订单";
            break;
        case "3":
            state = "订单被拒绝";
            break;
        case "9":
            state = "已完成";
            break;
        default :
            state = "订单状态错误";
            break;
    }
    var row = "<tr id='" +data["ID"]+
        "'>" +
        "<td><a href='/user/orderManage/detail/"+data["ID"]+"'>"+data["ID"]+"</a></td>" +
        "<td>"+data["Total"]+"</td>"+
        "<td>"+data["OrderTime"]+"</td>" +
        "<td>"+data["ExpectTime"]+"</td>" +
        "<td>"+data["Name"]+"</td>" +
        "<td>"+data["Phone"]+"</td>" +
        "<td>"+state+"</td>" +
        "</tr>";
    return row;
}


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


    getOrderList(1)
});