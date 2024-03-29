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
        url: "/master/activa-group/activa-group",
        type: "GET",
    },
    dom: '<"html5buttons">lBrtip',
    columns: [
        { data: "DT_RowIndex", searchable: false },
        { data: "name" },
        { data: "estimate_age" },
        { data: "depreciation_rate" },
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
                url: "/master/activa-group/activa-group/" + id,
                type: "DELETE",
                success: function (data) {
                    if (data.status == 'success') {
                        swal(data.message, {
                            icon: "success",
                        });
                        table.draw();
                    }else if (data.status == 'restricted') {
                        swal(data.message, {
                            icon: "warning",
                        });
                    }else {
                        swal(data.message, {
                            icon: "error"
                        });
                    }
                },
            });
        } else {
            swal("Data master Anda tidak jadi dihapus!");
        }
    });
}

function calc(params) {
    var estimasi_umur = $('#estimate_age').val();
    var persen = 100 / estimasi_umur;
    persen == 'Infinity' ? $('#depreciation_rate').val('100.00') : $('#depreciation_rate').val(parseFloat(persen).toFixed(2));
}
