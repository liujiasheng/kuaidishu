/**
 * Created by Administrator on 14-7-11.
 */

function changeMainClass(){
    var mid = $("#mainClass-select").val();
    $("#class-select").attr("disabled","");
    $.get("goodsClass/goodsClass/getClass",{
        "id" : mid
    },function(data){
        if(data && data.state){
            var classTable = data.classTable;
            $("#class-select").children().remove();
            $("#class-select").prepend('<option value="0">全部</option>' + getClassTableHtml(classTable));
        }
    },"json")
        .always(function(){
            $("#class-select").removeAttr("disabled");
        });

    adminUserSelect["mainClassId"] = mid;
    adminUserSelect["classId"] = 0;
    adminUserSelect["curPage"] = 1;
    adminUserSelect["pageCount"] = 10;
    changeTable();
    $("#curPage").text("1");
}
function changeMainClassInAdd(){
    var mid = $("#reg-mainClassId").val();
    $("#reg-classId").attr("disabled","");
    $.get("goodsClass/goodsClass/getClass",{
        "id" : mid
    },function(data){
        if(data && data.state){
            var classTable = data.classTable;
            $("#reg-classId").children().remove();
            $("#reg-classId").prepend(getClassTableHtml(classTable));
        }
    },"json")
        .always(function(){
            $("#reg-classId").removeAttr("disabled");
        });
}
function changeMainClassInEdit(callback){
    var mid = $("#edit-mainClassId").val();
    $("#edit-classId").attr("disabled","");
    $.get("goodsClass/goodsClass/getClass",{
        "id" : mid
    },function(data){
        if(data && data.state){
            var classTable = data.classTable;
            $("#edit-classId").children().remove();
            $("#edit-classId").prepend(getClassTableHtml(classTable));
        }
    },"json")
        .always(function(){
            $("#edit-classId").removeAttr("disabled");
            callback();
        });

}

function viewGoods(id){
    displayLoading(true);
    $.post("goods/goods/view",{id:id}, function(data){
        if(data.state){
            var goods = data.goods;
            $("#view-image").attr("src",getImageAddress(goods["Image"]));
            $("#view-seller").text(goods["SellerName"]);
            $("#view-mainClass").text(goods["MainClassName"]);
            $("#view-class").text(goods["ClassName"]);
            $("#view-name").text(goods["Name"]);
            $("#view-price").text(goods["Price"] + "/" + goods["Unit"]);
            $("#view-state").text(getGoodsStateByNum(goods["State"]));

            $("#viewGoods").modal("show");
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function editGoodsOpen(id){
    flushFormValues();
    displayLoading(true);
    $.post("goods/goods/view",{id:id}, function(data){
        if(data.state){
            var goods = data.goods;
            $("#edit-id").val(id);
            $("#edit-sellerId").val(goods["SellerID"]);
            $("#edit-seller").val(goods["SellerName"]);
            $("#edit-mainClassId").val(goods["MainClassID"]);
            changeMainClassInEdit(function(){
                $("#edit-classId").val(goods["ClassID"]);
            });
            $("#edit-name").val(goods["Name"]);
            $("#edit-img").attr("src",getImageAddress(goods["Image"]));
//            $("#edit-price").val(goods["Price"]);
            $("#edit-unit").val(goods["Unit"]);
            $("#edit-state").val(goods["State"]);

            $("#editGoods").modal("show");
        }
    },"json").always(function(){
            displayLoading(false);
        });
}



function checkAddGoods(
    sellerId,
    mainClassId,
    classId,
    name,
    price,
    image,
    standard
    ){
    var msgObj = $("#reg-message");
    if(!jQuery.isNumeric(sellerId) ||
        !jQuery.isNumeric(mainClassId)||
        !jQuery.isNumeric(classId)){
        showMessage(msgObj, "* 参数错误");
        return false;
    }
    if(mainClassId=="0"){
        showMessage(msgObj, "* 请选择主分类");
        return false;
    }
    if(!checkGoodsName(name)){
        showMessage(msgObj, "* 商品名称格式错误");
        return false;
    }
    if(image==null){
        showMessage(msgObj, "* 图片不能为空");
        return false;
    }
    if(image!=null && image["size"]>1024*1024*8){
        showMessage(msgObj, "* 图片最大为8M");
        return false;
    }
    if(!jQuery.isNumeric(price)){
        showMessage(msgObj, "* 商品价格格式错误");
        return false;
    }
    if(!checkGoodsStandard(standard)){
        showMessage(msgObj, "* 商品规格格式错误");
        return false;
    }
    return true;
}

function addGoods(){
    //get front data
    var sellerId = $("#reg-sellerId").val();
    var mainClassId = $("#reg-mainClassId").val();
    var classId  = $("#reg-classId").val();
    var mainClassName = $("#reg-mainClassId option[value="+mainClassId+"]").text();
    var className = $("#reg-classId option[value="+classId+"]").text();
    var sellerName = $("#reg-sellerId option[value="+sellerId+"]").text();
    var name = $("#reg-name").val();
    var price = $("#reg-price").val();
    var unit = $("#reg-unit").val();
    var image = $("#reg-image")[0].files[0];
//    var standard = $('#reg-standard').val();
    var standard = "默认";

    if(!checkAddGoods(
        sellerId,
        mainClassId,
        classId,
        name,
        price,
        image,
        standard
    )) return;

    var fd = new FormData();
    fd.append("sellerId",sellerId);
    fd.append("mainClassId",mainClassId);
    fd.append("classId",classId);
    fd.append("name",name);
    fd.append("price",price);
    fd.append("unit",unit);
    fd.append("comment","");
    fd.append("barcode","");
    fd.append("remain",100);
    fd.append("state",1);
    fd.append("image",image);
    fd.append("standard",standard);

    $.ajax({
        url: 'goods/goods/addGoods',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success:function(data){
            if(data.state){
                var row = getGoodsRowHtml({
                    id : data.id,
                    name: name,
                    price: price,
                    mainClassName: mainClassName,
                    className: className,
                    sellerName: sellerName,
                    state:1
                });
                $("#goodsTable").prepend(row);
                showMessage($("#reg-message"),"");
                $("#addGoods").modal("hide");
            }else{
                showMessage($("#reg-message"),data.message);
            }
        }
    });
}

function checkEditGoods(
    id,
    sellerId,
    mainClassId,
    classId,
    name,
    price,
    state,
    image
    ){
    var msgObj = $("#edit-message");
    if(!jQuery.isNumeric(id) ||
        !jQuery.isNumeric(sellerId) ||
        !jQuery.isNumeric(mainClassId)||
        !jQuery.isNumeric(classId)){
        showMessage(msgObj, "* 参数错误");
        return false;
    }
    if(!checkGoodsName(name)){
        showMessage(msgObj, "* 商品名称格式错误");
        return false;
    }
    if(image!=null && image["size"]>1024*1024*8){
        showMessage(msgObj, "* 图片最大为8M");
        return false;
    }
    if(!jQuery.isNumeric(price)){
        showMessage(msgObj, "* 商品价格格式错误");
        return false;
    }
    return true;
}
function editGoods(){
    var id = $("#edit-id").val();
    var sellerId = $("#edit-sellerId").val();
    var mainClassId = $("#edit-mainClassId").val();
    var classId = $("#edit-classId").val();
    var name = $("#edit-name").val();
//    var price = $("#edit-price").val();
    var price = 100; // unused price
    var unit = $("#edit-unit").val();
    var state = $("#edit-state").val();
    var image = $("#edit-image")[0].files[0];

    var mainClassName = $("#edit-mainClassId option[value="+mainClassId+"]").text();
    var className = $("#edit-classId option[value="+classId+"]").text();
    var sellerName = $("#edit-seller").val();

    if(!checkEditGoods(
        id,
        sellerId,
        mainClassId,
        classId,
        name,
        price,
        state,
        image
    )) return;

    var aimGoods = {
        id : id,
        name: name,
        price: price,
        mainClassName: mainClassName,
        className: className,
        sellerName: sellerName,
        state:state
    };

    var fd = new FormData();
    fd.append("id",id);
    fd.append("sellerId",sellerId);
    fd.append("mainClassId",mainClassId);
    fd.append("classId",classId);
    fd.append("name",name);
    fd.append("price",price);
    fd.append("unit",unit);
    fd.append("comment","");
    fd.append("barcode","");
    fd.append("remain",100);
    fd.append("state",state);
    fd.append("image",image);

    $.ajax({
        url: 'goods/goods/editGoods',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success:function(data){
            if(data.state){
                var row = getGoodsRowHtml({
                    id : data.id,
                    name: name,
                    price: price,
                    mainClassName: mainClassName,
                    className: className,
                    sellerName: sellerName,
                    state:1
                });
                $("#tr-"+id).replaceWith(getGoodsRowHtml(aimGoods));
                $("#editGoods").modal("hide");
            }else{
                showMessage($("#edit-message"),data.message);
            }
        }
    });
}

function getGoodsRowHtml(goods){
    return '<tr id="tr-' + goods["id"] + '">'+
        '<td><input id="check-' + goods["id"] + '" type="checkbox"></td>' +
        '<td>' + goods["name"] + '</td>' +
//        '<td>' + goods["price"] + '</td>' +
        '<td>' + goods["mainClassName"]+'-'+goods["className"] + '</td>' +
        '<td>' + goods["sellerName"] + '</td>' +
        '<td>' + getGoodsStateByNum(goods["state"]) + '</td>' +
        '<td><a href="#" onclick="viewGoods(' + goods["id"] + ')">查看</a> ' +
        '<a href="#" onclick="editGoodsOpen(' + goods["id"] + ')">编辑</a> '+
        '<a href="#" onclick="editStandardOpen('+ goods["id"] +')">规格</a>'+
        '</td></tr>';
}

function getGoodsStateByNum(num){
    num = num.toString();
    var state = "";
    switch (num){
        case "1" : state = "在售"; break;
        case "2" : state = "售罄"; break;
        default  : state = "未知";break;
    }
    return state;
}

function getClassTableHtml(classTable){
    var html = '';
    if( classTable.length > 0 ){
        for(var i=0;i<classTable.length;i++){
            html += '<option value="'+ classTable[i]["id"] +'">'+ classTable[i]["name"] +'</option>'
        }
    }
    return html;
}

var adminUserSelect = {
    "state" : 0,
    "searchText" : "",
    "curPage" : 1,
    "pageCount" : 10,
    "sellerId" : 0,
    "mainClassId" : 0,
    "classId" : 0
};

function changeTable(){
    displayLoading(true);
    $.post("goods/goods/search",adminUserSelect,function(data){
        if(data.count > 0){
            $("#goodsTable").children().remove();
            var newTable = '';
            var goodsTable = data.goodsTable;
            for(var k = 0; k<goodsTable.length; k++){
                newTable += getGoodsRowHtml(goodsTable[k]);
            }
            $("#goodsTable").prepend(newTable);
            var pages = Math.ceil(data.count/10);
            $("#allPages").text(pages.toString());
            flushEmptyResultHtml();
        }else{
            $("#goodsTable").children().remove();
            $("#allPages").text("1");
            $("table").after(getEmptyResultHtml());
        }
    },"json").always(function(){
            displayLoading(false);
        });

}

function changeSeller(){
    var sellerId = $("#seller-select").val();
    adminUserSelect["sellerId"] = sellerId;
    adminUserSelect["curPage"] = 1;
    adminUserSelect["pageCount"] = 10;
    changeTable();
    $("#curPage").text("1");
}

function changeClass(){
    var classId = $("#class-select").val();
    adminUserSelect["classId"] = classId;
    adminUserSelect["curPage"] = 1;
    adminUserSelect["pageCount"] = 10;
    changeTable();
    $("#curPage").text("1");
}

function imageChanged(input, imgObj){
    if (input.files && input.files[0])
    {
        var reader = new FileReader();
        reader.onload = function (e)
        {
            imgObj.parent().parent().show();
            imgObj.attr("src", e.target.result);
        };
        reader.readAsDataURL(input.files[0]);
    }
}


function getImageAddress(filename){
    if(filename=="") return  "http://static.kuaidishu.com/goodsimg/medium/goods.jpg";
    return "http://static.kuaidishu.com/goodsimg/medium/" + filename;
}


function flushFormValues(){
    $("#reg-sellerId").val("");
//    $("#reg-mainClassId").val("");
//    $("#reg-classId").val("");
    $("#reg-name").val("");
    $("#reg-showImg").hide();
    $("#reg-image").val("");
    $("#reg-price").val("");
    $("#reg-message").text("");

    $("#edit-image").val("");
    $("#edit-message").text("");
}


//---------------多规格编辑----------------//
//data: id standard price state
function getStandardHtml(data){
    var html = ' <div class="form-group standard-group"> '+
        '<input type="text" class="form-control standard-id" style="display: none" value="'+data["id"]+'"/>'+
        '<div class="col-sm-4"> '+
        '    <input type="text" class="form-control standard-standard" value="'+data["standard"]+'"/>'+
        '</div>'+
        '<div class="col-sm-4">'+
        '    <input type="text" class="form-control standard-price" value="'+data["price"]+'"/>'+
        '</div>'+
        '<div class="col-sm-3">'+
        '    <select class="form-control standard-state">'+
        '        <option value="1"'+(data["state"]=="1"?"selected":"")+'>在售</option>'+
        '        <option value="2"'+(data["state"]=="2"?"selected":"")+'>售罄</option>'+
        '    </select>'+
        '</div>'+
        '<div class="col-sm-1">'+
        '    <button type="button" class="close" onclick="removeStandard($(this))"><span aria-hidden="true">&times;</span></button>'+
        '</div>'+
        '</div>';
    return html;
}

function addBlankStandard(){
    var blank = getStandardHtml({
        standard:"",
        price:"",
        state:"1"
    });
    pushBeforeStandardAdd(blank);
}

function pushBeforeStandardAdd(html){
    $(html).insertBefore($('#standard-add'));
}

function removeStandard(btn){
    //非最后一个规格
    if($('.standard-group').length > 1){
        btn.parent().parent().remove();
    }
}

//edit Standard open
function editStandardOpen(goodsId){
    displayLoading(true);
    $('.standard-group').remove();
    showMessage($('#edit-standard-message'),"");
    $.post("goods/goods/getGoodsStandard",
        {id:goodsId},
        function(data){
        if(data.state){
            var rowsHtml = "";
            var standards = data.standards;
            if( !standards || standards.length<1 ) return;
            for(var i=0; i<standards.length; i++){
                rowsHtml += getStandardHtml({
                    id: standards[i]["ID"],
                    standard: standards[i]["Standard"],
                    price: standards[i]["Price"],
                    state: standards[i]["State"]
                });
            }
            pushBeforeStandardAdd(rowsHtml);
            $('#edit-standard-name').text(data.goods["name"]);
            $('#edit-standard-id').val(goodsId);
            $('#editStandard').modal('show');
        }
    },"json").always(function(){
            displayLoading(false);
        });

}

function getStandards(){
    var standards = [];
//    $($('.standard-group')[0]).children().children('.standard-price').val()
    $('.standard-group').each(function(){
        var id = $(this).children('.standard-id').val();
        var standard = $(this).children().children('.standard-standard').val();
        var price = $(this).children().children('.standard-price').val();
        var state = $(this).children().children('.standard-state').val();
        standards.push({
            id: id,
            standard: standard,
            price: price,
            state: state
        });
    });
    return standards;
}

function editStandardSubmit(){
    var goodsId = $('#edit-standard-id').val();
    var standards = getStandards();
    $.post("goods/goods/editGoodsStandard",{
            goodsId: goodsId,
            standards: standards
        },
        function(data){
            if(data.state){
                $('#editStandard').modal('hide');
            }else{
                showMessage($('#edit-standard-message'),data.message);
            }
        },"json");
}

//---------------多规格编辑 End----------------//


