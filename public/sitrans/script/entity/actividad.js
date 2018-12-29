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

        $('div.block-information').on('click', 'a#plantrabajo_estadistica', function (evento)
        {
            evento.preventDefault();
            var link = $(this).attr('data-href');
            obj = $(this);
            $.ajax({
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    base.blockUI({message: window.loadingMessage});
                },
                success: function (data) {
                    if ($('div#basicmodal').html(data)) {
                        $('div#basicmodal').modal('show');
                        $('span.counterup').counterUp({            delay: 10,
                            time: 1000
                        });
                    }
                },
                error: function ()
                {
                    base.Error();
                },
                complete: function () {
                    base.unblockUI();
                }
            });
        });



    }
    //Configuracion del Datatable de las actividades del plan actual
    var configurarDataTable = function () {
        table = $('table#actividad_table').DataTable({
            "pagingType": "simple_numbers",
            // definimos el valor inicial de elementos por pagina
            columns: [
                {data: 'nombre'},
                {data: 'fecha'},
                {data: 'fechaf'},
                {data: 'estado'},
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
                type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                dataType: 'html',
                url: link,
                beforeSend: function (data) {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando..."});
                },
                success: function (data) {
                    //$('table#table').html(data);
                    $('table#actividad_table').html(data);
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

        $('div.block-information').on('click', 'a.refrescar', function (evento){
            evento.preventDefault();
            refrescarAction();
        });
    }

    var refrescarAction=function(){
        var link = Routing.generate('plantrabajo_show.'+_locale,{'id':plantrabajo});
        obj = $(this);
        $.ajax({
            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
            dataType: 'html',
            url: link,
            beforeSend: function (data) {
                base.blockUI({message: 'Actualizando datos'});
            },
            success: function (data) {
                //$('table#table').html(data);
                $('table#table').html(data);
                table.destroy();
                configurarDataTable();
            },
            error: function ()
            {
                base.Error();
            },
            complete: function () {
                base.unblockUI();
            }
        });
    }

    //Funcionalidad para el dibujo basico de iCheck
    function dibujariCheck() {
        var callbacks_list = $('.demo-callbacks ul');
        $('.demo-list input').on('ifCreated ifClicked ifChanged ifChecked ifUnchecked ifDisabled ifEnabled ifDestroyed', function (event) {
            callbacks_list.prepend('<li><span>#' + this.id + '</span> is ' + event.type.replace('if', '').toLowerCase() + '</li>');
        }).iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
    }
    //Confeccion del formulario para una nueva actividad
    var configurarFormulario= function () {
        $('input#actividad_fecha').datetimepicker();
        $('input#actividad_fechaf').datetimepicker();
        $('select#actividad_estado').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        $('select#actividad_areaconocimiento').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        Ladda.bind( '.mt-ladda-btn', { timeout: 2000 } );
    //    dibujariCheck();
    }

    //Funcionalidad para la carga de formularios de registro y edicion
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

    //Funcionalidad para el  registro de una nueva actividad
    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#actividad_new', function (evento)
        {
            evento.preventDefault();
            var padre = $(this).parent();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    //   base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    //  base.unblockUI();
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormulario();
                    } else {
                        if (data['mensaje'])
                            base.enviarMensaje(null, null, data['mensaje'])

                        $('div#basicmodal').modal('hide');
                        total += 1;
                        var pagina = table.page();
                        objeto = table.row.add({
                            "estado": '<span class="label label-sm label-'+data['estadocolor']+'">'+data['estado']+' </span>',
                            "nombre": '<p data-objetivo="'+data['esobjetivo']+'">'+data['nombre']+'</p>',
                            "fecha": data['fecha'],
                            "fechaf": data['fechaf'],
                            "acciones": "<ul class='list-inline'><li><a class='btn btn-default btn-sm edicion' data-href=" + Routing.generate('actividad_show.'+_locale,{id:data['id']}) + ">Visualizar</a></li><li><a class='btn blue btn-block btn-outline btn-sm edicion' data-href=" + Routing.generate('actividad_edit.'+_locale,{id:data['id']}) + "><i class='fa fa-edit'></i>Editar</a></li><li><a class='btn btn-danger btn-sm  eliminar_actividad' data-href=" + Routing.generate('actividad_delete.'+_locale,{id:data['id']}) + "><i class='fa fa-trash-o'></i>Eliminar</a></li></ul>",
                        });
                        objeto.draw();
                        table.page(pagina).draw('page');
                        //    refrescarAction();
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
    var edicionAction = function () {
        $('div#basicmodal').on('submit', 'form#actividad_edit', function (evento)
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
                        configurarFormulario();
                    } else {
                        if (data['mensaje'])
                            base.enviarMensaje(null, null, data['mensaje'])
                        $('div#basicmodal').modal('hide');
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(1)').children('p').attr('data-objetivo',data["esobjetivo"]);
                        obj.parents('tr').children('td:nth-child(1)').html(data['nombre']);
                        obj.parents('tr').children('td:nth-child(2)').html(data['fecha']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['fechaf']);
                        obj.parents('tr').children('td:nth-child(4)').children('span:nth-child(1)').attr('class','label label-sm label-'+data["estadocolor"]);
                        obj.parents('tr').children('td:nth-child(4)').children('span:nth-child(1)').attr('title',data["estado"]);
                        obj.parents('tr').children('td:nth-child(4)').children('span:nth-child(1)').html(data["estado"]);
                        //   refrescarAction();
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
    var eliminar = function () {
        $('table#table').on('click', 'a.eliminar_actividad', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');

            bootbox.confirm({
                title: "Eliminar actividad?",
                message: "<p>¿Está seguro que desea eliminar esta actividad, <b>esta acción no se podrá deshacer</b>?.</p>",
                buttons: {
                    confirm: {
                        label: 'Sí, estoy seguro',
                        className: 'blue'
                    },
                    cancel: {
                        label: 'No',
                        className: 'default'
                    }
                },
                callback: function (result) {
                    if (result == true)
                        $.ajax({
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            // dataType: 'html', esta url se comentarea porque lo k estamos mandando es un json y no un html plano
                            url: link,
                            beforeSend: function () {
                                base.blockUI({message: 'Eliminando'});
                            },
                            complete: function () {
                                base.unblockUI();
                            },
                            success: function (data) {
                                table.row(obj.parents('tr'))
                                    .remove()
                                    .draw('page');
                                base.enviarMensaje(null, null, data['mensaje']);
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
        $('div.modal').on('click', 'a#cargar_antiguos_link', function (evento)
        {
            evento.preventDefault();
            var padre = $('div#portlet_tab2');
            var link = $(this).attr('data-href');
            $.ajax({
                url: link,
                type: "GET",
                beforeSend: function () {
                    base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    base.unblockUI();
                },
                success: function (data) {
                    if(padre.html(data)) {
                        configurarDataTableAntiguos();
                        dibujariCheck();
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

        $('div.modal').on('draw.dt','table#table_antiguos',function(){
            dibujariCheck();
            $('table#table_antiguos input').iCheck('uncheck');
            $('table#table_antiguos input').each(function(obj,valor){
                if($(this).attr('value')==objantiguo)
                    $(this).iCheck('check');
            });
        });

        $('div.modal').on('ifChecked','table#table_antiguos input',function(){
            objantiguo=$(this).attr('value');
            $('a#cargar_actividades_link').removeClass('hidden');
            $('a#cargar_actividades_link').fadeIn(800);
            $('input#plantrabajo_antiguo').val($(this).attr('value'));
        });
    }

    //PASO 2. Gestion de actividades

    var cargarActividadesAntiguas=function(){
        $('div.modal').on('click', 'a#cargar_actividades_link', function (evento)
        {
            evento.preventDefault();
            var padre = $('div#portlet_tab2');
            var link = Routing.generate('plantrabajo_actividadesajax.'+_locale,{'id':objantiguo});
            $.ajax({
                url: link,
                type: "GET",
                beforeSend: function () {
                    base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    base.unblockUI();
                },
                success: function (data) {
                    if(padre.html(data)) {
                        configurarDataTableAntiguasActividades();
                        dibujariCheck();
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

        $('div.modal').on('draw.dt','table#table_actividadesantiguas',function(){
            dibujariCheck();
            $('table#table_antiguos input').iCheck('uncheck');
            $('table#table_antiguos input').each(function(obj,valor){
                if(listadoactividades.indexOf($(this).attr('value'))!=-1)
                    $(this).iCheck('check');
            });
        });

        $('div.modal').on('ifChecked','table#table_actividadesantiguas input',function(){
            listadoactividades.push($(this).attr('value'));
            $('a#enviar_actividades').removeClass('hidden');
            $('a#enviar_actividadesk').fadeIn(800);
        });
        $('div.modal').on('ifUnchecked','table#table_actividadesantiguas input',function(){
            var pendienteeliminar=$(this).attr('value');
            indice=listadoactividades.indexOf(pendienteeliminar);
            if(indice>=0) {
                listadoactividades.splice(indice, 1);
                if (listadoactividades.length == 0)
                    $('a#enviar_actividades').addClass('hidden');
            }
        });

        $('div.modal').on('click', 'a#enviar_actividades', function (evento)
        {
            evento.preventDefault();
            var link = Routing.generate('actividad_clonar.'+_locale,{'id':plantrabajo});
            $.ajax({
                url: link,
                type: "POST",
                data: {'array':JSON.stringify(listadoactividades)},
                beforeSend: function () {
                    base.blockUI({message: 'Cargando'});
                },
                complete: function () {
                    base.unblockUI();
                },
                success: function (data) {
                    if(!data['error']){
                        refrescarAction();
                        $('div#basicmodal').modal('hide');
                        estadistica();
                    }
                    base.enviarMensaje('Confirmacion','success',data['mensaje']);
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
                    newAction();
                    edicion();
                    edicionAction();
                    eliminar();
                    estadistica();
                    cargarPlanesAntiguos();
                    cargarActividadesAntiguas();

                    $('div#basicmodal').on('hide.bs.modal',function(){
                        tableantiguos=null;
                        objantiguo = null;
                    });

                }
            );
        }
    }
}();
