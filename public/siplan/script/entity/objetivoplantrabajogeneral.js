var objetivoplantrabajogeneral = function () {
    var objetivotable = null;
    var obj = null;

    var configurarDataTable = function () {
        objetivotable = $('table#objetivoplantrabajo_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'descripcion'},
                {data: 'acciones'}
            ]});
    }

    var configurarFormulario = function () {
        $("div#basicmodal form").validate({
            rules:{
                'objetivo_plan_trabajo_general[descripcion]': {required:true},
                'objetivo_plan_trabajo_general[numero]': {required:true, min:1},
            }
        })
    }

    var edicion = function () {
        $('body').on('click', 'a.editarobjetivo', function (evento)
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
        $('table#objetivoplantrabajo_table').on('click', 'a.objetivo_plan_trabajo_show', function (evento)
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
        $('div#basicmodal').on('submit', 'form#objetivoplantrabajogeneral_new', function (evento)
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
                        var pagina = objetivotable.page();
                        objeto = objetivotable.row.add({
                            "numero": data['numero'],
                            "descripcion": data['descripcion'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm objetivo_plan_trabajo_show' data-href=" + Routing.generate('objetivo_plan_trabajo_general_show',{id:data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-info btn-sm editarobjetivo' data-href=" + Routing.generate('objetivo_plan_trabajo_general_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm btn-danger objetivo_plan_trabajo_general_delete' data-csrf="+data['csrf']+"  data-href=" + Routing.generate('objetivo_plan_trabajo_general_delete',{id:data['id']}) + "><i class='flaticon-delete-1'></i>Eliminar</a></li>" +
                                "</ul>",
                        });
                        objeto.draw();
                        objetivotable.page(pagina).draw('page');
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
        $('div#basicmodal').on('submit', 'form#objetivoplantrabajogeneral_edit', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
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
                    else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(1)').html(data['numero']);
                        obj.parents('tr').children('td:nth-child(2)').html(data['descripcion']);
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('table#objetivoplantrabajo_table').on('click', 'a.objetivo_plan_trabajo_general_delete', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            obj=$(this);
            $('div#basicmodal').modal('hide');
            bootbox.confirm({
                title: 'Eliminar actividad principal',
                message: '¿Está seguro que desea eliminar esta actividad principal?',
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
                            // dataType: 'html', esta url se comentobjetivoplantrabajo porque lo k estamos mandando es un json y no un html plano
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
                                objetivotable.row(obj.parents('tr'))
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
                    edicionAction();
                    edicion();
                    eliminar();
                }
            );
        }
    }
}();
