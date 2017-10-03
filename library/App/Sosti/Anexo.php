<?php
/**
 * Description of Anexo
 *
 * @author Leonan Alves dos Anjos
 */


class App_Sosti_Anexo {
    
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
    
    /**
    * @param 
    * 
    */
    public function renameFile(Zend_Form_Element_File $element_file){
        
        $userfile = $element_file->getFileName();/*caminho completo do arquivo gravado no servidor*/
        $tempDirectory = "temp";
        $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
        $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SOSTITEMPDOC' . date("dmYHisu") . $userfilename;
        $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
        $filterFileRename->filter($userfile);/*Renomeando a partir do caminho completo do arquivo no servidor*/
        
        return realpath($fullFilePath);
    }
    
    public function anexa(Zend_Form_Element_File $element_file)
    {
        
        if ($element_file->isUploaded()) {
                    /*O documento foi carregado para o form*/
                    $element_file->receive();
                    /*O documento foi recebido no servidor*/
                    if ($element_file->isReceived()) {
//                        echo "o documento foi salvo na pasta temp<br/>";
                        /*Renomeando o arquivo gravado no servidor*/
                       
                        $file =  $this->renameFile($element_file);/*caminho completo do arquivo renomeado no servidor*/

                        //$red = new Services_Red_Incluir(true); /*DESENVOLVIMENTO*/
                        $red = new Services_Red_Incluir(false); /*PRODUÇÃO*/
                        $red->debug = false;
                        $red->temp = APPLICATION_PATH . '/../temp';

                        $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $file);
                        //Zend_Debug::dump($this->_parametros_red); Zend_Debug::dump($this->_metadados_red); Zend_Debug::dump($file); Zend_Debug::dump($retornoIncluir_red);
                            
                        if(is_array($retornoIncluir_red)){
                            
                            return $retornoIncluir_red['numeroDocumento'];
                            
                        } else {
                            /*tratamento de erro*/
                            $retornoIncluir_red_array = explode('|', $retornoIncluir_red);
                            $retornoIncluir_red_array_erro["codigo"] = $retornoIncluir_red_array[0];
                            $retornoIncluir_red_array_erro["descricao"] = $retornoIncluir_red_array[1];
                            $retornoIncluir_red_array_erro["idDocumento"] = $retornoIncluir_red_array[2];

                            switch ($retornoIncluir_red_array_erro["codigo"]) {

                                case 'Erro: 80':

                                        return $retornoIncluir_red_array_erro["idDocumento"];

                                    break;
                                default:

                                    return $retornoIncluir_red_array_erro;
                            }
                        }
                    }
                }
        
    }
    
}

?>
