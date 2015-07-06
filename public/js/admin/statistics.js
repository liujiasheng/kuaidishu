/**
 * Created by Administrator on 14-9-22.
 */

var analysisSelection = {
    type : 'user',
    startTime : '',
    endTime : ''
};
var analysisChartTitles = {
    userRegister : '用户新增注册量',
    userTotalRegister: '用户累计注册量',
    orderCount : '订单量',
    orderTotal: '订单金额量',
    domain3Count: '楼栋订单量',
    domain3Total: '楼栋订单金额量',
    goodsCount: '产品销量'
};

$().ready(function(){
    $('.input-group.date').datepicker({
        format: "yyyy-mm-dd"
    });

    changeDateInterval(7);
});

function importReport(){

    var sid = $('#sellerSelect').val();
    var st = $('#startTimeInput').val();
    var et = $('#endTimeInput').val();
    if(sid == null || st == null || et == null) return;
    var url = '/admin/statistics/statistics/exportSellerReportForm?s='+sid+'&st='+st+'&et='+et;
    window.open(url, '_blank');

}

function changeAnalysisSelection(){
    var type = analysisSelection['type'];
    var st = analysisSelection['startTime'];
    var et = analysisSelection['endTime'];
    $('#analysisStartTime').val(st);
    $('#analysisEndTime').val(et);

    switch (type){
        case 'user':
            $('#orderAnalysisArea').hide();
            $('#goodsAnalysisArea').hide();
            $('#userAnalysisArea').show();
            updateUserAnalysisChart(type, st, et);
            break;
        case 'goods':
            $('#orderAnalysisArea').hide();
            $('#userAnalysisArea').hide();
            $('#goodsAnalysisArea').show();
//            updateGoodsAnalysisChart(type, st, et, null, null, null, null);
            break;
        case 'order':
            $('#userAnalysisArea').hide();
            $('#goodsAnalysisArea').hide();
            $('#orderAnalysisArea').show();
            updateOrderAnalysisChart(type, st, et);
            break;
        default :
            break;
    }
}

function updateUserAnalysisChart( type, st, et ){
    st = st + ' '+ '00:00:00';
    et = et + ' ' + '23:59:59';
    var url = '/admin/statistics/statistics/updateAnalysisChart?type='+type+'&st='+st+'&et='+et;
    $.get(url,{},function(data){
        if(data.state){
            var chartData = data.chartData;
            var drawData = [];
            var totalRegData = [];var tmpTotal = 0;
            for(var i in chartData){
                drawData.push([i, chartData[i]]);
                totalRegData.push([i, chartData[i] + tmpTotal]);
                tmpTotal = chartData[i] + tmpTotal;
            }
            drawChart('analysisChart1', [drawData], 'userRegister');
            drawChart('analysisChart2', [totalRegData], 'userTotalRegister');
        }
    },"json");
}

function updateGoodsAnalysisChart( type, st, et, limit, sid, mcid, cid ){
    st = st + ' '+ '00:00:00';
    et = et + ' ' + '23:59:59';
    var url = '/admin/statistics/statistics/updateAnalysisChart?type='+type+'&st='+st+'&et='+et;
    $.get(url,{},function(data){
        if(data.state){
            var chartData = data.chartData;

            var goodsCountObj = chartData['goodsCount'];
            var goodsCountData = [];
            for(var i in goodsCountObj){
                goodsCountData.push([goodsCountObj[i]['Name'], goodsCountObj[i]['Count']]);
            }
            drawBarChart('goodsAnalysisChart1', goodsCountData, 'goodsCount');
        }
    },"json");
}

function updateOrderAnalysisChart( type, st, et ){
    st = st + ' '+ '00:00:00';
    et = et + ' ' + '23:59:59';
    var url = '/admin/statistics/statistics/updateAnalysisChart?type='+type+'&st='+st+'&et='+et;
    $.get(url,{},function(data){
        if(data.state){
            var chartData = data.chartData;

            var orderCountObj = chartData['orderCount'];
            var orderCountData = [];
            for(var i in orderCountObj){
                orderCountData.push([i, orderCountObj[i]]);
            }

            var orderTotalObj = chartData['orderTotal'];
            var orderTotalData = [];
            for(var i in orderTotalObj){
                orderTotalData.push([i, orderTotalObj[i]]);
            }

            var domain3CountObj = chartData['domain3Count'];
            var domain3CountTicks = [];
            var domain3CountData = [];
            for(var i in domain3CountObj){
                domain3CountTicks.push(i);
                domain3CountData.push(domain3CountObj[i]);
            }

            var domain3TotalObj = chartData['domain3Total'];
            var domain3TotalTicks = [];
            var domain3TotalData = [];
            for(var i in domain3TotalObj){
                domain3TotalTicks.push(i);
                domain3TotalData.push(domain3TotalObj[i]);
            }

            drawChart('orderAnalysisChart1', [orderCountData], 'orderCount');
            drawChart('orderAnalysisChart2', [orderTotalData], 'orderTotal');
            drawDateBarChart('orderAnalysisChart3', domain3CountData, domain3CountTicks, 'domain3Count');
            drawDateBarChart('orderAnalysisChart4', domain3TotalData, domain3TotalTicks, 'domain3Total');
        }
    },"json");
}

function drawChart( chart, lines, type ){
    $('#'+chart).children().remove();
    var plot1 = $.jqplot(chart, lines, {
        title: analysisChartTitles[type],
        animate: true,
        highlighter: {
            show: true,
            sizeAdjust: 1,
            tooltipOffset: 9,
            tooltipAxes: 'xy'
        },
        seriesDefaults:{
            shadow:false,
            fill:true,
            fillAndStroke:true,
            fillAlpha:0.2,
            fillColor:'#999'
        },
        grid:{
            backgroundColor: '#ffffff',
            shadow: false
        },
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickOptions:{formatString:'%Y-%m-%d'}
            },
            yaxis:{
                min : 0
            }
        },
        series:[
            {
                lineWidth:4,
                markerOptions:{style:'circle'}
            }
        ]
    });
}
function drawBarChart( chart, lines, type){
    $('#'+chart).children().remove();
    $.jqplot(chart, [lines], {
        title: analysisChartTitles[type],
        // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
        animate: !$.jqplot.use_excanvas,
        seriesDefaults:{
            renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true }
        },
        grid:{
            backgroundColor: '#ffffff',
            shadow: false
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer
            }
        },
        highlighter: { show: false }
    });
}
function drawDateBarChart( chart, lines, ticks, type ){
    $('#'+chart).children().remove();
    $.jqplot(chart, [lines], {
        title: analysisChartTitles[type],
        // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
        animate: !$.jqplot.use_excanvas,
        seriesDefaults:{
            renderer:$.jqplot.BarRenderer,
            pointLabels: { show: true }
        },
        grid:{
            backgroundColor: '#ffffff',
            shadow: false
        },
        axes: {
            xaxis: {
                renderer: $.jqplot.CategoryAxisRenderer,
                ticks: ticks
            }
        },
        highlighter: { show: false }
    });
}

function changeDataType( type ){
    if(type){
        analysisSelection['type'] = type;
    }
    changeAnalysisSelection();
}

function changeDateInterval( interval ){
    var d = new Date();
    d.setDate(d.getDate() - interval);
    var st = getDateString(d);
    var et = getTodayDate();
    analysisSelection['startTime'] = st;
    analysisSelection['endTime'] = et;
    changeAnalysisSelection();
}

function getTodayDate(){
    var today = new Date();
    return getDateString(today);
}

function getDateString( d ){
    var dd = d.getDate();
    var mm = d.getMonth()+1; //January is 0!
    var yyyy = d.getFullYear();

    if(dd<10) {
        dd='0'+dd
    }

    if(mm<10) {
        mm='0'+mm
    }
    return yyyy+'-'+mm+'-'+dd;
}
