var actividadgeneral = function () {
    //Configuracion del datatable
    table = null;
    obj = null;

    //Configuracion del Datatable de las actividades del plan actual(OPTIMIZADO)
    var configurarDataTable = function () {
        table = $('table#actividad_table').DataTable({
            "pagingType": "simple_numbers",
            // definimos el valor inicial de elementos por pagina
            "language": {
                url: datatable_url
            },
            columns: [
                {data: 'nombre'},
                {data: 'fecha'},
                {data: 'fechaF'},
                {data: 'acciones'}
            ]}
        );
    }

    //Refrescamiento del listado de actividades del plan actual
    var refrescar = function () {
        $('body').on('click', 'a#actividad_tablerefrescar', function (evento){
            evento.preventDefault();
            var link =  $(this).attr('data-href');
            $.ajax({
                type: 'get',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    $('table#actividad_table').html(data['table']);
                    table.destroy();
                    configurarDataTable();
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

    //Confeccion del formulario para una nueva actividad(OPTIMIZADO)
    var configurarFormularioActividad= function () {
        $('input#actividad_general_fecha').datetimepicker();
        $('input#actividad_general_fechaf').datetimepicker();
        $('select#actividad_general_areaconocimiento').select2({
            dropdownParent: $("#basicmodal"),
        });
        Ladda.bind( '.mt-ladda-btn', { timeout: 2000 } );

        $('textarea#actividad_general_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true
        });
    }

    //Funcionalidad para la carga de formularios de registro y edicion
    var edicionActividad = function () {
        $('body').on('click', 'a.edicion_actividad', function (evento)
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
                        configurarFormularioActividad();
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


    var showActividad = function () {
        $('table#actividad_table').on('click', 'a.actividad_show', function (evento)
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

    //Funcionalidad para el  registro de una nueva actividad
    var newActionActividad = function () {
        $('div#basicmodal').on('submit', 'form#actividad_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                //FIN de configuracion obigatoria para el envioa de archivos por form data
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Guardando..."});
                },
                complete: function () {
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormularioActividad();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "nombre": data['nombre'],
                            "fecha": data['fecha'],
                            "fechaF": data['fechaF'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm actividad_show' data-href=" + Routing.generate('actividadgeneral_show',{id:data['id']}) + "><i class='flaticon-eye'></i></a>" +
                                "</li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-info btn-sm edicion_actividad' data-href=" + Routing.generate('actividadgeneral_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i></a>" +
                                "</li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-danger btn-sm  eliminar_actividad'  data-csrf=" + data['csrf'] +" data-href=" + Routing.generate('actividadgeneral_delete',{id:data['id']}) + ">" +
                                "<i class='flaticon-delete-1'></i></a></li></ul>",
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

    //Funcionalidad para la edicion de una nueva actividad
    var edicionActionActividad = function () {
        $('div#basicmodal').on('submit', 'form#actividadgeneral_edit', function (evento)
        {
            evento.preventDefault();
             var padre = $(this).parent();
             $.ajax({
                 url: $(this).attr("action"),
                 type: "POST",
                 data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                 beforeSend: function () {
                     //    base.blockUI({message: 'Cargando'});
                 },
                 complete: function () {
                     //  base.unblockUI();
                 },
                 success: function (data) {
                     if (data['error']) {
                         padre.html(data['form']);
                         configurarFormularioActividad();
                     } else {
                         if (data['mensaje'])
                             toastr.success(data['mensaje']);

                         $('div#basicmodal').modal('hide');
                         var pagina = table.page();
                         obj.parents('tr').children('td:nth-child(1)').html(data['nombre']);
                         obj.parents('tr').children('td:nth-child(2)').html(data['fecha']);
                         obj.parents('tr').children('td:nth-child(3)').html(data['fechaF']);
                     }
                 },
                 error: function ()
                 {
                     base.Error();
                 }
             });
        });
    }

    //Funcionalidad para la eliminacion de una actividad
    var eliminarActividad = function () {
        $('table#actividad_table').on('click', 'a.eliminar_actividad', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar actividad",
                message: "<p>¿Está seguro que desea eliminar esta actividad.</p>",
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
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            url: link,
                            data: {
                                _token: token
                            },
                            beforeSend: function () {
                                mApp.block("body",
                                    {overlayColor:"#000000",type:"loader",state:"success",message:"Eliminando..."});
                            },
                            complete: function () {
                                mApp.unblock("body");
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
                    edicionActividad();
                    showActividad();
                    newActionActividad();
                    edicionActionActividad();
                    eliminarActividad();
                }
            );
        }
    }
}();
