$(document).ready(function() {
    var notificacoes = 0;
    $.ajax({
        url: base_url + '/admin/index/ajaxmensagens',
        dataType: 'html',
        type: 'GET',
        data: this.value,
        contentType: 'application/json',
        processData: false,
        success: function (data) {
            $('#msg').show();
            $("#msg").html(data);
            notificacoes = 1;
            if (notificacoes === 1) {
                $.ajax({
                    url: base_url + '/admin/index/ajaxminhassolicitacoes',
                    dataType: 'html',
                    type: 'GET',
                    data: this.value,
                    contentType: 'application/json',
                    processData: false,
                    beforeSend:function() {
                        $('#fieldSolicitacoes').addClass('carregandoInputSelect');
                    },
                    success: function (data) {
                        $('#conteudoSolicit').show();
                        $("#conteudoSolicit").html(data);
                        $('#fieldSolicitacoes').removeClass('carregandoInputSelect');
                    }
                });
            }
        }
    });
    $.ajax({
        url: base_url + '/admin/index/ajaxextensaoprazo',
        dataType: 'html',
        type: 'GET',
        data: this.value,
        contentType: 'application/json',
        processData: false,
        beforeSend:function() {
            $('#fieldCaixaExtensao').addClass('carregandoInputSelect');
        },
        success: function (data) {
            $('#conteudoExtensao').show();
            $("#conteudoExtensao").html(data);
            $('#fieldCaixaExtensao').removeClass('carregandoInputSelect');
        }
    });
    $.ajax({
        url: base_url + '/admin/index/ajaxminhasnotificacoes',
        dataType: 'html',
        type: 'GET',
        data: this.value,
        contentType: 'application/json',
        processData: false,
        beforeSend:function() {
            $('#fieldNotificacoes').addClass('carregandoInputSelect');
        },
        success: function (data) {
            $('#conteudoNotificacoes').show();
            $("#conteudoNotificacoes").html(data);
            $('#fieldNotificacoes').removeClass('carregandoInputSelect');
        }
    });
   
    $.ajax({
        url: base_url + '/admin/index/ajaxmeusavisos',
        dataType: 'html',
        type: 'GET',
        data: this.value,
        contentType: 'application/json',
        processData: false,
        beforeSend:function() {
            $('#fieldMeusAvisos').addClass('carregandoInputSelect');
        },
        success: function (data) {
            $('#conteudoMeusAvisos').show();
            $("#conteudoMeusAvisos").html(data);
            $('#fieldMeusAvisos').removeClass('carregandoInputSelect');
        }
    });
});