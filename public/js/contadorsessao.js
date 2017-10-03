/**
 * time_render é o 'date' de renderização e timeout é o 'date' que espira a sessão medida em segundos
 */
     var 
        futuro = new Date((timeout*1000)),
        hoje = new Date((time_render*1000)),
        dialog_time_out = null,
        intervalo = null,

        ss  = null,
        mm  = null,
        hh  = null,
        dd  = null;

    function atualizaContador() {

          hoje.setSeconds(hoje.getSeconds()+1);

          ss = parseInt((futuro - hoje) / 1000);
          mm = parseInt(ss / 60);
          hh = parseInt(mm / 60);
          dd = parseInt(hh / 960);

          ss = ss - (mm * 60);
          mm = mm - (hh * 60);
          hh = hh - (dd * 24);

          var faltam = '';
          faltam += 'Sua Sessão expira em: <br/>';
          faltam += (toString(hh).length) ? hh+'<span style=\"font-size:10px;\">h</span>&nbsp;' : '';
          faltam += (toString(mm).length) ? mm+'<span style=\"font-size:10px;\">m</span>&nbsp;' : '';
          faltam += ss+'<span style=\"font-size:10px;\">s</span>&nbsp;&nbsp;';

          if (dd+hh+mm+ss > 0) {
            $('#contador').html(faltam);
            return; 
          } else {
            $('#contador').html('<span style="color: red; "><strong>Sessão Expirada</strong></span>');
            clearInterval(intervalo);
            msg = "<strong style='color: red; '>Sessão Expirada!</strong><br/> Um novo login é necessário. <br/>Para redirecionar para a página de login selecione OK ou feche essa janela. ";
            dialog_time_out.append(msg);
            //$(document).append(dialog_time_out);
            dialog_time_out.dialog("open");
            return;
          }
    }
    
    if( !( typeof(timeout) == 'undefined' && typeof(time_render) == 'undefined' ) ){
        intervalo = window.setInterval('atualizaContador()', 1000);
    }

    $(function(){
            dialog_time_out = $("#dialog_time_out");
            dialog_time_out.dialog({
                                  title    : 'Aviso',
                                  autoOpen : false,
                                  modal    : true,
                                  show: 'fold',
                                  hide: 'fold',
                                  resizable: false,
                                  width: 300,
                                  height: 200,
                                  position: ['center'],
                                  buttons : {
                                        Ok: function() {
                                                window.location = base_url+"/login/logout";
                                        }
                                 },
                                 close : function() {
                                                window.location = base_url+"/login/logout";
                                        }

         });
        }
    );
    
    window.setInterval(function() {
        $(document).bind("ajaxSend", function() {
           $("#loading").hide();
         });
//         .bind("ajaxComplete", function() {
//           $("#loading").show();
//         });
        $.ajax({
            url: base_url+"/guardiao/index/session",
            type: 'post',
            dataType: "json",
            success: function(data) {
                $(this).json(data);
            },
        });
    },600000);