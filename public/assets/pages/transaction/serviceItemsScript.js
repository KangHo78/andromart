
"use strict";

var table = $("#table").DataTable({
    pageLength: 10,
    processing: true,
    serverSide: true,
    responsive: true,
    lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "Semua"],
    ],
    ajax: {
        url: "/transaction/service/service-items",
        type: "GET",
    },
    dom: '<"html5buttons">lBrtip',
    columns: [
        // { data: "code" },
        { data: "Informasi" },
        { data: "dataBuyer" },
        { data: "dataItem" },
        { data: "finance" },
        { data: "currentStatus" },
        { data: "action", orderable: false, searchable: true },
    ],
    buttons: [
        {
            extend: "print",
            text: "Print Semua",
            exportOptions: {
                modifier: {
                    selected: null,
                },
                columns: ":visible",
            },
            messageTop: "Dokumen dikeluarkan tanggal " + moment().format("L"),
            // footer: true,
            header: true,
        },
        {
            extend: "csv",
        },
        {
            extend: "print",
            text: "Print Yang Dipilih",
            exportOptions: {
                columns: ":visible",
            },
        },
        {
            extend: "excelHtml5",
            exportOptions: {
                columns: ":visible",
            },
        },
        {
            extend: "pdfHtml5",
            exportOptions: {
                columns: [0, 1, 2, 5],
            },
        },
        {
            extend: "colvis",
            postfixButtons: ["colvisRestore"],
            text: "Sembunyikan Kolom",
        },
    ],
});

$(".filter_name").on("keyup", function () {
    table.search($(this).val()).draw();
});

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

function del(id) {
    swal({
        title: "Apakah Anda Yakin?",
        text: "Aksi ini tidak dapat dikembalikan, dan akan menghapus data Service Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "/transaction/service/service/" + id,
                type: "DELETE",
                success: function (data) {
                    if(data.status == 'success'){
                        swal(data.message, {
                            icon: "success",
                        });
                        table.draw();
                    }else if(data.status == 'restricted'){
                        swal(data.message, {
                            icon: "warning",
                        });
                    }else{
                        swal(data.message, {
                            icon: "error",
                        });
                    }
                },
            });
        } else {
            swal("Data pengguna Anda tidak jadi dihapus!");
        }
    });
}
var idSaved = '';
function save() {
    swal({
        title: "Apakah Anda Yakin?",
        text: "Aksi ini tidak dapat dikembalikan, dan akan menyimpan data Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willSave) => {
        if (willSave) {
            var validation = 0;
            $('.validation').each(function(){
                if ($(this).val() == '' || $(this).val() == null || $(this).val() == 0) {
                    validation++;
                    iziToast.warning({
                        type: 'warning',
                        title: $(this).data('name') +' Harus Di isi'
                    });
                }else{
                    validation-1;
                }
            });
            if (validation != 0) {
                return false;
            }
            $.ajax({
                url: "/transaction/service/service-items",
                data: $(".form-data").serialize(),
                type: 'POST',
                // contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status == 'success'){
                        swal(data.message, {
                            icon: "success",
                        });
                        $('.tombolSave').css('display','none')
                        $('.tombolPrint').css('display','block');
                        // location.reload();
                        idSaved = data.id;
                    }else{
                        swal(data.message, {
                            icon: "warning",
                        });
                    }
                },
                error: function(data) {
                    // edit(id);
                }
            });

        } else {
            swal("Data Belum Disimpan !");
        }
    });

}

function print(params) {
    window.open(params+'/transaction/service/service/'+idSaved);
}

function updateData(params) {
    swal({
        title: "Apakah Anda Yakin?",
        text: "Aksi ini tidak dapat dikembalikan, dan akan menyimpan data Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willSave) => {
        if (willSave) {
            var validation = 0;
            $('.validation').each(function(){
                if ($(this).val() == '' || $(this).val() == null || $(this).val() == 0) {
                    validation++;
                    iziToast.warning({
                        type: 'warning',
                        title: $(this).data('name') +' Harus Di isi'
                    });
                }else{
                    validation-1;
                }
            });
            if (validation != 0) {
                return false;
            }
            $.ajax({
                url: "/transaction/service/service/"+params,
                data: $(".form-data").serialize(),
                type: 'PUT',
                // contentType: false,
                processData: false,
                success: function(data) {
                    if (data.status == 'success'){
                        swal(data.message, {
                            icon: "success",
                        });
                        location.reload();
                    }else{
                        swal(data.message, {
                            icon: "warning",
                        });
                    }
                },
                error: function(data) {
                    // edit(id);
                }
            });

        } else {
            swal("Data Belum Disimpan !");
        }
    });

}


function changeDiscount(params) {
    if (params == 'percent') {
        $('#totalDiscountValue').css('pointer-events','none');
        $('#totalDiscountValue').css('background-color','#e9ecef');
        $('#totalDiscountPercent').css('pointer-events','auto');
        $('#totalDiscountPercent').css('background-color','#fff');
    }else{
        $('#totalDiscountPercent').css('pointer-events','none');
        $('#totalDiscountPercent').css('background-color','#e9ecef');
        $('#totalDiscountValue').css('pointer-events','auto');
        $('#totalDiscountValue').css('background-color','#fff');
    }
}

function category() {
    var dataItems = [];
    $('.brand').empty();

    var params = $('.type').find(':selected').val();
    $.each($('.brandData'), function(){
        if (params == $(this).data('category')) {
            dataItems += '<option value="'+this.value+'" >'+$(this).data('name')+'</option>';
        }
    });
    $('.brand').append('<option value="">- Select -</option>');
    $('.brand').append(dataItems);
    // Reset Series
    $('.series').empty();
    $('.series').append('<option value="">- Select -</option>');

    $("#brandService").select2({
        // allowClear: true,
        escapeMarkup: function (markup) {
            return markup;
        },
        placeholder: "- Select -",
        language: {
            noResults: function () {
                return "<a href='/master/brand/brand/create' target='_blank'>Tambah Merk</a>";
            },
        },
    });
}

$(document.body).on("change",".brand",function(){
    var dataItems = [];
    $('.items').empty();
    var params = $('.brand').find(':selected').val();
    // console.log(params);
    $.each($('.itemsData'), function(){
        // console.log($(this).data('brand'));
        if (params == $(this).data('brand')) {
            dataItems += '<option value="'+this.value+'" data-buy="'+$(this).data('buy')+'">'+$(this).data('name')+' - '+parseInt($(this).data('buy')).toLocaleString('en-US')+'</option>';
        }
    });
    // alert('asd');
    $('.items').append('<option value="">- Select -</option>');
    $('.items').append(dataItems);

    $("#itemsService").select2({
        allowClear: true,
        escapeMarkup: function (markup) {
            return markup;
        },
        placeholder: "- Select -",
        language: {
            noResults: function () {
                return "<a href='/master/type/type/create' target='_blank'>Tambah Seri</a>";
            },
        },
    });

});

$(document.body).on("change",".items",function(){
    
    var id = $('.items').find(':selected').val();
    var buy = $('.items').find(':selected').data('buy');
    console.log(buy);
    $('#totalPriceBuy').val(parseInt(buy).toLocaleString('en-US'));
    sumTotalSell();

    $.ajax({
        url: "/transaction/service/service-items/check-stock",
        data: { id: id },
        type: "POST",
        success: function (data) {
            if(data.status == 'success'){
                iziToast.success({
                    type: 'success',
                    title: 'Stock Ada'
                });
            }else{
                iziToast.warning({
                    type: 'warning',
                    title: 'Stock Item/Barang Kosong'
                });
            }
        }
    });
});



function addItem() {
    var index = $('.priceDetail').length;
    var dataDetail = $('.dataDetail').length;

    var dataItems = [];
    $.each($('.itemsData'), function(){
        if ($(this).data('stock') == null) {
            var stocks = 0;
        }else{
            var stocks = $(this).data('stock');
        }
        dataItems += '<option data-index="'+(index+1)+'" data-hpp="'+$(this).data('hpp')+'"  data-price="'+$(this).data('price')+'" data-stock="'+stocks+'" value="'+this.value+'">'+$(this).data('name')+'</option>';
    });

    $('.dropHereItem').append(
        '<tr class="dataDetail dataDetail_'+(dataDetail+1)+'">'+
            '<td style="display:none">'+
                '<input type="text" class="form-control priceDetailSparePart priceDetailSparePart_'+(index+1)+'" name="priceDetailSparePart[]" value="0">'+
                '<input type="text" class="form-control priceDetailLoss priceDetailLoss_'+(index+1)+'" name="priceDetailLoss[]" value="0">'+
            '</td>'+
            '<td>'+
            '<select class="select2 itemsDetail" name="itemsDetail[]">'+
                '<option value="-" data-index="'+(index+1)+'">- Select -</option>'+
                dataItems+
            '</select>'+
            '</td>'+
            '<td>'+
                '<input type="text" class="form-control cleaveNumeral priceDetail priceDetail_'+(index+1)+'" name="priceDetail[]" data-index="'+(index+1)+'" value="0" style="text-align: right">'+
                '<input type="hidden" class="form-control priceHpp priceHpp_'+(index+1)+'" name="priceHpp[]" value="0">'+
            '</td>'+
            '<td>'+
                '<input type="text" class="form-control qtyDetail qtyDetail_'+(index+1)+'" name="qtyDetail[]" data-index="'+(index+1)+'" value="1" style="text-align: right">'+
            '</td>'+
            '<td>'+
                '<input type="text" class="form-control stock stock_'+(index+1)+'" readonly name="stockDetail[]" data-index="'+(index+1)+'" value="0" style="text-align: right">'+
            '</td>'+
            '<td>'+
                '<input readonly type="text" class="form-control totalPriceDetail totalPriceDetail_'+(index+1)+'" name="totalPriceDetail[]" value="0" style="text-align: right">'+
                '<input readonly type="hidden" class="form-control totalPriceHpp totalPriceHpp_'+(index+1)+'" name="totalPriceHpp[]" value="0" style="text-align: right">'+
            '</td>'+
            '<td>'+
                '<input type="text" class="form-control" name="descriptionDetail[]">'+
            '</td>'+
            '<td>'+
                '<select class="form-control typeDetail typeDetail_'+(index+1)+'" name="typeDetail[]">'+
                    '<option selected data-index="'+(index+1)+'" value="SparePart">SparePart</option>'+
                    '<option data-index="'+(index+1)+'" value="Loss">Loss</option>'+
                '</select>'+
            '</td>'+
            '<td>'+
                '<button type="button" class="btn btn-danger removeDataDetail" value="'+(index+1)+'" >X</button>'+
            '</td>'+
        '</tr>'
    );
    $('.select2').select2();
    $(".cleaveNumeral")
    .toArray()
    .forEach(function (field) {
        new Cleave(field, {
            numeral: true,
            numeralThousandsGroupStyle: "thousand",
        });
    });

    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();

    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }
}

$(document.body).on("change",".lainnyaEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.lainnyaEquipmentDescUsed').css('display','block');
    }else{
        $('.lainnyaEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".chargerEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.chargerEquipmentDescUsed').css('display','block');
    }else{
        $('.chargerEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".bateraiEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.bateraiEquipmentDescUsed').css('display','block');
    }else{
        $('.bateraiEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".hardiskSsdEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.hardiskSsdEquipmentDescUsed').css('display','block');
    }else{
        $('.hardiskSsdEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".RamEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.RamEquipmentDescUsed').css('display','block');
    }else{
        $('.RamEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".aksesorisEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.aksesorisEquipmentDescUsed').css('display','block');
    }else{
        $('.aksesorisEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".kabelEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.kabelEquipmentDescUsed').css('display','block');
    }else{
        $('.kabelEquipmentDescUsed').css('display','none');
    }
});
$(document.body).on("change",".tasLaptopEquipment",function(){
    if ($(this).is(':checked') == true) {
        $('.tasLaptopEquipmentDescUsed').css('display','block');
    }else{
        $('.tasLaptopEquipmentDescUsed').css('display','none');
    }
});

function customerChange() {
     $('#customerName').val($('.customerId').find(':selected').data('name'));
     $('#customerPhone').val($('.customerId').find(':selected').data('phone'));
     $('#customerAdress').val($('.customerId').find(':selected').data('address'));
}

// mengganti item
$(document.body).on("change",".itemsDetail",function(){
    var index = $(this).find(':selected').data('index');
    // console.log($(this).val());
    // console.log(index);

    if ($(this).val() == '-') {
        $('.priceDetail_' + index).val(0);
        $('.stock_' + index).val(0);
        $('.qtyDetail_' + index).val(0);
        $('.totalPriceDetail_' + index).val(0);
        $('.priceDetailLoss_'+index).val(0);
        $('.priceDetailSparePart_'+index).val(0);
        $('.priceHpp_'+index).val(0);
    }else{
        var typeDetail = $('.typeDetail_' + index).find(':selected').val();
        if (isNaN(parseInt($(this).find(':selected').data('price')))) {
            var itemPrice = 0;
            var itemHpp = 0;
        } else {
            var itemPrice = $(this).find(':selected').data('price');
            var itemHpp = $(this).find(':selected').data('hpp');
        }
        if (isNaN(parseInt($('.qtyDetail_' + index).val()))) {
            var itemQty = 0;
        } else {
            var itemQty = $('.qtyDetail_' + index).val().replace(/,/g, ''), asANumber = +itemQty;
        }
        $('.priceDetail_' + index).val(parseInt(itemPrice).toLocaleString('en-US'));
        var totalItemPrice = itemPrice * itemQty;

        $('.priceHpp_' + index).val(parseInt(itemHpp).toLocaleString('en-US'));
        var totalItemHpp = itemHpp * itemQty;
        $('.stock_' + index).val($(this).find(':selected').data('stock'));
        $('.totalPriceDetail_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        if(typeDetail == 'SparePart'){
            $('.priceDetailSparePart_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
            $('.priceDetailLoss_'+index).val(0);
            $('.totalPriceHpp_'+index).val(totalItemHpp);
        }else{
            $('.priceDetailLoss_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
            $('.priceDetailSparePart_'+index).val(0);
            $('.totalPriceHpp_'+index).val(0);
        }
    }
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();
    var totalPriceHpp = 0;
    $('.totalPriceHpp').each(function(){
        totalPriceHpp += parseInt(this.value.replace(/,/g, ""))
    });
    $('#totalHppAtas').val(parseInt(totalPriceHpp).toLocaleString('en-US'));
    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }

});

// menghapus kolom
$(document.body).on("click",".removeDataDetail",function(){
    $('.dataDetail_'+this.value).remove();
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();

    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }
});

$(document.body).on("click",".removeDataDetailExisting",function(){
    $('.dropDeletedExistingData').append('<input type="hidden" class="form-control" value="'+$(this).data('id')+'" name="deletedExistingData[]">');
    $('.dataDetail_'+this.value).remove();
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();
    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }
});

// merubah qty
$(document.body).on("keyup",".qtyDetail",function(){
    var index = $(this).data('index');
    var typeDetail = $('.typeDetail_'+index).find(':selected').val();
    if(isNaN(parseInt($('.priceDetail_'+index).val()))){
        var itemPrice =  0;var itemHpp =0; }else{
        var itemPrice = $('.priceDetail_'+index).val().replace(/,/g, ''),asANumber = +itemPrice;
        var itemHpp = $('.priceHpp_'+index).val().replace(/,/g, ''),asANumber = +itemHpp;}
    if(isNaN(parseInt(this.value))){
        var itemQty =  0; }else{
        var itemQty = this.value.replace(/,/g, ''),asANumber = +itemQty;}
    var totalItemPrice = itemPrice*itemQty;
    var totalItemHpp = itemHpp*itemQty;
    $('.totalPriceDetail_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
    if(typeDetail == 'SparePart'){
        $('.priceDetailSparePart_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailLoss_'+index).val(0);
        $('.totalPriceHpp_'+index).val(parseInt(totalItemHpp).toLocaleString('en-US'));
    }else{
        $('.priceDetailLoss_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailSparePart_'+index).val(0);
        $('.totalPriceHpp_'+index).val(0);
    }
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();
    var totalPriceHpp = 0;
    $('.totalPriceHpp').each(function(){
        totalPriceHpp += parseInt(this.value.replace(/,/g, ""))
    });
    console.log(typeDetail);
    $('#totalHppAtas').val(parseInt(totalPriceHpp).toLocaleString('en-US'));
    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }


});

// merubah harga
$(document.body).on("keyup",".priceDetail",function(){
    var index = $(this).data('index');
    var typeDetail = $('.typeDetail_'+index).find(':selected').val();
    if(isNaN(parseInt(this.value))){
        var itemPrice =  0; }else{
        var itemPrice = this.value.replace(/,/g, ''),asANumber = +itemPrice;}
    if(isNaN(parseInt($('.qtyDetail_'+index).val()))){
        var itemQty =  0; }else{
        var itemQty = $('.qtyDetail_'+index).val().replace(/,/g, ''),asANumber = +itemQty;}
    var totalItemPrice = itemPrice*itemQty;
    $('.totalPriceDetail_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
    if(typeDetail == 'SparePart'){
        $('.priceDetailSparePart_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailLoss_'+index).val(0);
    }else{
        $('.priceDetailLoss_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailSparePart_'+index).val(0);
    }
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();

    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }

});

// merubah harga jasa
$(document.body).on("keyup",".priceServiceDetail",function(){
    $('.totalPriceServiceDetail').val(this.value);
    $('#totalService').val(this.value);
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();

    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }
});

// fungsi sum
function sum() {
    var priceDetailSparePart = 0;
    $('.priceDetailSparePart').each(function () {
        priceDetailSparePart += parseInt(this.value.replace(/,/g, ""));
    });
    $('#totalSparePart').val(parseInt(priceDetailSparePart).toLocaleString('en-US'));
    var priceDetailLoss = 0;
    $('.priceDetailLoss').each(function(){
        priceDetailLoss += parseInt(this.value.replace(/,/g, ""))
    });
    $('#totalLoss').val(parseInt(priceDetailLoss).toLocaleString('en-US'));
    var totalPriceHpp = 0;
    $('.totalPriceHpp').each(function(){
        totalPriceHpp += parseInt(this.value.replace(/,/g, ""))
    });
    $('#totalHppAtas').val(parseInt(totalPriceHpp).toLocaleString('en-US'));
}

// fungsi rubah tipe
$(document.body).on("change",".typeDetail",function(){
    var value = this.value;
    var index = $(this).find(':selected').data('index');
    if(isNaN(parseInt($('.priceDetail_'+index).val()))){
        var itemPrice =  0; }else{
        var itemPrice = $('.priceDetail_'+index).val().replace(/,/g, ''),asANumber = +itemPrice;}
    if(isNaN(parseInt($('.qtyDetail_'+index).val()))){
        var itemQty =  0; }else{
        var itemQty = $('.qtyDetail_'+index).val().replace(/,/g, ''),asANumber = +itemQty;}
    if(isNaN(parseInt($('.priceHpp_'+index).val()))){
        var itemHpp =  0; }else{
        var itemHpp = $('.priceHpp_'+index).val().replace(/,/g, ''),asANumber = +itemHpp;}
    var totalItemPrice = itemPrice*itemQty;
    var totalItemHpp = itemHpp*itemQty;
    if(value == 'SparePart'){
        $('.priceDetailSparePart_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailLoss_' + index).val(0);
        $('.totalPriceHpp_'+index).val(totalItemHpp);
    }else{
        $('.priceDetailLoss_'+index).val(parseInt(totalItemPrice).toLocaleString('en-US'));
        $('.priceDetailSparePart_'+index).val(0);
        $('.totalPriceHpp_'+index).val(0);
    }
    var checkVerificationDiscount =  $('input[name="typeDiscount"]:checked').val();
    // mengecek HPP jika loss
    sum();
    sumTotal();
    if (checkVerificationDiscount == 'percent') {
        sumDiscont();
    }else{
        sumDiscontValue();
    }
});

function sumDiscont() {
    if(isNaN(parseInt($('#totalSparePart').val()))){
        var totalSparePart =  0;
    }else{
        var totalSparePart = $('#totalSparePart').val().replace(/,/g, ''),asANumber = +totalSparePart;}

    if(isNaN(parseInt($('#totalService').val()))){
        var totalService =  0;
    }else{
        var totalService = $('#totalService').val().replace(/,/g, ''),asANumber = +totalService;}

    if(isNaN(parseInt($('#totalDiscountPercent').val()))){
        var totalDiscountPercent =  0;
    }else{
        var totalDiscountPercent = $('#totalDiscountPercent').val().replace(/,/g, ''),asANumber = +totalDiscountPercent;}

    if(totalDiscountPercent <= 100){
        var sumTotalPrice = (parseInt(totalDiscountPercent)/100)*(parseInt(totalService));
        $('#totalDiscountValue').val(parseInt(sumTotalPrice).toLocaleString('en-US'));
        $('#totalDiscountPercent').val(totalDiscountPercent);
    }else{
        $('#totalDiscountPercent').val(0);
        $('#totalDiscountValue').val(0);
        var sumTotalPrice = (100/100)*(parseInt(totalService));}
    sumTotal();
}

function sumDiscontValue() {
    if(isNaN(parseInt($('#totalSparePart').val()))){
        var totalSparePart =  0;
    }else{
        var totalSparePart = $('#totalSparePart').val().replace(/,/g, ''),asANumber = +totalSparePart;}
    if(isNaN(parseInt($('#totalService').val()))){
        var totalService =  0;
    }else{
        var totalService = $('#totalService').val().replace(/,/g, ''),asANumber = +totalService;}

    if(isNaN(parseInt($('#totalDiscountValue').val()))){
        var totalDiscountValue =  0;
    }else{
        var totalDiscountValue = $('#totalDiscountValue').val().replace(/,/g, ''),asANumber = +totalDiscountValue;}
        var totalValue = parseInt(totalService);

        if(totalDiscountValue <= totalValue){
            console.log(totalDiscountValue);
            console.log(totalValue);
            var sumTotalPrice = (parseInt(totalDiscountValue)/totalValue)*100;
        }else{
            var sumTotalPrice = 100;
        }
    if (isNaN(parseInt(sumTotalPrice))) {
        $('#totalDiscountPercent').val(0);
    }else{
        $('#totalDiscountPercent').val(parseFloat(sumTotalPrice).toLocaleString('en-US'));
    }
    sumTotal();
}

function sumTotal() {
    var checkVerificationPrice =  $('input[name="verificationPrice"]:checked').val();

    if(isNaN(parseInt($('#totalSparePart').val()))){
        var totalSparePart =  0;
    }else{
        var totalSparePart = $('#totalSparePart').val().replace(/,/g, ''),asANumber = +totalSparePart;}

    if(isNaN(parseInt($('#totalService').val()))){
        var totalService =  0;
    }else{
        var totalService = $('#totalService').val().replace(/,/g, ''),asANumber = +totalService;}

    if(isNaN(parseInt($('#totalDiscountValue').val()))){
        var totalDiscountValue =  0;
    }else{
        var totalDiscountValue = $('#totalDiscountValue').val().replace(/,/g, ''),asANumber = +totalDiscountValue;}

    if(checkVerificationPrice == 'Y'){
        var sumTotal = 0;
    }else{
        var sumTotal = parseInt(totalService)+parseInt(totalSparePart)-parseInt(totalDiscountValue);}

    var totalValue = parseInt(totalService)+parseInt(totalSparePart);

    if (totalDiscountValue <= totalValue) {
        $('#totalPrice').val(parseInt(sumTotal).toLocaleString('en-US'));
    }else{
        $('#totalPrice').val(totalValue);
        $('#totalDiscountValue').val(0);
    }
}


function sumTotalSell(){
    var checkVerificationPrice =  $('input[name="verificationPrice"]:checked').val();

    if(isNaN(parseInt($('#totalSparePart').val()))){
        var totalSparePart =  0;
    }else{
        var totalSparePart = $('#totalSparePart').val().replace(/,/g, ''),asANumber = +totalSparePart;}

    if(isNaN(parseInt($('#totalService').val()))){
        var totalService =  0;
    }else{
        var totalService = $('#totalService').val().replace(/,/g, ''),asANumber = +totalService;}

    if(isNaN(parseInt($('#totalDiscountValue').val()))){
        var totalDiscountValue =  0;
    }else{
        var totalDiscountValue = $('#totalDiscountValue').val().replace(/,/g, ''),asANumber = +totalDiscountValue;}

    if(checkVerificationPrice == 'Y'){
        var sumTotal = 0;
    }else{
        var sumTotal = parseInt(totalService)+parseInt(totalSparePart)-parseInt(totalDiscountValue);}

    var totalValue = parseInt(totalService)+parseInt(totalSparePart);

    if (totalDiscountValue <= totalValue) {
        $('#totalPrice').val(parseInt(sumTotal).toLocaleString('en-US'));
    }else{
        $('#totalPrice').val(totalValue);
        $('#totalDiscountValue').val(0);
    }
}


// fungsi update status
function checkPriceServiceItems() {
    var serviceId = $('#seriesService').find(':selected').val();
    $('.activities').empty();
    $.ajax({
        url: "/transaction/service/service-items/check-price-service-items",
        data: {id:serviceId},
        type: 'POST',
        success: function(data) {
            if (data.status == 'success'){
                if(data.message == 'empty'){
                    alert(data);
                }else{
                    alert(data);
                }
            }
        },
        error: function(data) {
        }
    });
}


function jurnal(params) {
    // $('.dropHereJournals').
    $.ajax({
        url: "/transaction/service/service-items/check-journals",
        data: { id: params },
        type: "POST",
        success: function (data) {
            if (data.status == "success") {
                $(".dropHereJournals").empty();

                $.each(data.jurnal[0].journal_detail, function (index, value) {
                    if (value.debet_kredit == "K") {
                        var dk =
                            "<td>0</td><td>" +
                            parseInt(value.total).toLocaleString("en-US") +
                            "</td>";
                    } else {
                        var dk =
                            "<td>" +
                            parseInt(value.total).toLocaleString("en-US") +
                            "</td><td>0</td>";
                    }
                    $(".dropHereJournals").append(
                        "<tr>" +
                            "<td>" +
                            value.account_data.code +
                            "</td>" +
                            "<td>" +
                            value.account_data.name +
                            "</td>" +
                            dk +
                            "</tr>"
                    );
                });
            }
            $(".exampleModal").modal("show");
        },
    });
}


// mengecek stock barang

