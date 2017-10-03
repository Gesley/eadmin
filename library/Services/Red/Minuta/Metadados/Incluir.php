<?php

class Services_Red_Minuta_Metadados_Incluir implements Services_Red_Minuta_Metadados_Interface 
{
    public $dataHoraProducaoConteudo;
    public $descricaoTituloDocumento;
    public $numeroPasta;
    public $numeroTipoSigilo;
    public $nomeSistemaIntrodutor;
    public $ipMaquinaResponsavelIntervencao;
    public $secaoOrigemDocumento;
    public $prioridadeReplicacao;
    public $espacoDocumento;
    public $nomeMaquinaResponsavelIntervensao;
    public $indicadorAnotacao;
    public $numeroDocumento;
    public $secaoDestinoIdSecao;
    public $numeroTipoDocumento;
    public $dataHoraProtocolo;
    public $pastaProcessoNumero;

    public function __construct() {
        $this->dataHoraProducaoConteudo = date('d/m/Y H:i:s');
        $this->numeroPasta = "";
        $this->numeroTipoSigilo = "01";
        $this->nomeSistemaIntrodutor = "JURIS";
        $this->ipMaquinaResponsavelIntervencao = "172.16.5.62";
        $this->secaoOrigemDocumento = "0100";
        $this->prioridadeReplicacao = "N";
        $this->espacoDocumento = "I";
        $this->nomeMaquinaResponsavelIntervensao = "DIESP07";
        $this->indicadorAnotacao = "";
        $this->numeroDocumento = "";
        $this->secaoDestinoIdSecao = "0100";
    }

    public function getXml() {
        $doc = new DOMDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);

        $metadados = $doc->createElement('MetadadosInclusao');
        $root->appendChild($metadados);

        $metadados->setAttribute("dataHoraProducaoConteudo", $this->dataHoraProducaoConteudo);
        $metadados->setAttribute("descricaoTituloDocumento", $this->descricaoTituloDocumento);
        $metadados->setAttribute("numeroPasta", $this->numeroPasta);
        $metadados->setAttribute("numeroTipoSigilo", $this->numeroTipoSigilo);
        $metadados->setAttribute("nomeSistemaIntrodutor", $this->nomeSistemaIntrodutor);
        $metadados->setAttribute("ipMaquinaResponsavelIntervencao", $this->ipMaquinaResponsavelIntervencao);
        $metadados->setAttribute("secaoOrigemDocumento", $this->secaoOrigemDocumento);
        $metadados->setAttribute("prioridadeReplicacao", $this->prioridadeReplicacao);
        $metadados->setAttribute("espacoDocumento", $this->espacoDocumento);
        $metadados->setAttribute("nomeMaquinaResponsavelIntervensao", $this->nomeMaquinaResponsavelIntervensao);
        $metadados->setAttribute("indicadorAnotacao", $this->indicadorAnotacao);
        $metadados->setAttribute("numeroDocumento", $this->numeroDocumento);

        $secaoDestino = $doc->createElement('SecaoDestino');
        $secaoDestino->setAttribute("idSecao", $this->secaoDestinoIdSecao);

        $metadados->appendChild($secaoDestino);

        return $doc->saveXML($root);
    }

}

?>