<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Sisad_GerenciaredController extends Zend_Controller_Action {

    /**
     * Timer para mensuracao do tempo de carregamento da pagina
     *
     * @var int $_temporizador
     */
    private $_temporizador;

    public function postDispatch() {
        // Apresenta o tempo de carregamento da pagina
        $this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo();
    }

    public function init() {
        // Timer para mensuracao do tempo de carregamento da pagina
        $this->_temporizador = new Trf1_Admin_Timer ();
        $this->_temporizador->Inicio();

        /* Initialize action controller here */
        $this->view->titleBrowser = 'e-Sisad';
    }

    public function indexAction() {


        $this->view->title = "Testes do REd";
    }

    public function incluirAction() {

        $this->view->title = "Testes do REd";
        echo "incluir";
        exit;
        $nomeDocumento = 'g';
        $extencaoDocumento = '.pdf';

        $parametros = new Services_Red_Parametros_Incluir();
        $parametros->login = 'TR224PS';
        $parametros->ip = '172.16.5.62';
        $parametros->sistema = 'SISAD';
        $parametros->nomeMaquina = 'DISAD010-TRF1';

        $metadados = new Services_Red_Metadados_Incluir();
        //$metadados->dataHoraProducaoConteudo = "30/01/2009 11:15:00";
        $metadados->descricaoTituloDocumento = "Primeiro teste inclusao1";
        $metadados->numeroTipoSigilo = Services_Red::NUMERO_SIGILO_PUBLICO;
        $metadados->numeroTipoDocumento = "01";
        $metadados->nomeSistemaIntrodutor = "SISAD";
        $metadados->ipMaquinaResponsavelIntervencao = "172.16.5.62";
        $metadados->secaoOrigemDocumento = "0100";
        $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
        $metadados->espacoDocumento = "I";
        $metadados->nomeMaquinaResponsavelIntervensao = "DIESP07";
        $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
        $metadados->numeroDocumento = "";
        //$metadados->dataHoraProtocolo = "30/01/2008 11:15:00";
        $metadados->pastaProcessoNumero = "12345";
        $metadados->secaoDestinoIdSecao = "0100";


        /* Zend_Debug::dump($parametros);
          Zend_Debug::dump($metadados); */

        $red = new Services_Red_Incluir(true);
        $red->debug = true;
        $red->temp = APPLICATION_PATH . '/../temp';

        echo '<pre>Retorno RED<br>';

        $nomeArquivoDocumento = $nomeDocumento . $extencaoDocumento;
        $file = APPLICATION_PATH . '/../temp/' . $nomeArquivoDocumento;
        Zend_Debug::dump($file);


        //Zend_Debug::dump($red->getAutorizacao());
        Zend_Debug::dump($red->incluir($parametros, $metadados, $file));
    }

    /* Antiga action utilizada e substituída pela abaixo para contemplar recebimento de arquivo em Html */

    public function _recuperarAction() {
//        $numeroDocumento = '464410100228';
//        $numeroDocumento = '464430100207';
//        $numeroDocumento = '464470100240';
//        $numeroDocumento = '464440100234';

        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');
        $numeroDocumento = Zend_Filter::FilterStatic($this->_getParam('dcmto'), 'Alnum');
        $tipoDoc = Zend_Filter::FilterStatic($this->_getParam('tipo'), 'Alnum');

        if (!$tipoDoc) {
            $tipoDoc = 1; //pdf
        }

        $userNs = new Zend_Session_Namespace('userNs');

        if ($idDocumento != '') {
            $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();

            $server = new Zend_Json_Server_Request_Http();
            $data = Zend_Json::decode($server->getRawJson());
            $aNamespace = new Zend_Session_Namespace('userNs');

            $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);

            if (!$DocmDocumento) {
                $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTORascunho($idDocumento);
            }

            if ($DocmDocumento) {
                $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();
                if ($DocmDocumento[DTPD_NO_TIPO] == "Processo administrativo") {
                    $DocumentosProcesso = $SadTbDocmDocumento->getIdProcesso($idDocumento);
                    //Zend_debug::dump($DocumentosProcesso[0][ID_PROCESSO]);
                    $temVista = $SadTbPapdParteProcDoc->verificaParteVista(null, $DocumentosProcesso[0]['DCPR_ID_PROCESSO_DIGITAL'], 3); //3 = Tem vista
                } elseif ($DocmDocumento[DTPD_NO_TIPO] == "Minuta") {
                    $eRedator = $SadTbPapdParteProcDoc->verificaParteVista($idDocumento, null, 4); //4 = Redator
                    $eParticipante = $SadTbPapdParteProcDoc->verificaParteVista($idDocumento, null, 5); //5 = participantes

                    if (($eRedator) || ($eParticipante)) {
                        $temVista = 'S';
                    } else {
                        $temVista = 'N';
                    }
                } else {
                    //Zend_debug::dump($idDocumento); 
                    $temVista = $SadTbPapdParteProcDoc->verificaParteVista($idDocumento, null, 3); //3 = Tem vista
                }

                $sigiloso = 'N';

                if (in_array($DocmDocumento[CONF_ID_CONFIDENCIALIDADE], array("1", "3", "4", "5"))) {
                    $sigiloso = 'S';

                    if ($DocmDocumento[CONF_ID_CONFIDENCIALIDADE] == "5") {
                        $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                        $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->verificaPermissaoCorregedoria();
                    }

                    if (($temVista) || (!empty($UsuarioCorregedoria))) {
                        $sigiloso = 'N';
                    } else {
                        $sigiloso = 'S';
                    }
                }
            } else {
                echo 'Documento não encontrado.';
                return;
            }

            if ($sigiloso == 'S') {
                echo 'Documento confidencial.';
                return;
            }
        } else {
            echo 'Documento não encontrado.';
            return;
        }

        if ($numeroDocumento == '') {
            echo 'Este documento não possui arquivo.';
            return;
        }


        $e_arquivo_do_documento = FALSE;
        /**
         * Verifica se é uma numero do repositório correspondente ao documento  
         */
        $proprio_documento = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento AND DOCM_NR_DOCUMENTO_RED = $numeroDocumento");
        if (!is_null($proprio_documento)) {
            $e_arquivo_do_documento = TRUE;
        }

        /*         * *
         * Verifica se é anexo do documento
         */
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        $proprio_documento = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idDocumento AND ANEX_NR_DOCUMENTO_INTERNO = $numeroDocumento");

        if (!is_null($proprio_documento)) {
            $e_arquivo_do_documento = TRUE;
        }
        if (!$e_arquivo_do_documento) {
            echo 'Número de repositório não corresponde ao número do documento.';
            return;
        }

        //$extencaoDocumento = '.pdf';

        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($tipoDoc);
        $extencaoDocumento = '.' . $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];

        $parametros = new Services_Red_Parametros_Recuperar();
        $parametros->ip = substr($_SERVER['REMOTE_ADDR'], 0, 15);

        if (defined('APPLICATION_ENV')) {
            if (APPLICATION_ENV == 'development') {
                $parametros->login = 'TR227PS';
            } else if (APPLICATION_ENV == 'production') {
                $parametros->login = $userNs->matricula;
            }
        }
        $parametros->sistema = 'EADMIN';
        $parametros->nomeMaquina = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
        $parametros->numeroDocumento = $numeroDocumento;

//        $parametros = new Services_Red_Parametros_Recuperar();
//        $parametros->ip = '172.16.5.62';
//        $parametros->login = 'TR227PS';
//        $parametros->sistema = 'SISAD';
//        $parametros->nomeMaquina = 'DISAD010-TRF1';
//        $parametros->numeroDocumento = $numeroDocumento;

        try {

            if (defined('APPLICATION_ENV')) {
                if (APPLICATION_ENV == 'development') {
                    $red = new Services_Red_Recuperar(true); /* DESENVOLVIMENTO */
                } else if (APPLICATION_ENV == 'production') {
                    $red = new Services_Red_Recuperar(false); /* PRODUÇÃO */
                }
            }

            $red->debug = false;
            $retorno = $red->recuperar($parametros);

            $arquivo = $red->openHttpsUrl($retorno['url']);

            $tmpfname_aux = APPLICATION_PATH . '/../temp';
            $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp" . DIRECTORY_SEPARATOR;
            $tmpfname = $numeroDocumento . $extencaoDocumento;
            $tmpfname = $tmpfname_aux . $tmpfname;

            if (!$arquivo) {
                echo 'Documento não encontrado.';
                return;
            }
            $handle = fopen($tmpfname, "w");
            fwrite($handle, $arquivo);
            fclose($handle);
        } catch (Exception $exc) {
            //echo $exc->getMessage();
            $endereco_absoluto = realpath(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento);
            unlink($endereco_absoluto);
            echo 'Ocorreu um erro.</br>';
            echo $exc->getMessage();
            return;
        }

        /* $this->_helper->layout->disableLayout();
          header("Content-Type: application/pdf"); */
        //echo file_get_contents(APPLICATION_PATH . '/../temp/'.$numeroDocumento.$extencaoDocumento);        

        if ($recuperarVersao) {
            return $arquivo;
        } else {
            $download = new App_DownloadFile;
            $download->download(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento, $numeroDocumento . $extencaoDocumento, strlen($arquivo));

            $endereco_absoluto = realpath(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento);
            unlink($endereco_absoluto);
        }
    }

    public function recuperarhtmlAction() {
        /* Recuperar arquivo html chamado de um ajax
         */

        //$this->_helper->layout->disableLayout();
        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');
        $numeroDocumento = Zend_Filter::FilterStatic($this->_getParam('dcmto'), 'Alnum');
        $tipoDoc = Zend_Filter::FilterStatic($this->_getParam('tipo', 1), 'Alnum');
        $versao = Zend_Filter::FilterStatic($this->_getParam('versao'), 'Alnum');
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;
        $response = new stdClass();
        try {
            $arquivo = $this->recuperar($idDocumento, $numeroDocumento, $matricula);
            $response->success = true;
            $response->arquivo = $arquivo;
            $this->_helper->json->sendJson($response);
        } catch (Exception $exc) {
            $response->success = false;
            $response->erro = $exc->getMessage();
            $this->_helper->json->sendJson($response);
        }
    }

    public function recuperarAction() {
        /**
         * Ajusta o memory limit para 256M para permitir a recuperação de arquivos de até 50Megas sem estourar os 128Megas padrão
         */
        ini_set("memory_limit", "256M");

        /**
         * Parametros de recuperação passados via get
         */
        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');
        $numeroDocumento = Zend_Filter::FilterStatic($this->_getParam('dcmto'), 'Alnum');
        $tipoDoc = Zend_Filter::FilterStatic($this->_getParam('tipo', 1), 'Alnum');

        /**
         * Variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;


        /**
         * Chamada da função de controller de reuperação de documentos
         */
        try {
            $arquivo = $this->recuperar($idDocumento, $numeroDocumento, $matricula);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }

        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        
        /**
         * Se for o documento principal recupera utilizando a tabela do tipo de 
         * extensão
         */
        if($this->_getParam('principal') == 1) {
           /**
         * Recuperação do Banco de dados o tipo de extensão do arquivo
         * Valor padrão é 1 tipo PDF
         */
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($tipoDoc);
        $extencaoDocumento = '.' . $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];
        } else {
           /**
            * Recupera pelo nome do arquivo a extensao originalmente cadastrada
            */
   //        $arrayDoc = explode(".", $this->_getParam('nome'));
   //        $extencaoDocumento = '.'.$arrayDoc[count($arrayDoc)-1];
            $extencaoDocumento = '.'.$this->_getParam('extensao');
        }

        $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);

        if (!$DocmDocumento) {
            $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTORascunho($idDocumento);
        }

        if (!$DocmDocumento) {
            throw new Exception('Documento não encontrado.');
        }

        $nDownload = $DocmDocumento['DOCM_NR_DOCUMENTO'];

        if ($tipoDoc == 3) {
            $subject = $arquivo;
            $pattern = "/97-2003/";
            $retorno = array();

            $resultado = preg_match($pattern, $subject, $retorno);

            if ($resultado === 1) {
                $extencaoDocumento = ".doc";
            } else {
                $extencaoDocumento = ".docx";
            }
        }

        /**
         * Endereço temporário do arquivo recuperado
         */
        $tmpfname_aux = APPLICATION_PATH . '/../temp';
        $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp" . DIRECTORY_SEPARATOR;

        /**
         * Construção do nome do arquivo com extensão.
         */
        $tmpfname = $numeroDocumento . $extencaoDocumento;
        $tmpfname = $tmpfname_aux . $tmpfname;

        /**
         * Verifica se o arquivo não é vazio
         */
        if (!$arquivo) {
            echo 'Documento não encontrado.';
            return;
        }

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $arquivo);
        fclose($handle);
        $lengthArquivo = strlen($arquivo);
        unset($arquivo);

        $download = new App_DownloadFile;
        if ($download->isOpenInBrowser($numeroDocumento . $extencaoDocumento)) {
            $download->open(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento, $numeroDocumento . $extencaoDocumento, $lengthArquivo);
        } else {
            $download->download(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento, $nDownload . $extencaoDocumento, $lengthArquivo);
        }

        $endereco_absoluto = realpath(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento);
        unlink($endereco_absoluto);
    }

    public function retornaArquivoParaAssinaturaAction() {



        /**
         * Ajusta o memory limit para 256M para permitir a recuperação de arquivos de até 50Megas sem estourar os 128Megas padrão
         */
        ini_set("memory_limit", "256M");

        /**
         * Parametros de recuperação passados via get
         */
        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');

        /**
         * Variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;

        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();

        $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);
        if (!$DocmDocumento) {
            $this->_helper->json->sendJson(array('ERROR' => ' Documento não encontrado no banco de dados.'));
            return;
        }

        if ($DocmDocumento['DTPD_NO_TIPO'] == 'Processo administrativo') {
            $this->_helper->json->sendJson(array('ERROR' => 'Não é possível assinar processos administrativos.'));
            return;
        }

        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($DocmDocumento['DOCM_ID_TP_EXTENSAO']);
        $extencaoDocumento = $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];
//        if ('pdf' != strtolower($extencaoDocumento)) {
//            $this->_helper->json->sendJson(array('ERROR' => 'É possível assinar apenas arquivos PDF.'));
//            return;
//        }
        /**
         * Chamada da função de controller de reuperação de documentos
         */
        try {
            $arquivo = $this->recuperar($idDocumento, $DocmDocumento['DOCM_NR_DOCUMENTO_RED'], $matricula, true);
        } catch (Exception $exc) {
            $this->_helper->json->sendJson(array('ERROR' => $exc->getMessage()));
            return;
        }

        if (empty($arquivo['ARQUIVO'])) {
            $this->_helper->json->sendJson(array('ERROR' => ' Documento não encontrado.'));
            return;
        }

        //se tiver assinatura
        if (!empty($arquivo['ASSINATURA'])) {
            $this->_helper->json->sendJson(array('ERROR' => 'O arquivo já possui assinatura.'));
            return;
        }
        /**
         * Recuperação do Banco de dados o tipo de extensão do arquivo
         * Valor padrão é 1 tipo PDF
         */
        /**
         * Endereço temporário do arquivo recuperado
         */
        $tmpfname_aux = APPLICATION_PATH . '/../temp';
        $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp" . DIRECTORY_SEPARATOR;

        /**
         * Construção do nome do arquivo com extensão.
         */
        $nomeArquivo = 'ASSINATURA_' . date('dmYHisu');
        $tmpfname = $tmpfname_aux . $nomeArquivo;

        //grava o arquivo
        $handle = fopen($tmpfname . '.pdf', "w");
        fwrite($handle, $arquivo['ARQUIVO']);
        fclose($handle);
        //lê o arquivo
        //EDITAR O ARQUIVO COLOCANDO UM RODAPÉ NO DOCUMENTO
        //SOBRESCREVE O DOCUMENTO NA TEMP
        $pdf = Zend_Pdf::load($tmpfname . '.pdf');

        $altura = 750;

        $paginaAssinatura = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
        $paginaAssinatura->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD), 14);
        $paginaAssinatura->drawText('Dados sobre assinatura:', 10, $altura + 35, 'UTF-8');
        $paginaAssinatura->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ROMAN), 10);
        $paginaAssinatura->drawText('Documento assinador digitalmente conforme MP 2.200-2/2001, Lei 11.419/2006, Resolução 397/2004/CJF e IN-13-04 /TRF-1ª Região', 10, $altura + 21, 'UTF-8');
        $paginaAssinatura->drawText($userNs->nome . ' em ' . date('d/m/Y') . ' pelo sistema e-Admin/e-Sisad', 10, $altura + 11, 'UTF-8');
        $paginaAssinatura->drawText('Pesquise o documento em http://sistemas.trf1.jus.br/app/e-Admin/sisad/pesquisadcmto pelo número ' . $DocmDocumento['DOCM_NR_DOCUMENTO'], 10, $altura + 1, 'UTF-8');
        $pdf->pages[] = $paginaAssinatura;
        $pdf->save($tmpfname . '_1.pdf');

        unlink(realpath($tmpfname . '.pdf'));

        $handle = fopen($tmpfname . '_1.pdf', "r");
        $binarioArquivo = fread($handle, filesize($tmpfname . '_1.pdf'));
        fclose($handle);
        $hexadecimalArquivo = bin2hex($binarioArquivo);
        $retorno = array('NOME_ARQUIVO' => $nomeArquivo . '_1.pdf', 'HEXADECIMAL_ARQUIVO' => $hexadecimalArquivo, 'ERROR' => '');
        $this->_helper->json->sendJson($retorno);
    }

    public function retornaArquivoEAssinaturaAction() {


        /**
         * Ajusta o memory limit para 256M para permitir a recuperação de arquivos de até 50Megas sem estourar os 128Megas padrão
         */
        ini_set("memory_limit", "256M");

        /**
         * Parametros de recuperação passados via get
         */
        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');

        /**
         * Variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;

        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();

        $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);
        if (!$DocmDocumento) {
            $this->_helper->json->sendJson(array('ERROR' => 'Documento não encontrado no banco de dados.'));
            return;
        }

        if ($DocmDocumento['DTPD_NO_TIPO'] == 'Processo administrativo') {
            $this->_helper->json->sendJson(array('ERROR' => 'Não existe assinatura para processos administrativos.'));
            return;
        }

        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($DocmDocumento['DOCM_ID_TP_EXTENSAO']);
        $extencaoDocumento = $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];
//        if ('pdf' != strtolower($extencaoDocumento)) {
//            $this->_helper->json->sendJson(array('ERROR' => 'É possível assinar apenas arquivos PDF.'));
//            return;
//        }
        /**
         * Chamada da função de controller de reuperação de documentos
         */
        try {
            $arquivo = $this->recuperar($idDocumento, $DocmDocumento['DOCM_NR_DOCUMENTO_RED'], $matricula, true);
        } catch (Exception $exc) {
            $this->_helper->json->sendJson(array('ERROR' => $exc->getMessage()));
            return;
        }

        if (empty($arquivo['ARQUIVO'])) {
            $this->_helper->json->sendJson(array('ERROR' => 'Documento não encontrado.'));
            return;
        }

        //se tiver assinatura
        if (empty($arquivo['ASSINATURA'])) {
            $this->_helper->json->sendJson(array('ERROR' => 'O arquivo não possui assinatura.'));
            return;
        }
        /**
         * Recuperação do Banco de dados o tipo de extensão do arquivo
         * Valor padrão é 1 tipo PDF
         */
        /**
         * Endereço temporário do arquivo recuperado
         */
        $tmpfname_aux = APPLICATION_PATH . '/../temp';
        $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp" . DIRECTORY_SEPARATOR;

        /**
         * Construção do nome do arquivo com extensão.
         */
        $nomeArquivo = 'ASSINATURA_' . date('dmYHisu');
        $tmpfname = $tmpfname_aux . $nomeArquivo;

        $hexadecimalArquivo = bin2hex($arquivo['ARQUIVO']);
        $hexadecimalAssinatura = bin2hex($arquivo['ASSINATURA']);
        $retorno = array('HEXADECIMAL_ARQUIVO' => $hexadecimalArquivo, 'HEXADECIMAL_ASSINATURA' => $hexadecimalAssinatura, 'ERROR' => '');
        $this->_helper->json->sendJson($retorno);
    }

    public function donwloadArquivoEAssinaturaCompactadoAction() {


        /**
         * Ajusta o memory limit para 256M para permitir a recuperação de arquivos de até 50Megas sem estourar os 128Megas padrão
         */
        ini_set("memory_limit", "256M");

        /**
         * Parametros de recuperação passados via get
         */
        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');

        /**
         * Variáveis de sessão
         */
        $userNs = new Zend_Session_Namespace('userNs');
        $matricula = $userNs->matricula;

        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();

        $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);
        if (!$DocmDocumento) {
            $this->_helper->json->sendJson(array('ERROR' => 'Documento não encontrado no banco de dados.'));
            return;
        }

        if ($DocmDocumento['DTPD_NO_TIPO'] == 'Processo administrativo') {
            $this->_helper->json->sendJson(array('ERROR' => 'Não existe assinatura para processos administrativos.'));
            return;
        }

        $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($DocmDocumento['DOCM_ID_TP_EXTENSAO']);
        $extencaoDocumento = $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];
//        if ('pdf' != strtolower($extencaoDocumento)) {
//            $this->_helper->json->sendJson(array('ERROR' => 'É possível assinar apenas arquivos PDF.'));
//            return;
//        }
        /**
         * Chamada da função de controller de reuperação de documentos
         */
        try {
            $arquivo = $this->recuperar($idDocumento, $DocmDocumento['DOCM_NR_DOCUMENTO_RED'], $matricula, true);
        } catch (Exception $exc) {
            $this->_helper->json->sendJson(array('ERROR' => $exc->getMessage()));
            return;
        }

        if (empty($arquivo['ARQUIVO'])) {
            $this->_helper->json->sendJson(array('ERROR' => 'Documento não encontrado.'));
            return;
        }

        //se tiver assinatura
        if (empty($arquivo['ASSINATURA'])) {
            $this->_helper->json->sendJson(array('ERROR' => 'O arquivo não possui assinatura.'));
            return;
        }
        /**
         * Recuperação do Banco de dados o tipo de extensão do arquivo
         * Valor padrão é 1 tipo PDF
         */
        /**
         * Endereço temporário do arquivo recuperado
         */
        $caminho = substr(APPLICATION_PATH, 0, strpos(APPLICATION_PATH, 'application')) . 'temp' . DIRECTORY_SEPARATOR;


        App_Utilidades_Arquivo::gravaBinarioNaTemp($arquivo['ARQUIVO'], $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf');
        App_Utilidades_Arquivo::gravaBinarioNaTemp($arquivo['ASSINATURA'], $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf.P7s');

        $caminhoArquivo = $caminho . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf';
        $caminhoAssinatura = $caminho . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf.P7s';
        $caminhoZip = $caminho . 'assinatura_documento_' . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.zip';

        App_Utilidades_Arquivo::compactarComZip(array(
            array('caminho' => $caminhoArquivo, 'nome' => $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf'),
            array('caminho' => $caminhoAssinatura, 'nome' => $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf.P7s')
                ), $caminhoZip);

        $endereco_absoluto = realpath($caminho . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf');
        unlink($endereco_absoluto);
        $endereco_absoluto = realpath($caminho . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.pdf.P7s');
        unlink($endereco_absoluto);

        $lengthArquivo = filesize($caminhoZip);


        $download = new App_DownloadFile;
        $download->download(
                $caminhoZip
                , 'assinatura_documentdo_' . $DocmDocumento['DOCM_NR_DOCUMENTO'] . '.zip'
                , $lengthArquivo);
        $endereco_absoluto = realpath($caminhoZip);
        unlink($endereco_absoluto);
    }

    public function recuperar($idDocumento, $numeroDocumento, $matricula, $comAssinatura = false) {
        //$envProducao = false;
        //if (APPLICATION_ENV == 'development') {
        //    $matricula = 'TR227PS';
        //    $envProducao = true;
        // }

        $userNs = new Zend_Session_Namespace('userNs');

        if ($idDocumento == '') {
            throw new Exception('Numero do documento nao envidado');
        }

        if ($numeroDocumento == '') {
            throw new Exception('Este documento não possui arquivo.');
        }

        if ($matricula == '') {
            throw new Exception('Usuario invalido.');
        }

        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();

        $server = new Zend_Json_Server_Request_Http();
        $data = Zend_Json::decode($server->getRawJson());
        //$aNamespace = new Zend_Session_Namespace('userNs');

        $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTO($idDocumento);

        if (!$DocmDocumento) {
            $DocmDocumento = $SadTbDocmDocumento->getDadosDCMTORascunho($idDocumento);
        }

        if (!$DocmDocumento) {
            throw new Exception('Documento não encontrado.');
        }

        $SadTbPapdParteProcDoc = new Application_Model_DbTable_SadTbPapdParteProcDoc();


        if ($DocmDocumento['DTPD_NO_TIPO'] == "Processo administrativo") {
            $DocumentosProcesso = $SadTbDocmDocumento->getIdProcesso($idDocumento);
            //Zend_debug::dump($DocumentosProcesso[0][ID_PROCESSO]);
            $temVista = $SadTbPapdParteProcDoc->verificaParteVista(null, $DocumentosProcesso[0]['DCPR_ID_PROCESSO_DIGITAL'], 3); //3 = Tem vista
        } else {
            //Zend_debug::dump($idDocumento); 
            $temVista = $SadTbPapdParteProcDoc->verificaParteVista($idDocumento, null, 3); //3 = Tem vista
        }

        $sigiloso = 'N';

        if (in_array($DocmDocumento['CONF_ID_CONFIDENCIALIDADE'], array("1", "3", "4", "5"))) {
            $sigiloso = 'S';

            if ($DocmDocumento['CONF_ID_CONFIDENCIALIDADE'] == "5") {
                $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
                if ($envProducao == false) {
                    $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->getPossuiPerfil(36, $matricula); //DSV
                } else if ($envProducao == true) {
                    $UsuarioCorregedoria = $OcsTbPupePerfilUnidPessoa->getPossuiPerfil(38, $matricula); //PRD
                }
            }

            if (($temVista) || (!empty($UsuarioCorregedoria))) {
                $sigiloso = 'N';
            }
        }

        if ($sigiloso == 'S') {
            throw new Exception('Documento confidencial.');
        }

        $e_arquivo_do_documento = FALSE;

        /**
         * Verifica se é uma numero do repositório correspondente ao documento  
         */
        $proprio_documento = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = $idDocumento AND DOCM_NR_DOCUMENTO_RED = $numeroDocumento");

        if (!is_null($proprio_documento)) {
            $e_arquivo_do_documento = TRUE;
        }

        /*         * *
         * Verifica se é anexo do documento
         */
        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
        $proprio_documento = $SadTbAnexAnexo->fetchRow("ANEX_ID_DOCUMENTO = $idDocumento AND ANEX_NR_DOCUMENTO_INTERNO = $numeroDocumento");

        if (!is_null($proprio_documento)) {
            $e_arquivo_do_documento = TRUE;
        }

        if (!$e_arquivo_do_documento) {
            throw new Exception('MSG: Número de repositório não corresponde ao número do documento.');
        }

        $parametros = new Services_Red_Parametros_Recuperar();
        $parametros->ip = substr($_SERVER['REMOTE_ADDR'], 0, 15);
        $parametros->login = $matricula;
        $parametros->sistema = 'EADMIN';
        $parametros->nomeMaquina = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
        $parametros->numeroDocumento = $numeroDocumento;

        try {

            $red = new Services_Red_Recuperar($envProducao);
            $red->debug = false;

            if ($comAssinatura) {
                $retorno = $red->recuperar($parametros, true);
                $retorno = array(
                    'ARQUIVO' => $red->openHttpsUrl($retorno['URL_ARQUIVO']),
                    'ASSINATURA' => $red->openHttpsUrl($retorno['URL_ASSINATURA'])
                );
            } else {
                $retorno = $red->recuperar($parametros);
                $retorno = $red->openHttpsUrl($retorno['url']);
            }
            return $retorno;
        } catch (Exception $exc) {
            throw $exc;
            /*
              //echo $exc->getMessage();
              $endereco_absoluto = realpath(APPLICATION_PATH . '/../temp/'.$numeroDocumento.$extencaoDocumento);
              unlink($endereco_absoluto);
              echo 'Ocorreu um erro.</br>';
              echo $exc->getMessage();
              return;
             */
        }
    }

    public function recuperarbrowserAction() {
        $this->_helper->layout()->disableLayout();

        $service_documento = new Services_Sisad_Documento();
        $service_processo = new Services_Sisad_Processo();

        $idDocumento = Zend_Filter::FilterStatic($this->_getParam('id'), 'Alnum');
        $numeroDocumento = Zend_Filter::FilterStatic($this->_getParam('dcmto'), 'Alnum');
        $tipoDoc = Zend_Filter::FilterStatic($this->_getParam('tipo', 1), 'Alnum');

        $documento = $service_documento->getDocumento($idDocumento);
        if ($documento != false) {
            if ($documento['DTPD_ID_TIPO_DOC'] == Trf1_Sisad_Definicoes::ID_TIPO_PROCESSO) {
                $processo = $service_processo->getProcessoPorIdDocumento($documento);
                $documento = array_merge($documento, $processo);
            }
            $service_parteVista = new Services_Sisad_ParteVista();
            $isVisivelAoUsuario = $service_parteVista->isVisivelAoUsuario($documento);
            //não visivél ao usuário
            if (!$isVisivelAoUsuario) {
                $this->view->erro = 'O usuário não possui vistas ao documento';
            } else {
                $userNs = new Zend_Session_Namespace('userNs');
                $matricula = $userNs->matricula;
                $erro = '';
                if (is_null($numeroDocumento) || $numeroDocumento == 'null') {
                    $erro = 'Documento digital inexistente.';
                } else {
                    try {
                        $arquivo = $this->recuperar($idDocumento, $numeroDocumento, $matricula);
                    } catch (Exception $exc) {
                        $erro = $exc->getMessage();
                    }
                }

                $this->view->erro = $erro;
                $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
                $codTipoExtensao = $SadTbDocmDocumento->retornaExtensao($tipoDoc);
                $extencaoDocumento = '.' . $codTipoExtensao[0]['TPEX_DS_TP_EXTENSAO'];

                $tmpfname_aux = APPLICATION_PATH . '/../temp';
                $tmpfname_aux = substr($tmpfname_aux, 0, strpos($tmpfname_aux, 'application')) . "temp" . DIRECTORY_SEPARATOR;
                $tmpfname = $numeroDocumento . $extencaoDocumento;
                $tmpfname = $tmpfname_aux . $tmpfname;
                if (!isset($arquivo) || $arquivo === false) {
                    //echo 'Documento não encontrado!';
                    $this->view->erro = 'O servidor não retornou nenhum arquivo.';
                    return;
                }

                $handle = fopen($tmpfname, "w");
                fwrite($handle, $arquivo);
                fclose($handle);
                $download = new App_GetFile();
                $download->onBrowser(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento, $numeroDocumento . $extencaoDocumento, strlen($arquivo));

                $endereco_absoluto = realpath(APPLICATION_PATH . '/../temp/' . $numeroDocumento . $extencaoDocumento);
                unlink($endereco_absoluto);
            }
        } else {
            //formulário não validado
            $this->view->erro = 'Documento não localizado no sisad';
        }
    }

}
