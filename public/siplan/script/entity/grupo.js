var grupo = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#grupo_table').DataTable({
            "pagingType": "simple_numbers",
            /*"language": {
                url: datatable_url
            },*/
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'acciones'}
            ]
        });
    }

    var configurarFormulario = function () {
        $('select#grupo_creador').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        $('select#grupo_idmiembro').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        Ladda.bind('.mt-ladda-btn');

        $("div#basicmodal form").validate({
            rules: {
                'grupo[nombre]': {required: true}
            }
        })
    }

    var show = function () {
        $('body').on('click', 'a.grupo_show', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        $('div#basicmodal').modal('show');
                    }
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var edicion = function () {
        $('body').on('click', 'a.edicion', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        configurarFormulario();
                        $('div#basicmodal').modal('show');
                    }
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var refrescar = function () {
        $('a#grupo_tablerefrescar').click(function (evento) {
            evento.preventDefault();
            var link = $(this).attr('href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Actualizando..."});
                },
                success: function (data) {
                    $('table#grupo_table').html(data);
                    table.destroy();
                    configurarDataTable();
                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#grupo_new', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
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
                    else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "numero": total,
                            "nombre": data['nombre'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-sm grupo_show' data-href=" + Routing.generate('grupo_show', {id: data['id']}) + "><i class='flaticon-eye'></i></a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-info btn-sm edicion' data-href=" + Routing.generate('grupo_edit', {id: data['id']}) + "><i class='flaticon-edit-1'></i></a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-danger btn-sm  eliminar_grupo'  data-csrf=" + data['csrf'] + " data-href=" + Routing.generate('grupo_delete', {id: data['id']}) + ">" +
                            "<i class='flaticon-delete-1'></i></a></li></ul>",
                        });
                        objeto.draw();
                        table.page(pagina).draw('page');
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var edicionAction = function () {
        $('div#basicmodal').on('submit', 'form#grupo_edit', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                },
                complete: function () {
                    l.start();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    }
                    else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje'])

                        if (false == data['escreador']) {
                            var button = "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm grupo_show' data-href=" + Routing.generate('grupo_show', {id: data['id']}) + "><i class='flaticon-eye'></i></a>" +
                                "</li></ul>";
                            obj.parents('tr').children('td:nth-child(3)').html(button);
                        }

                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('table#grupo_table').on('click', 'a.eliminar_grupo', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: 'Eliminar grupo',
                message: '¿Está seguro que desea eliminar este grupo?',
                buttons: {
                    confirm: {
                        label: 'Si, estoy seguro',
                        className: 'btn-sm btn-primary'
                    },
                    cancel: {
                        label: 'Cancelar',
                        className: 'btn-sm btn-metal'
                    }
                },
                callback: function (result) {
                    if (result == true)
                        $.ajax({
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            // dataType: 'html', esta url se comentgrupo porque lo k estamos mandando es un json y no un html plano
                            url: link,
                            data: {
                                _token: token
                            },
                            beforeSend: function () {
                                mApp.block("body",
                                    {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        message: "Eliminando..."
                                    });
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
                            error: function () {
                                base.Error();
                            }
                        });
                }
            });
        });
    }

    var confirmarGrupo = function () {
        $('div#basicmodal').on('click', 'a#confirmar_grupo', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                url: link,
                data: {
                    _token: token
                },
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    toastr.success(data['mensaje']);
                    $('div#basicmodal').modal('hide');

                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
                }
            });
        });
    }

    var rechazarGrupo = function () {
        $('div#basicmodal').on('click', 'a#rechazar_grupo', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');

            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                url: link,
                data: {
                    _token: token
                },
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    toastr.success(data['mensaje']);
                    table.row(obj.parents('tr'))
                        .remove()
                        .draw('page');
                    $('div#basicmodal').modal('hide');

                },
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body")
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
                    show();
                    edicionAction();
                    eliminar();
                    confirmarGrupo();
                    rechazarGrupo();
                }
            );
        }
    }
}();
