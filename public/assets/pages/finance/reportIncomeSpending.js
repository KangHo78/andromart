$(".filter_name").on("keyup", function () {
    table.search($(this).val()).draw();
});

$.ajaxSetup({
    headers: {
        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
});

// fungsi update status
function checkData() {
    var dateS = $("#startDate").val();
    var dateE = $("#endDate").val();
    var tipe = $(".tipe").val();
    var cabang = $(".cabang").val();
    // $('.dropHereTotal').text(0);
    // $('.dropHereTotalVal').val(0);
    var jurnalDetailD = [];
    var jurnalDetailK = [];
    var jurnalDetailTransaksi = [];

    $(".dropHere").empty();
    $.ajax({
        url: "/finance/report/search-report-income-spending",
        data: { dateS: dateS, dateE: dateE, tipe: tipe, cabang: cabang },
        type: "POST",
        success: function (data) {
            if (data.status == "success") {
                $(".dropHere").empty();
                if (data.message == "empty") {
                    $(".dropHere").empty();
                } else {
                    var totalPengeluaran = 0;
                    var totalPendapatan = 0;
                    $.each(data.result, function (index, value) {
                        if (data.tipe == "Pengeluaran") {
                            $.each(
                                value.journal_detail,
                                function (index1, value1) {
                                    if (
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 29 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 12 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 28 ||
                                                    value.type == 'Transfer Masuk'     ||
                                                    value.ref.includes('SMT')          ||
                                        // value.ref.includes('PCS') ||
                                        value.ref.includes('SIN') ||
                                        value.ref.includes('SOT') 
 
                                    ) {
                                    } else {
                                        if (data.cabang == null) {
                                            jurnalDetailTransaksi[index] =
                                                "<b>" +
                                                value.journal_detail[0]
                                                    .account_data.code +
                                                "</b><br>" +
                                                value.journal_detail[0]
                                                    .account_data.name;
                                            if (value.code.includes("DD")) {
                                                jurnalDetailD[index] = " Rp. 0";
                                            } else {
                                                jurnalDetailK[index] =
                                                    " Rp. " +
                                                    parseInt(
                                                        value.journal_detail[0]
                                                            .total
                                                    ).toLocaleString("en-US");
                                            }
                                        } else {
                                            if (
                                                data.cabang ==
                                                value.journal_detail[0]
                                                    .account_data.branch_id
                                            ) {
                                                jurnalDetailTransaksi[index] =
                                                    "<b>" +
                                                    value.journal_detail[0]
                                                        .account_data.code +
                                                    "</b><br>" +
                                                    value.journal_detail[0]
                                                        .account_data.name;
                                                if (value.code.includes("DD")) {
                                                    jurnalDetailD[index] =
                                                        " Rp. 0";
                                                } else {
                                                    jurnalDetailK[index] =
                                                        " Rp. " +
                                                        parseInt(
                                                            value
                                                                .journal_detail[0]
                                                                .total
                                                        ).toLocaleString(
                                                            "en-US"
                                                        );
                                                }
                                            }
                                        }
                                    }
                                }
                            );
                            if (jurnalDetailK[index] == undefined) {
                                var jurnalDetailKReal = "";
                            } else {
                                var jurnalDetailKReal = jurnalDetailK[index];
                            }
                            if (jurnalDetailD[index] == undefined) {
                                var jurnalDetailDReal = "";
                            } else {
                                var jurnalDetailDReal = "";
                            }
                            if (jurnalDetailTransaksi[index] != undefined) {
                                if (value.code.includes("KK")) {
                                    totalPengeluaran += value.total;

                                    $(".dropHere").append(
                                        "<tr>" +
                                            "<td><b>" +
                                            value.code +
                                            "</b><br>" +
                                            value.type +
                                            "</td>" +
                                            "<td><b>" +
                                            moment(value.date).format(
                                                "DD MMMM YYYY"
                                            ) +
                                            "</b></td>" +
                                            "<td><b>" +
                                            value.ref +
                                            "</b><br>" +
                                            value.description +
                                            "</td>" +
                                            "<td>" +
                                            jurnalDetailTransaksi[index] +
                                            "</td>" +
                                            "<td><b>" +
                                            jurnalDetailDReal +
                                            "</b></td>" +
                                            "<td><b>" +
                                            jurnalDetailKReal +
                                            "</b></td>" +
                                            "</tr>"
                                    );
                                } else {
                                    totalPendapatan += 0;
                                }
                            }
                        } else if (data.tipe == "Pemasukan") {
                            $.each(
                                value.journal_detail,
                                function (index1, value1) {
                                    if (
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 29 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 12 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 28 ||
                                                    value.type == 'Transfer Masuk'||
                                                    value.ref.includes('SMT')     ||
                                                    // value.ref.includes('PCS') ||
                                                    value.ref.includes('SIN') ||
                                                    value.ref.includes('SOT') 
   
                                    ) {
                                    } else {
                                        if (data.cabang == null) {
                                            jurnalDetailTransaksi[index] =
                                                "<b>" +
                                                value.journal_detail[0]
                                                    .account_data.code +
                                                "</b><br>" +
                                                value.journal_detail[0]
                                                    .account_data.name;
                                            if (value.code.includes("DD")) {
                                                jurnalDetailD[index] =
                                                    " Rp. " +
                                                    parseInt(
                                                        value.journal_detail[0]
                                                            .total
                                                    ).toLocaleString("en-US");
                                            } else {
                                                jurnalDetailK[index] = " Rp. 0";
                                            }
                                        } else {
                                            if (
                                                data.cabang ==
                                                value.journal_detail[0]
                                                    .account_data.branch_id
                                            ) {
                                                jurnalDetailTransaksi[index] =
                                                    "<b>" +
                                                    value.journal_detail[0]
                                                        .account_data.code +
                                                    "</b><br>" +
                                                    value.journal_detail[0]
                                                        .account_data.name;
                                                if (value.code.includes("DD")) {
                                                    jurnalDetailD[index] =
                                                        " Rp. " +
                                                        parseInt(
                                                            value
                                                                .journal_detail[0]
                                                                .total
                                                        ).toLocaleString(
                                                            "en-US"
                                                        );
                                                } else {
                                                    jurnalDetailK[index] =
                                                        " Rp. 0";
                                                }
                                            }
                                        }
                                    }
                                }
                            );

                            if (jurnalDetailD[index] == undefined) {
                                var jurnalDetailDReal = "";
                            } else {
                                var jurnalDetailDReal = jurnalDetailD[index];
                            }
                            if (jurnalDetailK[index] == undefined) {
                                var jurnalDetailKReal = "";
                            } else {
                                var jurnalDetailKReal = "";
                            }
                            if (jurnalDetailTransaksi[index] != undefined) {
                                if (value.code.includes("KK")) {
                                    totalPengeluaran += 0;
                                } else {
                                    totalPendapatan += value.total;
                                    $(".dropHere").append(
                                        "<tr>" +
                                            "<td><b>" +
                                            value.code +
                                            "</b><br>" +
                                            value.type +
                                            "</td>" +
                                            "<td><b>" +
                                            moment(value.date).format(
                                                "DD MMMM YYYY"
                                            ) +
                                            "</b></td>" +
                                            "<td><b>" +
                                            value.ref +
                                            "</b><br>" +
                                            value.description +
                                            "</td>" +
                                            "<td>" +
                                            jurnalDetailTransaksi[index] +
                                            "</td>" +
                                            "<td><b>" +
                                            jurnalDetailDReal +
                                            "</b></td>" +
                                            "<td><b>" +
                                            jurnalDetailKReal +
                                            "</b></td>" +
                                            "</tr>"
                                    );
                                }
                            }
                        } else {
                            $.each(
                                value.journal_detail,
                                function (index1, value1) {
                                    // var asasa = value.journal_detail[0]
                                    // .account_data.code;
                                    console.log(value.journal_detail[0]
                                        .account_data);
                                    if (
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 29 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 12 ||
                                        value.journal_detail[0]
                                                    .account_data.main_detail_id == 28 ||
                                        value.type == 'Transfer Masuk'||
                                        value.ref.includes('SMT') ||
                                        // value.ref.includes('PCS') ||
                                        value.ref.includes('SIN') ||
                                        value.ref.includes('SOT') 
                                    ) {
                                    } else {
                                        if (data.cabang == null) {
                                            jurnalDetailTransaksi[index] =
                                                "<b>" +
                                                value.journal_detail[0]
                                                    .account_data.code +
                                                "</b><br>" +
                                                value.journal_detail[0]
                                                    .account_data.name;
                                            if (value.code.includes("DD")) {
                                                jurnalDetailD[index] =
                                                    " Rp. " +
                                                    parseInt(
                                                        value.journal_detail[0]
                                                            .total
                                                    ).toLocaleString("en-US");
                                            } else {
                                                jurnalDetailK[index] =
                                                    " Rp. " +
                                                    parseInt(
                                                        value.journal_detail[0]
                                                            .total
                                                    ).toLocaleString("en-US");
                                            }
                                        } else {
                                            if (
                                                data.cabang ==
                                                value.journal_detail[0]
                                                    .account_data.branch_id
                                            ) {
                                                jurnalDetailTransaksi[index] =
                                                    "<b>" +
                                                    value.journal_detail[0]
                                                        .account_data.code +
                                                    "</b><br>" +
                                                    value.journal_detail[0]
                                                        .account_data.name;
                                                if (value.code.includes("DD")) {
                                                    jurnalDetailD[index] =
                                                        " Rp. " +
                                                        parseInt(
                                                            value
                                                                .journal_detail[0]
                                                                .total
                                                        ).toLocaleString(
                                                            "en-US"
                                                        );
                                                } else {
                                                    jurnalDetailK[index] =
                                                        " Rp. " +
                                                        parseInt(
                                                            value
                                                                .journal_detail[0]
                                                                .total
                                                        ).toLocaleString(
                                                            "en-US"
                                                        );
                                                }
                                            }
                                        }
                                    }
                                }
                            );
                            // console.log(jurnalDetailTransaksi[index]);
                            if (jurnalDetailD[index] == undefined) {
                                var jurnalDetailDReal = "";
                            } else {
                                var jurnalDetailDReal = jurnalDetailD[index];
                            }

                            if (jurnalDetailK[index] == undefined) {
                                var jurnalDetailKReal = "";
                            } else {
                                var jurnalDetailKReal = jurnalDetailK[index];
                            }

                            if (jurnalDetailTransaksi[index] != undefined) {
                                if (value.code.includes("KK")) {
                                    totalPengeluaran += value.total;
                                } else {
                                    totalPendapatan += value.total;
                                }
                                $(".dropHere").append(
                                    "<tr>" +
                                        "<td><b>" +
                                        value.code +
                                        "</b><br>" +
                                        value.type +
                                        "</td>" +
                                        "<td><b>" +
                                        moment(value.date).format(
                                            "DD MMMM YYYY"
                                        ) +
                                        "</b></td>" +
                                        '<td ><b>' +
                                        '<a style="color:blue;text-decoration: underline" onclick="cecek('+"'"+value.ref+"'"+','+"'"+value.id+"'"+')">'+value.ref+'</a>' +
                                        "</b><br>" +
                                        value.description +
                                        "</td>" +
                                        "<td>" +
                                        jurnalDetailTransaksi[index] +
                                        "</td>" +
                                        "<td><b>" +
                                        jurnalDetailDReal +
                                        "</b></td>" +
                                        "<td><b>" +
                                        jurnalDetailKReal +
                                        "</b></td>" +
                                        "</tr>"
                                );
                            }
                        }
                    });

                    $(".dropPengeluaran").text(
                        "Rp. " +
                            parseInt(totalPengeluaran).toLocaleString("en-US")
                    );
                    $(".dropPendapatan").text(
                        "Rp. " +
                            parseInt(totalPendapatan).toLocaleString("en-US")
                    );
                    // $('.dropHereTotalVal').html('<input type="hidden" class="form-control" name="totalValue" value="'+totalAkhir+'">');
                }
            }
        },
        error: function (data) {},
    });
}

function printDiv() {
    var outputString =
        '<style type="text/css">' +
        "#areaToPrint {" +
        "font-size:5px;font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;color: black;" +
        "}" +
        "#areaToPrint td, #areaToPrint th {" +
        "border: 1px solid black;padding: 8px;" +
        "}" +
        "#areaToPrint tr:nth-child(even){" +
        "background-color: #f2f2f2;" +
        "}" +
        "#areaToPrint tr:hover {" +
        "background-color: #ddd;" +
        "}" +
        "#areaToPrint th {" +
        "padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #04AA6D;" +
        "}" +
        "</style>";

    var divToPrint = document.getElementById("areaToPrint");
    newWin = window.open("");
    newWin.document.write(divToPrint.outerHTML);
    newWin.document.write(outputString);
    newWin.print();
    newWin.close();
}
function cecek(params,params2) {

    if (params.includes("BYR")) {
        window.open(LinkBYR+'/transaction/service/service-payment/'+params);
    }else if (params.includes("PJT")) {
        window.open(LinkBYR+'/transaction/sale/sale/'+params);
    }else if (params.includes("PCS")) {
        window.open(LinkBYR+'/transaction/purchasing/purchase/'+params);
    }else if (params.includes("SPND")) {
        window.open(LinkBYR+'/transaction/payment/payment/'+params);
    }else if (params.includes("INCM")) {
        window.open(LinkBYR+'/transaction/payment/payment/'+params);
    }else if (params.includes("SHP")) {
        window.open(LinkBYR+'/finance/sharing-profit/sharing-profit/'+params);
    }else if (params.includes("LOS")) {
        window.open(LinkBYR+'/finance/loss-items/loss-items/'+params);
    }
    

    
    
    

}


