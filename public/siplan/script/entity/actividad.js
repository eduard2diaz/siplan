var actividad = function () {
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

    //INICIO DE FUNCIONALIDADES DEL INDEX
    //Funcionalidad para las estadisticas de las actividades
    var estadistica = function () {
        $('body').on('click', 'a#plantrabajo_estadistica', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando estadísticas..."});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data.view)) {
                        $('div#basicmodal').modal('show');
                        am4core.useTheme(am4themes_animated);
                        // Themes end
                        // Create chart instance
                        var chart = am4core.create("plantrabajo_grafica", am4charts.PieChart);
                        // Add data
                        chart.data=JSON.parse(data.data);
                        // Add and configure Series
                        var pieSeries = chart.series.push(new am4charts.PieSeries());
                        pieSeries.dataFields.value = "cantidad";
                        pieSeries.dataFields.category = "estado";
                        pieSeries.slices.template.stroke = am4core.color("#fff");
                        pieSeries.slices.template.strokeWidth = 2;
                        pieSeries.slices.template.strokeOpacity = 1;
                        // This creates initial animation
                        pieSeries.hiddenState.properties.opacity = 1;
                        pieSeries.hiddenState.properties.endAngle = -90;
                        pieSeries.hiddenState.properties.startAngle = -90;
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

    var refrescar = function () {
        //Refresca el listado de actividades(NO FILTRA POR EL ESTADO)
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
                    $('small#filtro').html('');
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

        //Refresca el listado de actividades a partir del estado de la misma
        $('body').on('click', 'a.refrescar', function (evento){
            evento.preventDefault();
            var link =  $(this).attr('data-href');
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    $('table#actividad_table').html(data['table']);
                    if(data['filtro']!=null)
                        $('small#filtro').html("<span class=\"m-nav__link-badge m-badge m-badge--dot m-badge--dot-small m-badge--danger\"></span> "+data['filtro']);
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
    //FIN DE FUNCIONALIDADES DEL INDEX

    //INICIO DE LAS FUNCIONALIDADES DE RESPUESTA

    //Gestion de respuestas
    var showRespuesta = function () {
        $('div#basicmodal').on('click', 'a.respuesta_show', function (evento)
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

    var agregarArchivoRespuesta = function () {
        $('div#basicmodal').on('click', 'a#adicionar_archivo_respuesta', function (evento)
        {
            evento.preventDefault();
            var datos='<tr>\n' +
                '    <td>\n' +
                '            <input id="respuesta_ficheros_'+cantidadarchivos+'_file" name="respuesta[ficheros]['+cantidadarchivos+'][file]" required="required" class="form-control" aria-describedby="respuesta_ficheros_'+cantidadarchivos+'_file-error" aria-invalid="false" type="file"><div id="respuesta_fichero_'+cantidadarchivos+'_button-error" class="form-control-feedback"></td>\n' +
                '</td>\n' +
                '    <td>\n' +
                '        <a class="btn btn-danger btn-sm eliminar_archivo pull-right"><i class="flaticon flaticon-delete-1"></i></a>\n' +
                '    </td>\n' +
                '</tr>';
            cantidadarchivos++;
            $('div#archivos table').append(datos);
        });
    }

    //Confeccion del formulario para una nueva respuesta(OPTIMIZADO)
    var configurarFormularioRespuesta= function () {
        $('textarea#respuesta_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true,
        });
    }

    //Funcionalidad para la carga de formularios de registro y edicion(PARA CREAR UNA RESPUESTA)
    var edicionRespuesta = function () {
        $('div#basicmodal').on('click', 'a.edicion_respuesta', function (evento)
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
                        configurarFormularioRespuesta();
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

    var newActionRespuesta = function () {
        $('div#basicmodal').on('submit', 'form#respuesta_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData:false,
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
                        configurarFormularioRespuesta();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);
                        $('div#basicmodal').modal('hide');
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var edicionActionRespuesta = function () {
        $('div#basicmodal').on('submit', 'form#respuesta_edit', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                contentType: false,
                cache: false,
                processData:false,
                beforeSend: function () {
                    //    base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    //  base.unblockUI();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormularioRespuesta();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);
                        $('div#basicmodal').modal('hide');
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    var eliminarRespuesta = function () {
        $('div#basicmodal').on('click', 'a.eliminar_respuesta', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar respuesta",
                message: "<p>¿Está seguro que desea eliminar esta respuesta, <b>esta acción no se podrá deshacer</b>?.</p>",
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
                                toastr.success(data['mensaje']);
                                $('div#basicmodal').modal('hide');
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
    //FIN DE LAS FUNCIONALIDADES DE RESPUESTA

    //INICIO DE FUNCIONALIDADES DEL NUEVO
    //Confeccion del formulario para una nueva actividad(OPTIMIZADO)
    var configurarFormularioActividad= function () {
        jQuery.validator.addMethod("greaterThan",
            function (value, element, params) {
                return moment(value) > moment($(params).val());
            }, 'Tiene que ser mayor  que la fecha de inicio');

        $('input#actividad_fecha').datetimepicker();
        $('input#actividad_fechaf').datetimepicker();
        $('select#actividad_estado').select2();
        $('select#actividad_areaconocimiento').select2();
        $("body form[name=actividad]").validate({
            rules:{
                'actividad[nombre]': {required:true},
                'actividad[areaconocimiento]': {required:true},
                'actividad[lugar]': {required:true},
                'actividad[fecha]': {required:true},
                'actividad[fechaf]': {required:true, greaterThan: "#actividad_fecha"},
                'actividad[dirigen]': {required:true},
                'actividad[participan]': {required:true},
            }
        })
    }

    //Funcionalidad para el  registro de una nueva actividad
    var newActionActividad = function () {
        $('body').on('submit', 'form#actividad_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
               // data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                //INicio de configuracion obigatoria para el envioa de archivos por form data
                contentType: false,
                cache: false,
                processData:false,
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
                        document.location.href=data['url'];
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }

    //Funcionlidad que agrega un input de tipo file al formulario
    var agregarArchivoActividad = function () {
        $('body').on('click', 'a#adicionar_archivo_actividad', function (evento)
        {
            evento.preventDefault();
            var datos='<tr>\n' +
                '    <td>\n' +
                '            <input id="actividad_ficheros_'+cantidadarchivos+'_file" name="actividad[ficheros]['+cantidadarchivos+'][file]" required="required" class="form-control" aria-describedby="actividad_ficheros_'+cantidadarchivos+'_file-error" aria-invalid="false" type="file"><div id="actividad_fichero_'+cantidadarchivos+'_button-error" class="form-control-feedback"></td>\n' +
                '</td>\n' +
                '    <td>\n' +
                '        <a class="btn btn-danger btn-sm eliminar_archivo pull-right"><i class="flaticon flaticon-delete-1"></i></a>\n' +
                '    </td>\n' +
                '</tr>';
            cantidadarchivos++;
            $('div#archivos table').append(datos);
        });
    }

    /*Funcionlidad que elimina un input de tipo file al formulario, dicho input en realidad no es aun un archivo
     existente entre los ficheros de la aplicacion*/
    var eliminarArchivo = function () {
        $('body').on('click', 'a.eliminar_archivo', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            obj.parents('tr').remove();
        });
    }

    //Funcionalidad que elimina uno de los ficheros existentes de la aplicacion
    var eliminarFichero = function () {
        $('body').on('click', 'table#ficheros_table a.eliminar_fichero', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            var token = $(this).attr('data-csrf');
            bootbox.confirm({
                title: "Eliminar fichero?",
                message: "<p>¿Está seguro que desea eliminar este fichero, <b>esta acción no se podrá deshacer</b>?.</p>",
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
                            // dataType: 'html', esta url se comentarea porque lo k estamos mandando es un json y no un html plano
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
                                obj.parents('tr').remove();
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

    //Funcionalidad para la edicion de una nueva actividad
    var edicionActionActividad = function () {
        $('body').on('submit', 'form#actividad_edit', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                //   data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                data: new FormData(this), //para enviar el formulario hay que serializarlo
                //INicio de configuracion obigatoria para el envioa de archivos por form data
                contentType: false,
                cache: false,
                processData:false,
                //FIN de configuracion obigatoria para el envioa de archivos por form data
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
                        document.location.href=data['url'];
                    }
                },
                error: function ()
                {
                    base.Error();
                }
            });
        });
    }
    //FIN DE LAS FUNCIONALIDADES DE NUEVO

    //INICIO DE CLONACION DE LAS ACTIVIDADES
    //PASO 1.Gestion de planes antiguos
     var cargarPlanesAntiguos=function(){
         //Cargado de planes antiguos
         $('body').on('click', 'a#cargar_antiguos_link', function (evento)
         {
             evento.preventDefault();
             var link = $(this).attr('data-href');
             $.ajax({
                 url: link,
                 type: "GET",
                 beforeSend: function () {
                     //base.blockUI({message: 'Cargando'});
                 },
                 complete: function () {
                     //base.unblockUI();
                 },
                 success: function (data) {
                     if($('div#basicmodal').html(data)) {
                         configurarDataTableAntiguos();
                         $('div#basicmodal').modal('show');
                     }
                 },
                 error: function ()
                 {
                     base.Error();
                 }
             });
         });


         //Listado de planes antiguos
         var configurarDataTableAntiguos = function () {
             tableantiguos = $('table#table_antiguos').DataTable({
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
                     {data: 'mes'},
                     {data: 'anno'}
                 ]}
             );
             Ladda.bind( '.mt-ladda-btn');
         }

         $('div#basicmodal').on('draw.dt','table#table_antiguos',function(){
             $('table#table_antiguos input input:radio').prop('checked',false);
             $('table#table_antiguos input input:radio').filter('[value="'+objantiguo+'"]').prop('checked',true);
         });

        $('div#basicmodal').on('click','table#table_antiguos input',function(){
            if( $(this).is(':checked') ) {
                objantiguo = $(this).attr('value');
                $('a#cargar_actividades_link').removeClass('m--hidden-desktop');
                $('a#cargar_actividades_link').fadeIn(800);
                $('input#plantrabajo_antiguo').val($(this).attr('value'));
            }
         });
     }

    var refrescarAction = function () {
        var link = Routing.generate('plantrabajo_show',{'id':plantrabajo});
        obj = $(this);
        $.ajax({
            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
            //dataType: 'html',
            url: link,
            beforeSend: function (data) {
                //  base.blockUI({message: 'Actualizando datos'});
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
                //  base.unblockUI();
            }
        });
    }

     //PASO 2. Gestion de actividades
     var cargarActividadesAntiguas=function(){
         $('div#basicmodal').on('click', 'a#cargar_actividades_link', function (evento)
         {
             evento.preventDefault();
             var padre = $('div#basicmodal');
             var link = Routing.generate('plantrabajo_actividadesajax',{'id':objantiguo});
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
                         configurarDataTableAntiguasActividades();
                     }
                 },
                 error: function ()
                 {
                     base.Error();
                 }
             });
         });

         //Gestion de actividades antiguas
         var configurarDataTableAntiguasActividades = function () {
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
                     {data: 'fecha'}
                 ]}
             );
         }

         var configurarDataTableErroresClonacion = function () {
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
                 $('a#enviar_actividades').removeClass('m--hidden-desktop');
                 $('a#enviar_actividadesk').fadeIn(800);
             }else{
                 var pendienteeliminar=$(this).attr('value');
                 indice=listadoactividades.indexOf(pendienteeliminar);
                 if(indice>=0) {
                     listadoactividades.splice(indice, 1);
                     if (listadoactividades.length == 0)
                         $('a#enviar_actividades').addClass('m--hidden-desktop');
                 }
             }
         });


         $('div#basicmodal').on('click', 'a#enviar_actividades', function (evento)
         {
             evento.preventDefault();
             var link = Routing.generate('actividad_clonar',{'id':plantrabajo});
             $.ajax({
                 url: link,
                 type: "POST",
                 data: {'array':JSON.stringify(listadoactividades)},
                 beforeSend: function () {
                    // base.blockUI({message: 'Cargando'});
                 },
                 complete: function () {
                     //base.unblockUI();
                 },
                 success: function (data) {
                         refrescarAction();
                         if(data['mensaje']){
                             toastr.success(data['mensaje']);
                             $('div#basicmodal').modal('hide');
                         }
                         else{
                             if(data['error'])
                                toastr.error(data['error']);
                             else
                                 if(data['warning'])
                                    toastr.warning(data['warning']);
                             $('div#basicmodal').html(data['errores']);
                             configurarDataTableErroresClonacion();
                         }

                 },
                 error: function ()
                 {
                     base.Error();
                 }
             });
         });

     }


     var cargarActividadesGeneral=function(){
         $('body').on('click', 'a#cargar_actividadesgeneral_link', function (evento)
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
             var link = Routing.generate('actividadgeneral_clonar');
             $.ajax({
                 url: link,
                 type: "POST",
                 data: {'array':JSON.stringify(listadoactividades)},
                 beforeSend: function () {
                    // base.blockUI({message: 'Cargando'});
                 },
                 complete: function () {
                     //base.unblockUI();
                 },
                 success: function (data) {
                         refrescarAction();
                         if(data['mensaje']){
                             toastr.success(data['mensaje']);
                             $('div#basicmodal').modal('hide');
                         }
                         else{
                             if(data['error'])
                                toastr.error(data['error']);
                             else
                                 if(data['warning'])
                                    toastr.warning(data['warning']);
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
                    estadistica();
                    eliminarActividad();
                    showActividad();
                }
            );
        },
        respuesta: function () {
            $().ready(function () {
                    showRespuesta();
                    edicionRespuesta();
                    newActionRespuesta();
                    agregarArchivoRespuesta();
                    edicionActionRespuesta();
                    eliminarRespuesta();
                    eliminarFichero()
                }
            );
        },
        nuevo: function () {
            $().ready(function () {
                configurarFormularioActividad();
                newActionActividad();
                edicionActionActividad();
                agregarArchivoActividad();
                eliminarArchivo();
                eliminarFichero();
                }
            );
        },
        clonacion: function () {
            $().ready(function () {
                //INICIO DE CLONACION DE MIS ACTIVIDADES
                cargarPlanesAntiguos();
                cargarActividadesAntiguas();
                //FIN DE CLONACION DE MIS ACTIVIDADES
                $('div#basicmodal').on('hide.bs.modal',function(){
                    tableantiguos=null;
                    objantiguo = null;
                    listadoactividades=new Array();
                });
                //INICIO DE LA CLONACION DE LAS ACTIVIDADES DEL PLAN GENERAL
                    cargarActividadesGeneral();
                //FIN DE LA CLONACION DE LAS ACTIVIDADES DEL PLAN GENERAL
                }
            );
        },
    }
}();
