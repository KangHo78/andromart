"use strict";

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

$("#item").on("change", function () {
    var idItem = this.value;
    $("#discount_value").addClass("d-none");
    $("#discount_percent").addClass("d-none");
    $.ajax({
        url: getdata,
        type: "GET",
        data: {
            item_id: idItem,
        },
        dataType: "json",
        success: function (data) {
            $("#saleDate").text(data.result.date);
            $("#qty").val(data.result.qty);
            $("#price").val(data.result.price);
            $("#total").val(data.result.total);
            $("#operator").val(data.result.operator);
            $("#sale_id").val(data.result.sale);
            $("#item_id_create").val(data.result.item);
            $("#sp_taker").val(data.result.sp_taker);
            $("#sp_seller").val(data.result.sp_seller);
            $("#taker").val(data.result.taker);
            $("#seller").val(data.result.seller);
            $("#faktur").val(data.result.faktur);
            if (data.result.discount_type == "percent") {
                $("#faktur").val(data.result.discount);
                $("#discount_percent").removeClass("d-none");
            } else {
                $("#faktur").val(data.result.discount);
                $("#discount_value").removeClass("d-none");
            }
        },
    });
});

function save() {
    var form = $("#stored");
    var formdata = new FormData(form[0]);
    $.ajax({
        url: url,
        data: formdata ? formdata : form.serialize(),
        type: "POST",
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status == "success") {
                swal(data.data, {
                    icon: "success",
                }).then(function () {
                    window.location = index;
                });
            } else if (data.status == "error") {
                for (var number in data.data) {
                    iziToast.error({
                        title: "Error",
                        message: data.data[number],
                    });
                }
            } else if (data.status == "service") {
                $("#exampleModal").modal("show");
            }
        },
    });
}

function returnType() {
    var form = $("#return");
    $("#sale").val($("#sale_id").val());
    $("#item_id").val($("#item_id_create").val());
    var formdata = new FormData(form[0]);
    $.ajax({
        url: returnURL,
        data: formdata ? formdata : form.serialize(),
        type: "POST",
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.status == "loss") {
                swal(data.data, {
                    icon: "info",
                }).then(function () {
                    window.location = service;
                });
            } else if (data.status == "new") {
                swal(data.data, {
                    icon: "info",
                }).then(function () {
                    window.location = index;
                });
            } else if (data.status == "money") {
                swal(data.data, {
                    icon: "info",
                }).then(function () {
                    window.location = index;
                });
            } else if (data.status == "att") {
                swal(data.data, {
                    icon: "info",
                }).then(function () {
                    window.location = buy;
                });
            } else if (data.status == "error") {
                for (var number in data.data) {
                    iziToast.error({
                        title: "Error",
                        message: data.data[number],
                    });
                }
            }
        },
    });
}
