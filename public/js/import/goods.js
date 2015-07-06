


function importGoods(){
    var sellerId = $('#import-sellerId').val();
    var xls = $('#import-xls')[0].files[0];
    var dir = $('#import-dir').val();

    var fd = new FormData();
    fd.append("sellerId",sellerId);
    fd.append("xls",xls);
    fd.append("dir",dir);

    $.ajax({
        url: 'goods/goodsImport/import',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success:function(data){
            if(data.state){
                alert(data.state);
            }else{
                alert(data.state + " " + data.message);
            }
        }
    });

}