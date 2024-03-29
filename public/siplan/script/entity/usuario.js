var usuario = function () {
    var table = null;
    var obj = null;

    var configurarDataTable = function () {
        table = $('table#usuario_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
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
                        configurarFormularioUsuario();
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

    var organigrama = function () {
        $('body').on('click', 'a#ver_organigrama', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data['view'])) {
                        $('div#basicmodal').modal('show');

                            var datascource = data['data'];
                            $('#chart-container').orgchart({
                                'data' : datascource,
                                'visibleLevel': 2,
                                'nodeContent': 'title',
                                'nodeID': 'id',
                                'exportButton': true,
                                'exportFilename': 'Mi organigrama'
                            });
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
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData:false,
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
                        configurarFormularioUsuario();
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
                            "<a class='btn btn-sm' href=" + Routing.generate('usuario_show', {id: data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-info btn-sm editar_usuario' data-href=" + Routing.generate('usuario_edit', {id: data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a>" +
                            "</li>" +
                            "<li class='m-nav__item'>" +
                            "<a class='btn btn-danger btn-sm  eliminar_usuario'  data-csrf=" + data['csrf'] + " data-href=" + Routing.generate('usuario_delete', {id: data['id']}) + ">" +
                            "<i class='flaticon-delete-1'></i>Eliminar</a></li></ul>",
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

    var escucharUsername = function () {
        $('div#basicmodal').on('keyup', 'input#usuario_usuario', function (evento) {
            evento.preventDefault();
            var val=$(this).val();
            if(val!="" && val.length>3){
                $('a#cargar_ldap_link').removeClass('m--hide');
            }else
                $('a#cargar_ldap_link').addClass('m--hide');
        });

        $('div#basicmodal').on('click', 'a#cargar_ldap_link', function (evento) {
            evento.preventDefault();
            var val=$('div#basicmodal input#usuario_usuario').val();
            if(val!="" && val.length>3){
                $.ajax({
                    url: Routing.generate('usuario_buscar_ldap',{'users': val}),
                    type: 'get',
                    beforeSend: function () {
                        mApp.block("div#basicmodal div.modal-body",
                            {overlayColor: "#000000", type: "loader", state: "success", message: "Cargando..."});
                    },
                    complete: function () {
                        mApp.unblock("div#basicmodal div.modal-body");
                    },
                    success: function (data) {
                        if (data['error']) {
                            if (data['error']==0)
                                toastr.danger('No existen usuarios con ese nombre de usuario');
                            else
                                toastr.warning('Hay varios usuarios con este nombre de usuario');
                        }else{
                            $('input#usuario_nombre').val(data['nombre']);
                            $('input#usuario_correo').val(data['correo']);
                        }
                    },
                    error: function () {
                    }
                });
            }
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
                    organigrama();
                    escucharUsername();
                }
            );
        }
    }
}();



