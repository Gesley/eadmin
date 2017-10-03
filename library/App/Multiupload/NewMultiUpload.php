<?php

/**
 * Description of Anexo
 *
 * @author Pedro Henrique dos Santos Correia
 */
class App_Multiupload_NewMultiUpload {

    public $_parametros_red;
    public $_metadados_red;

    public function __construct() {
        $this->_parametros_red = new Services_Red_Parametros_Incluir();
        $this->_metadados_red = new Services_Red_Metadados_Incluir();

        $this->_parametros_red->ip = Services_Red::IP_MAQUINA_EADMIN;
        $this->_parametros_red->login = Services_Red::LOGIN_USER_EADMIN;
        $this->_parametros_red->nomeMaquina = Services_Red::NOME_MAQUINA_EADMIN;
        $this->_parametros_red->sistema = Services_Red::NOME_SISTEMA_EADMIN;



        //Tipo de documento - Solicitação de serviços a TI * TRF1DSV-0154 * TRF1HML-158 * TRF1-160
        $this->_metadados_red->descricaoTituloDocumento = 'SOLICITAÇÃO DE TI ANEXO';
        $this->_metadados_red->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
        $this->_metadados_red->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
        $this->_metadados_red->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
        $this->_metadados_red->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
        $this->_metadados_red->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
        $this->_metadados_red->numeroDocumento = "";
        $this->_metadados_red->numeroTipoDocumento = "158";
        $this->_metadados_red->numeroTipoSigilo = Services_Red::NUMERO_SIGILO_PUBLICO;
        $this->_metadados_red->pastaProcessoNumero = /* ""; */ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
        $this->_metadados_red->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
        $this->_metadados_red->secaoDestinoIdSecao = "0100";
        $this->_metadados_red->secaoOrigemDocumento = "0100";
    }

    public function incluirarquivos($element_file) {
        $anexos = $element_file->getFileInfo();
        if(isset($anexos["DOCM_DS_HASH_RED"])){
            unset ($anexos["DOCM_DS_HASH_RED"]);
        }
        $cont = 0;
        foreach ($anexos as $value) {
            
            /* Tratando os valores do array. 
             * Se vier vazio continua para nao
                gerar erro.
             */
            if($value['name'] == ''){
                continue;
            }
            $userfile = $value["name"]; /* caminho completo do arquivo gravado no servidor */
            $userfilecomp = $value['tmp_name'];
            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SISADTEMPDOC' . date("dmYHisu") . $userfile;
            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
            $filterFileRename->filter($userfilecomp); /* Renomeando a partir do caminho completo do arquivo no servidor */

            if (defined('APPLICATION_ENV')) {
                if (APPLICATION_ENV == 'development') {
                    $red = new Services_Red_Incluir(true); /* DESENVOLVIMENTO */
                } else if (APPLICATION_ENV == 'production') {
                    $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
                } else if (APPLICATION_ENV == 'staging') {
                    $red = new Services_Red_Incluir(true); /* HOMOLOGAÇÃO */
                } else if (APPLICATION_ENV == 'testing') {
                    $red = new Services_Red_Incluir(true); /* TESTING */
                }
            }

            $red->debug = false;
            $red->temp = APPLICATION_PATH . '/../temp';

            $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $fullFilePath);

            $extensao = explode('.', $userfile);
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $ext = $mapperDocumento->retornaCodExtensao($extensao[1]);

            if (is_array($retornoIncluir_red)) {
                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red['numeroDocumento'];
                $nrDocRed["incluidos"][$cont]["NOME"] = $userfile;
                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
            } else {
                if (strlen($retornoIncluir_red) == 39) {
                    $nrDocRed["erro"] = "Servidor de arquivos fora do ar, não é possivel inserir anexo(s). O procedimento poderá ser efetuado sem anexo!<br />erro: $retornoIncluir_red";
                } else {
                    /* tratamento de erro */
                    $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                    $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                    $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                    $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                    switch ($retornoIncluir_red_array["codigo"]) {
                        case 'Erro: 80':
                            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                            $dadosDocumento = $tabelaSadTbDocmDocumento->fetchAll("DOCM_NR_DOCUMENTO_RED = " . $retornoIncluir_red_array['idDocumento'])->toArray();


                            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                            $anexoSisad = $SadTbAnexAnexo->fetchAll("ANEX_NR_DOCUMENTO_INTERNO = " . $retornoIncluir_red_array['idDocumento'])->toArray();
                            if (isset($dadosDocumento[0]) || isset($anexoSisad[0])) {
                                $nrDocRed["existentes"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["existentes"][$cont]["NOME"] = $userfile;
                                $nrDocRed["existentes"][$cont]["NR_DOCUMENTO"] = $dadosDocumento[0]["DOCM_NR_DOCUMENTO"];
                                $nrDocRed["existentes"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
                            } else {
                                $nrDocRed["incluidos"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["incluidos"][$cont]["NOME"] = $userfile;
                                $nrDocRed["incluidos"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
                            }
                            break;
                        default:
                            return null;
                    }
                }
            }
            $cont++;
        }
        return $nrDocRed;
    }

}

?>