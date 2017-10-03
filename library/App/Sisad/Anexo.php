<?php
/**
 * Description of Anexo
 *
 * @author Leonan Alves dos Anjos
 */


class App_Sisad_Anexo {
    
    public $_parametros_red;
    public $_metadados_red;
    
    public function __construct()
    {
        $this->_parametros_red = new Services_Red_Parametros_Incluir();
        
        $this->_parametros_red->login = Services_Red::LOGIN_USER_EADMIN;
        $this->_parametros_red->ip = Services_Red::IP_MAQUINA_EADMIN;
        $this->_parametros_red->sistema = Services_Red::NOME_SISTEMA_EADMIN;
        $this->_parametros_red->nomeMaquina = Services_Red::NOME_MAQUINA_EADMIN;
        
        
        $this->_metadados_red = new Services_Red_Metadados_Incluir();
        $this->_metadados_red->descricaoTituloDocumento = 'SOLICITAÇÃO DE TI ANEXO';
        $this->_metadados_red->numeroTipoSigilo = Services_Red::NUMERO_SIGILO_PUBLICO;
        /**
        * Tipo de documento
        * Solicitação de serviços a TI
        * TRF1DSV 154
        * TRF1HML 158
        * TRF1    160
        */
        $this->_metadados_red->numeroTipoDocumento = "158";
        $this->_metadados_red->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
        $this->_metadados_red->ipMaquinaResponsavelIntervencao = Services_Red::IP_MAQUINA_EADMIN;
        $this->_metadados_red->secaoOrigemDocumento = "0100";
        $this->_metadados_red->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
        $this->_metadados_red->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
        $this->_metadados_red->nomeMaquinaResponsavelIntervensao = Services_Red::NOME_MAQUINA_EADMIN;
        $this->_metadados_red->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
        $this->_metadados_red->numeroDocumento = "";
        $this->_metadados_red->pastaProcessoNumero = /*"";*/ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
        $this->_metadados_red->secaoDestinoIdSecao = "0100";
        
    }
    
    public function anexa(Zend_File_Transfer_Adapter_Http $element_file,$idDocumento, $dataHora = null)
    {
        $anexos = $element_file->getFileInfo();
        unset($anexos['DOCM_DS_HASH_RED']);
        foreach ($anexos as $value) {
            $userfile = $value['name'];/*caminho completo do arquivo gravado no servidor*/
            $userfilecomp = $value['tmp_name'];
            $tempDirectory = "temp";
            $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SOSTITEMPDOC' . date("dmYHisu") . $userfile;
            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
            $filterFileRename->filter($userfilecomp);/*Renomeando a partir do caminho completo do arquivo no servidor*/

            //$red = new Services_Red_Incluir(true); /*DESENVOLVIMENTO*/
            $red = new Services_Red_Incluir(false); /*PRODUÇÃO*/
            $red->debug = false;
            $red->temp = APPLICATION_PATH . '/../temp';

            $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $fullFilePath);
            //Zend_Debug::dump($this->_parametros_red); Zend_Debug::dump($this->_metadados_red); Zend_Debug::dump($file); Zend_Debug::dump($retornoIncluir_red);
            
            $ssolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
            $anex['ANEX_ID_DOCUMENTO'] = $idDocumento;
            if(isset ($datahora)){
                $anex['ANEX_DH_FASE'] = $datahora;
            }
            if(is_array($retornoIncluir_red)){
                
                $anex['ANEX_NR_DOCUMENTO_INTERNO'] = $retornoIncluir_red['numeroDocumento'];
                $anexar = $ssolSolicitacao->setIncluirAnexo($anex);

            } else {
                /*tratamento de erro*/
                $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                $retornoIncluir_red_array["codigo"] = $retornoIncluir_red_array[0];
                $retornoIncluir_red_array["descricao"] = $retornoIncluir_red_array[1];
                $retornoIncluir_red_array["idDocumento"] = $retornoIncluir_red_array[2];

                switch ($retornoIncluir_red_array["codigo"]) {
                    case 'Erro: 80':
                            $anex['ANEX_NR_DOCUMENTO_INTERNO'] = $retornoIncluir_red_array["idDocumento"];
                            $anexar = $ssolSolicitacao->setIncluirAnexo($anex);
                    break;
                }
           }
        }
        return null;
    }
}

?>