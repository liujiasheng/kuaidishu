/**
 * Created by Administrator on 14-8-24.
 */


function viewGoods(id){
    displayLoading(true);
    $.post("goodsMgr/SellerGoods/view",{id:id}, function(data){
        if(data.state){
            var goods = data.goods;
            $("#view-image").attr("src",getImageAddress(goods["Image"]));
            $("#view-seller").text(goods["SellerName"]);
            $("#view-mainClass").text(goods["MainClassName"]);
            $("#view-class").text(goods["ClassName"]);
            $("#view-name").text(goods["Name"]);
//            $("#view-price").text(goods["Price"] + "/" + goods["Unit"]);
            $("#view-state").text(getGoodsStateByNum(goods["State"]));

            $("#viewGoods").modal("show");
        }
    },"json").always(function(){
            displayLoading(false);
        });
}

function getImageAddress(filename){
    if(filename=="") return  "http://static.kuaidishu.com/goodsimg/medium/goods.jpg";
    return "http://static.kuaidishu.com/goodsimg/medium/" + filename;
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

//edit Goods Open
function editGoodsOpen(id){
    displayLoading(true);
    $.post("goodsMgr/SellerGoods/view",{id:id}, function(data){
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
function changeMainClassInEdit(callback){
    var mid = $("#edit-mainClassId").val();
    $("#edit-classId").attr("disabled","");
    $.get("/admin/goodsClass/goodsClass/getClass",{
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
function getGoodsRowHtml(goods){
    return "";
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
//edit Goods Submit
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
        url: 'goodsMgr/SellerGoods/edit',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success:function(data){
            if(data.state){
//                var row = getGoodsRowHtml({
//                    id : data.id,
//                    name: name,
//                    price: price,
//                    mainClassName: mainClassName,
//                    className: className,
//                    sellerName: sellerName,
//                    state:1
//                });
//                $("#tr-"+id).replaceWith(getGoodsRowHtml(aimGoods));
                $("#editGoods").modal("hide");
                location.reload();
            }else{
                showMessage($("#edit-message"),data.message);
            }
        }
    });
}

//edit Standard open
function editStandardOpen(goodsId){
    displayLoading(true);
    $('.standard-group').remove();
    showMessage($('#edit-standard-message'),"");
    $.post("goodsMgr/SellerGoods/getGoodsStandard",
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
    $.post("goodsMgr/SellerGoods/editGoodsStandard",{
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