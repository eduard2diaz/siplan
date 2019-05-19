var actividadarea = function () {
    //Configuracion del datatable
    table = null;
    obj = null;
    //Fin de la configuracion del datatable
    //Configuracion de los planes de trabajos antiguos
    tableantiguos=null;
    objantiguo = null;
    //Fin de la configuracion de los planes de trabajos antiguos
    //Gestion de actividades
    listadoactividades= new Array();

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

    var refrescarAction = function () {
            var link = Routing.generate('planmensualarea_show',{'id':planmensualarea})
            $.ajax({
                type: 'get',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor: "#000000", type: "loader", state: "success", message: "Actualizando..."});
                },
                success: function (data) {
                    $('table#actividad_table').html(data);
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

    }

    //Confeccion del formulario para una nueva actividad(OPTIMIZADO)
    var configurarFormularioActividad = function () {

        jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                return moment(value) > moment($(params).val());
            }, 'Tiene que ser mayor  que la fecha de inicio');

        $('input#actividad_area_fecha').datetimepicker();
        $('input#actividad_area_fechaf').datetimepicker();
        $('select#actividad_area_areaconocimiento').select2();
        $('textarea#actividad_area_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true
        });

        $("body form[name=actividad_area]").validate({
            rules: {
                'actividad_area[nombre]': {required: true},
                'actividad_area[lugar]': {required: true},
                'actividad_area[fecha]': {required: true},
                'actividad_area[fechaf]': {required: true, greaterThan: "#actividad_area_fecha"},
                'actividad_area[dirigen]': {required: true},
                'actividad_area[participan]': {required: true},
            }
        })
    }

    var showActividadGeneral = function () {
        $('table#actividad_table').on('click', 'a.actividadarea_show', function (evento) {
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
        $('body').on('submit', 'form[name=actividad_area]', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector('.ladda-button'));
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
                        document.location.href = data['url'];
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



    var cargarActividadesGeneral=function(){
        $('body').on('click', 'a.cargaractividadespadre', function (evento)
        {
            evento.preventDefault();
            var padre = $('div#basicmodal');
            var link = $(this).attr('data-href');
            $.ajax({
                url: link,
                type: "GET",
                beforeSend: function () {
                    //   base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    // base.unblockUI();
                },
                success: function (data) {
                    if(padre.html(data)) {
                        configurarDataTableAntiguasGeneral();
                        padre.modal('show')
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });

        //Gestion de actividades antiguas
        var configurarDataTableAntiguasGeneral = function () {
            tableantiguos = $('table#table_actividadesantiguas').DataTable({
                "pagingType": "simple_numbers",
                "language": {
                    "paginate": {
                        "first": "|<",
                        "previous": "Ant.",
                        "next": "Sig.",
                        "last": ">|",
                    },
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No hay elementos hasta el momento",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "No hay registros disponibles",
                    "infoFiltered": "(filtrando de_MAX_ total registros)",
                    "search": "Buscar"
                },
                columns: [
                    {data: 'numero'},
                    {data: 'nombre'},
                    {data: 'fecha'},
                    {data: 'fechaf'},
                    {data: 'arc'}
                ]}
            );
        }

        var configurarDataTableErroresClonacionGeneral = function () {
            tableantiguos = $('table#table_erroresclonacion').DataTable();
        }

        $('div#basicmodal').on('draw.dt','table#table_actividadesantiguas',function(){
            $("table#table_actividadesantiguas input[type=checkbox]").prop('checked', false);

            $('table#table_actividadesantiguas input').each(function(obj,valor){
                if(listadoactividades.indexOf($(this).attr('value'))!=-1)
                    $(this).prop('checked', true);
            });
        });

        $('div#basicmodal').on('click','table#table_actividadesantiguas input',function(){
            if( $(this).is(':checked') ) {
                listadoactividades.push($(this).attr('value'));
                $('a#enviar_actividadesgeneral').removeClass('m--hidden-desktop');
                $('a#enviar_actividadesgeneral').fadeIn(800);
            }else{
                var pendienteeliminar=$(this).attr('value');
                indice=listadoactividades.indexOf(pendienteeliminar);
                if(indice>=0) {
                    listadoactividades.splice(indice, 1);
                    if (listadoactividades.length == 0)
                        $('a#enviar_actividadesgeneral').addClass('m--hidden-desktop');
                }
            }
        });


        $('div#basicmodal').on('click', 'a#enviar_actividadesgeneral', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            $.ajax({
                url: link,
                type: "POST",
                data: {'array':JSON.stringify(listadoactividades)},
                beforeSend: function () {
                    l.start();
                },
                complete: function () {
                    l.stop();
                },
                success: function (data) {
                    if(data['mensaje']){
                        toastr.success(data['mensaje']);
                        $('div#basicmodal').modal('hide');
                        refrescarAction();
                    }
                    else{
                        if(data['error'])
                            toastr.error(data['error']);
                        else
                        if(data['warning']){
                            refrescarAction();
                            toastr.warning(data['warning']);
                        }
                        $('div#basicmodal').html(data['errores']);
                        configurarDataTableErroresClonacionGeneral();
                    }
                },
                error: function ()
                {
                    base.Error();
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
                        cargarActividadesGeneral();
                    }
                );
            },
            edicion: function () {
                $().ready(function () {
                        configurarFormularioActividad();
                        newActionActividad();
                    }
                );
            }
        }
    }();
