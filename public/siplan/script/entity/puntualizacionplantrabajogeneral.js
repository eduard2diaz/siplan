var puntualizacionplantrabajogeneral = function () {
    var puntualizaciontable = null;
    var obj = null;

    var configurarDataTable = function () {
        puntualizaciontable = $('table#puntualizacionplantrabajo_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'actividad'},
                {data: 'fecha'},
                {data: 'acciones'}
            ]});
    }

    var configurarFormulario = function () {
        $('input#puntualizacion_plan_trabajo_general_fechacreacion').datetimepicker();
        $('select#puntualizacion_plan_trabajo_general_tipo').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        $("div#basicmodal form").validate({
            rules:{
                'puntualizacion_plan_trabajo_general[actividad]': {required:true},
                'puntualizacion_plan_trabajo_general[descripcion]': {required:true},
                'puntualizacion_plan_trabajo_general[fechacreacion]': {required:true},
                'puntualizacion_plan_trabajo_general[tipo]': {required:true, min:0, max: 2},
            }
        })
    }

    var edicion = function () {
        $('body').on('click', 'a.editarpuntualizacion', function (evento)
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

    var show = function () {
        $('table#puntualizacionplantrabajo_table').on('click', 'a.puntualizacion_plan_trabajo_show', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get',
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
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

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#puntualizacionplantrabajogeneral_new', function (evento)
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
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    }
                    else
                    {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        var pagina = puntualizaciontable.page();
                        objeto = puntualizaciontable.row.add({
                            "actividad": data['actividad'],
                            "fecha": data['fecha'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm puntualizacion_plan_trabajo_show' data-href=" + Routing.generate('puntualizacion_plan_trabajo_general_show',{id:data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-danger puntualizacion_plan_trabajo_general_delete' data-csrf="+data['csrf']+"  data-href=" + Routing.generate('puntualizacion_plan_trabajo_general_delete',{id:data['id']}) + "><i class='flaticon-delete-1'></i>Eliminar</a></li>" +
                                "</ul>",
                        });
                        objeto.draw();
                        puntualizaciontable.page(pagina).draw('page');
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
        $('table#puntualizacionplantrabajo_table').on('click', 'a.puntualizacion_plan_trabajo_general_delete', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            obj=$(this);
            $('div#basicmodal').modal('hide');
            bootbox.confirm({
                title: 'Eliminar puntualización',
                message: '¿Está seguro que desea eliminar esta puntualización?',
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
                            // dataType: 'html', esta url se comentpuntualizacionplantrabajo porque lo k estamos mandando es un json y no un html plano
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
                                puntualizaciontable.row(obj.parents('tr'))
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
                    show();
                    newAction();
                    edicion();
                    eliminar();
                }
            );
        }
    }
}();
