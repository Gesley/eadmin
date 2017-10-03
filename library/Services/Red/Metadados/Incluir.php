<?php

class Services_Red_Metadados_Incluir implements Services_Red_Metadados_Interface 
{

    public $dataHoraProducaoConteudo;
    public $descricaoTituloDocumento;
    public $numeroPasta;
    public $numeroTipoSigilo;
    public $numeroTipoDocumento;
    public $nomeSistemaIntrodutor;
    public $ipMaquinaResponsavelIntervencao;
    public $secaoOrigemDocumento;
    public $prioridadeReplicacao;
    public $espacoDocumento;
    public $nomeMaquinaResponsavelIntervensao;
    public $indicadorAnotacao;
    public $numeroDocumento;
    public $dataHoraProtocolo;
    public $pastaProcessoNumero;
    public $secaoDestinoIdSecao;

    public function __construct() 
    {
        $this->dataHoraProducaoConteudo = date('d/m/Y H:i:s');
        $this->numeroTipoSigilo = "01";
        $this->numeroPasta = "";
        $this->numeroTipoDocumento = "01";
        $this->nomeSistemaIntrodutor = "JURIS";
        $this->ipMaquinaResponsavelIntervencao = "172.16.5.62";
        $this->secaoOrigemDocumento = "0100";
        $this->prioridadeReplicacao = "N";
        $this->espacoDocumento = "I";
        $this->nomeMaquinaResponsavelIntervensao = "DIESP07";
        $this->indicadorAnotacao = "";
        $this->numeroDocumento = "";
        $this->dataHoraProtocolo = date('d/m/Y H:i:s');
        $this->pastaProcessoNumero = "12345";
        $this->secaoDestinoIdSecao = "0100";
    }

    public function getXml() 
    {
        $doc = new DOMDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);

        $metadadosInclusaoProcesso = $doc->createElement('MetadadosInclusao');
        $root->appendChild($metadadosInclusaoProcesso);

        $dataHoraProducaoConteudo = $doc->createAttribute('dataHoraProducaoConteudo');
        $metadadosInclusaoProcesso->appendChild($dataHoraProducaoConteudo);
        $dataHoraProducaoConteudoTexto = $doc->createTextNode($this->dataHoraProducaoConteudo);
        $dataHoraProducaoConteudo->appendChild($dataHoraProducaoConteudoTexto);

        $descricaoTituloDocumento = $doc->createAttribute('descricaoTituloDocumento');
        $metadadosInclusaoProcesso->appendChild($descricaoTituloDocumento);
        $descricaoTituloDocumentoTexto = $doc->createTextNode($this->descricaoTituloDocumento);
        $descricaoTituloDocumento->appendChild($descricaoTituloDocumentoTexto);

        $numeroPasta = $doc->createAttribute('numeroPasta');
        $metadadosInclusaoProcesso->appendChild($numeroPasta);
        $numeroPastaTexto = $doc->createTextNode($this->numeroPasta);
        $numeroPasta->appendChild($numeroPastaTexto);

        $numeroTipoSigilo = $doc->createAttribute('numeroTipoSigilo');
        $metadadosInclusaoProcesso->appendChild($numeroTipoSigilo);
        $numeroTipoSigiloTexto = $doc->createTextNode($this->numeroTipoSigilo);
        $numeroTipoSigilo->appendChild($numeroTipoSigiloTexto);

        $nomeSistemaIntrodutor = $doc->createAttribute('nomeSistemaIntrodutor');
        $metadadosInclusaoProcesso->appendChild($nomeSistemaIntrodutor);
        $nomeSistemaIntrodutorTexto = $doc->createTextNode($this->nomeSistemaIntrodutor);
        $nomeSistemaIntrodutor->appendChild($nomeSistemaIntrodutorTexto);

        $ipMaquinaResponsavelIntervencao = $doc->createAttribute('ipMaquinaResponsavelIntervencao');
        $metadadosInclusaoProcesso->appendChild($ipMaquinaResponsavelIntervencao);
        $ipMaquinaResponsavelIntervencaoTexto = $doc->createTextNode($this->ipMaquinaResponsavelIntervencao);
        $ipMaquinaResponsavelIntervencao->appendChild($ipMaquinaResponsavelIntervencaoTexto);

        $secaoOrigemDocumento = $doc->createAttribute('secaoOrigemDocumento');
        $metadadosInclusaoProcesso->appendChild($secaoOrigemDocumento);
        $secaoOrigemDocumentoTexto = $doc->createTextNode($this->secaoOrigemDocumento);
        $secaoOrigemDocumento->appendChild($secaoOrigemDocumentoTexto);

        $prioridadeReplicacao = $doc->createAttribute('prioridadeReplicacao');
        $metadadosInclusaoProcesso->appendChild($prioridadeReplicacao);
        $prioridadeReplicacaoTexto = $doc->createTextNode($this->prioridadeReplicacao);
        $prioridadeReplicacao->appendChild($prioridadeReplicacaoTexto);

        $espacoDocumento = $doc->createAttribute('espacoDocumento');
        $metadadosInclusaoProcesso->appendChild($espacoDocumento);
        $espacoDocumentoTexto = $doc->createTextNode($this->espacoDocumento);
        $espacoDocumento->appendChild($espacoDocumentoTexto);

        $nomeMaquinaResponsavelIntervensao = $doc->createAttribute('nomeMaquinaResponsavelIntervensao');
        $metadadosInclusaoProcesso->appendChild($nomeMaquinaResponsavelIntervensao);
        $nomeMaquinaResponsavelIntervensaoTexto = $doc->createTextNode($this->nomeMaquinaResponsavelIntervensao);
        $nomeMaquinaResponsavelIntervensao->appendChild($nomeMaquinaResponsavelIntervensaoTexto);

        $indicadorAnotacao = $doc->createAttribute('indicadorAnotacao');
        $metadadosInclusaoProcesso->appendChild($indicadorAnotacao);
        $indicadorAnotacaoTexto = $doc->createTextNode($this->indicadorAnotacao);
        $indicadorAnotacao->appendChild($indicadorAnotacaoTexto);

        $numeroDocumento = $doc->createAttribute('numeroDocumento');
        $metadadosInclusaoProcesso->appendChild($numeroDocumento);
        $numeroDocumentoTexto = $doc->createTextNode($this->numeroDocumento);
        $numeroDocumento->appendChild($numeroDocumentoTexto);

        $secaoDestino = $doc->createElement('SecaoDestino');
        $metadadosInclusaoProcesso->appendChild($secaoDestino);

        $secaoDestinoIdSecao = $doc->createAttribute('idSecao');
        $secaoDestino->appendChild($secaoDestinoIdSecao);
        $secaoDestinoIdSecaoTexto = $doc->createTextNode($this->secaoDestinoIdSecao);
        $secaoDestinoIdSecao->appendChild($secaoDestinoIdSecaoTexto);

        return $doc->saveXML($root);
    }
}