/**
 * Created by Administrator on 14-8-14.
 */


function checkSearchData(){
    if( searchData == null ||
        searchData["type"] == null ||
        searchData["key"] == null ||
        searchData["page"] == null ||
        searchData["count"] == null ||
        searchData["mc"] == null ||
        searchData["sc"] == null)
    return false;

    return true;
}

var isLoading = false;
function pushMoreGoods(){
    if(!checkSearchData() || isLoading) return;
   // alert(searchData["type"] +" "+ searchData["key"] + " " + searchData["mc"] + " " + searchData["sc"]);

    searchData["page"] ++;
    $('.pushMoreBtn').button('loading');
    isLoading = true;
    $.post("home/search/search",searchData,function(data){
        if(data.state){
            if(data.html){
                $(data.html).insertBefore($('.pushMoreBtn'));
                initAddToCart();
                initBuyPopover();
            }else{
                //finished
                $('.pushMoreBtn').remove();
            }
        }else{
            alert("fail" + (data.message?data.message:""));
        }
    },"json").always(function(){
            $('.pushMoreBtn').button('reset');
            isLoading = false;
        });

}

$().ready(function(){
    $(window).scroll(function() {
        var buffer = 0;
        if($(window).scrollTop() + $(window).height() + buffer >= $(document).height() - 250) {
            pushMoreGoods();
        }
    });
});

