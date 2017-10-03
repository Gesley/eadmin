<?php

/**
 * Description of Anexo
 *
 * @author Pedro Henrique dos Santos Correia
 */
class App_Multiupload_Upload {

    public $_parametros_red;
    public $_metadados_red;

    public function __construct() {
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
        $this->_metadados_red->pastaProcessoNumero = /* ""; */ Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
        $this->_metadados_red->secaoDestinoIdSecao = "0100";
    }

    public function incluirarquivos(Zend_File_Transfer_Adapter_Http $element_file) {
        $anexos = $element_file->getFileInfo();
        if (isset($anexos["DOCM_DS_HASH_RED"])) {
            unset($anexos["DOCM_DS_HASH_RED"]);
        }
        $cont = 0;
        foreach ($anexos as $value) {
            $userfile = $value['name']; /* caminho completo do arquivo gravado no servidor */
            $userfilecomp = $value['tmp_name'];
            $tempDirectory = "temp";
            $userfilename = substr($userfile, strrpos($userfile, $tempDirectory) + strlen($tempDirectory) + 1);
            $fullFilePath = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR . 'SOSTITEMPDOC' . date("dmYHisu") . $userfile;
            $filterFileRename = new Zend_Filter_File_Rename(array('target' => $fullFilePath, 'overwrite' => true));
            $filterFileRename->filter($userfilecomp); /* Renomeando a partir do caminho completo do arquivo no servidor */
            //$red = new Services_Red_Incluir(true); /*DESENVOLVIMENTO*/
            $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
            $red->debug = false;
            $red->temp = APPLICATION_PATH . '/../temp';

            $extensao = explode('.', $userfile);
            $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $ext = $mapperDocumento->retornaCodExtensao(array_pop($extensao));

            $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $fullFilePath);
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
                            $dadosDocumento = $tabelaSadTbDocmDocumento->fetchAll("DOCM_NR_DOCUMENTO_RED = $retornoIncluir_red_array[idDocumento]")->toArray();
                            if (isset($dadosDocumento[0])) {
                                $nrDocRed["existentes"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                                $nrDocRed["existentes"][$cont]["NOME"] = $userfile;
                                $nrDocRed["existentes"][$cont]["ANEX_ID_TP_EXTENSAO"] = $ext[0]['TPEX_ID_TP_EXTENSAO'];
                                $nrDocRed["existentes"][$cont]["NR_DOCUMENTO"] = $dadosDocumento[0]["DOCM_NR_DOCUMENTO"];
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

    /**
     * Realiza a inclusão de um arquivo no red. Você pode passar o nome do sistema para concatenar no novo nome do arquivo
     * @param array $upload
     * @param array $sistema
     * @param array $caminho
     * @return array array(sucesso=>bool, mensagem=>string, status=>string, dados=>array)
     */
    public function incluirArquivoNoRed($upload = array(), $sistema = '', $caminho = '', $mudarNome = false, $assinatura = null) {
        if ($caminho != '') {
            //elimina o espaço que sobra nas bordas da string
            $caminho = trim($caminho);
            //caso não tenha um separador de diretório
            if (substr($caminho, -1) != '/' && substr($caminho, -1) != '\\') {
                $caminho .= DIRECTORY_SEPARATOR;
            }
        } else {
            $caminho = APPLICATION_PATH . '/../temp' . DIRECTORY_SEPARATOR;
        }
        $retorno = array();
        $userfile = $upload['name']; /* caminho completo do arquivo gravado no servidor */
        if ($mudarNome) {
            $novoCaminho = $caminho . strtoupper($sistema) . 'TEMPDOC' . date('dmYHisu') . $userfile;
            //renomea o arquivo no servidor para o novo nome
            $verifica = rename($caminho . $userfile, $novoCaminho);

            if ($verifica == false) {
                //caso caixa nesse if existe uma grande possibilidade do arquivo não
                //ter sido localizado na pasta temp.
                return array(
                    'sucesso' => false
                    , 'mensagem' => 'Não foi possível renomear o arquivo no servidor. Provavelmente o upload do arquivo falhou.'
                    , 'status' => 'error'
                    , 'dados' => array()
                );
            }
        } else {
            $novoCaminho = $caminho . $userfile;
        }

        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                $red = new Services_Red_Incluir(true); /* DESENVOLVIMENTO */
            } else if (APPLICATION_ENV == 'production' || APPLICATION_ENV == 'staging' || APPLICATION_ENV == 'testing') {
                $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
            }
        }

        $red->debug = false;
        $red->temp = APPLICATION_PATH . '/../temp';

        $extensao = explode('.', $userfile);
        $mapperDocumento = new Sisad_Model_DataMapper_Documento();
        $retornoExtensao = $mapperDocumento->retornaCodExtensao(array_pop($extensao));
        $codExtensao = $retornoExtensao[0];

        //tenta incluir o arquivo no RED
        if (is_null($assinatura)) {
            $retornoIncluirRed = $red->incluir($this->_parametros_red, $this->_metadados_red, $novoCaminho);
        } else {
            $caminhoAssinatura = $caminho . $assinatura['name'];
            $retornoIncluirRed = $red->incluirComAssinatura($this->_parametros_red, $this->_metadados_red, $novoCaminho, $caminhoAssinatura);
        }
        //se o documento for cadastrado no red
        if (is_array($retornoIncluirRed)) {
            $retorno = array(
                'sucesso' => true
                , 'mensagem' => 'Documento incluido no RED com sucesso.'
                , 'status' => 'success'
                , 'incluido_na_base' => true
                , 'dados' => array(
                    'ID_DOCUMENTO' => $retornoIncluirRed['numeroDocumento']
                    , 'NOME' => $userfile
                    , 'ANEX_ID_TP_EXTENSAO' => $codExtensao['TPEX_ID_TP_EXTENSAO']
                )
            );
        } else if (strlen($retornoIncluirRed) == 39) {
            //similar a posição "erro" das functions convencionais de inclusão de anexos
            //servidor fora do ar. Pode ser que algum dado também esteja sendo 
            //passado erroneamente por parametro. 
            //o literal 39 na comparação do if é a quantidade de caracteres da mensagem de servidor fora do ar
            $retorno = array(
                'sucesso' => false
                , 'mensagem' => 'Servidor de arquivos fora do ar, não é possivel inserir anexo(s). O procedimento poderá ser efetuado sem anexo.'
                , 'status' => 'error'
                , 'incluido_na_base' => false
                , 'dados' => array()
            );
        } else {
            /* tratamento de erro */
            $retornoIncluirRedArray = explode('|', $retornoIncluirRed);
            $retornoRed['codigo'] = $retornoIncluirRedArray[0];
            $retornoRed['descricao'] = $retornoIncluirRedArray[1];
            $retornoRed['idDocumento'] = $retornoIncluirRedArray[2];

            // se ja existir o documento no red
            if ($retornoRed['codigo'] == 'Erro: 80') {
                $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                $row = $tabelaSadTbDocmDocumento->fetchRow('DOCM_NR_DOCUMENTO_RED = ' . $retornoRed['idDocumento']);
                if (!is_null($row)) {
                    //similar a posição "existente" das functions convencionais de inclusão de anexos
                    //existe no red e foi incluido a algum documento
                    $dadosDocumento = $row->toArray();
                    $retorno = array(
                        'sucesso' => true //Mudei para true para fazer o apontamento do documento 
                        , 'mensagem' => 'O anexo ' . $userfile . ' pertence ao documento número ' . $dadosDocumento['DOCM_NR_DOCUMENTO'] . '.'
                        , 'status' => 'notice'
                        , 'incluido_na_base' => false
                        , 'dados' => array(
                            'ID_DOCUMENTO' => $retornoRed['idDocumento']
                            , 'NOME' => $userfile
                            , 'ANEX_ID_TP_EXTENSAO' => $codExtensao['TPEX_ID_TP_EXTENSAO']
                            , 'NR_DOCUMENTO' => $dadosDocumento['DOCM_NR_DOCUMENTO']
                        )
                    );
                } else {
                    //similar a posição "incluidos" das functions convencionais de inclusão de anexos
                    //existe no red mas não foi incluido em algum documento
                    $retorno = array(
                        'sucesso' => true
                        , 'mensagem' => 'O documento foi "resgatado" do RED com sucesso.'
                        , 'status' => 'success'
                        , 'incluido_na_base' => false
                        , 'dados' => array(
                            'ID_DOCUMENTO' => $retornoRed['idDocumento']
                            , 'NOME' => $userfile
                            , 'ANEX_ID_TP_EXTENSAO' => $codExtensao['TPEX_ID_TP_EXTENSAO']
                        )
                    );
                }
            } else {
                //erro desconhecido
                $retorno = array(
                    'sucesso' => false
                    , 'mensagem' => 'Erro desconhecido: "' . $retornoIncluirRed . '"'
                    , 'status' => 'error'
                    , 'incluido_na_base' => false
                    , 'dados' => array()
                );
            }
        }
        return $retorno;
    }

    public function anexarAoDocumento($caminho) {
        $cont = 0;


        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                $red = new Services_Red_Incluir(true); /* DESENVOLVIMENTO */
            } else if (APPLICATION_ENV == 'production') {
                $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
            }
        }

        $red->debug = false;
        $red->temp = APPLICATION_PATH . '/../temp';

        try {

            $retornoIncluir_red = $red->incluir($this->_parametros_red, $this->_metadados_red, $caminho);
        } catch (Exception $exc) {
            Zend_Debug::dump($exc, 'exceptionssssssss'); //exit;
        }
        if (is_array($retornoIncluir_red)) {
            $nrDocRed["incluidos"][$cont] = $retornoIncluir_red['numeroDocumento'];
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
                        $dadosDocumento = $tabelaSadTbDocmDocumento->fetchAll("DOCM_NR_DOCUMENTO_RED = $retornoIncluir_red_array[idDocumento]")->toArray();
                        if (isset($dadosDocumento[0])) {
                            $nrDocRed["existentes"][$cont]["ID_DOCUMENTO"] = $retornoIncluir_red_array["idDocumento"];
                            $nrDocRed["existentes"][$cont]["NOME"] = $caminho;
                            $nrDocRed["existentes"][$cont]["NR_DOCUMENTO"] = $dadosDocumento[0]["DOCM_NR_DOCUMENTO"];
                        } else {
                            $nrDocRed["incluidos"][$cont] = $retornoIncluir_red_array["idDocumento"];
                        }
                        break;
                    default:
                        return null;
                }
            }
        }
        $cont++;
        //}
        return $nrDocRed;
    }

}

?>