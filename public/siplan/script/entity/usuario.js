var usuario = function () {
    var table = null;
    var obj = null;

    var configurarFormulario = function () {
        $('select#usuario_jefe').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#usuario_area').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#usuario_cargo').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#usuario_idrol').select2({
            dropdownParent: $("#basicmodal"),
        });

        $("div#basicmodal form#usuario_new").validate({
            rules: {
                'usuario[nombre]': {required: true},
                'usuario[area]': {required: true},
                'usuario[cargo]': {required: true},
                'usuario[usuario]': {required: true},
                'usuario[correo]': {required: true},
                'usuario[password][first]': {required: true},
                'usuario[password][second]': {equalTo: "#usuario_password_first"},
                'usuario[idrol][]': {required: true},
            },
            highlight: function (element) {
                $(element).parent().parent().addClass('has-danger');
            },
            unhighlight: function (element) {
                $(element).parent().parent().removeClass('has-danger');
                $(element).parent().parent().addClass('has-success');
            }
        });
    }

    var configurarDataTable = function () {
        table = $('table#usuario_table').DataTable({
            "pagingType": "simple_numbers",
            /*"language": {
                url: datatable_url
            },*/
            columns: [
                {data: 'numero'},
                {data: 'nombre'},
                {data: 'area'},
                {data: 'cargo'},
                {data: 'acciones'}
            ]
        });
    }

    var refrescar = function () {
        $('a#usuario_tablerefrescar').click(function (evento) {
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
                    $('table#usuario_table').html(data);
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


    var nuevo = function () {
        $('body').on('click', 'a#nuevo_usuario', function (evento) {
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

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#usuario_new', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                complete: function () {
                    l.stop();
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    }
                    else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        objeto = table.row.add({
                            "numero": data['id'],
                            "nombre": data['nombre'],
                            "area": data['area'],
                            "cargo": data['cargo'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-sm' href=" + Routing.generate('usuario_show', {id: data['id']}) + "><i class='flaticon-eye'></i></a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-info btn-sm editar_usuario' data-href=" + Routing.generate('usuario_edit', {id: data['id']}) + "><i class='flaticon-edit-1'></i></a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-danger btn-sm  eliminar_usuario'  data-csrf=" + data['csrf'] + " data-href=" + Routing.generate('usuario_delete', {id: data['id']}) + ">" +
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

    var eliminar = function () {
        $('table#usuario_table').on('click', 'a.eliminar_usuario', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar usuario",
                message: "<p>¿Está seguro que desea eliminar este usuario?</p>",
                buttons: {
                    confirm: {
                        label: 'Sí, estoy seguro',
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
                            type: 'get',
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

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    newAction();
                    nuevo();
                    eliminar();
                }
            );
        }
    }
}();



