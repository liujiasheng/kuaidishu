/**
 * Created by Administrator on 14-7-17.
 */

//<li class="item">
//    <img src="<?php echo $this->basePath();?>/storage/goodsimg/5-5-8-菠萝汁-1405238406.jpg"/>
//    <p class="name">菠萝汁(广大快递鼠体验店广大快递鼠体验店广大快递鼠体验店)</p>
//    <p class="price">价格：12元</p>
//</li>

//加载小购物车项
function loadGoodsToCart(){
    var items = getCartItems();
    $.post("/home/home/getCartInfo",{"items":items},function(data){
        if(data.state){
            var cartItems = data.items;
            for(var i=0; i<cartItems.length; i++){
                $("#cart-ul").prepend(getCartMenuLiHtml(
                    cartItems[i].id,
                    "http://static.kuaidishu.com/goodsimg/tiny/"+cartItems[i].image,
//                    cartItems[i].name+"（"+ cartItems[i].mainClassName+"-"+cartItems[i].className + "）",
                    cartItems[i].name+ (cartItems[i]["standard"]=="默认"?"":"("+ cartItems[i]["standard"] + ")"),
                    cartItems[i].price,
                    cartItems[i].count,
                    cartItems[i].sellerName));
            }
            syncCartToBottomCart();
            $('.dropdown-menu li').click(function(e) {
                e.stopPropagation();
            });
        }
    },"json").always(function(){
            calCartTotal();
        });
}
//$().ready(function(){
//    loadGoodsToCart();
//});


function syncCartToBottomCart(){
    $('#cart-ul-bottom').children().remove();
    $('#cart-ul').clone().children().not('.account').appendTo('#cart-ul-bottom');
    $('#cart-ul-bottom').children().children('.price').children('.delete').remove();
}

function toggleBottomCart(){
    $('#cart-ul-bottom').parent().toggleClass('open');
}
function showBottomCart(){
    $('#cart-ul-bottom').parent().addClass('open');
}

//点击购买
function clickBuy(){

}

//添加商品到小购物车
function addGoodsToCart(id, goodDiv, price, standard){
    //show bottom cart nav
    $('#bottomCartNav').show();

    var img = goodDiv.children('img').attr('src');
    var name = goodDiv.children('div.goods-div').children('p').first().text();
    if(standard!="") {
        name += "("+ $.trim(standard) +")";
    }
    //var price = goodDiv.children('div.goods-div').children('p').children('.price').text();
    var seller = goodDiv.children('div.goods-div').children('.seller').text();

    var inCookie = checkItemExist(id);

    if(!inCookie){ //未存在cookie中
        addItemToCookie(id, 1);
        $("#cart-ul").prepend(getCartMenuLiHtml(id, img, name, price, 1, seller));
        $('.dropdown-menu li').click(function(e) {
            e.stopPropagation();
        });
    }else{ //已存在cookie中
        addOneCountInCookie(id);
        var old = $('#item-'+id).children('p.price').children('.c').text();
        $('#item-'+id).children('p.price').children('.c').text(parseInt(old)+1);
    }

    syncCartToBottomCart();
    showBottomCart();
    calCartTotal();
}

//计算小购物车的总额
function calCartTotal(){
    var lis = $("#cart-ul").children('.item:not(.account)');
    var counts = lis.children('.price').children('.c');
    var cnt = 0;
    var cntArr = new Array();
    counts.each(function(){
        var c = $(this).text();
        cnt += parseInt(c);
        cntArr.push(parseInt(c));
    });
    var prices = lis.children('.price').children('.p');
    var total = 0; var i = 0;
    prices.each(function(){
        var p = $(this).text();
        total += parseFloat(p) * cntArr[i++];
    });
    $(".cart-badge").text(cnt);
    $(".totalCnt").text(cnt);
    $(".totalPrice").text(total.toFixed(1));
}


function getCartMenuLiHtml(id, imgSrc, title, price, count, seller){
    return '<li class="item" id="item-'+ id +'">'+
        '<img src="' + imgSrc + '"/>'+
        '<p class="name">' + title + '—'+ seller + '</p>'+
        '<p class="price">' + '<strong class="p">' + price + '</strong>' + '元 ×<strong class="c">' + count + '</strong>' +
        '<a class="delete" href="###" style="text-align: right;width: 30%;float: right;" ' +
        'onclick="delItemInSmallCart(' + id + ',$(this))"><span class="glyphicon glyphicon-remove" style="color: #999 !important;"></span></a>' +
        '</p>'+
        '</li>';
}

function getCartItems(){
    var ck = $.cookie("cart");
    var items = new Array();
    if(ck){
        var objs = ck.split(',');
        objs = checkCookieDamage(objs);
        for(var i=0; i<objs.length; i++){
            items.push({"id":objs[i],"count":objs[++i]});
        }
    }
    return items;
}

//检查 cookie 是否受损，如果是，则清空
function checkCookieDamage(objs){
    var mark = true;
    if(objs.length % 2 !=0 ){
        mark = false;
    }
    for(var k=0;k<objs.length;k++){
        if(!$.isNumeric(objs[k])){
            mark = false;
        }
    }
    if(!mark){
        //clear cookie
        clearCartCookie();
    }
    return mark ? objs : new Array();
}

//从小购物车中删除商品
function delItemInSmallCart(id, obj){
    delItemFromCookie(id);
    obj.parent().parent().fadeOut(600, function(){
        $(this).remove();
        calCartTotal();
    });
}

//清空小购物车
function clearAllItemsInCart(){
    clearCartCookie();
    $('#cart-ul').children('.item:not(.account)').remove();
    $('#cart-ul-bottom').children().remove();
    calCartTotal();
}

//清空cart Cookie
function clearCartCookie(){
    $.cookie("cart", "", {path:'/'});
}

function checkItemExist(id){
    var items = getCartItems();
    for(var i=0;i<items.length;i++){
        if(items[i].id == id){
            return true;
        }
    }
    return false;
}

function addItemToCookie(id, count){
    var old = $.cookie("cart");
    var over = "";
    if(typeof old == 'undefined' || old == "")
        over = id + ',' + count;
    else
        over = old + ',' + id + ',' + count;
    $.cookie("cart", over, {path:'/'});
}

function addOneCountInCookie(id){
    var items = getCartItems();
    var newItems = "";
    for(var i=0;i<items.length;i++){
        if(items[i].id != id){
            newItems += items[i].id + ',' + items[i].count + ',';
        }else{
            newItems += items[i].id + ',' + (parseInt(items[i].count)+1) + ',';
        }
    }
    var over = "";
    if(newItems.length > 0) over = newItems.substring(0,newItems.length-1);
    $.cookie("cart", over, {path:'/'});
}

function delItemFromCookie(id){
    var items = getCartItems();
    var newItems = "";
    for(var i=0;i<items.length;i++){
        if(items[i].id != id){
            newItems += items[i].id + ',' + items[i].count + ',';
        }
    }
    var over = "";
    if(newItems.length > 0) over = newItems.substring(0,newItems.length-1);
    $.cookie("cart", over, {path:'/'});
}

//从购物车页面中删除商品
function delGoodsInCart(id, btn){
//    var retVal = confirm("确定删除？");
//    if(!retVal) return;
    delItemFromCookie(id);
//    btn.parent().parent().remove();
//
//    calCartTotalInCartPage();
    btn.parent().parent().fadeOut(600, function(){
        $(this).remove();
        calCartTotalInCartPage();
    });


}

function changeGoodsCountInCookie(id,count){
    var items = getCartItems();
    var newItems = "";
    for(var i=0;i<items.length;i++){
        if( parseInt(items[i].id) != id){
            newItems += items[i].id + ',' + items[i].count + ',';
        }else{
            newItems += items[i].id + ',' + count + ',';
        }
    }
    var over = "";
    if(newItems.length > 0) over = newItems.substring(0,newItems.length-1);
    $.cookie("cart", over, {path:'/'});
}

//修改商品数量
function changeGoodsCount( _id, _count){
    var id = parseInt(_id);
    var count = parseInt(_count);
    if(count < 1 || !jQuery.isNumeric(id) || !jQuery.isNumeric(_count) ){
        return;
    }
    changeGoodsCountInCookie(_id, _count);
    calCartTotalInCartPage();
}

//计算购物车页面的总额
function calCartTotalInCartPage(){
    var trs = $("tbody").children('.tr-item');
    var count = 0;
    var total = 0;
    trs.each(function(){
        var p = parseFloat($(this).children('.price').text());
        p.toFixed(1);
        var c = parseInt($(this).children().children('.count').val());
        $(this).children('.total').text( (p * c).toFixed(1) );
        count += c;
        total += p*c;
    });
    $(".cart-total").children('.count').text(count);
    $(".cart-total").children('.total').text(total.toFixed(1));
}

//+1放大效果
(function($) {
    $.extend({
        tipsBox: function(options) {
            options = $.extend({
                obj: null,  //jq对象，要在那个html标签上显示
                str: "<strong>+1</strong>",  //字符串，要显示的内容;也可以传一段html，如: "<b style='font-family:Microsoft YaHei;'>+1</b>"
                startSize: "30px",  //动画开始的文字大小
                endSize: "50px",    //动画结束的文字大小
                interval: 1000,  //动画时间间隔
                color: "red",    //文字颜色
                callback: function() {}    //回调函数
            }, options);
            $("body").append("<span class='num'>"+ options.str +"</span>");
            var box = $(".num");
            var left = options.obj.offset().left - options.obj.width() / 2;
            var top = options.obj.offset().top - options.obj.height();
            box.css({
                "position": "absolute",
                "left": left + "px",
                "top": top + "px",
                "z-index": 100,
                "font-size": options.startSize,
                "line-height": options.endSize,
                "color": options.color
            });
            box.animate({
                "font-size": options.endSize,
                "opacity": "0",
                "top": top - parseInt(options.endSize) + "px"
            }, options.interval , function() {
                box.remove();
                options.callback();
            });
        }
    });
})(jQuery);

$(function() {
//    $(".plusone").click(function() {
//        $.tipsBox({
//            obj: $(this),
//            str: "+1",
//            callback: function() {
//                //alert(5);
//            }
//        });
//    });

   initAddToCart();
});

function initAddToCart(){
    $('.addToCart').on('click', function () {
        addToCartEffect($(this));
    });
}

function hideAllBuyPopover(){
    $('.buy-popover').each(function() {
        $(this).popover('hide');
    });
}

var hasOpenBuyPopover = false;
function initBuyPopover(){
    $('html').unbind().click(function(e) {
        $('.buy-popover').popover('hide');
    });

    $('.buy-popover').unbind().popover({
        html: true,
        trigger: 'manual'
    }).click(function(e) {
            $(this).popover('toggle');
            e.stopPropagation();
        });
//    $(".buy-popover").popover();
}

function addToCartEffect(btn){
    var cart = $('#flyToThisCart');
    var imgtodrag = btn.parent().parent().find('img');
    if(imgtodrag.length < 1) {
        imgtodrag = btn.parent().parent().parent().parent().parent().find('img');
        btn.parent().parent().parent().removeClass("in");
    }

    if (imgtodrag) {
        var imgclone = imgtodrag.clone()
            .offset({
                top: imgtodrag.offset().top,
                left: imgtodrag.offset().left
            })
            .css({
                'opacity': '0.8',
                'position': 'absolute',
                'height': imgtodrag.height(),
                'width': imgtodrag.width(),
                'z-index': '1100'
            })
            .appendTo($('body'));

//                imgclone.animate({
//                    'top' : imgtodrag.offset().top - 100 ,
//                    'left' : (cart.offset().left + imgtodrag.offset().left)/2,
//                    'width' : 30,
//                    'height' : 30
//                },100);
        imgclone.animate({
            'top': cart.offset().top ,
            'left': cart.offset().left,
            'width': 10,
            'height': 10
        }, 800);

        imgclone.animate({
            'width': 0,
            'height': 0
        }, function () {
            $(this).detach()
        });
    }
}

