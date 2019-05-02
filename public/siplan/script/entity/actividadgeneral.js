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
                ]
            }
        );
    }

    //Refrescamiento del listado de actividades del plan actual
    var refrescar = function () {
        $('body').on('click', 'a#actividad_tablerefrescar', function (evento) {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            $.ajax({
                type: 'get',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Actualizando..."});
                },
                success: function (data) {
                    $('table#actividad_table').html(data['table']);
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

    //Confeccion del formulario para una nueva actividad(OPTIMIZADO)
    var configurarFormularioActividad = function () {

        jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                return moment(value) > moment($(params).val());
            }, 'Tiene que ser mayor  que la fecha de inicio');

        $('input#actividad_general_fecha').datetimepicker();
        $('input#actividad_general_fechaf').datetimepicker();
        $('select#actividad_general_areaconocimiento').select2();
        $('select#actividad_general_capitulo').select2();
        $('select#actividad_general_subcapitulo').select2();
        $('textarea#actividad_general_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true
        });

        $("body form[name=actividad_general]").validate({
            rules:{
                'actividad_general[nombre]': {required:true},
                'actividad_general[lugar]': {required:true},
                'actividad_general[fecha]': {required:true},
                'actividad_general[fechaf]': {required:true, greaterThan: "#actividad_general_fecha"},
                'actividad_general[dirigen]': {required:true},
                'actividad_general[participan]': {required:true},
                'actividad_general[capitulo]': {required:true},
                'actividad_general[subcapitulo]': {required:true},
            }
        })
    }

    var showActividadGeneral = function () {
        $('table#actividad_table').on('click', 'a.actividadgeneral_show', function (evento) {
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
                    mApp.unblock("body");
                }
            });
        });
    }

    //Funcionalidad para el  registro de una nueva actividad
    var newActionActividad = function () {
        $('body').on('submit', 'form[name=actividad_general]', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                //FIN de configuracion obigatoria para el envioa de archivos por form data
                beforeSend: function () {
                  l.start();
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormularioActividad();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);

                        $('div#basicmodal').modal('hide');
                            document.location.href=data['url'];
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    //Funcionalidad para la eliminacion de una actividad
    var eliminarActividad = function () {
        $('table#actividad_table').on('click', 'a.eliminar_actividad', function (evento) {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar actividad",
                message: "<p>¿Está seguro que desea eliminar esta actividad?</p>",
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
                                    {
                                        overlayColor: "#000000",
                                        type: "loader",
                                        state: "success",
                                        message: "Eliminando..."
                                    });
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
                            error: function () {
                                base.Error();
                            }
                        });
                }
            });
        });
    }

    var capituloListener = function () {
        $('body').on('change', 'select#actividad_general_capitulo', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('subcapitulo_findbycapitulo', {'capitulo': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando subcapítulos..."
                            });
                    },
                    success: function (data) {
                        var cadenasubcapitulo = "<option></option>";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadenasubcapitulo += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#actividad_general_subcapitulo').html(cadenasubcapitulo);
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("body");
                    }
                });
        });
        $('body').on('change', 'select#actividad_general_subcapitulo', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('arc_findbysubcapitulo', {'subcapitulo': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {
                                overlayColor: "#000000",
                                type: "loader",
                                state: "success",
                                message: "Cargando Área de resultados claves..."
                            });
                    },
                    success: function (data) {
                        var cadenasubcapitulo = "<option></option>";
                        var array = JSON.parse(data);
                        for (var i = 0; i < array.length; i++)
                            cadenasubcapitulo += "<option value=" + array[i]['id'] + ">" + array[i]['nombre'] + "</option>";
                        $('select#actividad_general_areaconocimiento').html(cadenasubcapitulo);
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("body");
                    }
                });
        });
    }

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    showActividadGeneral();
                    eliminarActividad();
                }
            );
        },
        edicion: function () {
            $().ready(function () {
                configurarFormularioActividad();
                    newActionActividad();
                    capituloListener();
                }
            );
        }
    }
}();
