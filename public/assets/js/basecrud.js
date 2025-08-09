class BaseCRUD
{
    static selector;

    static ajaxtable(params)
    {
    /*
        $('[datatable] thead tr').clone(true).appendTo('[datatable] thead');
        $('[datatable] thead tr:eq(1) th').each(function(i)
        {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" placeholder="Search ' + title + '" />');

            $('input', this).on('keyup change', function()
            {
                if (table.column(i).search() !== this.value)
                {
                    table.column(i).search(this.value).draw();
                }
            });
        });
        */


        params.responsive = true;
        params.orderCellsTo = true;
        params.fixedHeader = true;
        params.select = true;

        params.processing = true;
        params.serverSide = true;
        params.language = {
            url: "/assets/json/tr.json",
            searchPlaceholder: "Arama...",
        };

        if(typeof params.ajax == 'undefined')
        {
            params.ajax = {
                url: params.ajaxURL + (params.ajaxURL.indexOf('?') > 0 ? '&datatable=true' : '?datatable=true') ,
                type: 'POST',
            };
        }

        if(typeof params.order == 'undefined')
            params.order = [[0, 'asc']];

        if(typeof params.drawCallback == 'undefined')
        {
            params.drawCallback = function(settings, json)
            {
                $('[data-toggle="tooltip"]').tooltip();
            }
        }

        // Arama kutusu özelleştirme işlemi
        if(typeof params.dom == 'undefined') {
            // DOM yapısını sayfada sayfa başına seçici ve arama kutusu yanyana olacak şekilde düzenle
            params.dom = '<"dt-layout-row d-flex justify-content-between align-items-center"<"dt-length"l><"dt-custom-search">>rt<"dt-layout-row"<"dt-info"i><"dt-paging"p>>';
        }

        var table = $(BaseCRUD.selector).DataTable(params);

        // Sayfa yüklendikten sonra, DataTables arama alanını bizim istediğimiz stilde oluştur
        setTimeout(function() {
            // Özel arama alanını oluştur
            var searchHtml = '<div class="icon-field" style="margin:0;">' +
                '<span class="icon"><iconify-icon icon="ion:search-outline"></iconify-icon></span>' +
                '<input type="text" class="form-control" placeholder="Arama...">' +
                '</div>';

            // Özel arama alanını tablonun üst kısmına ekle
            $('.dt-custom-search').html(searchHtml);

            // Arama olayını bağla
            $('.dt-custom-search input').on('keyup', function() {
                table.search(this.value).draw();
            });
        }, 100);

        return table;
    }

    static delete(route, callback = null)
    {
        $('.dashboard-main-body').on("click", "[row-delete]", function() {
            var unique = $(this).attr('row-delete');

            Swal.fire({
                icon: "error",
                title: "",
                html: "Bu kayıtlar kalıcı olarak silinecektir.<br>İşlemi onaylıyor musunuz ?",
                showCancelButton: true,
                cancelButtonText: "İptal",
                confirmButtonText: "Sil",
            }).then(function(action){
                if (action.value)
                {
                    $.ajax({
                        url: route,
                        type: "DELETE",
                        data: {
                            _token: $('meta[name="csrf"]').attr('content'),
                            id: unique,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            if(data.status)
                            {
                                if(callback == null)
                                {
                                    $(BaseCRUD.selector).DataTable().ajax.reload();
                                } else {
                                    callback();
                                }
                            } else {
                                Swal.fire('', data.message, "warning");
                            }
                        }
                    });
                }
            });
        });
    }
}
