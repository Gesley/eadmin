<?php

class App_Minuta_Metadados_Incluir extends Services_Red_Minuta_Metadados_Incluir
{
    public function __construct($data) {        
        $this->dataHoraProducaoConteudo = date('d/m/Y H:i:s');
        $this->numeroPasta = "";
        $this->numeroTipoSigilo = $data['DOCM_ID_CONFIDENCIALIDADE'];
        $this->nomeSistemaIntrodutor = "SISAD";
        $this->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
        $this->secaoOrigemDocumento = "0100";
        $this->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
        $this->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
        $this->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
        $this->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_MINUTA;
        $this->numeroDocumento = "";
        $this->secaoDestinoIdSecao = "0100";
        $this->descricaoTituloDocumento = $data["DOCM_NR_DOCUMENTO"];
    }
}

?>