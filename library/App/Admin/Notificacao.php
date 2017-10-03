<?php
/**
 * Description of Anexo
 *
 * @author Rafael Nascimento Serrao de Carvalho
 */

class App_Admin_Notificacao {
    
    public function __construct()
    {
        
    }
    
    public function getnotificacoes($matricula)
    {
        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
        $verif = $this->getnotificacoescount($matricula);
        if ($verif[0]["COUNT"] != 0) {
            if ($verif[0]["COUNT"] < 200) {
                $verif = $tabelaNotf->getNotf($matricula);
                echo '<a href="#" title="Notificações do Sistema" id="botao_cx_notf" class=""></a>';
                $i = 1;
                foreach ($verif as $value) {
                    echo '<div id="tooltip_notf_#' . $i . '" class="tooltip_notf" dados="' . $value['NOTF_CD_MATRICULA'] . " - " . $value['NOTF_DH_NOTIFICACACAO'] . '">' .
                    'Sistema: ' . $value['NOTF_NM_SISTEMA_INTRODUTOR'] . "<br/>" .
                    'Assunto: ' . $value['NOTF_DS_TITULO'] . "<br/>" .
                    'Data: ' . $value['DATA_FORMATADA'] . "<br/>" .
                    'Mensagem: ' . $value['NOTF_DS_NOTIFICACAO'] . "<br/>" .
                    '</div>';
                    $i++;
                }
            } else {
                echo'<a href="#" title="Notificações do Sistema" id="botao_cx_notf" class=""></a>';
                echo'<div class="tooltip_notf"> Sua caixa de Notificações está cheia.<br/> 
                                                         Por Favor, exclua algumas.</div>';
            }
        }
    }
    
    public function getnotificacoescount($matricula)
    {
        $tabelaNotf = new Application_Model_DbTable_OcsTbNotfNotificacao();
        $dadosNotf = $tabelaNotf->getnotfCount($matricula);
        return $dadosNotf;
    }
}

?>