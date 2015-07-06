/**
 * Created by Administrator on 14-10-11.
 */
var Login = 1;
var Msg = 2;
var NEW_ORDER = 3;
var NEW_MESSAGE = 4;

var SELLER_LOGIN = '201';

//connect to push server
var wsurl = 'ws://192.168.1.105:8088/sessDemo';
var conn = null;
//end

//login to rpc server
function loginSeller(){
//    var sellerId = '100207';
    conn.call(SELLER_LOGIN, sellerId).then(function( result) {
        console.log(result);
    }, function(error) {
        console.log(error);
    });
}
//end

var localNewestOrderID = 0;

$().ready(function(){
    localNewestOrderID = $('tbody').first().children('tr').first().children().first().text();
    conn = new ab.Session(wsurl,
        function() {
            conn.subscribe('event:broadcast', function(topic, data) {
                // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                console.log('New article published to category "' + topic + '" : ' + data);
                switch (data.type){
                    case NEW_ORDER:
                        //alert('you have ' + data.orderIds.length + ' new orders');
                        var ids = data.orderIds;
                        var cnt = $('.haveNewOrderMsg').children('.count').text();
                        $('.haveNewOrderMsg').children('.count').text( parseInt(cnt) + ids.length );
                        $('.haveNewOrderMsg').show();
                        $('#player').get(0).play();
                        break;
                    default :
                        break;
                }
            });
            loginSeller();
        },
        function() {
            console.warn('WebSocket connection closed');
        },
        {'skipSubprotocolCheck': true}
    );
});

function getLocalNewestOrderID(){
//    if(localNewestOrderID == 0){
//        localNewestOrderID = $('tbody').first().children('tr').first().children().first().text();
//    }
    return localNewestOrderID;
}



function getNewerOrder(){
    var url = '/seller/orderMgr/SellerOrderMgr/getNewerOrder';
    $.post(url,{
        id: getLocalNewestOrderID()
    },function(data){
        if(data.state){
            var d = data.data;
            if(parseInt(d['count']) > 0){
//                alert('has');
                var ids = d['ids'];
                localNewestOrderID = ids[ids.length-1];

                var cnt = $('.haveNewOrderMsg').children('.count').text();
                $('.haveNewOrderMsg').children('.count').text( parseInt(cnt) + ids.length );
                $('.haveNewOrderMsg').show();
                $('#player').get(0).play();
            }else{
//                alert('has not');
            }
        }else{
            alert(data.message);
        }
    },"json");
}

function printPageByContent( htmlContent ){
    var commandTest = 'C:/Users/Administrator/Desktop/test.py';
    var newWindow = window.open("print page", "_blank");
    var html = '123';
    newWindow.document.write(html);
    newWindow.document.close();
    newWindow.print();
    newWindow.close();
}