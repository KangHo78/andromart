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
        url: "/transaction/service/service-payment",
        type: "GET",
    },
    dom: '<"html5buttons">lBrtip',
    columns: [
        { data: "code" },
        { data: "created_by" },
        { data: "informationService" },
        { data: "dateData" },
        { data: "currentStatus" },
        { data: "description" },
        { data: "totalValue" },
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
        text: "Aksi ini tidak dapat dikembalikan, dan akan menghapus data pengguna Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "/transaction/service/service/" + id,
                type: "DELETE",
                success: function () {
                    swal("Data pengguna berhasil dihapus", {
                        icon: "success",
                    });
                    table.draw();
                },
            });
        } else {
            swal("Data pengguna Anda tidak jadi dihapus!");
        }
    });
}
function alertNotification(params) {
    return iziToast.warning({
        title: 'Peringatan',
        message: params,
        position: 'topRight',
    });
}
function save() {
    // alert('asd');
    var totalPrice = $('#totalPrice').val().replace(/,/g, ''),asANumber = +totalPrice;
    var totalPayment = $('#totalPayment').val().replace(/,/g, ''),asANumber = +totalPayment;
    var totalPriceHidden = $('#totalPriceHidden').val();
    var type = $('.type').val();
    var checkDp = $('#checkDpData').val();

    if (parseInt(totalPayment) > parseInt(totalPriceHidden)) {
        return alertNotification('Pembayaran Lebih Dari Sisa Pembayaran');
    }
    if ((checkDp != 0) && (type == 'DownPayment')) {
        return alertNotification('Sudah Pernah DP. Harus Pilih Pembayaran Lunas');
    }
    if ((checkDp != 0) && (type == 'Lunas') && (parseInt(totalPrice) != 0)) {
        return alertNotification('Sudah Pernah DP. Tidak Boleh ada sisa di total');
    }
    if (type == 'DownPayment') {
        if (parseInt(totalPrice) == 0) {
          return alertNotification('Pembayaran Tidak Boleh 0 karena Pembayaran DP');
        }
    }else{
        if (parseInt(totalPrice) != 0) {
          return alertNotification('Total Bayar Tidak Boleh sisa karena Pemabayaran Lunas');
        }
    }

    swal({
        title: "Apakah Anda Yakin?",
        text: "Aksi ini tidak dapat dikembalikan, dan akan menyimpan data Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willSave) => {
        if (willSave) {
            $.ajax({
                url: "/transaction/service/service-payment",
                data: $(".form-data").serialize(),
                type: 'POST',
                success: function(data) {
                    if (data.status == 'success'){
                        swal(data.message, {
                            icon: "success",
                        });
                        location.reload();
                    }
                },
                error: function(data) {
                    // edit(id);
                }
            });
            
        } else {
            swal("Data Dana Kredit PDL Berhasil Dihapus!");
        }
    });

    
}

function sumTotal() {
    if(isNaN(parseInt($('#totalSparePart').val()))){
        var totalSparePart =  0;
    }else{
        var totalSparePart = $('#totalSparePart').val().replace(/,/g, ''),asANumber = +totalSparePart;}
    if(isNaN(parseInt($('#totalService').val()))){
        var totalService =  0;
    }else{
        var totalService = $('#totalService').val().replace(/,/g, ''),asANumber = +totalService;}
    if(isNaN(parseInt($('#totalDownPayment').val()))){
        var totalDownPayment =  0;
    }else{
        var totalDownPayment = $('#totalDownPayment').val().replace(/,/g, ''),asANumber = +totalService;}
    if(isNaN(parseInt($('#totalPayment').val()))){
        var totalPayment =  0;
    }else{
        var totalPayment = $('#totalPayment').val().replace(/,/g, ''),asANumber = +totalPayment;}

    
    var sumTotal = parseInt(totalService)+parseInt(totalSparePart)-parseInt(totalDownPayment)-parseInt(totalPayment);
    if (sumTotal < 0) {
        $('#totalPrice').val(parseInt(0).toLocaleString()); 
    }else{
        $('#totalPrice').val(parseInt(sumTotal).toLocaleString()); 
    }
}



// fungsi update status
function choseService() {
    var serviceId = $('.serviceId').find(':selected').val();
    $('.activities').empty();
    $.ajax({
        url: "/transaction/service/service-form-update-status-load-data",
        data: {id:serviceId},
        type: 'POST',
        success: function(data) {
            if (data.status == 'success'){
                if(data.message == 'empty'){
                    $('.DownPaymentHidden').css('display','none');
                    $('#totalService').val(0);
                    $('#totalSparePart').val(0); 
                    $('#totalPriceHidden').val(0); 
                    $('#checkDpData').val(''); 
                    $('.dropHereItem').empty();
                }else{
                    $('#totalService').val(parseInt(data.result.total_service).toLocaleString());
                    $('#totalSparePart').val(parseInt(data.result.total_part).toLocaleString()); 
                    $('#totalDownPayment').val(parseInt(data.result.total_downpayment).toLocaleString()); 
                    $('#totalPriceHidden').val(data.result.total_service+data.result.total_part); 
                    $('#checkDpData').val(data.result.total_downpayment); 
                    if (data.result.downpayment_date != null){
                        $('.DownPaymentHidden').css('display','block');
                    }else{
                        $('.DownPaymentHidden').css('display','none');
                    }

                    $.each(data.result.service_detail, function(index,value){
                        $('.dropHereItem').append(
                                '<tr>'+
                                    '<td>'+value.items.name+'</td>'+
                                    '<td>'+parseInt(value.price).toLocaleString()+'</td>'+
                                    '<td>'+parseInt(value.qty).toLocaleString()+'</td>'+
                                    '<td>'+parseInt(value.total_price).toLocaleString()+'</td>'+
                                    '<td>'+value.description+'</td>'+
                                    '<td>'+value.type+'</td>'+
                                '</tr>'
                            );
                        });
                }
                sumTotal();
                
            }
        },
        error: function(data) {
        }
    });
}