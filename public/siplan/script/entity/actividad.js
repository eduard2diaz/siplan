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
    
    //Confeccion del formulario para una nueva actividad(OPTIMIZADO)
    var configurarFormularioActividad= function () {
        $('input#actividad_fecha').datetimepicker();
        $('input#actividad_fechaf').datetimepicker();
        $('select#actividad_estado').select2({
            dropdownParent: $("#basicmodal"),
        });
        $('select#actividad_areaconocimiento').select2({
            dropdownParent: $("#basicmodal"),
        });
        Ladda.bind( '.mt-ladda-btn', { timeout: 2000 } );

        $('textarea#actividad_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true
        });
    }
    
    //Confeccion del formulario para una nueva respuesta(OPTIMIZADO)
    var configurarFormularioRespuesta= function () {
        Ladda.bind( '.mt-ladda-btn', { timeout: 2000 } );
        $('textarea#respuesta_descripcion').summernote({
            placeholder: 'Escriba una breve descripción sobre la actividad',
            height: 100,
            focus: true,
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

    //Funcionalidad para la carga de formularios de registro y edicion
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

    //Funcionalidad para el  registro de una nueva actividad
    var newActionActividad = function () {
        $('div#basicmodal').on('submit', 'form#actividad_new', function (evento)
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

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "nombre": data['nombre'],
                            "fecha": data['fecha'],
                            "fechaF": data['fechaF'],
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm actividad_show' data-href=" + Routing.generate('actividad_show',{id:data['id']}) + "><i class='flaticon-eye'></i></a>" +
                                "</li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-info btn-sm edicion_actividad' data-href=" + Routing.generate('actividad_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i></a>" +
                                "</li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-danger btn-sm  eliminar_actividad'  data-csrf=" + data['csrf'] +" data-href=" + Routing.generate('actividad_delete',{id:data['id']}) + ">" +
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

    //Funcionalidad para la edicion de una nueva actividad
    var edicionActionActividad = function () {
        $('div#basicmodal').on('submit', 'form#actividad_edit', function (evento)
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

    //CLONACION DE LAS ACTIVIDADES DE PLANES ANTERTIORES
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
             Ladda.bind( '.mt-ladda-btn');
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
                         if(data['mensaje'])
                             toastr.success(data['mensaje']);
                         else
                             if(data['error'])
                                toastr.error(data['error']);
                             else
                                 if(data['warning'])
                                    toastr.warning(data['warning']);
                         $('div#basicmodal').modal('hide');
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
        $('div#basicmodal').on('click', 'a#adicionar_archivo_actividad', function (evento)
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

    /*Funcionlidad que elimina un input de tipo file al formulario, dicho input en realidad no es aun un archivo
     existente entre los ficheros de la aplicacion*/
    var eliminarArchivo = function () {
        $('div#basicmodal').on('click', 'a.eliminar_archivo', function (evento)
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

    return {
        init: function () {
            $().ready(function () {
                    configurarDataTable();
                    refrescar();
                    edicionActividad();
                    edicionRespuesta();
                    showActividad();
                    showRespuesta()
                    newActionActividad();
                    newActionRespuesta();
                    edicionActionActividad();
                    edicionActionRespuesta();
                    eliminarActividad();
                    eliminarRespuesta();
                    estadistica();
                    cargarPlanesAntiguos();
                    cargarActividadesAntiguas();
                    agregarArchivoActividad();
                    agregarArchivoRespuesta();
                    eliminarArchivo();
                    eliminarFichero();

                    $('div#basicmodal').on('hide.bs.modal',function(){
                        tableantiguos=null;
                        objantiguo = null;
                        listadoactividades=new Array();
                    });

                }
            );
        }
    }
}();
