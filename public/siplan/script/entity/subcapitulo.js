var subcapitulo = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#subcapitulo_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'id'},
                {data: 'nombre'},
                {data: 'numero'},
                {data: 'capitulo'},
                {data: 'acciones'}
            ]});
    }

    var configurarFormulario = function () {
        $('select#subcapitulo_capitulo').select2({
            dropdownParent: $("#basicmodal"),
        });
        $("div#basicmodal form").validate({
            rules:{
                'subcapitulo[nombre]': {required:true},
                'subcapitulo[capitulo]': {required:true},
                'subcapitulo[numero]': {required:true, min: 1}
            }
        })
    }

    var edicion = function () {
        $('body').on('click', 'a.edicion', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        configurarFormulario();
                        $('div#basicmodal').modal('show');
                    }
                },
                error: function ()
                {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var refrescar = function () {
        $('a#subcapitulo_tablerefrescar').click(function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    $('table#subcapitulo_tabletable').html(data);
                    table.destroy();
                    configurarDataTable();
                },
                error: function ()
                {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }});
        });
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#subcapitulo_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    objantiguo = null;
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    }
                    else
                    {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "id": total,
                            "nombre": data['nombre'],
                            "numero": data['numero'],
                            "capitulo": data['capitulo'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-info edicion' data-href=" + Routing.generate('subcapitulo_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "</ul>",
                        });
                        objeto.draw();
                        table.page(pagina).draw('page');
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var edicionAction = function () {
        $('div#basicmodal').on('submit', 'form#subcapitulo_edit', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );

            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(),
                beforeSend: function () {
                    l.start();
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    }
                    else
                    {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['numero']);
                        obj.parents('tr').children('td:nth-child(4)').html(data['capitulo']);
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('div#basicmodal').on('click', 'a.eliminar_subcapitulo', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            $('div#basicmodal').modal('hide');
            bootbox.confirm({
                title: 'Eliminar subcapítulo',
                message: '¿Está seguro que desea eliminar este subcapítulo?',
                buttons: {
                    confirm: {
                        label: 'Si, estoy seguro',
                        className: 'btn-sm btn-primary'},
                    cancel: {
                        label: 'Cancelar',
                        className: 'btn-sm btn-metal'}
                },
                callback: function (result) {
                    if (result == true)
                        $.ajax({
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            // dataType: 'html', esta url se comentsubcapitulo porque lo k estamos mandando es un json y no un html plano
                            url: link,
                            data: {
                                _token: token
                            },
                            beforeSend: function () {
                                mApp.block("body",
                                    {overlayColor:"#000000",type:"loader",state:"success",message:"Eliminando..."});
                            },
                            complete: function () {
                                mApp.unblock("body")
                            },
                            success: function (data) {
                                table.row(obj.parents('tr'))
                                    .remove()
                                    .draw('page');
                                toastr.success(data['mensaje']);
                            },
                            error: function ()
                            {
                                base.Error();
                            }
                        });
                }
            });
        });
    }

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    newAction();
                    edicion();
                    edicionAction();
                    eliminar();
                }
            );
        }
    }
}();
