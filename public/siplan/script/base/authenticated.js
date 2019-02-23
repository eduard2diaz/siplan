//CONFIGURACION DE LOS CAMPOS DEL FORMULARIO DE USUARIO
var configurarFormularioUsuario = function () {
    $('select#usuario_jefe').select2({
        dropdownParent: $("#basicmodal"),
        //allowClear: true
    });
    $('select#usuario_area').select2({
        dropdownParent: $("#basicmodal"),
        //allowClear: true
    });
    $('select#usuario_cargo').select2({
        dropdownParent: $("#basicmodal"),
        //allowClear: true
    });
    $('select#usuario_idrol').select2({
        dropdownParent: $("#basicmodal"),
        //allowClear: true
    });
}

//VALIDACION DE LOS CAMPOS DE EDICION DE USUARIOS
function validarEditUser(){
    $("div#basicmodal form#usuario_edit").validate({
        rules:{
            'usuario[nombre]': {required:true},
            'usuario[apellido]': {required:true},
            'usuario[usuario]': {required:true},
            'usuario[correo]': {required:true, email:true},
            'usuario[idrol][]': {required:true},
            'usuario[password][second]': {
                equalTo: "#usuario_password_first"
            }
        },
        highlight: function (element) {
            $(element).parent().parent().addClass('has-danger');
        },
        unhighlight: function (element) {
            $(element).parent().parent().removeClass('has-danger');
            $(element).parent().parent().addClass('has-success');
        }
    });
}

function colorear(enfuncionamiento) {
    var badge_class=enfuncionamiento ? 'success' : 'danger';
    var badge_label=enfuncionamiento ? 'SI' : 'NO';
    var badge_object='<span class="m-badge m-badge--'+badge_class+' m--font-boldest m-badge--wide">\n' +badge_label+'</span>';

    return badge_object
}

//dentro de este tipo de funciones se pueden definir variables y otras funciones
var authenticated = function () {
    var obj = null;

    var actividadesPendientes = function () {
        $.ajax({
            url: Routing.generate('actividades_pendientes.'+_locale),
            type: "GET",
            beforeSend: function () {
                // base.blockUI({message: 'Cargando'});
            },
            complete: function () {
                //   base.unblockUI();
            },
            success: function (data) {
                $('ul#pending_task').html(data['html']);
                if (data['total'] > 0) {
                    $('span.pending_task_counter').html(data['total']);

                }
            },
            error: function () {
                //  base.Error();
            }
        });
    }

    var cargosListener = function () {
        $('div#basicmodal').on('change', 'select#usuario_area', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('cargo_ajax', {'area': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("div#basicmodal div#modal-body",
                            {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando datos..."});
                    },
                    success: function (data) {
                        $('select#usuario_cargo').html(data);
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("div#basicmodal div#modal-body");
                    }
                });
        });

        $('div#basicmodal').on('change', 'select#usuario_jefe', function (evento) {
            if ($(this).val() > 0)
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('area_findbyusuario', {'id': $(this).val()}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando datos..."});
                    },
                    success: function (data) {
                        $('select#usuario_area').html(data);
                        //LANZANDO YO MIMSO EL EVENTO
                        $('select#usuario_area').change();
                    },
                    error: function () {
                        base.Error();
                    },
                    complete: function () {
                        mApp.unblock("body");
                    }
                });
            else
                $.ajax({
                    type: 'get', //Se uso get pues segun los desarrolladores de yahoo es una mejoria en el rendimineto de las peticiones ajax
                    dataType: 'html',
                    url: Routing.generate('area_index.'+_locale,{'_format':'xml'}),
                    beforeSend: function (data) {
                        mApp.block("body",
                            {overlayColor:"#000000",type:"loader",state:"success",message:"Actualizando datos..."});
                    },
                    success: function (data) {
                        $('select#usuario_area').html(data);
                        $('select#usuario_area').change();
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

    //INTERFAZ DE VISUALIZACION DE DETALLES DEL USUARIO
    var usuarioProfile = function () {
        $('body').on('click', 'a.usuarioshow_ajax', function (evento) {
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
                error: function () {
                    base.Error();
                },
                complete: function () {
                    mApp.unblock("body");
                }
            });
        });
    }
    //EVENTO DE ESCUCHA DE EDICION DE USUARIO
    var edicionCurrentUser = function () {
        $('body').on('click', 'a.editar_usuario', function (evento) {
            evento.preventDefault();
            if ($('table#usuario_table').length)
                obj = $(this);
            var link = $(this).attr('data-href');
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
                       configurarFormularioUsuario();
                        $('div#basicmodal').modal('show');
                        validarEditUser();
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
    //PROCESAMIENTO DEL FORMULARIO DE EDICION DE USUARIOS
    var edicionCurrentUserAction = function () {
        $('div#basicmodal').on('submit', 'form#usuario_edit', function (evento) {
            evento.preventDefault();
            var padre = $(this).parent();
            var l = Ladda.create(document.querySelector( '.ladda-button' ) );
            l.start();
            $.ajax({
                url: $(this).attr("action"),
                type: "POST",
                data: $(this).serialize(), //para enviar el formulario hay que serializarlo
                beforeSend: function () {
                    mApp.block("body",
                        {overlayColor:"#000000",type:"loader",state:"success",message:"Cargando..."});
                },
                complete: function () {
                    l.stop();
                    mApp.unblock("body");
                },
                success: function (data) {
                    if (data['error']) {
                        padre.html(data['form']);
                        configurarFormularioUsuario();
                    } else {
                        if (data['mensaje'])
                            toastr.success(data['mensaje']);
                        if ($('table#usuario_table').length) {
                            obj.parents('tr').children('td:nth-child(2)').html(data['nombre']);
                            obj.parents('tr').children('td:nth-child(3)').html(data['area']);
                            obj.parents('tr').children('td:nth-child(4)').html(data['cargo']);
                        }
                        $('div#basicmodal').modal('hide');
                    }
                },
                error: function () {
                    base.Error();
                }
            });
        });
    }

    return {
        init: function () {
            $().ready(function(){
                usuarioProfile();
                edicionCurrentUser();
                edicionCurrentUserAction();
                cargosListener();
            });
        },
    };
}();