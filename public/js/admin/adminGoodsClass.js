/**
 * Created by Administrator on 14-8-11.
 */

//------------- 添加主分类 -----------------//
function addMainClassOpen(superClassID){
    resetFormData();
    $('#add-superClassID').val(superClassID);
    $('#addMainClass').modal('show');
}

function checkMainClass(
    superClassId,
    name
    ){
    return true;
}

function addMainClass(){
    var superClassID = $('#add-superClassID').val();
    var name = $('#add-name').val();

    if(!checkMainClass(superClassID, name)) return;

    $.post("goodsClass/goodsClass/addMainClass",{
        superClassID : superClassID,
        name : name
    },function(data){
        if(data.state){
            var html = getNewMainClassHtml({
                superClassId : superClassID,
                id : data.id,
                name : name
            });
            $(html).insertBefore( $('#addMainClassPanel-'+superClassID) );
            $('#addMainClass').modal('hide');
            resetFormData();
        }else{
            showMessage($('#add-message'),data.message);
        }
    },"json").always(function(){

        });

}

//-------------- 编辑主分类 ---------------//
function editMainClassOpen(btn, superClassId, mainClassId){
    $('#edit-name').val(btn.prev().text().trim());
    $('#edit-superClassID').val(superClassId);
    $('#edit-mainClassID').val(mainClassId);
    $('#editMainClass').modal('show');
}
function editMainClass(){
    var name = $('#edit-name').val();
    var superClassId = $('#edit-superClassID').val();
    var mainClassId = $('#edit-mainClassID').val();
    if(!checkMainClass(mainClassId, name)) return;

    $.post("goodsClass/goodsClass/editMainClass",{
        superClassId : superClassId,
        id : mainClassId,
        name : name
    },function(data){
        if(data.state){
            $('#collapse-main-'+mainClassId).text(name);
            $('#editMainClass').modal('hide');
        }else{
            showMessage($('#edit-message'),data.message);
        }
    },"json").always(function(){

        });
}

//-------------- 添加子分类 --------------//
function addClassOpen(mainClassID){
    resetFormData();
    $('#addClass-mainClassID').val(mainClassID);
    $('#addClass').modal('show');
}

function checkClass(
    mainClassId,
    name
    ){
    return true;
}

function addClass(){
    var mainClassID = $('#addClass-mainClassID').val();
    var name = $('#addClass-name').val();
    if(!checkClass(mainClassID, name)) return;
    $.post('goodsClass/goodsClass/addClass',{
        mainClassId : mainClassID,
        name : name
    },function(data){
        if(data.state){
            var html = getNewClassHtml({
                mainClassId : mainClassID,
                id : data.id,
                name : name
            });
            $(html).insertBefore($('#addClass-'+mainClassID));
            $('#addClass').modal('hide');
        }else{
            showMessage($('#addClass-message'), data.message);
        }
    },"json").always(function(){

        });
}

//------------- 修改子分类 --------------//
function editClassOpen(btn, mainClassId, classId){
    $('#editClass-classID').val(classId);
    $('#editClass-name').val(btn.text().trim());
    $('#editClass').modal('show');
}
function editClass(){
    var classId = $('#editClass-classID').val();
    var name = $('#editClass-name').val();
    if(!checkClass(classId, name)) return;

    $.post("goodsClass/goodsClass/editClass",{
        id : classId,
        name : name
    },function(data){
        if(data.state){
            $('#class-'+classId).text(name);
            $('#editClass').modal('hide');
        }else{
            showMessage($('#editClass-message'), data.message);
        }
    },"json").always(function(){

        });
}


//------------- 清楚form数据 ------------//
function resetFormData(){
    //addMainClass Form
    $('#add-name').val("");
    $('#add-message').text("");

    //editMainClass Form
    $('#edit-message').text("");

    //addClass Form
    $('#addClass-name').val("");
    $('#addClass-message').text("");

    //editClass Form
    $('#editClass-message').text("");
}

// id , name , superClassID
function getNewMainClassHtml(data){
    return '<div class="panel panel-default">'+
        '<div class="panel-heading">'+
        '<h4 class="panel-title">'+
        '<a data-toggle="collapse" id="collapse-main-'+data["id"]+'" data-parent="#accordion-'+data["superClassId"]+'" href="#collapse-'+data["id"]+'" class="collapsed">'+ data["name"] + '</a>'+
        ' <a class="pull-right" href="###" onclick="editMainClassOpen($(this),'+data["superClassId"]+', '+data["id"]+')">编辑</a>'+
        '</h4>'+
        '</div>'+
        '<div id="collapse-'+data["id"]+'" class="panel-collapse collapse" style="height: 0px;">'+
        '<div class="panel-body">'+
        '    <button class="btn btn-default goods-class add-goods-class" id="addClass-'+data["id"]+'" onclick="addClassOpen('+data["id"]+')">'+
        '        <span class="glyphicon glyphicon-plus"></span> 添加子分类'+
        '    </button>'+
        '</div>'+
        '</div>'+
        '</div>';
}

//onclick="editClassOpen($(this), <?php echo $mainClassID?>, <?php echo $class['ClassID']?>)"
// id, name
function getNewClassHtml(data){
    return '<button class="btn btn-default goods-class" '+
        ' onclick="editClassOpen($(this),'+ data["mainClassId"] + ','+ data["id"] + ')"'+
        ' id="class-'+data["id"]+'">'+data["name"]+'</button>';
}