require("datatables.net-bs4/css/dataTables.bootstrap4.min.css");
require('datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css');
require("datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css");
require("datatables.net-select-bs4/css/select.bootstrap4.min.css");

window.$ = Jquery = require('jquery');
require('bootstrap/dist/js/bootstrap.min');
require('./../../assets/js/dataTables.bootstrap4.min');
require( './../../assets/js/buttons.bootstrap4.min');
require( './../../assets/js/responsive.bootstrap4.min');
require( './../../assets/js/buttons.colVis.js' )(); // Column visibility
require( './../../assets/js/buttons.html5.js' )();  // HTML 5 file export
require( './../../assets/js/buttons.flash.js' )();  // Flash file export
require( './../../assets/js/buttons.print.js' )();  // Print view button
require('./../../assets/js/pdfmake.js');
require('./../../assets/js/vfs_fonts');
require('./../../assets/js/jszip.min.js');


$(function () {
    let languages = {
        'en': require('./lang/en.json'),
        'ar': require('./lang/ar.json'),
        'fr': require('./lang/fr.json'),
    };
    let defaultLang =languages[document.querySelector('html').lang];
    try {
        $.extend(true, $.fn.dataTable.Buttons.defaults.dom.button, {className: 'btn btn-sm btn-primary'});
        $.extend(true, $.fn.dataTable.defaults, {
            language: defaultLang,
            responsive: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, defaultLang.all]],
            order: [],
            scrollX: true,
            search: {
                "smart": true
            },
            dom: 'Bfrtlip',
            // dom: '<"top"Bf><"bottom"tpli><"clear"r>',
            pagingType: "full_numbers",
            buttons: [
                {
                    extend:"collection",
                    text:"export",
                    buttons:[
                        {
                            extend: 'print',
                            text:"<i class='fa fa-print'></i> print",
                            className:'col-md-12',
                            filename: 'Print',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                },
                                columns: ':visible'
                            },
                            customize: function (win) {
                                $(win.document.body).find('table').addClass('display').css('font-size', '9px');
                                $(win.document.body).find('tr:nth-child(odd) td').each(function(index){
                                    $(this).css('background-color','#D0D0D0');
                                });
                                $(win.document.body).find('h1').css('text-align','center');
                            }
                        },
                        {
                            extend: 'copyHtml5',
                            text: '<i class="fa fa-files-o"></i>',
                            className:'col-md-12',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                },
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '<i class="fa fa-text-o"></i> CSV',
                            className:'col-md-12',
                            filename: 'csv',
                            exportOptions: {
                                modifier: {
                                    search: 'none'
                                },
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel"></i> excel',
                            className:'col-md-12',
                            filename: 'EXCEL',
                            exportOptions: {
                                modifier: {
                                    search: 'none'
                                },
                                columns: ':visible'
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf"></i> pdf',
                            className:'col-md-12',
                            filename: 'PDF',
                            download: 'open',
                            exportOptions: {
                                modifier: {
                                    page: 'current'
                                },
                                columns: ':visible'
                            }
                        },
                        {
                            text: 'JSON',
                            action: function ( e, dt, button, config ) {
                                var data = dt.buttons.exportData();

                                $.fn.dataTable.fileSave(
                                    new Blob( [ JSON.stringify( data ) ] ),
                                    'FileJson.json'
                                );
                            }
                        }
                    ]
                },
                {
                    extend: 'collection',
                    text:"visibility",
                    buttons:[
                        'columnsToggle',
                        {
                            extend: 'colvisRestore',
                            text:" restore "
                        },
                    ]
                }

            ],
        });
    }catch(e){
        console.log('setting')
        console.error(e)
    }
    $.fn.dataTable.ext.classes.sPageButton = 'page-item';
    $.fn.dataTable.ext.errMode = 'throw';
    window.LaravelDataTables = {};
})
