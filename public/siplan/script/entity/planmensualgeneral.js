var planmensualgeneral = function () {
    var table = null;
    var obj = null;

    var configurarFormulario = function () {

        jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                return moment(value) > moment($(params).val());
            }, 'Tiene que ser mayor o igual que la fecha de inicio');

        $('select#plan_mensual_general_anno').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#plan_mensual_general_mes').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('input#plan_mensual_general_edicionfechainicio').datepicker();
        $('input#plan_mensual_general_edicionfechafin').datepicker();
        $("div#basicmodal form").validate({
            rules:{
                'plan_mensual_general[mes]': {required:true},
                'plan_mensual_general[anno]': {required:true},
                'plan_mensual_general[edicionfechainicio]': {required: true},
                'plan_mensual_general[edicionfechafin]': {required: true, greaterThan: "#plan_mensual_general_edicionfechainicio"},
            }
        })
    }

    var refrescar = function () {
        $('a#planmensualgeneral_tablerefrescar').click(function (evento)
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
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    $('table#planmensualgeneral_table').html(data);
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
                    mApp.unblock("body");
                }
            });
        });
    }

    var configurarDataTable = function () {
        table = $('table#planmensualgeneral_table').DataTable({
            "pagingType": "simple_numbers",
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'numero'},
                {data: 'mes'},
                {data: 'anno'},
                {data: 'fechainicio'},
                {data: 'fechafin'},
                {data: 'estado'},
                {data: 'acciones'}
            ]});
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#planmensualgeneral_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    l.start();
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
                            "numero": total,
                            "mes": data['mes'],
                            "anno": data['anno'],
                            "fechainicio": data['fechainicio'],
                            "fechafin": data['fechafin'],
                            "estado": data['estado'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-default btn-sm' href=" + Routing.generate('planmensualgeneral_show',{id:data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>"
                                +"<li class='m-nav__item'>" +
                                "<a class='btn btn-info edicion btn-sm' data-href=" + Routing.generate('planmensualgeneral_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-danger btn-sm  eliminar_planmensualgeneral' data-csrf=" + data['csrf'] + " data-href=" + Routing.generate('planmensualgeneral_delete',{id:data['id']}) + ">" +
                                "<i class='flaticon-delete-1'></i>Eliminar</a></li></ul>",
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
        $('div#basicmodal').on('submit', 'form#planmensualgeneral_edit', function (evento) {
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
                        obj.parents('tr').children('td:nth-child(2)').html(data['mes']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['anno']);
                        obj.parents('tr').children('td:nth-child(4)').html(data['fechainicio']);
                        obj.parents('tr').children('td:nth-child(5)').html(data['fechafin']);
                        obj.parents('tr').children('td:nth-child(6)').html(data['estado']);
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    var eliminar = function () {
        $('table#planmensualgeneral_table').on('click', 'a.eliminar_planmensualgeneral', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar plan mensual",
                message: "<p>¿Está seguro que desea eliminar este plan mensual?</p>",
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
                            type: 'get',
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
                    edicionAction();
                    edicion();
                    eliminar();
                }
            );
        }
    }
}();

