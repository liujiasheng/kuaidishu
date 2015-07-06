/**
 * Created by Administrator on 14-8-15.
 */

var searchData = {};

function checkSearchData(data){
    if( data === null ||
        data["sellerId"] === null ||
        data["key"] === null ||
        data["page"] === null ||
        data["count"] === null ||
        data["mc"] === null )
        return false;

    return true;
}

var isLoading = false;
function pushMoreGoods(mc){

    if(!checkSearchData(searchData[mc]) || isLoading) return;
    // alert(searchData["type"] +" "+ searchData["key"] + " " + searchData["mc"] + " " + searchData["sc"]);

    searchData[mc]["page"] ++;
    $('.pushMoreBtn').button('loading');
    isLoading = true;
    $.post("/home/sellerCtrl/seller/search",searchData[mc],function(data){
        if(data.state){
            if(data.html !== null){
                $('#pushMoreBtn-'+mc).remove();
                $('#mclass-'+mc).children().first().append(data.html);
                $('#searchPanel').append(data.html);
                initAddToCart();
                initBuyPopover();
            }else{
                //finished
                //$('.pushMoreBtn').remove();
            }
        }else{
            alert("fail" + (data.message?data.message:""));
        }
    },"json").always(function(){
            $('.pushMoreBtn').button('reset');
            isLoading = false;
        });

}

function innerSearch(){
    //var sid = $('#innerSearchSID').val();
    var text = $('#innerSearchText').val();
    if(text != ""){
        window.location.href = "?k=" + text;
    }
}

$().ready(function(){
    initBuyPopover();
    stickRightCol();
    $(window).scroll(function() {
        var buffer = 0;
        if($(window).scrollTop() + $(window).height() + buffer >= $(document).height() - 250) {
            //pushMoreGoods();
            var mc = "";
            if($('.tab-pane.active').length > 0){
                mc = $('.tab-pane.active')[0]["id"].split("-")[1];
            }else{
                mc = "0";
            }
            pushMoreGoods(mc);
        }
    });
});

//when scroll down, stick the right col to top
function stickRightCol(){
    var s = $(".seller-col-right");
    var pos = s.position();
    $(window).scroll(function() {
        var windowPos = $(window).scrollTop();

        if (windowPos >= pos.top - 10) {
            s.addClass("stick");
        } else {
            s.removeClass("stick");
        }
    });
}