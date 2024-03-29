"use strict";

var table = $("#table").DataTable({
    pageLength: 10,
    processing: true,
    serverSide: true,
    responsive: true,
    order:[],
    lengthMenu: [
        [10, 25, 50, -1],
        [10, 25, 50, "Semua"],
    ],
    ajax: {
        url: "/transaction/asset-addition/asset-addition",
        type: "GET",
    },
    dom: '<"html5buttons">lBrtip',
    columns: [
        { data: "code" },
        { data: "date" },
        { data: "income.name" },
        { data: "branch.name" },
        { data: "cash.name" },
        { data: "price" },
        { data: "description" },
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
        text: "Aksi ini tidak dapat dikembalikan, dan akan menghapus data master Anda.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: "/transaction/asset-addition/asset-addition/" + id,
                type: "DELETE",
                success: function () {
                    swal("Data master berhasil dihapus", {
                        icon: "success",
                    });
                    table.draw();
                },
            });
        } else {
            swal("Data master Anda tidak jadi dihapus!");
        }
    });
}



function dropValueCost() {
    var costValue = $('.costValue').find(':selected').data('cost');
    // alert(costValue);
    $('#rupiah').val(parseInt(costValue).toLocaleString('en-US'));
}

function branchChange() {
    var dataItems = [];
    $('.cost').empty();
    
    var params = $('.branch').find(':selected').val();
    $.each($('.accountData'), function(){
        if (params == $(this).data('branch')) {
            dataItems += '<option value="'+this.value+'">'+$(this).data('name')+'</option>';
        }
    });
    $('.cost').append('<option value="">- Select -</option>');
    $('.cost').append(dataItems);
    // Reset Series
}

function jurnal(params) {
    $('.dropHereJournals').empty();
    // $('.dropHereJournals').
    $.ajax({
        url: "/transaction/asset-addition/check-journals",
        data: {id:params},
        type: 'POST',
        success: function(data) {

            if (data.status == 'success'){
                $.each(data.jurnal.journal_detail, function(index,value){
                    if (value.debet_kredit == 'K') {
                        var dk = '<td>0</td><td>'+parseInt(value.total).toLocaleString('en-US')+'</td>';
                    }else{
                        var dk = '<td>'+parseInt(value.total).toLocaleString('en-US')+'</td><td>0</td>';
                    }
                    $('.dropHereJournals').append(
                            '<tr>'+
                                '<td>'+value.account_data.code+'</td>'+
                                '<td>'+value.account_data.name+'</td>'+
                                dk+
                            '</tr>'
                    );
                });
            }
            $('#exampleModal').modal('show')

        },
    });
}