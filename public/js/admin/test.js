/**
 * Created by Administrator on 14-10-12.
 */

var wsurl = 'ws://192.168.18.109:8088/sessDemo';
var conn = new ab.Session(wsurl,
    function() {
        conn.subscribe('event:broadcast', function(topic, data) {
            // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
            console.log('New article published to category "' + topic + '" : ' + data);
//            alert(data);
        });
    },
    function() {
        console.warn('WebSocket connection closed');
    },
    {'skipSubprotocolCheck': true}
);