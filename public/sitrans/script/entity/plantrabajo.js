var plantrabajo = function () {
    var table = null;
    var obj = null;

    var configurarFormulario = function () {
        $('select#plantrabajo_anno').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        $('select#plantrabajo_mes').select2({
            dropdownParent: $("#basicmodal"),
            //allowClear: true
        });
        Ladda.bind( '.mt-ladda-btn');
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
                        $("div#basicmodal form").validate({
                            rules:{
                                'plantrabajo[mes]': {required:true},
                                'plantrabajo[anno]': {required:true}
                            }
                        })
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
        table = $('table#plantrabajo_table').DataTable({
            "pagingType": "simple_numbers",
            /*"language": {
                url: datatable_url
            },*/
            columns: [
                {data: 'numero'},
                {data: 'mes'},
                {data: 'anno'},
                {data: 'acciones'}
            ]});
    }

    var newAction = function () {
        $('div#basicmodal').on('submit', 'form#plantrabajo_new', function (evento)
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
                            "acciones": "<ul class='m-nav m-nav--inline m--pull-right'>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-info btn-sm' href=" + Routing.generate('plantrabajo_show',{id:data['id']}) + "><i class='flaticon-eye'></i>Visualizar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-sm edicion' data-href=" + Routing.generate('plantrabajo_edit',{id:data['id']}) + "><i class='flaticon-edit-1'></i>Editar</a></li>" +
                                "<li class='m-nav__item'>" +
                                "<a class='btn btn-danger btn-sm  eliminar_plantrabajo' data-href=" + Routing.generate('plantrabajo_delete',{id:data['id']}) + ">" +
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
        $('div#basicmodal').on('submit', 'form#plantrabajo_edit', function (evento)
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
                    l.start();
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
                        var pagina = table.page();
                        obj.parents('tr').children('td:nth-child(2)').html(data['mes']);
                        obj.parents('tr').children('td:nth-child(3)').html(data['anno']);
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
        $('table#plantrabajo_table').on('click', 'a.eliminar_plantrabajo', function (evento)
        {
            evento.preventDefault();
            var obj = $(this);
            var link = $(this).attr('data-href');
            bootbox.confirm({
                title: "Eliminar plan de trabajo",
                message: "<p>Esta seguro que desea eliminar este plan de trabajo</p>",
                buttons: {
                    confirm: {
                        label: 'Si, estoy seguro',
                        className: 'btn btn-primary'},
                    cancel: {
                        label: 'Cancelar',
                        className: 'btn btn-metal'}
                },
                callback: function (result) {
                    if (result == true)
                        $.ajax({
                            type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                            // dataType: 'html', esta url se comentplantrabajo porque lo k estamos mandando es un json y no un html plano
                            url: link,
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
                    newAction();
                    edicion();
                    edicionAction();
                    eliminar();
                }
            );
        }
    }
}();

