/**
 * Created by Administrator on 14-9-13.
 */

//加载小购物车项
function loadGoodsToCart_mobile(){
    var items = getCartItems();
    $.post("/home/home/getCartInfo",{"items":items},function(data){
        if(data.state){
            var cartItems = data.items;
            var count = 0;
            var total = 0;
            for(var i=0; i<cartItems.length; i++){
                var c = parseInt(cartItems[i]["count"]);
                var p = parseFloat(cartItems[i]["price"]);
                count += c;
                total += p * c;
            }
            $('#mobile-cart-badge').text(count);
            $('#mobile-cart-total').text(total);
        }
    },"json").always(function(){

        });
}
$().ready(function(){
    loadGoodsToCart_mobile();
});

function checkSearchData(){
    if( searchData == null ||
        searchData["sellerId"] == null ||
        searchData["key"] == null ||
        searchData["page"] == null ||
        searchData["count"] == null ||
        searchData["mc"] == null )
        return false;

    return true;
}

var isLoading = false;
function pushMoreGoods_mobile(){
    if(!checkSearchData() || isLoading) return;
    // alert(searchData["type"] +" "+ searchData["key"] + " " + searchData["mc"] + " " + searchData["sc"]);

    searchData["page"] ++;
    $('.pushMoreBtn').button('loading');
    isLoading = true;
    $.post("/mobile/mobile/searchGoods",searchData,function(data){
        if(data.state){
            if(data.html){
                $('.pushMoreBtn').remove();
//                $(data.html).insertBefore($('.pushMoreBtn'));
                $(".mobile-seller-goods").append(data.html);
                //bindPushMoreBtn();
//                initAddToCart();
//                initBuyPopover();
            }else{
                //finished
                $('.pushMoreBtn').remove();
            }
        }else{
            alert("fail" + (data.message?data.message:""));
        }
    },"json").always(function(){
//            $('.pushMoreBtn').button('reset');
            isLoading = false;
        });

}

//添加商品到小购物车
function addGoodsToCart_mobile(id, price){
    var inCookie = checkItemExist(id);

    if(!inCookie){ //未存在cookie中
        addItemToCookie(id, 1);
    }else{ //已存在cookie中
        addOneCountInCookie(id);
    }
    addBottomTotal_mobile(price);
}

function addBottomTotal_mobile(price){
    var oldPrice = parseFloat($('#mobile-cart-total').text());
    var p = parseFloat(price) + oldPrice;
    $('#mobile-cart-total').text(p.toFixed(1));
    var oldCount = parseInt($('#mobile-cart-badge').text());
    var c = oldCount + 1;
    $('#mobile-cart-badge').text(c);
}


function bindPushMoreBtn(){
    $(window).unbind().scroll(function() {
        var buffer = 0;
        if($(window).scrollTop() + $(window).height() + buffer >= $(document).height()) {
            if($(".pushMoreBtn").length>0)
                pushMoreGoods_mobile();
        }
    });
}
function bindPlusOneEffect(){
    $(".plusone").click(function() {
        $.tipsBox({
            obj: $(this),
            str: "+1",
            callback: function() {
                //alert(5);
            }
        });
    });
}
function subBtnPlusOneEffect(btn){
    $.tipsBox({
        obj: btn,
        str: "+1",
        callback: function() {
            //alert(5);
        }
    });
}
$().ready(function(){
    bindPushMoreBtn();
    bindPlusOneEffect();

//    getEditDomainList(function(){
//        getEditDomain2List($('#DomainEdit').val(), function(){
//            getEditDomain3List($('#DomainEdit').val(), $('#DomainEdit2').val(), function(){});
//        });
//    });
});



function getEditDomainList(callback){
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
                if(callback) callback();
//                    alert(data.message.message)
            } else {
                alert(data.message.message)

            }
        }, 'json');
}

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

function getOptionHtml(data){
    var option = "<option>" +data+
        "</option>";
    return option;
}

function innerSearch(){
    //var sid = $('#innerSearchSID').val();
    var text = $('#innerSearchText').val();
    if(text != ""){
        window.location.href = "?k=" + text;
    }
}

//submit order in mobile
function confirmCart_mobile(btn){
    if( parseFloat($('.cart-total').children('.total').text()) < 0.00001){
        alert("订单为空");//$.ambiance({message:"订单为空"});
    }else{
        btn.button('loading');
        //location.href='order#fillAddress';

        var reqData = {
            "items": getCartItems(),
            "info" : {
                "name" : $('#editInputReceiverName').val(),
                "domain" : $('#DomainEdit').val(),
                "domain2" : $('#Domain2Edit').val(),
                "domain3" : $('#Domain3Edit').val(),
                "address" : $('#editInputStreetAddress').val(),
                "phone" : $('#editInputPhone').val()
            },
            "remark" : $('#remarkText').val()
        };
        $.post("/m/order/order/submitOrder",reqData,function(data){
            if(data.state){
                var ids = data.ids;
                //var username = data.account["username"];
                var idStr = ids[0];
                for(var i = 1; i<ids.length; i++){
                    idStr += ","+ids[i];
                }
                location.href = "/m/ordered?order=" + idStr;
                //clear cookie
                $.cookie("cart", "", {path:'/'});
            }else{
                alert(data.message);
            }
        },"json").always(function(){
                btn.button('reset');
            });

    }
}