<?php

class Sosti_SladesenvolvimentoController extends Zend_Controller_Action
{
	/**
	 * Timer para mensuracao do tempo de carregamento da pagina
	 *
	 * @var int $_temporizador
	 */
	private $_temporizador;
	
	public function postDispatch() {
		// Apresenta o tempo de carregamento da pagina
		$this->view->tempoResposta = $this->_temporizador->MostraMensagemTempo ();
	}
	
    public function init()
    {
		// Timer para mensuracao do tempo de carregamento da pagina
		$this->_temporizador = new Trf1_Admin_Timer ();
		$this->_temporizador->Inicio ();
		
        $this->view->titleBrowser = 'e-Sosti - Sistema de Atendimento a Solicitações de TI';
        $this->view->module = $this->getRequest()->getModuleName();
        $this->view->controller = $this->getRequest()->getControllerName();
        $this->view->action = $this->getRequest()->getActionName();
    }

    public function indicadoresnivelservicoAction()
    {
        ini_set('memory_limit', '-1');
        set_time_limit( 1200 );//10 minutos para gerar o relatório
        
        /*
        // Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
        $userNs = new Zend_Session_Namespace('userNs');
        $horaInicio = mktime(10,00,00);
        $horaFinal =  mktime(19,00,00);
        $horaAtual = mktime(date("H"), date("i"), date("s"));
        $msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a funcionalidade de emissão de relatórios de SLA somente estará disponível antes das 10:00 e após às 19:00.";
        
        if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') == 0 || strcmp($userNs->matricula, 'TR18077PS') == 0){
        */
        // Validação de acesso às funcionalidades do SLA, conforme servidor web
        $negocio = new Trf1_Sosti_Negocio_Sla ();
        $permiteSla = $negocio->permiteSla ();
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $permiteSla ['permissao'] = true;
        
        if ($permiteSla ['permissao']) {
            $this->view->mostraRelatorio = "S";
            $userNs = new Zend_Session_Namespace('userNs'); 
            $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $SosTbGrexGrupoServExped = new Application_Model_DbTable_SosTbGrexGrupoServExped();
            $SosTbFemvFechamentoMovimen = new Application_Model_DbTable_SosTbFemvFechamentoMovimen();
            $SosTbFeslFechamentoSla = new Application_Model_DbTable_SosTbFeslFechamentoSla();
          //  $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $negocioFaturamento = new Trf1_Sosti_Negocio_Faturamento();
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DO DESEN. E SUSTENTAÇÃO', $userNs->matricula);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;
            /**
             * Gera o cache
             */      
            $frontendOptions = array(
                'lifetime' => 1800, // cache lifetime of 30 minutes
                'automatic_serialization' => true
            );
            $cache_dir = APPLICATION_PATH . '/../temp';
            $backendOptions = array(
                'cache_dir' => $cache_dir 
            );
            // getting a Zend_Cache_Core object
            $cache = Zend_Cache::factory('Core',
                                         'File',
                                         $frontendOptions,
                                         $backendOptions);
            $idCache = $userNs->matricula.'SLADESENTEMPXLS';

            $tempoSla = new App_Sosti_TempoSla();
            $TempoSlaDesenvolvimento = new App_Sosti_TempoSlaDesenvolvimento();
            /**
            *Importa a classe de Importar Excel 
            */  
            include(realpath(APPLICATION_PATH.'/../library/PHPExcel/Classes/PHPExcel.php'));

            /**
            *Form para importar o execel. 
            */  
            $ImportaPlanilha = new  Sosti_Form_ImportaPlanilha();
            $this->view->form = $ImportaPlanilha;
            $this->view->title = "SLA - DESENVOLVIMENTO E SUSTENTAÇÃO - TRF1";


            $Sla_Desenvolvimento_ns = new Zend_Session_Namespace('Sla_Desenvolvimento_ns');
            if ($Sla_Desenvolvimento_ns->data != '') {
                $ImportaPlanilha->populate($Sla_Desenvolvimento_ns->data);
            }
            $formValido = true;
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $tipoEntrada    = $data["TIPO_ENTRADA"];
                
                
                try {
                    if ($tipoEntrada == "TIPO_ENTRADA_FATURAMENTO")
                    {
                        #Zend_Debug::dump($data);
                        
                        $numerosSolicSomente     = array();
                        $solicitacoes_emconjunto = array();
                        $solicitacoes_conjuntos  = array();
                        $secundarias             = array();
                        $arrCount                = 0;
                        
                        foreach ($data['solicitacao'] as $v => $vv)
                        {
                            $kvv    = explode(",",$vv);
                            $bs     = explode("\"",$kvv[1]);
                            $nSolic = $bs[3];
                            $numerosSolicSomente[] = trim($nSolic);
                            try
                            {

                                #$numerosSolic[$arrCount]['SOLICS'] = trim($valorCol); = NUMERO DA SOLICITAÇÃO
                                $numerosSolic[$arrCount]['SOLICS']      = $nSolic;
                                $numerosSolic[$arrCount]['SECUNDARIA']  = false;
                                $arrCount++;
                            }
                                catch (Exception $exc)
                                {

                                }
                        }
                        #Zend_Debug::dump($numerosSolic,'NumSolic');
                        
                        $exiteInvalido = false;
                        $msg_to_user = '';
                        foreach ($numerosSolic as $chave => $value) 
                        {

                            if (strlen($numerosSolic[$chave]['SOLICS']) != 28) 
                            {
                                $exiteInvalido = true;
                                $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois não é um número de 28 digitos. Ou está sem o separador entre números de solicitações.";
                            }
                            
                            $tamanho_da_string = strlen($numerosSolic[$chave]['SOLICS']);
                            $exiteInvalidoPorCaractere = false;
                            for ($i = 0; $i < $tamanho_da_string; $i++) {
                                $auxNumero = (string)$numerosSolic[$chave]['SOLICS'];
                                if (!is_numeric($auxNumero[$i])) {
                                    $exiteInvalidoPorCaractere = true;
                                }
                            }
                            if ($exiteInvalidoPorCaractere) {
                                $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois possui caracteres não numéricos. Ou está sem o separador entre números de solicitações.";
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }
                        }
                        if ($exiteInvalido) 
                        {
                            $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView .= $msg_to_user;
                            throw new Exception(' Foi encontrado um problema.');
                        }
                        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
                        $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
                
                        $sqlY = "SELECT  DOCM_ID_DOCUMENTO,
                                        DOCM_NR_DOCUMENTO,
                                        DOCM_NR_SEQUENCIAL_DOC,
                                        DOCM_NR_DCMTO_USUARIO,
                                        DOCM_DH_CADASTRO,
                                        DOCM_CD_MATRICULA_CADASTRO,
                                        DOCM_ID_TIPO_DOC,
                                        DOCM_SG_SECAO_GERADORA,
                                        DOCM_CD_LOTACAO_GERADORA,
                                        DOCM_SG_SECAO_REDATORA,
                                        DOCM_CD_LOTACAO_REDATORA,
                                        DOCM_ID_PCTT,
                                        DOCM_ID_TIPO_SITUACAO_DOC,
                                        DOCM_ID_CONFIDENCIALIDADE,
                                        DOCM_NR_DOCUMENTO_RED,
                                        DOCM_DH_EXPIRACAO_DOCUMENTO,
                                        DOCM_DS_PALAVRA_CHAVE,
                                        DOCM_IC_ARQUIVAMENTO,
                                        DOCM_ID_PESSOA,
                                        DOCM_IC_DOCUMENTO_EXTERNO,
                                        DOCM_IC_ATIVO,
                                        DOCM_IC_PROCESSO_AUTUADO,
                                        DOCM_ID_MOVIMENTACAO,
                                        DOCM_DH_FASE,
                                        DOCM_ID_DOCUMENTO_PAI,
                                        DOCM_ID_PESSOA_TEMPORARIA,
                                        DOCM_ID_TP_EXTENSAO,
                                        DOCM_IC_MOVI_INDIVIDUAL,
                                        DOCM_IC_APENSADO,
                                        DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC 
                                        FROM SAD_TB_DOCM_DOCUMENTO 
                                        WHERE ".$clausulaInDocm;
                            $stmtY = $db->query($sqlY);
                            $solicsBanco = $stmtY->fetchAll();

//                $solicsBanco = $tabelaSadTbDocmDocumento->fetchAll($clausulaInDocm);

                $numerosNaoEncontrados = array();
                $idsSolics = array();
                foreach ($numerosSolic as $chave => $value) 
                {
                    $encontrado = false;
                    foreach ($solicsBanco as $valueBanco) 
                    {
                        if (strcmp($numerosSolic[$chave]['SOLICS'], $valueBanco['DOCM_NR_DOCUMENTO']) == 0) 
                        {
                            $encontrado = true;
                            #echo "encontrado!!";
                        }
                    }
                    if ($encontrado == false) 
                    {
                        $numerosNaoEncontrados[] = $numerosSolic[$chave];
                    }
                    $encontrado = false;
                }

                if (count($numerosNaoEncontrados) > 0) {
                    $msg_to_user = '';
                    foreach ($numerosNaoEncontrados as $chave => $value) {
                        $msg_to_user .= "<br>O valor da célula " . $numerosNaoEncontrados[$chave]['COL'] . $numerosNaoEncontrados[$chave]['LIN'] . ": \"" . $numerosNaoEncontrados[$chave]['SOLICS'] . "\" não foi encontrado na base de dados.";
                    }
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView .= $msg_to_user;
                    throw new Exception(' Foi encontrado um problema.');
                }
                        
                        foreach ($solicsBanco as $valueBanco) 
                 {
                    $idsSolics[] = $valueBanco['DOCM_ID_DOCUMENTO'];
                 }

                $idsSolicsImplode = implode(',',$idsSolics);
                
                $Sla_Desenvolvimento_ns->data               = $data;
                $Sla_Desenvolvimento_ns->idsSolicsImplode   = $idsSolicsImplode;
                $Sla_Desenvolvimento_ns->PontosFuncao       = $PontosFuncao;
                        
                        
                    }
                    else
                   { 
                   $ip = $ImportaPlanilha->switchTipoEntrada($data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA]);
                    
                    
                    /**
                     * Validação do campo de upload de arquivo
                     */
                    if ($ImportaPlanilha->isValid($data)) 
                    {
                        
                        $data = array_merge($this->getRequest()->getPost(), $ImportaPlanilha->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                        
                        
                        
                        
                        #Zend_Debug::dump($data);
                       
                        
                        /**
                         * Recebimento do arquivo na pasta temp e instanciação da classe de importação
                         */
                        if($ImportaPlanilha->PLANILHA_ARQUIVO->isUploaded()){
                            $ImportaPlanilha->PLANILHA_ARQUIVO->receive();
                            if ($ImportaPlanilha->PLANILHA_ARQUIVO->isReceived()) {

                                $fullFilePath = $ImportaPlanilha->PLANILHA_ARQUIVO->getFileName(); /* caminho completo do arquivo gravado no servidor */
                                $arquivoPlanilhaNome = $ImportaPlanilha->PLANILHA_ARQUIVO->getFileName(null,false); /* caminho completo do arquivo gravado no servidor */
                                $objPHPExcel = PHPExcel_IOFactory::load($fullFilePath);
                                
                                if (($arquivoPlanilha = $cache->load($idCache)) === false ) {
                                    $arquivoPlanilha = file_get_contents($fullFilePath);
                                    $cache->save($arquivoPlanilha, $idCache);
                                }
                                $Sla_Desenvolvimento_ns->arquivoPlanilhaPath = $fullFilePath;
                                $Sla_Desenvolvimento_ns->arquivoPlanilhaNome = $arquivoPlanilhaNome;
                                unlink($fullFilePath);


                                /**
                                 * Validação de coordenada de celula. 
                                 */
                                $CellCollection = $objPHPExcel->getActiveSheet()->getCellCollection();
                                $Validate_InArray = new Zend_Validate_InArray($CellCollection);
                                $ImportaPlanilha->getElement('CELULA_INICIAL')->addValidator($Validate_InArray);
                                $ImportaPlanilha->getElement('CELULA_FINAL')->addValidator($Validate_InArray);
                                $ImportaPlanilha->getElement('CELULA_TOTAL_PF')->addValidator($Validate_InArray);
                            }
                        }

                        if ($ImportaPlanilha->isValid($data)) {

                            if ($data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) {
                             
                            /**
                             * Validação das coordenadas informadas são da mesma coluna 
                             * e recolhimento das coordenadas para o recolhimento dos números de solicitações 
                             */
                            $coordenadaInicial = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_INICIAL')->getValue())->coordinateFromString($ImportaPlanilha->getElement('CELULA_INICIAL')->getValue());
                            $colunaInicio = $coordenadaInicial[0];
                            $linhaIncial = $coordenadaInicial[1];
                            $coordenadaFinal = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_FINAL')->getValue())->coordinateFromString($ImportaPlanilha->getElement('CELULA_FINAL')->getValue());
                            $colunaFim = $coordenadaFinal[0];
                            $linhaFinal = $coordenadaFinal[1];
                            if ($colunaInicio != $colunaFim) {
                                throw new Exception(' A coordenada de coluna da celula inicial deve ser a mesma da coordenada de coluna da celula final.');
                            }
                            $coluna = $colunaInicio;


                            /**
                             * Recolhe a celula referente ao total de ponto de função 
                             */
                            $PontosFuncao = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_TOTAL_PF')->getValue())->getCalculatedValue();

                            $exiteInvalidoPorCaractere = false;
                            $tamanho_da_string = strlen($PontosFuncao);
                            for ($i = 0; $i < $tamanho_da_string; $i++) {
                                $auxNumero = (string)$PontosFuncao;
                                if (!is_numeric($auxNumero[$i])) {
                                    if( 
                                            (strcmp($auxNumero[$i],',') != 0)
                                            &&
                                            (strcmp($auxNumero[$i],'.') != 0)
                                      ){
                                        $exiteInvalidoPorCaractere = true;
                                    }
                                }
                            }
                            if ($exiteInvalidoPorCaractere) {
                                $msg_to_user .= "<br>O valor da célula " . $ImportaPlanilha->getElement('CELULA_TOTAL_PF')->getValue() . ": \"" . $PontosFuncao . "\" não é um número de total de Pontos de Função válido, pois possui caracteres não numéricos diferentes de (,e.). ";
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }



                            /***********************************
                             * DADOS E COORDENADAS NA PLANILHA *
                             ***********************************/
                            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                            $numerosSolic = array();
                            $numerosSolicSomente = array();
                            $arrCount = 0;
                            $solicitacoes_emconjunto = array();
                            $solicitacoes_conjuntos = array();
                            $secundarias = array();

                            foreach ($sheetData as $chaveLin => $valorLin) {
                                if ($chaveLin >= $linhaIncial && $chaveLin <= $linhaFinal) {
                                    foreach ($valorLin as $chaveCol => $valorCol) {
                                        if (
                                                strcmp($chaveCol, $coluna) == 0
                                        ) {
                                            /*
                                             *  Realiza o explode na célula para recolher mais de um número de solicitação se exixtir
                                             */
                                            $explodeValorCol = explode($ImportaPlanilha->getElement('SEPARADOR_MULTIPLO_NUMEROS')->getValue(), trim($valorCol));
                                            foreach ($explodeValorCol as $chave_col_explode => $valor_col_explode) {
                                                /**
                                                 * Realiza o trim dos valores achados no explode
                                                 */
                                                $trimvalor = trim($valor_col_explode);
                                                /**
                                                 * Retira a posição da array do explode se o valor for vazio
                                                 */
                                                if (strlen($trimvalor) > 0) {
                                                    $explodeValorCol[$chave_col_explode] = $trimvalor;
                                                } else {
                                                    unset($explodeValorCol[$chave_col_explode]);
                                                }
                                            }

                                            /**
                                             * Se existir mais de um número de solicitação na célula armazena na array de solicitações com as mesmas coordenadas de célula.
                                             */
                                            if ( count($explodeValorCol) > 1 ) {
                                                $contador_controle = 0;
                                                foreach ($explodeValorCol as $valor_col_explode) {
                                                    $numerosSolic[$arrCount]['SOLICS'] = trim($valor_col_explode);
                                                    $numerosSolic[$arrCount]['COL'] = $coluna;
                                                    $numerosSolic[$arrCount]['LIN'] = $chaveLin;
                                                    $numerosSolicSomente[] = trim($valor_col_explode);

                                                    /**
                                                    * Dados para agrupar as solicitaçãoe que estão na mesma celula
                                                    */
                                                    $solicitacoes_conjuntos[$chaveLin][] = $numerosSolic[$arrCount]['SOLICS'];
                                                    $solicitacoes_emconjunto[] = $numerosSolic[$arrCount]['SOLICS'];
                                                    if($contador_controle > 0){
                                                        $numerosSolic[$arrCount]['SECUNDARIA'] = true;
                                                        $secundarias[] = $numerosSolic[$arrCount]['SOLICS'];
                                                    }else{
                                                        $numerosSolic[$arrCount]['SECUNDARIA'] = false;
                                                    }

                                                    $arrCount++;
                                                    $contador_controle++;
                                                }
                                            } else {
                                                /**
                                                 * Senao armazena o valor normalmente na array
                                                 */
                                                $numerosSolic[$arrCount]['SOLICS'] = trim($valorCol);
                                                $numerosSolic[$arrCount]['COL'] = $coluna;
                                                $numerosSolic[$arrCount]['LIN'] = $chaveLin;
                                                $numerosSolicSomente[] = trim($valorCol);
                                                $numerosSolic[$arrCount]['SECUNDARIA'] = false;
                                                $arrCount++;
                                            }
                                        }
                                    }
                                }
                            }

                            
//                            if ($filtro == "S")
//                            {    
                            /*****************************************************
                             * VALIDAÇÃO DO NUMERO DE CARACTERES DO NUMERO SOSTI * 
                             *****************************************************/
                            
                            $exiteInvalido = false;
                            $msg_to_user = '';
                            foreach ($numerosSolic as $chave => $value) 
                            {
                                $cSolics[] = $numerosSolic[$chave]['SOLICS'];
                                
                                if (strlen($numerosSolic[$chave]['SOLICS']) != 28) 
                                {
                                    $exiteInvalido = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois não é um número de 28 digitos. Ou está sem o separador entre números de solicitações.";
                                }
                                $tamanho_da_string = strlen($numerosSolic[$chave]['SOLICS']);
                                $exiteInvalidoPorCaractere = false;
                                for ($i = 0; $i < $tamanho_da_string; $i++) 
                                {
                                    $auxNumero = (string)$numerosSolic[$chave]['SOLICS'];
                                    if (!is_numeric($auxNumero[$i])) {
                                        $exiteInvalidoPorCaractere = true;
                                    }
                                }
                                if ($exiteInvalidoPorCaractere) 
                                {
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois possui caracteres não numéricos. Ou está sem o separador entre números de solicitações.";
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView .= $msg_to_user;
                                    throw new Exception(' Foi encontrado um problema.');
                                }
                            }
                            
                            if ($exiteInvalido) {
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }
                            
                            /********************************************
                             * VALIDAÇÃO DE EXISTÊNCIA NA BASE DE DADOS *
                             ********************************************/

                            $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
                            $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
            
                            $sqlx = "SELECT  DOCM_ID_DOCUMENTO,
                                        DOCM_NR_DOCUMENTO,
                                        DOCM_NR_SEQUENCIAL_DOC,
                                        DOCM_NR_DCMTO_USUARIO,
                                        DOCM_DH_CADASTRO,
                                        DOCM_CD_MATRICULA_CADASTRO,
                                        DOCM_ID_TIPO_DOC,
                                        DOCM_SG_SECAO_GERADORA,
                                        DOCM_CD_LOTACAO_GERADORA,
                                        DOCM_SG_SECAO_REDATORA,
                                        DOCM_CD_LOTACAO_REDATORA,
                                        DOCM_ID_PCTT,
                                        DOCM_ID_TIPO_SITUACAO_DOC,
                                        DOCM_ID_CONFIDENCIALIDADE,
                                        DOCM_NR_DOCUMENTO_RED,
                                        DOCM_DH_EXPIRACAO_DOCUMENTO,
                                        DOCM_DS_PALAVRA_CHAVE,
                                        DOCM_IC_ARQUIVAMENTO,
                                        DOCM_ID_PESSOA,
                                        DOCM_IC_DOCUMENTO_EXTERNO,
                                        DOCM_IC_ATIVO,
                                        DOCM_IC_PROCESSO_AUTUADO,
                                        DOCM_ID_MOVIMENTACAO,
                                        DOCM_DH_FASE,
                                        DOCM_ID_DOCUMENTO_PAI,
                                        DOCM_ID_PESSOA_TEMPORARIA,
                                        DOCM_ID_TP_EXTENSAO,
                                        DOCM_IC_MOVI_INDIVIDUAL,
                                        DOCM_IC_APENSADO,
                                        DBMS_LOB.SUBSTR(DOCM_DS_ASSUNTO_DOC, 4000, 1) DOCM_DS_ASSUNTO_DOC 
                                        FROM SAD_TB_DOCM_DOCUMENTO 
                                        WHERE ".$clausulaInDocm;
                            $stmtX = $db->query($sqlx);
                            $solicsBanco = $stmtX->fetchAll();

                            $numerosNaoEncontrados = array();
                            $idsSolics = array();
                            foreach ($numerosSolic as $chave => $value) {
                                $encontrado = false;
                                foreach ($solicsBanco as $valueBanco) {
                                    if (strcmp($numerosSolic[$chave]['SOLICS'], $valueBanco['DOCM_NR_DOCUMENTO']) == 0) {
                                        $encontrado = true;
                                    }
                                }
                                if ($encontrado == false) {
                                    $numerosNaoEncontrados[] = $numerosSolic[$chave];
                                }
                                $encontrado = false;
                            }

                            if (count($numerosNaoEncontrados) > 0) {
                                $msg_to_user = '';
                                foreach ($numerosNaoEncontrados as $chave => $value) 
                                {
                                    $msg_to_user .= "<br />O SOSTI da célula " . $numerosNaoEncontrados[$chave]['COL'] . $numerosNaoEncontrados[$chave]['LIN'] . " número " . $numerosNaoEncontrados[$chave]['SOLICS'] . " não foi encontrado na base de dados.";
                                }
                                $msg_to_user = "<div class='notice'><strong>Descrição:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception('Problema na importação.');
                            }
                            
                            /**************************************
                             * VALIDAÇÃO DE DUPLICIDADE NA TABELA *
                             **************************************/
                           
                            $msg = false;
                            $msg_erro = "";
                            $diffSolics = array_unique(array_diff_assoc($cSolics, array_unique($cSolics)));
                            $msg_erro .= "Existem registros duplicados para os Sostis N°(s):<ul>";
                            foreach($diffSolics as $d)
                            {
                                $msg = true;
                                $msg_erro .= "<li>".$d."</li>";
                            }
                            $msg_erro .= "</ul>";
                            if ($msg) 
                            {
                                $msg_erro = "<div class='notice'><strong>Descrição:</strong> $msg_erro</div>";
                                $this->view->flashMessagesView .= $msg_erro;
                                throw new Exception(' Foi encontrado um problema na validação dos SOSTIs.');
                            }
                            
                            
                            /*******************************************
                             * VALIDAÇÃO PARA SOLICITAÇÕES EM GARANTIA *
                             *******************************************/
                            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                            
                            $NegociaGarantiaDesenvolvimentoRelatorioSla = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimentoRelatorioSla();
                            if($NegociaGarantiaDesenvolvimentoRelatorioSla->getVerificaGarantia($numerosSolicSomente)){
                                $Garantias = $NegociaGarantiaDesenvolvimentoRelatorioSla->getGarantias($numerosSolicSomente);
                                $numerosDeGarantiaEncontrados = array();
                                $idsSolics = array();
                                
                                foreach ($numerosSolic as $chave => $value) {
                                    foreach ($Garantias as $Garantia) {
                                        if (strcmp($numerosSolic[$chave]['SOLICS'], $Garantia['DOCM_NR_DOCUMENTO']) == 0) {
                                            $numerosSolic[$chave]['DADOS_GARANTIA'] = $Garantia;
                                            $numerosDeGarantiaEncontrados[] = $numerosSolic[$chave];
                                        }
                                    }
                                }
                                if (count($numerosDeGarantiaEncontrados) > 0) {
                                    $msg_to_user = '';
                                    foreach ($numerosDeGarantiaEncontrados as $chave => $value) 
                                        {   

                                             $getDadosSolic["DOCM_NR_DOCUMENTO"] = $numerosDeGarantiaEncontrados[$chave]['SOLICS'];
                                             $dadosSolic    = $negocioFaturamento->getRelatorioRias($getDadosSolic);
                                             $idSolic       = $dadosSolic[0]['SSOL_ID_DOCUMENTO'];
                                             
                                             
                                            /************************************************
                                             * CASO GARANTIA
                                             ************************************************
                                             * STATUSDSV            = NÃO FATURAR (1)
                                             * CLASSIFICAÇÃO DSV    = GARANTIA (2)
                                             * FASE                 = BAIXA(1000)
                                             ************************************************/
                                            
                                                $db->beginTransaction();
                                                
                                                $dadosCadastro["PFDS_ID_SOLICITACAO"]   = $idSolic;
                                                
                                                $dadosCadastro["PFDS_ID_STATUS"]        = 1;
                                                $dadosCadastro["PFDS_ID_CLASSIFICACAO"] = 2;
                                                $incluiDados = $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
                                                
                                                #Zend_Debug::dump($dadosCadastro,'CADASTRO');
                                                
                                                $db->commit();
                                       
                                     $msg_to_user .= "<br>O valor da célula " . $numerosDeGarantiaEncontrados[$chave]['COL'] . $numerosDeGarantiaEncontrados[$chave]['LIN'] . ": \"" . $numerosDeGarantiaEncontrados[$chave]['SOLICS'] . "\" é uma solicitação considerada garantia.";
                                        if($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_ACEITE'] == "A"){
                                            $msg_to_user .= "<strong><i> Que a garantia foi aceita.</i></strong>";
                                        }else if(is_null($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_CONCORDANCIA'])){
                                            $msg_to_user .= "<span style=\"color: red;\"><strong><i> Que a divergência não foi avaliada.</i></strong></span>";
                                        }else if($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_CONCORDANCIA'] == "D"){
                                            $msg_to_user .= "<strong><i> Que foi confirmada na divergência.</i></strong>";
                                        }
                                    }
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView .= $msg_to_user;
                                }
                                throw new Exception('Foi encontrado um problema na garantia.');
                            } 
                            
                            
                            /*********************************************
                             * VALIDAÇÃO DO STATUS DE AVALIAÇÃO DO SOSTI *
                             *                                           *
                             *   Baixada    == NULL                      *
                             *   Avaliada   != 6                         *
                             *   Recusada   == 6                         *
                             *********************************************/
                            
                            $sostiValidado = false;
                            $msg_to_user = '';
                            foreach ($numerosSolic as $chave => $value) 
                            {
                                $numeroSosti                        = $numerosSolic[$chave]['SOLICS'];
                                #echo "<br />SOSTINR".$numeroSosti;
                                $getDadosSolic["DOCM_NR_DOCUMENTO"] = $numeroSosti;
                                $dadosSolic                         = $negocioFaturamento->getRelatorioRias($getDadosSolic,true);
                                #Zend_Debug::dump($dadosSolic,'DADOS');
                                $tipoSat                            = $dadosSolic[0]['STSA_ID_TIPO_SAT'];
                                
                                
                                
                                
                                if ($tipoSat == NULL)
                                {
                                    $sostiValidado = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" corresponde a um SOSTI sem avaliação";
                                }
                                else if ($tipoSat == 6)
                                {
                                    $sostiValidado = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" corresponde a um SOSTI que foi RECUSADO.";
                                }
                                
                                
                            }
                            
//                            Zend_Debug::dump($numerosSolic);exit;
                            if ($sostiValidado) {
                                $msg_to_user = "<div class='notice'><strong>Descrição:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                
                                #throw new Exception(' Foi encontrado um problema na avaliação dos SOSTIs.');
                            }
                            
//                            }######## FIM DO FILTRO

                            /*************************************
                             * RECOLHE IDS DOS SOSTIS PARA QUERY *
                             *************************************/
                            
                            foreach ($solicsBanco as $valueBanco) {
                                $idsSolics[] = $valueBanco['DOCM_ID_DOCUMENTO'];
                            }

                            $idsSolicsImplode = implode(',',$idsSolics);

                            /**
                             * Armazenando na sessao o último post
                             */
                            $Sla_Desenvolvimento_ns->data = $data;
                            $Sla_Desenvolvimento_ns->idsSolicsImplode = $idsSolicsImplode;
                            $Sla_Desenvolvimento_ns->PontosFuncao = $PontosFuncao;


                            /**
                             * Dados para agrupar as solicitaçãoe que estão na mesma celula
                             */
                            $solicitacoes_emconjunto = array_unique($solicitacoes_emconjunto);
                            foreach ($solicitacoes_conjuntos as $key => $value) {
                                $solicitacoes_conjuntos[$key] = array_unique($solicitacoes_conjuntos[$key]);
                            }
                            $secundarias = array_unique($secundarias);
                            $this->view->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                            $this->view->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                            $this->view->secundarias = $secundarias;

                            $Sla_Desenvolvimento_ns->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                            $Sla_Desenvolvimento_ns->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                            $Sla_Desenvolvimento_ns->secundarias = $secundarias;

                            }else{
                                /**
                                * Armazenando na sessao o último post
                                */
                                $secundarias = array();
                                $solicitacoes_emconjunto = array();
                                $solicitacoes_conjuntos = array();

                                $Sla_Desenvolvimento_ns->data = $data;
                                $Sla_Desenvolvimento_ns->idsSolicsImplode = NULL;
                                $Sla_Desenvolvimento_ns->PontosFuncao = NULL;

                                $this->view->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                                $this->view->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                                $this->view->secundarias = $secundarias;

                                $Sla_Desenvolvimento_ns->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                                $Sla_Desenvolvimento_ns->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                                $Sla_Desenvolvimento_ns->secundarias = $secundarias;

                            }


                        }else{
                            $ImportaPlanilha->populate($data);
                            $this->view->form = $ImportaPlanilha;
                            $formValido = false;
                        }
                   }else{
                        $ImportaPlanilha->populate($data);
                        $this->view->form = $ImportaPlanilha;
                        $formValido = false;
                    }
                }
                
                   } catch (Exception $exc) {
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong>" . $exc->getMessage() . "</div>";
                    $this->view->flashMessagesView = $msg_to_user . $this->view->flashMessagesView;
                    $ImportaPlanilha->populate($data);
                    $formValido = false;
                }

            }


            if ($Sla_Desenvolvimento_ns->data != '' && $formValido) {
                $this->view->data   = $Sla_Desenvolvimento_ns->data;
                $ImportaPlanilha->populate($Sla_Desenvolvimento_ns->data);
                
                #Zend_Debug::dump($ImportaPlanilha,'IMPORTA_PLANILHA');
                
                $idsSolicsImplode = $Sla_Desenvolvimento_ns->idsSolicsImplode;
                
                #Zend_Debug::dump($idsSolicsImplode,'implode');
                
                $PontosFuncao = $Sla_Desenvolvimento_ns->PontosFuncao;

                $solicitacoes_emconjunto    = $Sla_Desenvolvimento_ns->solicitacoes_emconjunto;
                $solicitacoes_conjuntos     = $Sla_Desenvolvimento_ns->solicitacoes_conjuntos;
                $secundarias                = $Sla_Desenvolvimento_ns->secundarias;
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                {
                    $secundarias[]="0";
                }
                
                #Zend_Debug::dump($solicitacoes_emconjunto,'em conjunto');
                #Zend_Debug::dump($solicitacoes_conjunto,'conjunto');
                #Zend_Debug::dump($secundarias,'sec');
                
                
                
                /**
                 * Indicadores de Níveis de Serviço
                 */
                $EPA_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'EPA');
                $MTA_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'MTA');
                
                
                #Zend_Debug::dump($EPA_DADOS);
                #Zend_Debug::dump($MTA_DADOS);
                
    //            $IDQ_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'IDQ');

                $Sla_Desenvolvimento_ns->EPA_DADOS  = $EPA_DADOS;
                $Sla_Desenvolvimento_ns->MTA_DADOS  = $MTA_DADOS;
    //            $Sla_Desenvolvimento_ns->IDQ_DADOS  = $IDQ_DADOS;

                if($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO)
                {

                    $intervaloData["DATA_INICIAL"] = $Sla_Desenvolvimento_ns->data["DATA_INICIAL"];
                    $intervaloData["DATA_FINAL"]   = $Sla_Desenvolvimento_ns->data["DATA_FINAL"];

                    $solicitacoesEpa = $indicadorNivelServ->getDatasSLA_EPA(
                            2, null, $intervaloData, $EPA_DADOS['SINS_ID_INDICADOR'], $MTA_DADOS['SINS_ID_INDICADOR']
                        );
//                            Zend_Debug::dump('chegou');exit;
                }
                else if(($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) ||
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                       )
                       {
                            $solicitacoesEpa = $indicadorNivelServ->getDatasSLA_EPA(2,$idsSolicsImplode, null         ,  $EPA_DADOS['SINS_ID_INDICADOR'],$MTA_DADOS['SINS_ID_INDICADOR']);
                            #Zend_Debug::dump($solicitacoesEpa,'EPA');
                        }
                
                    
                
    //            $solicitacoesIdq = $indicadorNivelServ->getDatasSLA_IDQ(2,$idsSolicsImplode,$IDQ_DADOS['SINS_ID_INDICADOR']);



                /*********************************************** || PERIODO ||
                 * CONTABILIZAR SOMENTE CLASSIFICADOS 
                 ***********************************************/
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
                    if($Sla_Desenvolvimento_ns->data["CONTA_NAO_CATEGORIZADO"] == 'N'){
                        foreach ($solicitacoesEpa as $chaveEapSis => $valueEapSis) {
                            if( is_null($solicitacoesEpa[$chaveEapSis]["SERVICO_SISTEMA"]) && is_null($solicitacoesEpa[$chaveEapSis]["SSPA_DT_PRAZO"]) ){
                                unset($solicitacoesEpa[$chaveEapSis]);
                            }
                        }
                        $solicitacoesEpa_aux = $solicitacoesEpa;
                        unset($solicitacoesEpa);
                        $contador = 0;
                        foreach ($solicitacoesEpa_aux as $chaveEapSis => $valueEapSis) {
                            $solicitacoesEpa[$contador++] = $solicitacoesEpa_aux[$chaveEapSis];
                        }
                    }
                }
                /************************************************
                 * **********************************************
                 */

                /*********************************************** || PERIODO ||
                 * DESCONSIDERAR VINCULADAS
                 ***********************************************/
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
                    foreach ($solicitacoesEpa as $chaveEapSis => $valueEapSis) {
                        if( $solicitacoesEpa[$chaveEapSis]["PRINCIPAL_OU_ORF"] == '0' ){
                            unset($solicitacoesEpa[$chaveEapSis]);
                        }
                    }
                    $solicitacoesEpa_aux = $solicitacoesEpa;
                    unset($solicitacoesEpa);
                    $contador = 0;
                    foreach ($solicitacoesEpa_aux as $chaveEapSis => $valueEapSis) {
                        $solicitacoesEpa[$contador++] = $solicitacoesEpa_aux[$chaveEapSis];
                    }
                }
                /************************************************
                 * **********************************************
                 */

                
                /**************************************************/
                /**************************************************/
                /**
                * Tratamentos para agrupar as solicitaçãoe que estão na mesma celula
                */
                foreach ($solicitacoesEpa as $chaveEAP => $valorEPA) 
                {
                    $solicitacoesEpa[$chaveEAP]['REFERENCIA'] = null;
                }
                #Zend_Debug::dump($solicitacoesEpa,'solEPA');
                
                foreach ($solicitacoesEpa as $chaveEAP => $valorEPA) 
                {
                    #echo "entra aqui";
                    if( !( array_search((string)$solicitacoesEpa[$chaveEAP]["DOCM_NR_DOCUMENTO"], $solicitacoes_emconjunto,true) === false ) ){
                        foreach ($solicitacoes_conjuntos as $conj_c => $conj_v) {
                            if( !( array_search((string)$solicitacoesEpa[$chaveEAP]["DOCM_NR_DOCUMENTO"],$solicitacoes_conjuntos[$conj_c],true) === false ) ){
                                foreach ($solicitacoes_conjuntos[$conj_c] as $vConjunto) {
                                    foreach ($solicitacoesEpa as $cEap => $vEap) {
                                        if (strcmp((string) $solicitacoesEpa[$cEap]["DOCM_NR_DOCUMENTO"], (string) $vConjunto) === 0) {
                                            $solicitacoesEpa[$cEap]['REFERENCIA'] = "==".$conj_c."==";
                                        }
                                    }
                                }
                            }
                        }
                    }
                    #Zend_Debug::dump($solicitacoesEpa,'FOREACH');
                }
                /*
                foreach ($solicitacoesIdq as $chaveIDQ => $valorIDQ) {
                    $solicitacoesIdq[$chaveIDQ]['REFERENCIA'] = null;
                }
                foreach ($solicitacoesIdq as $chaveIDQ => $valorIDQ) {
                    if( !( array_search((string)$solicitacoesIdq[$chaveIDQ]["DOCM_NR_DOCUMENTO"], $solicitacoes_emconjunto,true) === false ) ){
                        foreach ($solicitacoes_conjuntos as $conj_c => $conj_v) {
                            if( !( array_search((string)$solicitacoesIdq[$chaveIDQ]["DOCM_NR_DOCUMENTO"],$solicitacoes_conjuntos[$conj_c],true) === false ) ){
                                foreach ($solicitacoes_conjuntos[$conj_c] as $vConjunto) {
                                    foreach ($solicitacoesIdq as $cIDQ => $vIDQ) {
                                        if (strcmp((string) $solicitacoesIdq[$cIDQ]["DOCM_NR_DOCUMENTO"], (string) $vConjunto) === 0) {
                                            $solicitacoesIdq[$cIDQ]['REFERENCIA'] = "==".$conj_c."==";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                  */
                /***************************************************************************************************/
                /***************************************************************************************************/




                /**************************************************************************************/
                /**************************************************************************************/
                /**
                * Calcular o EPA – Volume de ordens de serviço executadas nos prazos acordados
                */

                /**
                 * Configurações do horário de expediente
                 */
                $expedienteNormal = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "NORMAL");
                $expedienteEmergencia = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "EMERGENCIAL");

                $expediente = array( 'NORMAL'=>array('INICIO'=>$expedienteNormal["INICIO"],'FIM'=>$expedienteNormal["FIM"]),'EMERGENCIAL'=>array('INICIO'=>$expedienteEmergencia['INICIO'],'FIM'=>$expedienteEmergencia['FIM']) );
                $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos( $expediente["NORMAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]) ;
                $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos( $expediente["EMERGENCIAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]) ;
                $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
                $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;

                ################# ADICIONADO 
                #ECHO "CONTANDO SOLICITAÇÕES: " .count($solicitacoesEpa);
                
                
                /**
                 * Calcula os prazos das solicitações
                 */
                if(count($solicitacoesEpa)>0)
                {
                    $TempoSlaDesenvolvimentoArr = $TempoSlaDesenvolvimento->PrazoSlaDesenvolvimento($solicitacoesEpa, 'MOFA_ID_MOVIMENTACAO', 'DATA_CHAMADO', 'SSPA_DT_PRAZO', 'CORRETIVA','EMERGENCIA', 'PROBLEMA','CAUSA', 'ASIS_PRZ_SOL_PROBLEMA', 'ASIS_PRZ_SOL_CAUSA_PROBLEMA', 'ASIS_PRZ_EXECUCAO_SERVICO',$expediente);
                    
                    #Zend_Debug::dump($TempoSlaDesenvolvimentoArr,'TEMPO_SLA_DESENVOLVIMENTO');
                }

                /**
                 * Calcula o tempo total das solicitações não contablizado o tempo em que a solicitação ficou aguardando a resposta do pedido de informação.
                 */
                if(count($solicitacoesEpa)>0)
                {
                    $TempoTotalPedidoInforArr = $tempoSla->TempoTotalPedidoInfor($solicitacoesEpa, 'MOFA_ID_MOVIMENTACAO', "DATA_CHAMADO", "DATA_FIM_CHAMADO","", "", $expediente);
                }
                #Zend_Debug::dump($TempoTotalPedidoInforArr,'ARR');
                
                $i = 0;
                $countSolicitacoesUtrapassadas = 0;
                $somaAtrasosMta = 0;
                $solicitacoesEpaFechamento = array();
                $solicitacoesMtaFechamento = array();
                $solicitacoesIdqFechamento = array();
                $AtrasosMta = 0;
                
                #Zend_Debug::dump($solicitacoesEpa,'EPA');
                
                
                foreach ($solicitacoesEpa as $epa) {
                    
                    $solicitacoesEpa[$i]["PRAZO_DATA"] = NULL;
                    if( array_search((string)$epa["DOCM_NR_DOCUMENTO"], $secundarias,true) === false ){
                        
                        #ECHO "hey ";
                        
                        $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"] = $TempoTotalPedidoInforArr[$epa["MOFA_ID_MOVIMENTACAO"]]["TEMPO_UTIL_TOTAL"];
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_SEGUNDOS_UTEIS"];
                        $solicitacoesEpa[$i]["PRAZO_CORRIDO_PADRAO"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_CORRIDO_PADRAO"];

                        if(is_null($solicitacoesEpa[$i]["SSPA_DT_PRAZO"])){
                            /**
                            * Verifica se esta dentro ou fora do prazo.
                            */
                            if ($solicitacoesEpa[$i]["PRAZO_CORRIDO_PADRAO"] === true) {
                                $dataInicial = $solicitacoesEpa[$i]["DATA_CHAMADO"];
                                $timeStampInicial = (int)mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                $solicitacoesEpa[$i]["PRAZO_DATA"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_DATA"];
                                $dataPrazo = $solicitacoesEpa[$i]["PRAZO_DATA"];
                                $timeStampFinal = (int)mktime(substr($dataPrazo, 11, 2), substr($dataPrazo, 14, 2), substr($dataPrazo, 17, 2), substr($dataPrazo, 3, 2), substr($dataPrazo, 0, 2), substr($dataPrazo, 6, 4));
                                if ($timeStampFinal >= $timeStampInicial) {
                                    $prazoUltrapassado[$i] = false;
                                } else {
                                    $prazoUltrapassado[$i] = true;
                                }
                            } else {
                                if(!is_null($epa["SERVICO_SISTEMA"])){
                                    $prazoUltrapassado[$i] = $tempoSla->verificaPrazoUltrapassado($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"]);
                                }else{
                                    $prazoUltrapassado[$i] = false;
                                }
                            }
                        }else{
                            if($solicitacoesEpa[$i]["PRAZO_ULTRAPASSADO"] == '0'){
                                $prazoUltrapassado[$i] = false;
                            }else{
                                $prazoUltrapassado[$i] = true;
                            }
                        }



                        if ($prazoUltrapassado[$i] == false) {
                            if (is_null($epa['DESCONSIDERADO_EPA'])) {
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "S";
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "N";
                            }
                            //Array para o fechamento do sla
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            if (is_null($epa['DESCONSIDERADO_MTA'])) {
                                $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "S";
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "N";
                            }
                            //Array para o fechamento do sla
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];

                            $solicitacoesEpa[$i]["NO_PRAZO"] = "S";
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = 0;

                        }else{
                            if (is_null($epa['DESCONSIDERADO_EPA'])) {
                                $countSolicitacoesUtrapassadas++;
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "S";
                                //Array para o fechamento do sla
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'N';
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "N";
                                //Array para o fechamento do sla
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            }
                            $solicitacoesEpa[$i]["NO_PRAZO"] = "N";


                            /**
                             * Calcula o atraso 
                             */
                            if(is_null($solicitacoesEpa[$i]["SSPA_DT_PRAZO"])){
                                $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"] - $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"];
                            }else{
                                if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                                    $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $tempoSla->tempoTotalSLA($solicitacoesEpa[$i]["SSPA_DT_PRAZO"], $solicitacoesEpa[$i]["DATA_FIM_CHAMADO"], $expediente['EMERGENCIAL']['INICIO'], $expediente['EMERGENCIAL']['FIM']);
                                }else{
                                    $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $tempoSla->tempoTotalSLA($solicitacoesEpa[$i]["SSPA_DT_PRAZO"], $solicitacoesEpa[$i]["DATA_FIM_CHAMADO"], $expediente['NORMAL']['INICIO'], $expediente['NORMAL']['FIM']);
                                }
                            }

                            if($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] <= 0){
                                $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = 0;
                            }else{
                                $AtrasosMta = 0;
                                if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                                    $AtrasosMta = $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] / ($expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] * 60 * 60);
                                }else{
                                    $AtrasosMta = $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] / ($expediente["NORMAL"]["DIA_UTIL_HORAS"] * 60 * 60);
                                }

                                if (is_null($epa['DESCONSIDERADO_MTA'])) {
                                    $somaAtrasosMta += $AtrasosMta;
                                    $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "S";
                                    //Array para o fechamento do sla
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'N';
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                                } else {
                                    $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "N";
                                    //Array para o fechamento do sla
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                                }
                            }
                        }

                        /**
                        * Somente para Visualisação de dados
                        */
                        if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                            $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                        }else{
                            $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                        }
                    }else{
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = NULL;
                        $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = NULL;
                        $solicitacoesEpa[$i]["NO_PRAZO"] = NULL;
                        $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = NULL;
                        $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = NULL;
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"] = NULL;
                        $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = NULL;

                        //Array para o fechamento do sla
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];

                        //Array para o fechamento do sla
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];


                    }
                    $i++;
                }

                $totalEpaSolicitacoes = (count($solicitacoesEpa));
                /**
                 * Subtrai do total de solicitações as solicitações secundarias(as solicitações que estão em uma mesma célula menos a primeira que é contablizada)
                 */
                $totalEpaSolicitacoes = $totalEpaSolicitacoes - count($secundarias);

                $countSolicitacoesNoPrazo = $totalEpaSolicitacoes - $countSolicitacoesUtrapassadas;

                if($totalEpaSolicitacoes > 0){
                    $totalEpa = ($countSolicitacoesNoPrazo/$totalEpaSolicitacoes) * 100  ;
                }else{
                    $totalEpa = 100;
                }

                $totalEpa = (float) sprintf('%.2f',$totalEpa);

                if ($totalEpa < 75) {
                    $glosaEpa = 10;
                }
                if (($totalEpa >= 75) && ($totalEpa < 80)) {
                    $glosaEpa = 5;
                }
                if (($totalEpa >= 80) && ($totalEpa < 85)) {
                    $glosaEpa = 4;
                }
                if (($totalEpa >= 85) && ($totalEpa < 90)) {
                    $glosaEpa = 3;
                }
                if (($totalEpa >= 90) && ($totalEpa < 95)) {
                    $glosaEpa = 2;
                }
                if (($totalEpa >= 95) && ($totalEpa <= 99)) {
                    $glosaEpa = 1;
                }
                if ($totalEpa > 99) {
                    $glosaEpa = 0;
                }
                /**
                 * Carrega as variáveis para gerar o indicador:
                 * EPA – Volume de ordens de serviço executadas nos prazos acordados
                 */
                $slaDsvEpaNs = new Zend_Session_Namespace('slaDsvEpaNs');
                $slaDsvEpaNs->totalEpaSolicitacoes = $totalEpaSolicitacoes;
                $slaDsvEpaNs->countSolicitacoesNoPrazo = $countSolicitacoesNoPrazo;
                $slaDsvEpaNs->countSolicitacoesUtrapassadas = $countSolicitacoesUtrapassadas;
                $slaDsvEpaNs->idIndicadorEPA = $EPA_DADOS['SINS_ID_INDICADOR'];
                $slaDsvEpaNs->solicitacoesEpa = $solicitacoesEpa;
                $slaDsvEpaNs->secundarias = $secundarias;

                $this->view->totalEpaSolicitacoes = $totalEpaSolicitacoes;
                $this->view->countSolicitacoesNoPrazo = $countSolicitacoesNoPrazo;
                $this->view->countSolicitacoesUtrapassadas = $countSolicitacoesUtrapassadas;
                $this->view->idIndicadorEPA = $EPA_DADOS['SINS_ID_INDICADOR'];
                //solicitações
                $this->view->solicitacoesEpa = $solicitacoesEpa;

                /**************************************************************************************/
                /**************************************************************************************/


                /**
                 * Calcular a Média de tempo de atraso das ordens de serviços do mês
                 */
                $totalMtaSolicitacoes = $totalEpaSolicitacoes;
                $countSolicitacoesForadoPrazoMta= $countSolicitacoesUtrapassadas;
                $countSolicitacoesNoPrazoMta = $countSolicitacoesNoPrazo;

                if($totalMtaSolicitacoes > 0){
                    $totalMta = ($somaAtrasosMta/$totalMtaSolicitacoes);
                }else{
                    $totalMta = 0;
                }
                $totalMtaAux = (float) sprintf('%.2f',$totalMta);
                $totalMta = (float) sprintf('%.2f',$totalMta);

                if ($totalMta > 25) {
                    $glosaMta = 10;
                }
                if (($totalMta >= 21) && ($totalMta <= 25)) {
                    $glosaMta = 5;
                }
                if (($totalMta >= 16) && ($totalMta < 21)) {
                    $glosaMta = 4;
                }
                if (($totalMta >= 11) && ($totalMta < 16)) {
                    $glosaMta = 3;
                }
                if (($totalMta >= 8) && ($totalMta < 11)) {
                    $glosaMta = 2;
                }
                if (($totalMta >= 1) && ($totalMta < 8)) {
                    $glosaMta = 1;
                }
                if ($totalMta < 1) {
                    $glosaMta = 0;
                }
                /**
                 * Carrega as variáveis para gerar o indicador:
                 * MTA – Média de tempo de atraso das ordens de serviços do mês
                 */
                $slaDsvMtaNs = new Zend_Session_Namespace('slaDsvMtaNs');
                $slaDsvMtaNs->totalMtaSolicitacoes = $totalMtaSolicitacoes;
                $slaDsvMtaNs->countSolicitacoesForadoPrazoMta = $countSolicitacoesForadoPrazoMta;
                $slaDsvMtaNs->noPrazoMtaSolicitacoes = $countSolicitacoesNoPrazoMta;
                $slaDsvMtaNs->mediaAtrasos = $totalMtaAux;
                $slaDsvMtaNs->idIndicadorMTA = $MTA_DADOS['SINS_ID_INDICADOR'];
                $slaDsvMtaNs->solicitacoesMta = $solicitacoesEpa;

                $this->view->totalMtaSolicitacoes = $totalMtaSolicitacoes;
                $this->view->countSolicitacoesForadoPrazoMta = $countSolicitacoesForadoPrazoMta;
                $this->view->noPrazoMtaSolicitacoes = $countSolicitacoesNoPrazoMta;
                $this->view->mediaAtrasos = $totalMtaAux;

                $this->view->idIndicadorMTA = $MTA_DADOS['SINS_ID_INDICADOR'];
                //solicitações
                $this->view->solicitacoesMta = $solicitacoesEpa;
                /**************************************************************************************/
                /**************************************************************************************/

                /**************************************************************************************/
                /**************************************************************************************/
                /**
                 * Carrega as variáveis para gerar o indicador:
                 * IDQ – Índice de Defeitos Qualidade
                 */
                $apfDesenvolvedora = new Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora();
                $tarefaSolicit = new Tarefa_Model_DataMapper_TarefaSolicitacao();
                foreach ($solicitacoesEpa as $i=>$idq) {
                    $solicitacoesEpa[$i]['QTDE_DEFEITOS'] = $tarefaSolicit->getDefeitosSolicitacoesSla($idq["SSOL_ID_DOCUMENTO"]);
                    $arraySomaDefeitos[] = $solicitacoesEpa[$i]['QTDE_DEFEITOS'];
                    try {
                        $pfSolicitacao = $apfDesenvolvedora->fetchAll("PFDS_ID_SOLICITACAO = ".$idq["SSOL_ID_DOCUMENTO"]);
                        $arrayPfLiquido[] = $pfSolicitacao[0]['PFDS_QT_PF_LIQUIDO'];
                    } catch (Exception $ex) {
                        $arrayPfLiquido[] = 0;
                    }
                }
                $somaDefeitos = array_sum($arraySomaDefeitos);
                $somaTotalPfLiquido = array_sum($arrayPfLiquido);
                /**
                 * RN02 – Para calcular a meta alcançada o sistema deverá dividir a quantidade total de 
                 * defeitos pelo total de pontos de função bruto das solicitações filtradas.
                 * RN03 – Para calcular a glosa, o sistema deverá verificar o valor da “Meta Alcançada”:
                 * Caso ele seja menor que 0,1, o sistema deverá informar que a glosa será de 0%.
                 * Caso ela seja >= 0,1 e <=0,2 o sistema deverá informar que a glosa será de 2,00%. 
                 * Caso o valor seja maior que 0,2 o sistema deverá informar que a glosa será de 5,00%.
                 */
                $totalIdq = (float) sprintf('%.2f',$somaDefeitos / $somaTotalPfLiquido);
                if ($totalIdq <  "0.1") {
                    $glosaIdq = "0";
                } elseif (($totalIdq >= "0.1") && ($totalIdq <= "0.2")) {
                    $glosaIdq = "2,00";
                } elseif ($totalIdq > "0.2" ) {
                    $glosaIdq = "5,00";
                }
                
                $slaDsvIdqNs = new Zend_Session_Namespace('slaDsvIdqNs');
                $slaDsvIdqNs->totalIdqSolicitacoes = $this->view->totalMtaSolicitacoes;
                $slaDsvIdqNs->totalDefeitos = $somaDefeitos;
                $slaDsvIdqNs->totalPfLiquido = $somaTotalPfLiquido;
                $slaDsvIdqNs->totalIdq = $totalIdq;
                $slaDsvIdqNs->glosaIdq = $glosaIdq;

                $this->view->solicitacoesIdq = $solicitacoesEpa;
                $this->view->totalIdqSolicitacoes = $slaDsvIdqNs->totalIdqSolicitacoes;
                $this->view->totalDefeitos = $slaDsvIdqNs->totalDefeitos;
                $this->view->totalPfLiquido = $slaDsvIdqNs->totalPfLiquido;
                $this->view->totalIdq = $slaDsvIdqNs->totalIdq;
                $this->view->glosaIdq = $slaDsvIdqNs->glosaIdq;

                /**
                 * Array contendo a meta alcançada para todos os índices
                 */
                $meta[0] = $totalEpa.'%';//Índice de Início de Atendimento no Prazo
                $meta[1] = $totalMta.' dias';//Índice de Índices de Soluções dos Chamados Encerradas no Prazo
    //            $meta[2] = $totalIdq.' er/pf';//Índice de Ausência de Prazo

                /**
                 * Array contendo o valor a ser glosado para todos os índices
                 */
                $glosa[0] = $glosaEpa.'%';
                $glosa[1] = $glosaMta.'%';
    //            $glosa[2] = $glosaIdq.'%';
                /**
                 * Inclui a posição da meta alcançada no array dos indicadores mínimos
                 */
                $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(2, '');
                $fim =  count($indicadoresMinimos);
                for ($i = 0; $i<$fim; $i++) {
                    if( $indicadoresMinimos[$i]["SINS_CD_INDICADOR"] == "3" ){
                        unset($indicadoresMinimos[$i]);
                    }
                }

                $fim =  count($indicadoresMinimos);
                for ($i = 0; $i<$fim; $i++) {
                        $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                        $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
                }
                $this->view->indicadoresMinimos = $indicadoresMinimos;
                $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
                $indMin->data = $indicadoresMinimos;
                $indMin->title = "SLA - DESENVOLVIMENTO E SUSTENTAÇÃO - TRF1";
    //            $indMin->periodo = 'PERÍODO: '.$Sla_Desenvolvimento_ns->data['DATA_INICIAL'].' À '.$Sla_Desenvolvimento_ns->data['DATA_FINAL'];
                $Sla_Desenvolvimento_ns->solicitacoesEpaFechamento = $solicitacoesEpaFechamento;
                $Sla_Desenvolvimento_ns->solicitacoesMtaFechamento = $solicitacoesMtaFechamento;
    //            $Sla_Desenvolvimento_ns->solicitacoesIdqFechamento = $solicitacoesIdqFechamento;


                $fechadas = array();
                $contaFechadas = -1;
                if  (
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) ||
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                        
                    ) {
                    /**
                     * Validação de fechamento - Verifica se uma dada movimentação referente a uma solicitação já não foi fechada 
                     */
                    foreach ($solicitacoesEpaFechamento as $chaveEpa => $EpaFechamento) {
                        $solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_INDICADOR'] = $EPA_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv =  $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_INDICADOR']
                                );

                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesEpaFechamento[$chaveEpa]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }

                    foreach ($solicitacoesMtaFechamento as $chaveMta => $MtaFechamento) {
                        $solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_INDICADOR'] = $MTA_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv = $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_INDICADOR']
                                );
                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesMtaFechamento[$chaveMta]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }

                    /*
                    foreach ($solicitacoesIdqFechamento as $chaveIdq => $IdqFechamento) {
                        $solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_INDICADOR'] = $IDQ_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv = $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_INDICADOR']
                                );
                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesIdqFechamento[$chaveIdq]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }
                     */
                }
                
//                if ($filtro == "S")
//                {
                    if(count($fechadas)>0)
                    {
                    $fechadas = array_unique($fechadas);
                    $fechadasStr = implode('.<br>',$fechadas);
                    $msg_to_user = "A(s) solicitação(ões) nº: <br> ".$fechadasStr.". <br> Já foi(ram) fechada(s). Retire-a(s) da planilha para que seja possível gerar o Relatório.";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $Sla_Desenvolvimento_ns->unsetAll();
                    $this->_helper->_redirector('indicadoresnivelservico', 'sladesenvolvimento', 'sosti');
                    }
//                }
                }
            
        }else{
        	/*
            $this->_helper->flashMessenger(array('message' => $msgUsuario, 'status' => 'notice'));
            $this->_helper->_redirector('index', 'index', 'admin');
            */
        	$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
            $this->_helper->_redirector ( 'index', 'index', 'admin' );
        }
    }
      
    public function relatoriofaturamentoAction()
    {
        ini_set('memory_limit', '-1');
        set_time_limit( 1200 );//10 minutos para gerar o relatório
        
        /*
        // Restricoes aplicadas conforme pedido nos sostis  2013010001155011550160000357 e 2013010001155011550160000349
        $userNs = new Zend_Session_Namespace('userNs');
        $horaInicio = mktime(10,00,00);
        $horaFinal =  mktime(19,00,00);
        $horaAtual = mktime(date("H"), date("i"), date("s"));
        $msgUsuario = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco
                       de dados, a funcionalidade de emissão de relatórios de SLA somente estará disponível antes das 10:00 e após às 19:00.";
        
        if ( ($horaAtual <= $horaInicio || $horaAtual >= $horaFinal) || strcmp($userNs->matricula, 'TR300785') == 0 || strcmp($userNs->matricula, 'TR179603') == 0 || strcmp($userNs->matricula, 'TR18077PS') == 0){
        */
        // Validação de acesso às funcionalidades do SLA, conforme servidor web
        $negocio = new Trf1_Sosti_Negocio_Sla ();
        $permiteSla = $negocio->permiteSla ();

        $permiteSla ['permissao'] = true;
        
        if ($permiteSla ['permissao']) {
            $this->view->mostraRelatorio = "S";
            $userNs = new Zend_Session_Namespace('userNs'); 
            $indicadorNivelServ = new Application_Model_DbTable_SosTbSinsIndicNivelServ();
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $OcsTbPupePerfilUnidPessoa = new Application_Model_DbTable_OcsTbPupePerfilUnidPessoa();
            $SosTbGrexGrupoServExped = new Application_Model_DbTable_SosTbGrexGrupoServExped();
            $SosTbFemvFechamentoMovimen = new Application_Model_DbTable_SosTbFemvFechamentoMovimen();
            $SosTbFeslFechamentoSla = new Application_Model_DbTable_SosTbFeslFechamentoSla();
          //  $mapperDocumento = new Sisad_Model_DataMapper_Documento();
            $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
            $possui_permissao_fechamento = $OcsTbPupePerfilUnidPessoa->getPossuiPerfilPorNome('GESTOR DO CONTRATO DO DESEN. E SUSTENTAÇÃO', $userNs->matricula);
            $this->view->possui_permissao_fechamento = $possui_permissao_fechamento;
            /**
             * Gera o cache
             */      
            $frontendOptions = array(
                'lifetime' => 1800, // cache lifetime of 30 minutes
                'automatic_serialization' => true
            );
            $cache_dir = APPLICATION_PATH . '/../temp';
            $backendOptions = array(
                'cache_dir' => $cache_dir 
            );
            // getting a Zend_Cache_Core object
            $cache = Zend_Cache::factory('Core',
                                         'File',
                                         $frontendOptions,
                                         $backendOptions);
            $idCache = $userNs->matricula.'SLADESENTEMPXLS';

            $tempoSla = new App_Sosti_TempoSla();
            $TempoSlaDesenvolvimento = new App_Sosti_TempoSlaDesenvolvimento();
            /**
            *Importa a classe de Importar Excel 
            */  
            include(realpath(APPLICATION_PATH.'/../library/PHPExcel/Classes/PHPExcel.php'));

            /**
            *Form para importar o execel. 
            */  
            $ImportaPlanilha = new  Sosti_Form_ImportaPlanilha();
            $this->view->form = $ImportaPlanilha;
            $this->view->title = "SLA - DESENVOLVIMENTO E SUSTENTAÇÃO - TRF1";


            $Sla_Desenvolvimento_ns = new Zend_Session_Namespace('Sla_Desenvolvimento_ns');
            if ($Sla_Desenvolvimento_ns->data != '') {
                $ImportaPlanilha->populate($Sla_Desenvolvimento_ns->data);
            }

            $formValido = true;
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $tipoEntrada    = $data["TIPO_ENTRADA"];
                
                
                try {
                    if ($tipoEntrada == "TIPO_ENTRADA_FATURAMENTO")
                    {
                        #Zend_Debug::dump($data);
                        
                        $numerosSolicSomente     = array();
                        $solicitacoes_emconjunto = array();
                        $solicitacoes_conjuntos  = array();
                        $secundarias             = array();
                        $arrCount                = 0;
                        
                        foreach ($data['solicitacao'] as $v => $vv)
                        {
                            $kvv    = explode(",",$vv);
                            $bs     = explode("\"",$kvv[1]);
                            $nSolic = $bs[3];
                            $numerosSolicSomente[] = trim($nSolic);
                            try
                            {

                                #$numerosSolic[$arrCount]['SOLICS'] = trim($valorCol); = NUMERO DA SOLICITAÇÃO
                                $numerosSolic[$arrCount]['SOLICS']      = $nSolic;
                                $numerosSolic[$arrCount]['SECUNDARIA']  = false;
                                $arrCount++;
                            }
                                catch (Exception $exc)
                                {

                                }
                        }
                        #Zend_Debug::dump($numerosSolic,'NumSolic');
                        
                        $exiteInvalido = false;
                        $msg_to_user = '';
                        foreach ($numerosSolic as $chave => $value) 
                        {

                            if (strlen($numerosSolic[$chave]['SOLICS']) != 28) 
                            {
                                $exiteInvalido = true;
                                $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois não é um número de 28 digitos. Ou está sem o separador entre números de solicitações.";
                            }
                            
                            $tamanho_da_string = strlen($numerosSolic[$chave]['SOLICS']);
                            $exiteInvalidoPorCaractere = false;
                            for ($i = 0; $i < $tamanho_da_string; $i++) {
                                $auxNumero = (string)$numerosSolic[$chave]['SOLICS'];
                                if (!is_numeric($auxNumero[$i])) {
                                    $exiteInvalidoPorCaractere = true;
                                }
                            }
                            if ($exiteInvalidoPorCaractere) {
                                $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois possui caracteres não numéricos. Ou está sem o separador entre números de solicitações.";
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }
                        }
                        if ($exiteInvalido) 
                        {
                            $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                            $this->view->flashMessagesView .= $msg_to_user;
                            throw new Exception(' Foi encontrado um problema.');
                        }
                        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
                $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
                $solicsBanco = $tabelaSadTbDocmDocumento->fetchAll($clausulaInDocm);

                $numerosNaoEncontrados = array();
                $idsSolics = array();
                foreach ($numerosSolic as $chave => $value) 
                {
                    $encontrado = false;
                    foreach ($solicsBanco as $valueBanco) 
                    {
                        if (strcmp($numerosSolic[$chave]['SOLICS'], $valueBanco['DOCM_NR_DOCUMENTO']) == 0) 
                        {
                            $encontrado = true;
                            #echo "encontrado!!";
                        }
                    }
                    if ($encontrado == false) 
                    {
                        $numerosNaoEncontrados[] = $numerosSolic[$chave];
                    }
                    $encontrado = false;
                }

                if (count($numerosNaoEncontrados) > 0) {
                    $msg_to_user = '';
                    foreach ($numerosNaoEncontrados as $chave => $value) {
                        $msg_to_user .= "<br>O valor da célula " . $numerosNaoEncontrados[$chave]['COL'] . $numerosNaoEncontrados[$chave]['LIN'] . ": \"" . $numerosNaoEncontrados[$chave]['SOLICS'] . "\" não foi encontrado na base de dados.";
                    }
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                    $this->view->flashMessagesView .= $msg_to_user;
                    throw new Exception(' Foi encontrado um problema.');
                }
                        
                        foreach ($solicsBanco as $valueBanco) 
                 {
                    $idsSolics[] = $valueBanco['DOCM_ID_DOCUMENTO'];
                 }

                $idsSolicsImplode = implode(',',$idsSolics);
                
                $Sla_Desenvolvimento_ns->data               = $data;
                $Sla_Desenvolvimento_ns->idsSolicsImplode   = $idsSolicsImplode;
                $Sla_Desenvolvimento_ns->PontosFuncao       = $PontosFuncao;
                        
                        
                    }
                    else
                   { 
                   $ip = $ImportaPlanilha->switchTipoEntrada($data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA]);
                    
                    
                    /**
                     * Validação do campo de upload de arquivo
                     */
                    if ($ImportaPlanilha->isValid($data)) 
                    {
                        
                        $data = array_merge($this->getRequest()->getPost(), $ImportaPlanilha->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                        
                        
                        
                        
                        #Zend_Debug::dump($data);
                       
                        
                        /**
                         * Recebimento do arquivo na pasta temp e instanciação da classe de importação
                         */
                        if($ImportaPlanilha->PLANILHA_ARQUIVO->isUploaded()){
                            $ImportaPlanilha->PLANILHA_ARQUIVO->receive();
                            if ($ImportaPlanilha->PLANILHA_ARQUIVO->isReceived()) {

                                $fullFilePath = $ImportaPlanilha->PLANILHA_ARQUIVO->getFileName(); /* caminho completo do arquivo gravado no servidor */
                                $arquivoPlanilhaNome = $ImportaPlanilha->PLANILHA_ARQUIVO->getFileName(null,false); /* caminho completo do arquivo gravado no servidor */
                                $objPHPExcel = PHPExcel_IOFactory::load($fullFilePath);
                                
                                if (($arquivoPlanilha = $cache->load($idCache)) === false ) {
                                    $arquivoPlanilha = file_get_contents($fullFilePath);
                                    $cache->save($arquivoPlanilha, $idCache);
                                }
                                $Sla_Desenvolvimento_ns->arquivoPlanilhaPath = $fullFilePath;
                                $Sla_Desenvolvimento_ns->arquivoPlanilhaNome = $arquivoPlanilhaNome;
                                unlink($fullFilePath);


                                /**
                                 * Validação de coordenada de celula. 
                                 */
                                $CellCollection = $objPHPExcel->getActiveSheet()->getCellCollection();
                                $Validate_InArray = new Zend_Validate_InArray($CellCollection);
                                $ImportaPlanilha->getElement('CELULA_INICIAL')->addValidator($Validate_InArray);
                                $ImportaPlanilha->getElement('CELULA_FINAL')->addValidator($Validate_InArray);
                                $ImportaPlanilha->getElement('CELULA_TOTAL_PF')->addValidator($Validate_InArray);
                            }
                        }

                        if ($ImportaPlanilha->isValid($data)) {

                            if ($data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) {
                             
                            /**
                             * Validação das coordenadas informadas são da mesma coluna 
                             * e recolhimento das coordenadas para o recolhimento dos números de solicitações 
                             */
                            $coordenadaInicial = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_INICIAL')->getValue())->coordinateFromString($ImportaPlanilha->getElement('CELULA_INICIAL')->getValue());
                            $colunaInicio = $coordenadaInicial[0];
                            $linhaIncial = $coordenadaInicial[1];
                            $coordenadaFinal = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_FINAL')->getValue())->coordinateFromString($ImportaPlanilha->getElement('CELULA_FINAL')->getValue());
                            $colunaFim = $coordenadaFinal[0];
                            $linhaFinal = $coordenadaFinal[1];
                            if ($colunaInicio != $colunaFim) {
                                throw new Exception(' A coordenada de coluna da celula inicial deve ser a mesma da coordenada de coluna da celula final.');
                            }
                            $coluna = $colunaInicio;


                            /**
                             * Recolhe a celula referente ao total de ponto de função 
                             */
                            $PontosFuncao = $objPHPExcel->getActiveSheet()->getCell($ImportaPlanilha->getElement('CELULA_TOTAL_PF')->getValue())->getCalculatedValue();

                            $exiteInvalidoPorCaractere = false;
                            $tamanho_da_string = strlen($PontosFuncao);
                            for ($i = 0; $i < $tamanho_da_string; $i++) {
                                $auxNumero = (string)$PontosFuncao;
                                if (!is_numeric($auxNumero[$i])) {
                                    if( 
                                            (strcmp($auxNumero[$i],',') != 0)
                                            &&
                                            (strcmp($auxNumero[$i],'.') != 0)
                                      ){
                                        $exiteInvalidoPorCaractere = true;
                                    }
                                }
                            }
                            if ($exiteInvalidoPorCaractere) {
                                $msg_to_user .= "<br>O valor da célula " . $ImportaPlanilha->getElement('CELULA_TOTAL_PF')->getValue() . ": \"" . $PontosFuncao . "\" não é um número de total de Pontos de Função válido, pois possui caracteres não numéricos diferentes de (,e.). ";
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }



                            /***********************************
                             * DADOS E COORDENADAS NA PLANILHA *
                             ***********************************/
                            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                            $numerosSolic = array();
                            $numerosSolicSomente = array();
                            $arrCount = 0;
                            $solicitacoes_emconjunto = array();
                            $solicitacoes_conjuntos = array();
                            $secundarias = array();

                            foreach ($sheetData as $chaveLin => $valorLin) {
                                if ($chaveLin >= $linhaIncial && $chaveLin <= $linhaFinal) {
                                    foreach ($valorLin as $chaveCol => $valorCol) {
                                        if (
                                                strcmp($chaveCol, $coluna) == 0
                                        ) {
                                            /*
                                             *  Realiza o explode na célula para recolher mais de um número de solicitação se exixtir
                                             */
                                            $explodeValorCol = explode($ImportaPlanilha->getElement('SEPARADOR_MULTIPLO_NUMEROS')->getValue(), trim($valorCol));
                                            foreach ($explodeValorCol as $chave_col_explode => $valor_col_explode) {
                                                /**
                                                 * Realiza o trim dos valores achados no explode
                                                 */
                                                $trimvalor = trim($valor_col_explode);
                                                /**
                                                 * Retira a posição da array do explode se o valor for vazio
                                                 */
                                                if (strlen($trimvalor) > 0) {
                                                    $explodeValorCol[$chave_col_explode] = $trimvalor;
                                                } else {
                                                    unset($explodeValorCol[$chave_col_explode]);
                                                }
                                            }

                                            /**
                                             * Se existir mais de um número de solicitação na célula armazena na array de solicitações com as mesmas coordenadas de célula.
                                             */
                                            if ( count($explodeValorCol) > 1 ) {
                                                $contador_controle = 0;
                                                foreach ($explodeValorCol as $valor_col_explode) {
                                                    $numerosSolic[$arrCount]['SOLICS'] = trim($valor_col_explode);
                                                    $numerosSolic[$arrCount]['COL'] = $coluna;
                                                    $numerosSolic[$arrCount]['LIN'] = $chaveLin;
                                                    $numerosSolicSomente[] = trim($valor_col_explode);

                                                    /**
                                                    * Dados para agrupar as solicitaçãoe que estão na mesma celula
                                                    */
                                                    $solicitacoes_conjuntos[$chaveLin][] = $numerosSolic[$arrCount]['SOLICS'];
                                                    $solicitacoes_emconjunto[] = $numerosSolic[$arrCount]['SOLICS'];
                                                    if($contador_controle > 0){
                                                        $numerosSolic[$arrCount]['SECUNDARIA'] = true;
                                                        $secundarias[] = $numerosSolic[$arrCount]['SOLICS'];
                                                    }else{
                                                        $numerosSolic[$arrCount]['SECUNDARIA'] = false;
                                                    }

                                                    $arrCount++;
                                                    $contador_controle++;
                                                }
                                            } else {
                                                /**
                                                 * Senao armazena o valor normalmente na array
                                                 */
                                                $numerosSolic[$arrCount]['SOLICS'] = trim($valorCol);
                                                $numerosSolic[$arrCount]['COL'] = $coluna;
                                                $numerosSolic[$arrCount]['LIN'] = $chaveLin;
                                                $numerosSolicSomente[] = trim($valorCol);
                                                $numerosSolic[$arrCount]['SECUNDARIA'] = false;
                                                $arrCount++;
                                            }
                                        }
                                    }
                                }
                            }

                             
                            
//                            if ($filtro == "S")
//                            {    
                            /*****************************************************
                             * VALIDAÇÃO DO NUMERO DE CARACTERES DO NUMERO SOSTI * 
                             *****************************************************/
                            
                            $exiteInvalido = false;
                            $msg_to_user = '';
                            foreach ($numerosSolic as $chave => $value) 
                            {
                                $cSolics[] = $numerosSolic[$chave]['SOLICS'];
                                
                                if (strlen($numerosSolic[$chave]['SOLICS']) != 28) 
                                {
                                    $exiteInvalido = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois não é um número de 28 digitos. Ou está sem o separador entre números de solicitações.";
                                }
                                $tamanho_da_string = strlen($numerosSolic[$chave]['SOLICS']);
                                $exiteInvalidoPorCaractere = false;
                                for ($i = 0; $i < $tamanho_da_string; $i++) 
                                {
                                    $auxNumero = (string)$numerosSolic[$chave]['SOLICS'];
                                    if (!is_numeric($auxNumero[$i])) {
                                        $exiteInvalidoPorCaractere = true;
                                    }
                                }
                                if ($exiteInvalidoPorCaractere) 
                                {
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" não é um número de solicitação válido, pois possui caracteres não numéricos. Ou está sem o separador entre números de solicitações.";
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView .= $msg_to_user;
                                    throw new Exception(' Foi encontrado um problema.');
                                }
                            }
                            
                            if ($exiteInvalido) {
                                $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception(' Foi encontrado um problema.');
                            }
                            
                            /********************************************
                             * VALIDAÇÃO DE EXISTÊNCIA NA BASE DE DADOS *
                             ********************************************/
                            
                            $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
                            $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
                            $solicsBanco = $SadTbDocmDocumento->fetchAll($clausulaInDocm);

                            $numerosNaoEncontrados = array();
                            $idsSolics = array();
                            foreach ($numerosSolic as $chave => $value) {
                                $encontrado = false;
                                foreach ($solicsBanco as $valueBanco) {
                                    if (strcmp($numerosSolic[$chave]['SOLICS'], $valueBanco['DOCM_NR_DOCUMENTO']) == 0) {
                                        $encontrado = true;
                                    }
                                }
                                if ($encontrado == false) {
                                    $numerosNaoEncontrados[] = $numerosSolic[$chave];
                                }
                                $encontrado = false;
                            }

                            if (count($numerosNaoEncontrados) > 0) {
                                $msg_to_user = '';
                                foreach ($numerosNaoEncontrados as $chave => $value) 
                                {
                                    $msg_to_user .= "<br />O SOSTI da célula " . $numerosNaoEncontrados[$chave]['COL'] . $numerosNaoEncontrados[$chave]['LIN'] . " número " . $numerosNaoEncontrados[$chave]['SOLICS'] . " não foi encontrado na base de dados.";
                                }
                                $msg_to_user = "<div class='notice'><strong>Descrição:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                throw new Exception('Problema na importação.');
                            }
                            
                            /**************************************
                             * VALIDAÇÃO DE DUPLICIDADE NA TABELA *
                             **************************************/
                           
                            $msg = false;
                            
                            $diffSolics = array_unique(array_diff_assoc($cSolics, array_unique($cSolics)));
                            $msg_erro .= "Existem registros duplicados para os Sostis N°(s):<ul>";
                            foreach($diffSolics as $d)
                            {
                                $msg = true;
                                $msg_erro .= "<li>".$d."</li>";
                            }
                            $msg_erro .= "</ul>";
                            if ($msg) 
                            {
                                $msg_erro = "<div class='notice'><strong>Descrição:</strong> $msg_erro</div>";
                                $this->view->flashMessagesView .= $msg_erro;
                                throw new Exception(' Foi encontrado um problema na validação dos SOSTIs.');
                            }
                            
                            
                            /*******************************************
                             * VALIDAÇÃO PARA SOLICITAÇÕES EM GARANTIA *
                             *******************************************/
                            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                            
                            $NegociaGarantiaDesenvolvimentoRelatorioSla = new Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimentoRelatorioSla();
                            if($NegociaGarantiaDesenvolvimentoRelatorioSla->getVerificaGarantia($numerosSolicSomente)){
                                $Garantias = $NegociaGarantiaDesenvolvimentoRelatorioSla->getGarantias($numerosSolicSomente);
                                $numerosDeGarantiaEncontrados = array();
                                $idsSolics = array();
                                
                                foreach ($numerosSolic as $chave => $value) {
                                    foreach ($Garantias as $Garantia) {
                                        if (strcmp($numerosSolic[$chave]['SOLICS'], $Garantia['DOCM_NR_DOCUMENTO']) == 0) {
                                            $numerosSolic[$chave]['DADOS_GARANTIA'] = $Garantia;
                                            $numerosDeGarantiaEncontrados[] = $numerosSolic[$chave];
                                        }
                                    }
                                }
                                if (count($numerosDeGarantiaEncontrados) > 0) {
                                    $msg_to_user = '';
                                    foreach ($numerosDeGarantiaEncontrados as $chave => $value) 
                                        {   
                                             
                                             $getDadosSolic["DOCM_NR_DOCUMENTO"] = $numerosDeGarantiaEncontrados[$chave]['SOLICS'];
                                             $dadosSolic    = $negocioFaturamento->getRelatorioRias($getDadosSolic);
                                             $idSolic       = $dadosSolic[0]['SSOL_ID_DOCUMENTO'];
                                             
                                             
                                            /************************************************
                                             * CASO GARANTIA
                                             ************************************************
                                             * STATUSDSV            = NÃO FATURAR (1)
                                             * CLASSIFICAÇÃO DSV    = GARANTIA (2)
                                             * FASE                 = BAIXA(1000)
                                             ************************************************/
                                            
                                                $db->beginTransaction();
                                                
                                                $dadosCadastro["PFDS_ID_SOLICITACAO"]   = $idSolic;
                                                
                                                $dadosCadastro["PFDS_ID_STATUS"]        = 1;
                                                $dadosCadastro["PFDS_ID_CLASSIFICACAO"] = 2;
                                                $incluiDados = $negocioFaturamento->salvarDadosDesenvolvedora($dadosCadastro);
                                                
                                                #Zend_Debug::dump($dadosCadastro,'CADASTRO');
                                                
                                                $db->commit();
                                       
                                     $msg_to_user .= "<br>O valor da célula " . $numerosDeGarantiaEncontrados[$chave]['COL'] . $numerosDeGarantiaEncontrados[$chave]['LIN'] . ": \"" . $numerosDeGarantiaEncontrados[$chave]['SOLICS'] . "\" é uma solicitação considerada garantia.";
                                        if($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_ACEITE'] == "A"){
                                            $msg_to_user .= "<strong><i> Que a garantia foi aceita.</i></strong>";
                                        }else if(is_null($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_CONCORDANCIA'])){
                                            $msg_to_user .= "<span style=\"color: red;\"><strong><i> Que a divergência não foi avaliada.</i></strong></span>";
                                        }else if($numerosDeGarantiaEncontrados[$chave]['DADOS_GARANTIA']['NEGA_IC_CONCORDANCIA'] == "D"){
                                            $msg_to_user .= "<strong><i> Que foi confirmada na divergência.</i></strong>";
                                        }
                                    }
                                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong> $msg_to_user</div>";
                                    $this->view->flashMessagesView .= $msg_to_user;
                                }
                                throw new Exception('Foi encontrado um problema na garantia.');
                            } 
                            
                            
                            /*********************************************
                             * VALIDAÇÃO DO STATUS DE AVALIAÇÃO DO SOSTI *
                             *                                           *
                             *   Baixada    == NULL                      *
                             *   Avaliada   != 6                         *
                             *   Recusada   == 6                         *
                             *********************************************/
                            
                            $sostiValidado = false;
                            $msg_to_user = '';
                            foreach ($numerosSolic as $chave => $value) 
                            {
                                $numeroSosti                        = $numerosSolic[$chave]['SOLICS'];
                                #echo "<br />SOSTINR".$numeroSosti;
                                $getDadosSolic["DOCM_NR_DOCUMENTO"] = $numeroSosti;
                                $dadosSolic                         = $negocioFaturamento->getRelatorioRias($getDadosSolic,true);
                                #Zend_Debug::dump($dadosSolic,'DADOS');
                                $tipoSat                            = $dadosSolic[0]['STSA_ID_TIPO_SAT'];
                                
                                
                                
                                
                                if ($tipoSat == NULL)
                                {
                                    $sostiValidado = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" corresponde a um SOSTI sem avaliação";
                                }
                                else if ($tipoSat == 6)
                                {
                                    $sostiValidado = true;
                                    $msg_to_user .= "<br>O valor da célula " . $numerosSolic[$chave]['COL'] . $numerosSolic[$chave]['LIN'] . ": \"" . $numerosSolic[$chave]['SOLICS'] . "\" corresponde a um SOSTI que foi RECUSADO.";
                                }
                                
                                
                            }
                            
                            if ($sostiValidado) {
                                $msg_to_user = "<div class='notice'><strong>Descrição:</strong> $msg_to_user</div>";
                                $this->view->flashMessagesView .= $msg_to_user;
                                
                                #throw new Exception(' Foi encontrado um problema na avaliação dos SOSTIs.');
                            }
                            
//                            }######## FIM DO FILTRO

                            /*************************************
                             * RECOLHE IDS DOS SOSTIS PARA QUERY *
                             *************************************/
                            
                            foreach ($solicsBanco as $valueBanco) {
                                $idsSolics[] = $valueBanco['DOCM_ID_DOCUMENTO'];
                            }

                            $idsSolicsImplode = implode(',',$idsSolics);

                            /**
                             * Armazenando na sessao o último post
                             */
                            $Sla_Desenvolvimento_ns->data = $data;
                            $Sla_Desenvolvimento_ns->idsSolicsImplode = $idsSolicsImplode;
                            $Sla_Desenvolvimento_ns->PontosFuncao = $PontosFuncao;


                            /**
                             * Dados para agrupar as solicitaçãoe que estão na mesma celula
                             */
                            $solicitacoes_emconjunto = array_unique($solicitacoes_emconjunto);
                            foreach ($solicitacoes_conjuntos as $key => $value) {
                                $solicitacoes_conjuntos[$key] = array_unique($solicitacoes_conjuntos[$key]);
                            }
                            $secundarias = array_unique($secundarias);
                            $this->view->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                            $this->view->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                            $this->view->secundarias = $secundarias;

                            $Sla_Desenvolvimento_ns->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                            $Sla_Desenvolvimento_ns->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                            $Sla_Desenvolvimento_ns->secundarias = $secundarias;

                            }else{
                                /**
                                * Armazenando na sessao o último post
                                */
                                $secundarias = array();
                                $solicitacoes_emconjunto = array();
                                $solicitacoes_conjuntos = array();

                                $Sla_Desenvolvimento_ns->data = $data;
                                $Sla_Desenvolvimento_ns->idsSolicsImplode = NULL;
                                $Sla_Desenvolvimento_ns->PontosFuncao = NULL;

                                $this->view->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                                $this->view->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                                $this->view->secundarias = $secundarias;

                                $Sla_Desenvolvimento_ns->solicitacoes_emconjunto = $solicitacoes_emconjunto;
                                $Sla_Desenvolvimento_ns->solicitacoes_conjuntos = $solicitacoes_conjuntos;
                                $Sla_Desenvolvimento_ns->secundarias = $secundarias;

                            }


                        }else{
                            $ImportaPlanilha->populate($data);
                            $this->view->form = $ImportaPlanilha;
                            $formValido = false;
                        }
                   }else{
                        $ImportaPlanilha->populate($data);
                        $this->view->form = $ImportaPlanilha;
                        $formValido = false;
                    }
                }} catch (Exception $exc) {
                    $msg_to_user = "<div class='notice'><strong>Alerta:</strong>" . $exc->getMessage() . "</div>";
                    $this->view->flashMessagesView = $msg_to_user . $this->view->flashMessagesView;
                    $ImportaPlanilha->populate($data);
                    $formValido = false;
                }

            }


            if ($Sla_Desenvolvimento_ns->data != '' && $formValido) {
                $this->view->data   = $Sla_Desenvolvimento_ns->data;
                $ImportaPlanilha->populate($Sla_Desenvolvimento_ns->data);
                
                #Zend_Debug::dump($ImportaPlanilha,'IMPORTA_PLANILHA');
                
                $idsSolicsImplode = $Sla_Desenvolvimento_ns->idsSolicsImplode;
                
                #Zend_Debug::dump($idsSolicsImplode,'implode');
                
                $PontosFuncao = $Sla_Desenvolvimento_ns->PontosFuncao;

                $solicitacoes_emconjunto    = $Sla_Desenvolvimento_ns->solicitacoes_emconjunto;
                $solicitacoes_conjuntos     = $Sla_Desenvolvimento_ns->solicitacoes_conjuntos;
                $secundarias                = $Sla_Desenvolvimento_ns->secundarias;
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                {
                    $secundarias[]="0";
                }
                
                #Zend_Debug::dump($solicitacoes_emconjunto,'em conjunto');
                #Zend_Debug::dump($solicitacoes_conjunto,'conjunto');
                #Zend_Debug::dump($secundarias,'sec');
                
                
                
                /**
                 * Indicadores de Níveis de Serviço
                 */
                $EPA_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'EPA');
                $MTA_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'MTA');
                
                
                #Zend_Debug::dump($EPA_DADOS);
                #Zend_Debug::dump($MTA_DADOS);
                
    //            $IDQ_DADOS  = $indicadorNivelServ->getIndicadorPorCaixaeSiglaIndicador(2, 'IDQ');

                $Sla_Desenvolvimento_ns->EPA_DADOS  = $EPA_DADOS;
                $Sla_Desenvolvimento_ns->MTA_DADOS  = $MTA_DADOS;
    //            $Sla_Desenvolvimento_ns->IDQ_DADOS  = $IDQ_DADOS;

                if($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO)
                {

                    $intervaloData["DATA_INICIAL"] = $Sla_Desenvolvimento_ns->data["DATA_INICIAL"];
                    $intervaloData["DATA_FINAL"]   = $Sla_Desenvolvimento_ns->data["DATA_FINAL"];

                    $solicitacoesEpa = $indicadorNivelServ->getDatasSLA_EPA(2,null             ,$intervaloData, $EPA_DADOS['SINS_ID_INDICADOR'],$MTA_DADOS['SINS_ID_INDICADOR']);
                    
                    
                }
                else if(($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) ||
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                       )
                       {
                            $solicitacoesEpa = $indicadorNivelServ->getDatasSLA_EPA(2,$idsSolicsImplode, null         ,  $EPA_DADOS['SINS_ID_INDICADOR'],$MTA_DADOS['SINS_ID_INDICADOR']);
                            
                        }
                
                    
                
    //            $solicitacoesIdq = $indicadorNivelServ->getDatasSLA_IDQ(2,$idsSolicsImplode,$IDQ_DADOS['SINS_ID_INDICADOR']);

                $i = 0;
                $faturamento = new Trf1_Sosti_Negocio_Faturamento();
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) 
                {
                    foreach ($solicitacoesEpa as $solics) 
                    {
                            $FaturamentoContratada = $faturamento->dadosFaturamentoContratada($solics['SSOL_ID_DOCUMENTO']);   
                            
                            $pfBruto    = $FaturamentoContratada[0]['PFDS_QT_PF_BRUTO'];
                            $pfLiquido  = $FaturamentoContratada[0]['PFDS_QT_PF_LIQUIDO'];
                            
                            $solicitacoesEpa[$i]["PFBRUTO"]     = $pfBruto;
                            $solicitacoesEpa[$i]["PFLIQUIDO"]   = $pfLiquido;
                            
                            $i++;
                    }        
                }
                
                /*********************************************** || PERIODO ||
                 * CONTABILIZAR SOMENTE CLASSIFICADOS 
                 ***********************************************/
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
                    if($Sla_Desenvolvimento_ns->data["CONTA_NAO_CATEGORIZADO"] == 'N'){
                        foreach ($solicitacoesEpa as $chaveEapSis => $valueEapSis) {
                            if( is_null($solicitacoesEpa[$chaveEapSis]["SERVICO_SISTEMA"]) && is_null($solicitacoesEpa[$chaveEapSis]["SSPA_DT_PRAZO"]) ){
                                unset($solicitacoesEpa[$chaveEapSis]);
                            }
                        }
                        $solicitacoesEpa_aux = $solicitacoesEpa;
                        unset($solicitacoesEpa);
                        $contador = 0;
                        foreach ($solicitacoesEpa_aux as $chaveEapSis => $valueEapSis) {
                            $solicitacoesEpa[$contador++] = $solicitacoesEpa_aux[$chaveEapSis];
                        }
                    }
                }
                /************************************************
                 * **********************************************
                 */

                /*********************************************** || PERIODO ||
                 * DESCONSIDERAR VINCULADAS
                 ***********************************************/
                if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
                    foreach ($solicitacoesEpa as $chaveEapSis => $valueEapSis) {
                        if( $solicitacoesEpa[$chaveEapSis]["PRINCIPAL_OU_ORF"] == '0' ){
                            unset($solicitacoesEpa[$chaveEapSis]);
                        }
                    }
                    $solicitacoesEpa_aux = $solicitacoesEpa;
                    unset($solicitacoesEpa);
                    $contador = 0;
                    foreach ($solicitacoesEpa_aux as $chaveEapSis => $valueEapSis) {
                        $solicitacoesEpa[$contador++] = $solicitacoesEpa_aux[$chaveEapSis];
                    }
                }
                /************************************************
                 * **********************************************
                 */

                
                /**************************************************/
                /**************************************************/
                /**
                * Tratamentos para agrupar as solicitaçãoe que estão na mesma celula
                */
                foreach ($solicitacoesEpa as $chaveEAP => $valorEPA) 
                {
                    $solicitacoesEpa[$chaveEAP]['REFERENCIA'] = null;
                }
                #Zend_Debug::dump($solicitacoesEpa,'solEPA');
                
                foreach ($solicitacoesEpa as $chaveEAP => $valorEPA) 
                {
                    #echo "entra aqui";
                    if( !( array_search((string)$solicitacoesEpa[$chaveEAP]["DOCM_NR_DOCUMENTO"], $solicitacoes_emconjunto,true) === false ) ){
                        foreach ($solicitacoes_conjuntos as $conj_c => $conj_v) {
                            if( !( array_search((string)$solicitacoesEpa[$chaveEAP]["DOCM_NR_DOCUMENTO"],$solicitacoes_conjuntos[$conj_c],true) === false ) ){
                                foreach ($solicitacoes_conjuntos[$conj_c] as $vConjunto) {
                                    foreach ($solicitacoesEpa as $cEap => $vEap) {
                                        if (strcmp((string) $solicitacoesEpa[$cEap]["DOCM_NR_DOCUMENTO"], (string) $vConjunto) === 0) {
                                            $solicitacoesEpa[$cEap]['REFERENCIA'] = "==".$conj_c."==";
                                        }
                                    }
                                }
                            }
                        }
                    }
                    #Zend_Debug::dump($solicitacoesEpa,'FOREACH');
                }
                /*
                foreach ($solicitacoesIdq as $chaveIDQ => $valorIDQ) {
                    $solicitacoesIdq[$chaveIDQ]['REFERENCIA'] = null;
                }
                foreach ($solicitacoesIdq as $chaveIDQ => $valorIDQ) {
                    if( !( array_search((string)$solicitacoesIdq[$chaveIDQ]["DOCM_NR_DOCUMENTO"], $solicitacoes_emconjunto,true) === false ) ){
                        foreach ($solicitacoes_conjuntos as $conj_c => $conj_v) {
                            if( !( array_search((string)$solicitacoesIdq[$chaveIDQ]["DOCM_NR_DOCUMENTO"],$solicitacoes_conjuntos[$conj_c],true) === false ) ){
                                foreach ($solicitacoes_conjuntos[$conj_c] as $vConjunto) {
                                    foreach ($solicitacoesIdq as $cIDQ => $vIDQ) {
                                        if (strcmp((string) $solicitacoesIdq[$cIDQ]["DOCM_NR_DOCUMENTO"], (string) $vConjunto) === 0) {
                                            $solicitacoesIdq[$cIDQ]['REFERENCIA'] = "==".$conj_c."==";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                  */
                /***************************************************************************************************/
                /***************************************************************************************************/




                /**************************************************************************************/
                /**************************************************************************************/
                /**
                * Calcular o EPA – Volume de ordens de serviço executadas nos prazos acordados
                */

                /**
                 * Configurações do horário de expediente
                 */
                $expedienteNormal = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "NORMAL");
                $expedienteEmergencia = $SosTbGrexGrupoServExped->getExpedientePorGrupoPorNomeExpediente(2, "EMERGENCIAL");

                $expediente = array( 'NORMAL'=>array('INICIO'=>$expedienteNormal["INICIO"],'FIM'=>$expedienteNormal["FIM"]),'EMERGENCIAL'=>array('INICIO'=>$expedienteEmergencia['INICIO'],'FIM'=>$expedienteEmergencia['FIM']) );
                $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos( $expediente["NORMAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["NORMAL"]["INICIO"]) ;
                $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] = $tempoSla->converteHorasParaSegundos( $expediente["EMERGENCIAL"]["FIM"]) - $tempoSla->converteHorasParaSegundos($expediente["EMERGENCIAL"]["INICIO"]) ;
                $expediente["NORMAL"]["DIA_UTIL_HORAS"] = $expediente["NORMAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;
                $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] = $expediente["EMERGENCIAL"]["DIA_UTIL_SEGUNDOS"] / 60 / 60;

                ################# ADICIONADO 
                #ECHO "CONTANDO SOLICITAÇÕES: " .count($solicitacoesEpa);
                
                
                /**
                 * Calcula os prazos das solicitações
                 */
                if(count($solicitacoesEpa)>0)
                {
                    $TempoSlaDesenvolvimentoArr = $TempoSlaDesenvolvimento->PrazoSlaDesenvolvimento($solicitacoesEpa, 'MOFA_ID_MOVIMENTACAO', 'DATA_CHAMADO', 'SSPA_DT_PRAZO', 'CORRETIVA','EMERGENCIA', 'PROBLEMA','CAUSA', 'ASIS_PRZ_SOL_PROBLEMA', 'ASIS_PRZ_SOL_CAUSA_PROBLEMA', 'ASIS_PRZ_EXECUCAO_SERVICO',$expediente);
                    
                    #Zend_Debug::dump($TempoSlaDesenvolvimentoArr,'TEMPO_SLA_DESENVOLVIMENTO');
                }

                /**
                 * Calcula o tempo total das solicitações não contablizado o tempo em que a solicitação ficou aguardando a resposta do pedido de informação.
                 */
                if(count($solicitacoesEpa)>0)
                {
                    $TempoTotalPedidoInforArr = $tempoSla->TempoTotalPedidoInfor($solicitacoesEpa, 'MOFA_ID_MOVIMENTACAO', "DATA_CHAMADO", "DATA_FIM_CHAMADO","", "", $expediente);
                }
                #Zend_Debug::dump($TempoTotalPedidoInforArr,'ARR');
                
                $i = 0;
                $countSolicitacoesUtrapassadas = 0;
                $somaAtrasosMta = 0;
                $solicitacoesEpaFechamento = array();
                $solicitacoesMtaFechamento = array();
                $solicitacoesIdqFechamento = array();
                $AtrasosMta = 0;
                
                #Zend_Debug::dump($solicitacoesEpa,'EPA');
                
                foreach ($solicitacoesEpa as $epa) {
                    
                    $solicitacoesEpa[$i]["PRAZO_DATA"] = NULL;
                    if( array_search((string)$epa["DOCM_NR_DOCUMENTO"], $secundarias,true) === false ){
                        
                        #ECHO "hey ";
                        
                        $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"] = $TempoTotalPedidoInforArr[$epa["MOFA_ID_MOVIMENTACAO"]]["TEMPO_UTIL_TOTAL"];
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_SEGUNDOS_UTEIS"];
                        $solicitacoesEpa[$i]["PRAZO_CORRIDO_PADRAO"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_CORRIDO_PADRAO"];

                        if(is_null($solicitacoesEpa[$i]["SSPA_DT_PRAZO"])){
                            /**
                            * Verifica se esta dentro ou fora do prazo.
                            */
                            if ($solicitacoesEpa[$i]["PRAZO_CORRIDO_PADRAO"] === true) {
                                $dataInicial = $solicitacoesEpa[$i]["DATA_CHAMADO"];
                                $timeStampInicial = (int)mktime(substr($dataInicial,11,2),substr($dataInicial,14,2),substr($dataInicial,17,2),substr($dataInicial,3,2),substr($dataInicial,0,2),substr($dataInicial,6,4));
                                $solicitacoesEpa[$i]["PRAZO_DATA"] = $TempoSlaDesenvolvimentoArr[$epa["MOFA_ID_MOVIMENTACAO"]]["PRAZO_DATA"];
                                $dataPrazo = $solicitacoesEpa[$i]["PRAZO_DATA"];
                                $timeStampFinal = (int)mktime(substr($dataPrazo, 11, 2), substr($dataPrazo, 14, 2), substr($dataPrazo, 17, 2), substr($dataPrazo, 3, 2), substr($dataPrazo, 0, 2), substr($dataPrazo, 6, 4));
                                if ($timeStampFinal >= $timeStampInicial) {
                                    $prazoUltrapassado[$i] = false;
                                } else {
                                    $prazoUltrapassado[$i] = true;
                                }
                            } else {
                                if(!is_null($epa["SERVICO_SISTEMA"])){
                                    $prazoUltrapassado[$i] = $tempoSla->verificaPrazoUltrapassado($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"]);
                                }else{
                                    $prazoUltrapassado[$i] = false;
                                }
                            }
                        }else{
                            if($solicitacoesEpa[$i]["PRAZO_ULTRAPASSADO"] == '0'){
                                $prazoUltrapassado[$i] = false;
                            }else{
                                $prazoUltrapassado[$i] = true;
                            }
                        }



                        if ($prazoUltrapassado[$i] == false) {
                            if (is_null($epa['DESCONSIDERADO_EPA'])) {
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "S";
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "N";
                            }
                            //Array para o fechamento do sla
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                            $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            if (is_null($epa['DESCONSIDERADO_MTA'])) {
                                $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "S";
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "N";
                            }
                            //Array para o fechamento do sla
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                            $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];

                            $solicitacoesEpa[$i]["NO_PRAZO"] = "S";
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = 0;

                        }else{
                            if (is_null($epa['DESCONSIDERADO_EPA'])) {
                                $countSolicitacoesUtrapassadas++;
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "S";
                                //Array para o fechamento do sla
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'N';
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            }else{
                                $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = "N";
                                //Array para o fechamento do sla
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                                $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                            }
                            $solicitacoesEpa[$i]["NO_PRAZO"] = "N";


                            /**
                             * Calcula o atraso 
                             */
                            if(is_null($solicitacoesEpa[$i]["SSPA_DT_PRAZO"])){
                                $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"] - $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"];
                            }else{
                                if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                                    $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $tempoSla->tempoTotalSLA($solicitacoesEpa[$i]["SSPA_DT_PRAZO"], $solicitacoesEpa[$i]["DATA_FIM_CHAMADO"], $expediente['EMERGENCIAL']['INICIO'], $expediente['EMERGENCIAL']['FIM']);
                                }else{
                                    $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = $tempoSla->tempoTotalSLA($solicitacoesEpa[$i]["SSPA_DT_PRAZO"], $solicitacoesEpa[$i]["DATA_FIM_CHAMADO"], $expediente['NORMAL']['INICIO'], $expediente['NORMAL']['FIM']);
                                }
                            }

                            if($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] <= 0){
                                $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] = 0;
                            }else{
                                $AtrasosMta = 0;
                                if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                                    $AtrasosMta = $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] / ($expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"] * 60 * 60);
                                }else{
                                    $AtrasosMta = $solicitacoesEpa[$i]["ATRASO_SEGUNDOS"] / ($expediente["NORMAL"]["DIA_UTIL_HORAS"] * 60 * 60);
                                }

                                if (is_null($epa['DESCONSIDERADO_MTA'])) {
                                    $somaAtrasosMta += $AtrasosMta;
                                    $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "S";
                                    //Array para o fechamento do sla
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'N';
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                                } else {
                                    $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = "N";
                                    //Array para o fechamento do sla
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                                    $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];
                                }
                            }
                        }

                        /**
                        * Somente para Visualisação de dados
                        */
                        if($solicitacoesEpa[$i]["EMERGENCIA"] == "S"){
                            $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"], $expediente["EMERGENCIAL"]["DIA_UTIL_HORAS"]);
                        }else{
                            $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                            $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = $tempoSla->FormataSaidaSegundos($solicitacoesEpa[$i]["ATRASO_SEGUNDOS"], $expediente["NORMAL"]["DIA_UTIL_HORAS"]);
                        }
                    }else{
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS_STR"] = NULL;
                        $solicitacoesEpa[$i]["TEMPO_UTIL_TOTAL_STR"] = NULL;
                        $solicitacoesEpa[$i]["NO_PRAZO"] = NULL;
                        $solicitacoesEpa[$i]["CONSIDERADO_EPA"] = NULL;
                        $solicitacoesEpa[$i]["CONSIDERADO_MTA"] = NULL;
                        $solicitacoesEpa[$i]["PRAZO_SEGUNDOS_UTEIS"] = NULL;
                        $solicitacoesEpa[$i]["ATRASO_SEGUNDOS_STR"] = NULL;

                        //Array para o fechamento do sla
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                        $solicitacoesEpaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];

                        //Array para o fechamento do sla
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $epa["MOFA_ID_MOVIMENTACAO"];
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
                        $solicitacoesMtaFechamento[$epa["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $epa["DOCM_NR_DOCUMENTO"];


                    }
                    $i++;
                }


                $totalEpaSolicitacoes = (count($solicitacoesEpa));
                /**
                 * Subtrai do total de solicitações as solicitações secundarias(as solicitações que estão em uma mesma célula menos a primeira que é contablizada)
                 */
                $totalEpaSolicitacoes = $totalEpaSolicitacoes - count($secundarias);

                $countSolicitacoesNoPrazo = $totalEpaSolicitacoes - $countSolicitacoesUtrapassadas;

                if($totalEpaSolicitacoes > 0){
                    $totalEpa = ($countSolicitacoesNoPrazo/$totalEpaSolicitacoes) * 100  ;
                }else{
                    $totalEpa = 100;
                }

                $totalEpa = (float) sprintf('%.2f',$totalEpa);

                if ($totalEpa < 75) {
                    $glosaEpa = 10;
                }
                if (($totalEpa >= 75) && ($totalEpa < 80)) {
                    $glosaEpa = 5;
                }
                if (($totalEpa >= 80) && ($totalEpa < 85)) {
                    $glosaEpa = 4;
                }
                if (($totalEpa >= 85) && ($totalEpa < 90)) {
                    $glosaEpa = 3;
                }
                if (($totalEpa >= 90) && ($totalEpa < 95)) {
                    $glosaEpa = 2;
                }
                if (($totalEpa >= 95) && ($totalEpa <= 99)) {
                    $glosaEpa = 1;
                }
                if ($totalEpa > 99) {
                    $glosaEpa = 0;
                }
                /**
                 * Carrega as variáveis para gerar o indicador:
                 * EPA – Volume de ordens de serviço executadas nos prazos acordados
                 */
                $slaDsvEpaNs = new Zend_Session_Namespace('slaDsvEpaNs');
                $slaDsvEpaNs->totalEpaSolicitacoes = $totalEpaSolicitacoes;
                $slaDsvEpaNs->countSolicitacoesNoPrazo = $countSolicitacoesNoPrazo;
                $slaDsvEpaNs->countSolicitacoesUtrapassadas = $countSolicitacoesUtrapassadas;
                $slaDsvEpaNs->idIndicadorEPA = $EPA_DADOS['SINS_ID_INDICADOR'];
                $slaDsvEpaNs->solicitacoesEpa = $solicitacoesEpa;
                $slaDsvEpaNs->secundarias = $secundarias;

                $this->view->totalEpaSolicitacoes = $totalEpaSolicitacoes;
                $this->view->countSolicitacoesNoPrazo = $countSolicitacoesNoPrazo;
                $this->view->countSolicitacoesUtrapassadas = $countSolicitacoesUtrapassadas;
                $this->view->idIndicadorEPA = $EPA_DADOS['SINS_ID_INDICADOR'];
                //solicitações
                $this->view->solicitacoesEpa = $solicitacoesEpa;
                
                
                /**************************************************************************************/
                /**************************************************************************************/


                /**
                 * Calcular a Média de tempo de atraso das ordens de serviços do mês
                 */
                $totalMtaSolicitacoes = $totalEpaSolicitacoes;
                $countSolicitacoesForadoPrazoMta= $countSolicitacoesUtrapassadas;
                $countSolicitacoesNoPrazoMta = $countSolicitacoesNoPrazo;

                if($totalMtaSolicitacoes > 0){
                    $totalMta = ($somaAtrasosMta/$totalMtaSolicitacoes);
                }else{
                    $totalMta = 0;
                }
                $totalMtaAux = (float) sprintf('%.2f',$totalMta);
                $totalMta = (float) sprintf('%.2f',$totalMta);

                if ($totalMta > 25) {
                    $glosaMta = 10;
                }
                if (($totalMta >= 21) && ($totalMta <= 25)) {
                    $glosaMta = 5;
                }
                if (($totalMta >= 16) && ($totalMta < 21)) {
                    $glosaMta = 4;
                }
                if (($totalMta >= 11) && ($totalMta < 16)) {
                    $glosaMta = 3;
                }
                if (($totalMta >= 8) && ($totalMta < 11)) {
                    $glosaMta = 2;
                }
                if (($totalMta >= 1) && ($totalMta < 8)) {
                    $glosaMta = 1;
                }
                if ($totalMta < 1) {
                    $glosaMta = 0;
                }
                /**
                 * Carrega as variáveis para gerar o indicador:
                 * MTA – Média de tempo de atraso das ordens de serviços do mês
                 */
                $slaDsvMtaNs = new Zend_Session_Namespace('slaDsvMtaNs');
                $slaDsvMtaNs->totalMtaSolicitacoes = $totalMtaSolicitacoes;
                $slaDsvMtaNs->countSolicitacoesForadoPrazoMta = $countSolicitacoesForadoPrazoMta;
                $slaDsvMtaNs->noPrazoMtaSolicitacoes = $countSolicitacoesNoPrazoMta;
                $slaDsvMtaNs->mediaAtrasos = $totalMtaAux;
                $slaDsvMtaNs->idIndicadorMTA = $MTA_DADOS['SINS_ID_INDICADOR'];
                $slaDsvMtaNs->solicitacoesMta = $solicitacoesEpa;

                $this->view->totalMtaSolicitacoes = $totalMtaSolicitacoes;
                $this->view->countSolicitacoesForadoPrazoMta = $countSolicitacoesForadoPrazoMta;
                $this->view->noPrazoMtaSolicitacoes = $countSolicitacoesNoPrazoMta;
                $this->view->mediaAtrasos = $totalMtaAux;

                $this->view->idIndicadorMTA = $MTA_DADOS['SINS_ID_INDICADOR'];
                //solicitações
                $this->view->solicitacoesMta = $solicitacoesEpa;
                /**************************************************************************************/
                /**************************************************************************************/

                /**************************************************************************************/
                /**************************************************************************************/
    //            /**
    //             * Calcular O Índice de defeito (qualidade)
    //             * 
    //             */
    //            $somaErros = 0;
    //            $i = 0;
    //            $solicitacoesIdqErros = array();
    //            foreach ($solicitacoesIdq as $idq) {
    //                 if( array_search((string)$idq["DOCM_NR_DOCUMENTO"], $secundarias,true) === false ){
    //                        if ((int) $idq['ERROS_SISTEMA'] > 0) {
    //                            if (is_null($idq['DESCONSIDERADO_IDQ'])) {
    //                                $somaErros += $idq['ERROS_SISTEMA'];
    //                                $solicitacoesIdq[$i]["CONSIDERADO_IDQ"] = "S";
    //                                $solicitacoesIdqErros[$i] = $solicitacoesIdq[$i];
    //                                //Array para o fechamento do sla
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $idq["MOFA_ID_MOVIMENTACAO"];
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'N';
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $idq["DOCM_NR_DOCUMENTO"];
    //                                $solicitacoesIdq[$i]["CONTAB_POSITIVA"] = "N";//Apenas para conferência
    //                            } else {
    //                                $solicitacoesIdq[$i]["CONSIDERADO_IDQ"] = "N";
    //                                $solicitacoesIdqErros[$i] =  $solicitacoesIdq[$i];
    //                                //Array para o fechamento do sla
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $idq["MOFA_ID_MOVIMENTACAO"];
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
    //                                $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $idq["DOCM_NR_DOCUMENTO"];
    //                                $solicitacoesIdq[$i]["CONTAB_POSITIVA"] = "S";//Apenas para conferência
    //                            }
    //                        }else{
    //                            $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $idq["MOFA_ID_MOVIMENTACAO"];
    //                            $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
    //                            $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $idq["DOCM_NR_DOCUMENTO"];
    //                            if (is_null($idq['DESCONSIDERADO_IDQ'])) {
    //                                $solicitacoesIdq[$i]["CONSIDERADO_IDQ"] = "S";
    //                            } else {
    //                                $solicitacoesIdq[$i]["CONSIDERADO_IDQ"] = "N";
    //                            }
    //                        }
    //                 }else{
    //                     $solicitacoesIdq[$i]["CONSIDERADO_IDQ"] = NULL;
    //                     
    //                     $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_ID_MOVIMENTACAO'] = $idq["MOFA_ID_MOVIMENTACAO"];
    //                     $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['FEMV_IC_CONTAB_POSITIVA']= 'S';
    //                     $solicitacoesIdqFechamento[$idq["MOFA_ID_MOVIMENTACAO"]]['DOCM_NR_DOCUMENTO']= $idq["DOCM_NR_DOCUMENTO"];
    //                 }
    //                $i++;
    //            }
    //            
    //            
    //            $totalIdqSolicitacoes = count($solicitacoesIdq);
    //             /**
    //             * Subtrai do total de solicitações as solicitações secundarias(as solicitações que estão em uma mesma célula menos a primeira que é contablizada)
    //             */
    //            $totalIdqSolicitacoes = $totalIdqSolicitacoes - count($secundarias);
    //            
    //            if($totalIdqSolicitacoes > 0){
    //                $totalIdq = ($somaErros/$PontosFuncao);
    //            }else{
    //                $totalIdq = 0;
    //            }
    //            
    //            
    //            /**
    //             * Segundo a interpretação literal do contrato
    //             */
    //            
    //            if ($totalIdq > 0.2) {
    //                $glosaIdq = 5;
    //            }
    //            if (($totalIdq > 0.0) && ($totalIdq <= 0.2)) {
    //                $glosaIdq = 2;
    //            }
    //            if ($totalIdq <= 0.0) {
    //                $glosaIdq = 0;
    //            }
    //            
    //            
    ////            /**
    ////             * Segundo último acordo 
    ////             */
    ////            if ($totalIdq > 0.2) {
    ////                $glosaIdq = 1;
    ////            }else{
    ////                $glosaIdq = 0;
    ////            }
    //            
    //            $totalIdqAux = $totalIdq;
    //            $totalIdq = (float) sprintf('%.6f',$totalIdq);
    //            /**
    //             * Carrega as variáveis para gerar o indicador:
    //             * IDQ – Índice de Soluções das Solicitações no Prazo
    //             */
    //            $slaDsvIdqNs = new Zend_Session_Namespace('slaDsvIdqNs');
    //            $slaDsvIdqNs->totalIdqSolicitacoes = $totalIdqSolicitacoes;
    //            $slaDsvIdqNs->totalErros = $somaErros;
    //            $slaDsvIdqNs->mediaPontosFuncao = $PontosFuncao;
    //            $slaDsvIdqNs->mediaErroPf = (float) sprintf('%.6f',$totalIdqAux);
    //            $slaDsvIdqNs->idIndicadorIDQ = $IDQ_DADOS['SINS_ID_INDICADOR'];
    //            $slaDsvIdqNs->solicitacoesIdq = $solicitacoesIdq;
    //
    //            $this->view->totalIdqSolicitacoes = $totalIdqSolicitacoes;
    //            $this->view->totalErros = $somaErros;
    //            $this->view->mediaPontosFuncao = $PontosFuncao;
    //            $this->view->mediaErroPf = (float) sprintf('%.6f',$totalIdqAux);;
    //            $this->view->idIndicadorIDQ = $IDQ_DADOS['SINS_ID_INDICADOR'];
    //            //solicitações
    //            $this->view->solicitacoesIdq = $solicitacoesIdq;
                /**************************************************************************************/
                /**************************************************************************************/

                /**
                 * Array contendo a meta alcançada para todos os índices
                 */
                $meta[0] = $totalEpa.'%';//Índice de Início de Atendimento no Prazo
                $meta[1] = $totalMta.' dias';//Índice de Índices de Soluções dos Chamados Encerradas no Prazo
    //            $meta[2] = $totalIdq.' er/pf';//Índice de Ausência de Prazo

                /**
                 * Array contendo o valor a ser glosado para todos os índices
                 */
                $glosa[0] = $glosaEpa.'%';
                $glosa[1] = $glosaMta.'%';
    //            $glosa[2] = $glosaIdq.'%';
                /**
                 * Inclui a posição da meta alcançada no array dos indicadores mínimos
                 */
                $indicadoresMinimos = $indicadorNivelServ->getIndicNivelServicoGrupo(2, '');
                $fim =  count($indicadoresMinimos);
                for ($i = 0; $i<$fim; $i++) {
                    if( $indicadoresMinimos[$i]["SINS_CD_INDICADOR"] == "3" ){
                        unset($indicadoresMinimos[$i]);
                    }
                }

                $fim =  count($indicadoresMinimos);
                for ($i = 0; $i<$fim; $i++) {
                        $indicadoresMinimos[$i]['META_ALCANCADA'] = $meta[$i];
                        $indicadoresMinimos[$i]['GLOSA'] = $glosa[$i];
                }
                $this->view->indicadoresMinimos = $indicadoresMinimos;
                $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
                $indMin->data = $indicadoresMinimos;
                $indMin->title = "SLA - DESENVOLVIMENTO E SUSTENTAÇÃO - TRF1";
    //            $indMin->periodo = 'PERÍODO: '.$Sla_Desenvolvimento_ns->data['DATA_INICIAL'].' À '.$Sla_Desenvolvimento_ns->data['DATA_FINAL'];
                $Sla_Desenvolvimento_ns->solicitacoesEpaFechamento = $solicitacoesEpaFechamento;
                $Sla_Desenvolvimento_ns->solicitacoesMtaFechamento = $solicitacoesMtaFechamento;
    //            $Sla_Desenvolvimento_ns->solicitacoesIdqFechamento = $solicitacoesIdqFechamento;


                $fechadas = array();
                $contaFechadas = -1;
                if  (
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) ||
                        ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO)
                        
                    ) {
                    /**
                     * Validação de fechamento - Verifica se uma dada movimentação referente a uma solicitação já não foi fechada 
                     */
                    foreach ($solicitacoesEpaFechamento as $chaveEpa => $EpaFechamento) {
                        $solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_INDICADOR'] = $EPA_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv =  $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_INDICADOR']
                                );
                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesEpaFechamento[$chaveEpa]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }

                    foreach ($solicitacoesMtaFechamento as $chaveMta => $MtaFechamento) {
                        $solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_INDICADOR'] = $MTA_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv = $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_INDICADOR']
                                );
                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $tabelaSadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesMtaFechamento[$chaveMta]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }

                    /*
                    foreach ($solicitacoesIdqFechamento as $chaveIdq => $IdqFechamento) {
                        $solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_INDICADOR'] = $IDQ_DADOS['SINS_ID_INDICADOR'];
                        $rowFemv = $SosTbFemvFechamentoMovimen->fetchRow(
                                " FEMV_ID_MOVIMENTACAO = ".$solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_MOVIMENTACAO']
                                ." AND ".
                                " FEMV_ID_INDICADOR = ".$solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_INDICADOR']
                                );
                        if(!is_null($rowFemv)){
                           $dadosFechamentoFechado = $rowFemv->toArray();
                           $rowFesl = $SosTbFeslFechamentoSla->fetchRow("FESL_ID_DOCUMENTO = ".$dadosFechamentoFechado['FEMV_ID_DOCUMENTO'])->toArray();
                           $docRelatorioGlosas = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO"])->toArray();
                           $docPlanilhaReferencia = $SadTbDocmDocumento->fetchRow("DOCM_ID_DOCUMENTO = ". $rowFesl["FESL_ID_DOCUMENTO_REFERENCIA"])->toArray();
                           $msgValidacao = $solicitacoesIdqFechamento[$chaveIdq]['DOCM_NR_DOCUMENTO'].". Documento Relatório: ".$docRelatorioGlosas["DOCM_NR_DOCUMENTO"]." e Documento Planilha: ".$docPlanilhaReferencia["DOCM_NR_DOCUMENTO"];
                           $fechadas[$contaFechadas++] = $msgValidacao;
                        }
                    }
                     */
                }
                
//                if ($filtro == "S")
//                {
                    if(count($fechadas)>0)
                    {
                    $fechadas = array_unique($fechadas);
                    $fechadasStr = implode('.<br>',$fechadas);
                    $msg_to_user = "A(s) solicitação(ões) nº: <br> ".$fechadasStr.". <br> Já foi(ram) fechada(s). Retire-a(s) da planilha para que seja possível gerar o Relatório.";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                    $Sla_Desenvolvimento_ns->unsetAll();
                    $this->_helper->_redirector('indicadoresnivelservico', 'sladesenvolvimento', 'sosti');
                    }
//                }
                }
            
        }else{
        	/*
            $this->_helper->flashMessenger(array('message' => $msgUsuario, 'status' => 'notice'));
            $this->_helper->_redirector('index', 'index', 'admin');
            */
        	$this->_helper->flashMessenger ( array ('message' => $permiteSla ['mensagem'], 'status' => 'notice' ) );
            $this->_helper->_redirector ( 'index', 'index', 'admin' );
        }
    }
    
    public function fechamentoAction() {
        $Sla_Desenvolvimento_ns = new Zend_Session_Namespace('Sla_Desenvolvimento_ns');
        $userNs = new Zend_Session_Namespace('userNs');
        $form = new Sosti_Form_FechamentoDsv();
        $this->view->form = $form;
        $this->view->op = Zend_Filter::filterStatic($this->_getParam('op', 0), 'int');
        /**
         * Gera o cache
         */      
        $frontendOptions = array(
            'lifetime' => 1800, // cache lifetime of 30 minutes
            'automatic_serialization' => true
        );
        $cache_dir = APPLICATION_PATH . '/../temp';
        $backendOptions = array(
            'cache_dir' => $cache_dir 
        );
        // getting a Zend_Cache_Core object
        $cache = Zend_Cache::factory('Core',
                                    'File',
                                    $frontendOptions,
                                    $backendOptions);
        $idCache = $userNs->matricula.'SLADESENTEMPXLS';
        if ($Sla_Desenvolvimento_ns->data[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA] == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
            $msg_to_user = "Fechamento por período ainda não implementado.";
            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
            $this->_helper->_redirector('fechamento', 'sladesenvolvimento', 'sosti');
        }
        
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->populate($data);
            /**
             * Altentificação por senha
             */
            $authAdapter = new App_Auth_Adapter_Db();
            $authAdapter->setIdentity($userNs->matricula);
            $authAdapter->setCredential($form->getValue('COU_COD_PASSWORD'));
            $matricula = strtoupper(substr($userNs->matricula, 0, 2));
            switch ($matricula) {
                case 'JU':
                case 'DS':
                    $banco = 'TRF1';
                    break;
                case 'TR':
                    $banco = $matricula . 'F1';
                    break;
                default :
                    $banco = 'JF' . $matricula;
                    break;
            }
            $authAdapter->setDbName('TR');
            $result = $authAdapter->verify($authAdapter);
            if ($result == false) {
                $msg_to_user = "Senha incorreta.";
                $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'notice'));
                $this->_helper->_redirector('fechamento', 'sladesenvolvimento', 'sosti');
            }
            if ($form->isValid($data)) {
                /** Aplica Filtros - Mantem Post */
                $data = array_merge($this->getRequest()->getPost(), $form->populate($this->getRequest()->getPost())->getValues()); /* Aplica Filtros - Mantem Post */
                /** Aplica Filtros - Mantem Post */
                try {
                    if (is_null($Sla_Desenvolvimento_ns->arquivoPlanilhaPath)) {
                        $msg_to_user = "É Necessário gerar o Relatório.";
                        throw new Exception($msg_to_user, 1001);
                    }
                    ////Criação do arquivo .pdf do execel
                    /**
                     * Carregando o arquivo na pasta temp para criação do objeto PhpExecel
                     */
                    fwrite(fopen($Sla_Desenvolvimento_ns->arquivoPlanilhaPath, 'w'), $cache->load($idCache));
                    $objPHPExcel = PHPExcel_IOFactory::load($Sla_Desenvolvimento_ns->arquivoPlanilhaPath);
                    unlink($Sla_Desenvolvimento_ns->arquivoPlanilhaPath);
                    /**
                     * Convertendo o arquivo .xls ou xlsx para pdf utilizando o MPDF
                     */
                    $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                    $rendererLibrary = 'MPDF53';
                    $rendererLibraryPath = realpath(APPLICATION_PATH . '/../library' . DIRECTORY_SEPARATOR . $rendererLibrary);
                    $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                    PHPExcel_Settings::setPdfRenderer(
                            $rendererName, $rendererLibraryPath
                    );
                    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
                    $objWriter->setSheetIndex(0);
                    fwrite(fopen($Sla_Desenvolvimento_ns->arquivoPlanilhaPath, 'w'), $cache->load($idCache));
                    $arquivoPDFPathPlanToPDF = $Sla_Desenvolvimento_ns->arquivoPlanilhaPath;
                    $arquivoPDFPathPlanToPDF = str_replace('.xlsx', '.pdf', $arquivoPDFPathPlanToPDF);
                    $arquivoPDFPathPlanToPDF = str_replace('.xls', '.pdf', $arquivoPDFPathPlanToPDF);
                    $objWriter->save($arquivoPDFPathPlanToPDF);
                    unlink($Sla_Desenvolvimento_ns->arquivoPlanilhaPath);


                    ////Criação do arquivo .pdf da planilha de Glosas
                    $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
                     $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
                     $userNamespace = new Zend_Session_Namespace('userNs');
                     $this->view->slaIndices = $indMin->data;
                     $this->view->titulo = $indMin->title;
                     $this->view->periodo = $indMin->periodo;
                     $this->view->horaAtual = $dados->dataHoraAtual();
                     $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
                     /**
                      * Carrega as variáveis para gerar o indicador:
                      * EPA – Volume de ordens de serviço executadas nos prazos acordados
                      */
                     $slaDsvEpaNs = new Zend_Session_Namespace('slaDsvEpaNs');
                     $this->view->totalEpaSolicitacoes = $slaDsvEpaNs->totalEpaSolicitacoes;
                     $this->view->countSolicitacoesNoPrazo = $slaDsvEpaNs->countSolicitacoesNoPrazo;
                     $this->view->countSolicitacoesUtrapassadas = $slaDsvEpaNs->countSolicitacoesUtrapassadas;
                     $this->view->idIndicadorEPA = $slaDsvEpaNs->idIndicadorEPA;
                     $this->view->solicitacoesEpa = $slaDsvEpaNs->solicitacoesEpa;
                     $this->view->secundarias = $slaDsvEpaNs->secundarias;
                     if (count($slaDsvEpaNs->solicitacoesEpa) > 0) {
                         $iEpa = 0;
                         foreach ($slaDsvEpaNs->solicitacoesEpa as $epa) {
                             $iEpa++;
                             $documentoEpa[$iEpa] = $epa['SSOL_ID_DOCUMENTO'];
                         }
                         $this->view->documentoEpa = array_unique($documentoEpa);
                     }
                     /**
                      * Carrega as variáveis para gerar o indicador:
                      * MTA – Média de tempo de atraso das ordens de serviços do mês
                      */
                     $slaDsvMtaNs = new Zend_Session_Namespace('slaDsvMtaNs');
                     $this->view->totalMtaSolicitacoes = $slaDsvMtaNs->totalMtaSolicitacoes;
                     $this->view->countSolicitacoesForadoPrazoMta = $slaDsvMtaNs->countSolicitacoesForadoPrazoMta;
                     $this->view->noPrazoMtaSolicitacoes = $slaDsvMtaNs->noPrazoMtaSolicitacoes;
                     $this->view->mediaAtrasos = $slaDsvMtaNs->mediaAtrasos;
                     $this->view->idIndicadorMTA = $slaDsvMtaNs->idIndicadorMTA;
                     $this->view->solicitacoesMta = $slaDsvMtaNs->solicitacoesMta;
                     if (count($slaDsvMtaNs->solicitacoesMta) > 0) {
                         $iMta = 0;
                         foreach ($slaDsvMtaNs->solicitacoesMta as $mta) {
                             $iMta++;
                             $documentoMta[$iMta] = $mta['SSOL_ID_DOCUMENTO'];
                         }
                         $this->view->documentoMta = array_unique($documentoMta);
                     }
                     /**
                      * Carrega as variáveis para gerar o indicador:
                      * IDQ – Índice de Soluções das Solicitações no Prazo
                      */
                     /*
                     $slaDsvIdqNs = new Zend_Session_Namespace('slaDsvIdqNs');
                     $this->view->totalIdqSolicitacoes = $slaDsvIdqNs->totalIdqSolicitacoes;
                     $this->view->totalErros = $slaDsvIdqNs->totalErros;
                     $this->view->mediaPontosFuncao = $slaDsvIdqNs->mediaPontosFuncao;
                     $this->view->mediaErroPf = $slaDsvIdqNs->mediaErroPf;
                     $this->view->idIndicadorIDQ = $slaDsvIdqNs->idIndicadorIDQ;
                     $this->view->solicitacoesIdq = $slaDsvIdqNs->solicitacoesIdq;
                     if (count($slaDsvIdqNs->solicitacoesIdq) > 0) {
                         $iIdq = 0;
                         foreach ($slaDsvIdqNs->solicitacoesIdq as $idq) {
                             $iIdq++;
                             $documentoIdq[$iIdq] = $idq['SSOL_ID_DOCUMENTO'];
                         }
                         $this->view->documentoIdq = array_unique($documentoIdq);
                     }
                     */
                     
                     $this->view->param = 'pdf';
                     $this->view->descricao = $data["DOCM_DS_ASSUNTO_DOC_RELATORIO"];
                     
                    
                    $this->render('indicadoresnivelservicoexportacao');
                    $response = $this->getResponse();
                    $body = $response->getBody();
                    $response->clearBody();
//            $this->_helper->layout->disableLayout();
                    define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
                    define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
//                    include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));
                    $mpdf = new mPDF('', // mode - default ''
                                    '', // format - A4, for example, default ''
                                    8, // font size - default 0
                                    '', // default font family
                                    10, // margin_left
                                    10, // margin right
                                    10, // margin top
                                    10, // margin bottom
                                    9, // margin header
                                    9, // margin footer
                                    'L');

                    $mpdf->AddPage('P', '', '0', '1');
                    $mpdf->WriteHTML($body);
                    /* destino de onde sera gravado o documento */
                    $dest = realpath(APPLICATION_PATH . '/../temp/');
                    $name = 'SISAD_TEMP_DOC_SLA_DSV' . date("dmYHisu") . '.pdf';
                    $name = $dest . DIRECTORY_SEPARATOR . $name;
                    $arquivoPDFPathTabelaGlosas = $name;
                    $mpdf->Output($name, 'F');


                    $parametros = new Services_Red_Parametros_Incluir();
                    if (defined('APPLICATION_ENV')) {
                        if (APPLICATION_ENV == 'development') {
                            $parametros->login = 'TR227PS';
                        } else if (APPLICATION_ENV == 'production') {
                            $parametros->login = $userNs->matricula;
                        }
                    }
                    $parametros->ip = substr($_SERVER['REMOTE_ADDR'], 0, 15);
                    $parametros->sistema = Services_Red::NOME_SISTEMA_EADMIN;
                    $parametros->nomeMaquina = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);

                    /**
                     * Inclusão da planilha
                     */
                    $mapperDocumento = new Sisad_Model_DataMapper_Documento();
                    $tabelaSadTbDocmDocumento = new Sisad_Model_DbTable_SadTbDocmDocumento();
                    $SosTbFeslFechamentoSla = new Application_Model_DbTable_SosTbFeslFechamentoSla();
                    $SosTbFemvFechamentoMovimen = new Application_Model_DbTable_SosTbFemvFechamentoMovimen();
                    $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModoMoviDocumento;
                    $Dual = new Application_Model_DbTable_Dual();
                    $datahora = $Dual->sysdate();

                    /**
                     * Metadados DOCM
                     */
                    $unidade = Zend_Json::decode($data["UNIDADE"]);
                    $dataPlantoPDFdoc["DOCM_SG_SECAO_GERADORA"] = $unidade['LOTA_SIGLA_SECAO'];
                    $dataPlantoPDFdoc["DOCM_CD_LOTACAO_GERADORA"] = $unidade['LOTA_COD_LOTACAO'];
                    $dataPlantoPDFdoc["DOCM_SG_SECAO_REDATORA"] = $unidade['LOTA_SIGLA_SECAO'];
                    $dataPlantoPDFdoc["DOCM_CD_LOTACAO_REDATORA"] = $unidade['LOTA_COD_LOTACAO'];
                    $dataPlantoPDFdoc["DOCM_ID_TIPO_DOC"] = 99; //Planilha
                    $dataPlantoPDFdoc["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($dataPlantoPDFdoc['DOCM_SG_SECAO_REDATORA'], $dataPlantoPDFdoc['DOCM_CD_LOTACAO_REDATORA'], $dataPlantoPDFdoc['DOCM_ID_TIPO_DOC']);
                    $dataPlantoPDFdoc["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($dataPlantoPDFdoc['DOCM_SG_SECAO_REDATORA'], $dataPlantoPDFdoc['DOCM_CD_LOTACAO_REDATORA'], $dataPlantoPDFdoc['DOCM_CD_LOTACAO_GERADORA'], $dataPlantoPDFdoc['DOCM_ID_TIPO_DOC'], $dataPlantoPDFdoc["DOCM_NR_SEQUENCIAL_DOC"]);
                    $dataPlantoPDFdoc["DOCM_DH_CADASTRO"] = $datahora;
                    $dataPlantoPDFdoc["DOCM_CD_MATRICULA_CADASTRO"] = $userNamespace->matricula;
                    $dataPlantoPDFdoc["DOCM_ID_PCTT"] = 2383; //AVALIAÇÃO DE SERVIÇOS PRESTADOS
                    $dataPlantoPDFdoc["DOCM_DS_ASSUNTO_DOC"] = $data["DOCM_DS_ASSUNTO_DOC_PLANILHA"];
                    $dataPlantoPDFdoc["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Digital
                    $dataPlantoPDFdoc["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Público
                    $dataPlantoPDFdoc["DOCM_NR_DOCUMENTO_RED"] = $dataPlantoPDFdoc["DOCM_NR_DOCUMENTO_RED"];
                    $dataPlantoPDFdoc["DOCM_DS_PALAVRA_CHAVE"] = "Planilha base Cálculo indicador SLA";

                    $metadados = new Services_Red_Metadados_Incluir();
                    $metadados->descricaoTituloDocumento = $Sla_Desenvolvimento_ns->arquivoPlanilhaNome;
                    $metadados->numeroTipoSigilo = $dataPlantoPDFdoc["DOCM_ID_CONFIDENCIALIDADE"]/* Services_Red::NUMERO_SIGILO_PUBLICO; */;
                    $metadados->numeroTipoDocumento = $dataPlantoPDFdoc["DOCM_ID_TIPO_DOC"]; //Planilha;
                    $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                    $metadados->ipMaquinaResponsavelIntervencao = substr($_SERVER['REMOTE_ADDR'], 0, 15);
                    $metadados->secaoOrigemDocumento = "0100";
                    $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                    $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                    $metadados->nomeMaquinaResponsavelIntervensao = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                    $metadados->numeroDocumento = "";
                    $metadados->pastaProcessoNumero = Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
                    $metadados->secaoDestinoIdSecao = "0100";

                    $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
                    $red->debug = false;
                    $red->temp = APPLICATION_PATH . '/../temp';
                    $file = $arquivoPDFPathPlanToPDF; /* caminho completo do arquivo gravado no servidor */
                    $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
//                    Zend_Debug::dump($retornoIncluir_red); exit;
                    if (!is_array($retornoIncluir_red)) {
                        throw new Exception($retornoIncluir_red);
                    }
                    $dataPlantoPDFdoc["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];


                    /*                     * *
                     *  Inclusão do PDF da tabela de glosas no red
                     */
                    $unidade = Zend_Json::decode($data["UNIDADE"]);
                    $dataTabelaGlosasPDFdoc["DOCM_SG_SECAO_GERADORA"] = $unidade['LOTA_SIGLA_SECAO'];
// Zend_Debug::dump(idDocTabelaGlosas);exit;
                    $dataTabelaGlosasPDFdoc["DOCM_CD_LOTACAO_GERADORA"] = $unidade['LOTA_COD_LOTACAO'];
                    $dataTabelaGlosasPDFdoc["DOCM_SG_SECAO_REDATORA"] = $unidade['LOTA_SIGLA_SECAO'];
                    $dataTabelaGlosasPDFdoc["DOCM_CD_LOTACAO_REDATORA"] = $unidade['LOTA_COD_LOTACAO'];
                    $dataTabelaGlosasPDFdoc["DOCM_ID_TIPO_DOC"] = 122; //Relatório
                    $dataTabelaGlosasPDFdoc["DOCM_NR_SEQUENCIAL_DOC"] = $mapperDocumento->getNumeroSequencialDCMTO($dataTabelaGlosasPDFdoc['DOCM_SG_SECAO_REDATORA'], $dataTabelaGlosasPDFdoc['DOCM_CD_LOTACAO_REDATORA'], $dataTabelaGlosasPDFdoc['DOCM_ID_TIPO_DOC']);
                    $dataTabelaGlosasPDFdoc["DOCM_NR_DOCUMENTO"] = $mapperDocumento->getNumeroDCMTO($dataTabelaGlosasPDFdoc['DOCM_SG_SECAO_REDATORA'], $dataTabelaGlosasPDFdoc['DOCM_CD_LOTACAO_REDATORA'], $dataTabelaGlosasPDFdoc['DOCM_CD_LOTACAO_GERADORA'], $dataTabelaGlosasPDFdoc['DOCM_ID_TIPO_DOC'], $dataTabelaGlosasPDFdoc["DOCM_NR_SEQUENCIAL_DOC"]);
                    $dataTabelaGlosasPDFdoc["DOCM_DH_CADASTRO"] = $datahora;
                    $dataTabelaGlosasPDFdoc["DOCM_CD_MATRICULA_CADASTRO"] = $userNamespace->matricula;
                    $dataTabelaGlosasPDFdoc["DOCM_ID_PCTT"] = 2383; //AVALIAÇÃO DE SERVIÇOS PRESTADOS
                    $dataTabelaGlosasPDFdoc["DOCM_DS_ASSUNTO_DOC"] = $data["DOCM_DS_ASSUNTO_DOC_RELATORIO"];
                    $dataTabelaGlosasPDFdoc["DOCM_ID_TIPO_SITUACAO_DOC"] = 1; //Digital
                    $dataTabelaGlosasPDFdoc["DOCM_ID_CONFIDENCIALIDADE"] = 0; //Público
                    $dataTabelaGlosasPDFdoc["DOCM_NR_DOCUMENTO_RED"] = $dataTabelaGlosasPDFdoc["DOCM_NR_DOCUMENTO_RED"];
                    $dataTabelaGlosasPDFdoc["DOCM_DS_PALAVRA_CHAVE"] = "Planilha base Cálculo indicador SLA";
                    


                    $metadados = new Services_Red_Metadados_Incluir();
                    $metadados->descricaoTituloDocumento = 'Tabela de Glosas geradas a partir da planilha: ' . $Sla_Desenvolvimento_ns->arquivoPlanilhaNome;
                    $metadados->numeroTipoSigilo = $dataTabelaGlosasPDFdoc["DOCM_ID_CONFIDENCIALIDADE"]/* Services_Red::NUMERO_SIGILO_PUBLICO; */;
                    $metadados->numeroTipoDocumento = $dataTabelaGlosasPDFdoc["DOCM_ID_TIPO_DOC"]; //Planilha;
                    $metadados->nomeSistemaIntrodutor = Services_Red::NOME_SISTEMA_EADMIN;
                    $metadados->ipMaquinaResponsavelIntervencao = substr($_SERVER['REMOTE_ADDR'], 0, 15);
                    $metadados->secaoOrigemDocumento = "0100";
                    $metadados->prioridadeReplicacao = Services_Red::PRIORIDADE_REPLICACAO_NORMAL;
                    $metadados->espacoDocumento = Services_Red::ESPACO_DOCUMENTO_PADRAO;
                    $metadados->nomeMaquinaResponsavelIntervensao = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
                    $metadados->indicadorAnotacao = Services_Red::INDICADOR_ANOTACAO_DOCUMENTO_NAO_MINUTA;
                    $metadados->numeroDocumento = "";
                    $metadados->pastaProcessoNumero = Services_Red::PASTA_PROCESSO_NUMERO_EADMIN;
                    $metadados->secaoDestinoIdSecao = "0100";

                    $red = new Services_Red_Incluir(false); /* PRODUÇÃO */
                    $red->debug = false;
                    $red->temp = APPLICATION_PATH . '/../temp';

                    $file = $arquivoPDFPathTabelaGlosas; /* caminho completo do arquivo gravado no servidor */

                    $retornoIncluir_red = $red->incluir($parametros, $metadados, $file);
                    if (!is_array($retornoIncluir_red)) {
                        throw new Exception($retornoIncluir_red);
                    }
                    $dataTabelaGlosasPDFdoc["DOCM_NR_DOCUMENTO_RED"] = $retornoIncluir_red['numeroDocumento'];

                    $solicitacoesEpaFechamento = $Sla_Desenvolvimento_ns->solicitacoesEpaFechamento;
                    $solicitacoesMtaFechamento = $Sla_Desenvolvimento_ns->solicitacoesMtaFechamento;
//                    $solicitacoesIdqFechamento = $Sla_Desenvolvimento_ns->solicitacoesIdqFechamento;

                    $EPA_DADOS = $Sla_Desenvolvimento_ns->EPA_DADOS;
                    $MTA_DADOS = $Sla_Desenvolvimento_ns->MTA_DADOS;
//                    $IDQ_DADOS = $Sla_Desenvolvimento_ns->IDQ_DADOS;

                    $PontosFuncao = $Sla_Desenvolvimento_ns->PontosFuncao;

                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $db->beginTransaction();
                    try {

                        $rowSadTbDocmPlanilha = $tabelaSadTbDocmDocumento->createRow($dataPlantoPDFdoc);
                        //Zend_Debug::dump($rowSadTbDocmPlanilha->toArray());
                        $idDocPlanilha = $rowSadTbDocmPlanilha->save();
                        $rowSadTbDocmPlanilha = $tabelaSadTbDocmDocumento->createRow($dataTabelaGlosasPDFdoc);
                        //Zend_Debug::dump($rowSadTbDocmPlanilha->toArray());
                        $idDocTabelaGlosas = $rowSadTbDocmPlanilha->save();

                        $dadosFechamento = array();
                        $dadosFechamento["FESL_ID_DOCUMENTO"] = $idDocTabelaGlosas;
                        $dadosFechamento["FESL_ID_DOCUMENTO_REFERENCIA"] = $idDocPlanilha;
                        $dadosFechamento["FESL_CD_MATRICULA_ASSINATURA"] = $userNs->matricula;
                        $dadosFechamento["FESL_DH_INICIAL_AFERICAO"] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dadosFechamento["FESL_DH_FINAL_AFERICAO"] = new Zend_Db_Expr("TO_DATE(SYSDATE,'DD/MM/YYYY HH24:MI:SS')");
                        $dadosFechamento["FESL_NR_PONTO_FUNCAO"] = new Zend_Db_Expr("TO_NUMBER($PontosFuncao)");


                        $rowSosTbFesl = $SosTbFeslFechamentoSla->createRow($dadosFechamento);
                        //Zend_Debug::dump($rowSosTbFesl->toArray());
                        $rowSosTbFesl->save();


                        foreach ($solicitacoesEpaFechamento as $chaveEpa => $EpaFechamento) {
                            $solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_DOCUMENTO'] = $idDocTabelaGlosas;
                            $solicitacoesEpaFechamento[$chaveEpa]['FEMV_ID_INDICADOR'] = $EPA_DADOS['SINS_ID_INDICADOR'];
                            $rowSosTbFemv = $SosTbFemvFechamentoMovimen->createRow($solicitacoesEpaFechamento[$chaveEpa]);
                            //Zend_Debug::dump($rowSosTbFemv->toArray());
                            $rowSosTbFemv->save();
                        }

                        foreach ($solicitacoesMtaFechamento as $chaveMta => $MtaFechamento) {
                            $solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_DOCUMENTO'] = $idDocTabelaGlosas;
                            $solicitacoesMtaFechamento[$chaveMta]['FEMV_ID_INDICADOR'] = $MTA_DADOS['SINS_ID_INDICADOR'];
                            $rowSosTbFemv = $SosTbFemvFechamentoMovimen->createRow($solicitacoesMtaFechamento[$chaveMta]);
                            //Zend_Debug::dump($rowSosTbFemv->toArray());
                            $rowSosTbFemv->save();
                        }

//                        foreach ($solicitacoesIdqFechamento as $chaveIdq => $IdqFechamento) {
//                            $solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_DOCUMENTO'] = $idDocTabelaGlosas;
//                            $solicitacoesIdqFechamento[$chaveIdq]['FEMV_ID_INDICADOR'] = $IDQ_DADOS['SINS_ID_INDICADOR'];
//                            $rowSosTbFemv = $SosTbFemvFechamentoMovimen->createRow($solicitacoesIdqFechamento[$chaveIdq]);
//                            //Zend_Debug::dump($rowSosTbFemv->toArray());
//                            $rowSosTbFemv->save();
//                        }

                        /**
                         * Movimentação para unidade escolhida 
                         */
                        $idDocmDocumento = $idDocPlanilha;
                        /* dados da origem do documento */

                        $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                        $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $unidade['LOTA_SIGLA_SECAO'];
                        $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $unidade['LOTA_COD_LOTACAO'];

                        /* dados do destino do documento */
                        $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $unidade['LOTA_SIGLA_SECAO'];
                        $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $unidade['LOTA_COD_LOTACAO'];
                        $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';

                        $dataMofaMoviFase["MOFA_ID_FASE"] = 1010; /* ENCAMINHAMENTO SISAD */
                        $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                        $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Encaminhamento à unidade.";
                        $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, array(), null, false);
                        $idDocmDocumento = $idDocTabelaGlosas;
                        $SadTbModeMoviDestinatario->encaminhaDocumento($idDocmDocumento, $dataMoviMovimentacao, $dataModeMoviDestinatario, $dataMofaMoviFase, array(), null, false);
                        $db->commit();
                     } catch (Exception $exc) {
                        $db->rollBack();
                     }
                    $cache->remove($idCache);
                    $Sla_Desenvolvimento_ns->unsetAll();
                    $msg_to_user = "Fechamento realizado.";
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                    $this->_helper->_redirector('fechamento', 'sladesenvolvimento', 'sosti',array('op'=>'1'));
                 } catch (Exception $exc) {
                    $erro = $exc->getMessage();
                    $tErr = explode("-", $erro);
                    $codigoErro = substr($tErr[1], 0, 5);
                    switch ($codigoErro) {
                        case '22297':
                            $cache->remove($idCache);
                            $Sla_Desenvolvimento_ns->unsetAll();
                            $msg_to_user = "Fechamento realizado.";
                            $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => 'success'));
                            $this->_helper->_redirector('fechamento', 'sladesenvolvimento', 'sosti',array('op'=>'1'));
                            break;
                        case '1001':
                            $msg_to_user = "<br><br><strong>Atenção!</strong><br/> $erro ";
                            $typeMensage = 'notice';
                            break;
                        case '00001':
                            $msg_to_user = "<br><br><strong>Atenção!</strong><br/> A planilha já foi fechada. ";
                            $typeMensage = 'notice';
                            break;
                        default:
                            $msg_to_user = "Ocorreu um erro no fechamento. <br/> $erro ";
                            $typeMensage = 'error';
                            break;
                    }
                    $this->_helper->flashMessenger(array('message' => $msg_to_user, 'status' => $typeMensage));
                    $this->_helper->_redirector('fechamento', 'sladesenvolvimento', 'sosti');
                 }
                $this->render('fechamento');
            } else {
                $form->populate($data);
                $this->view->form = $form;
            }
        }
    }
    
    public function indicadoresnivelservicoexportacaoAction() 
    {
        ini_set("memory_limit","1024M");
        set_time_limit( 1200 );
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
        /**
         * Carrega as variáveis para gerar o indicador:
         * EPA – Volume de ordens de serviço executadas nos prazos acordados
         */
        $slaDsvEpaNs = new Zend_Session_Namespace('slaDsvEpaNs');
        $this->view->totalEpaSolicitacoes = $slaDsvEpaNs->totalEpaSolicitacoes;
        $this->view->countSolicitacoesNoPrazo = $slaDsvEpaNs->countSolicitacoesNoPrazo;
        $this->view->countSolicitacoesUtrapassadas = $slaDsvEpaNs->countSolicitacoesUtrapassadas;
        $this->view->idIndicadorEPA = $slaDsvEpaNs->idIndicadorEPA;
        $this->view->solicitacoesEpa = $slaDsvEpaNs->solicitacoesEpa;
        $this->view->secundarias = $slaDsvEpaNs->secundarias;
        if (count($slaDsvEpaNs->solicitacoesEpa) > 0) {
            $iEpa = 0;
            foreach ($slaDsvEpaNs->solicitacoesEpa as $epa) {
                $iEpa++;
                $documentoEpa[$iEpa] = $epa['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoEpa = array_unique($documentoEpa);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * MTA – Média de tempo de atraso das ordens de serviços do mês
         */
        $slaDsvMtaNs = new Zend_Session_Namespace('slaDsvMtaNs');
        $this->view->totalMtaSolicitacoes = $slaDsvMtaNs->totalMtaSolicitacoes;
        $this->view->countSolicitacoesForadoPrazoMta = $slaDsvMtaNs->countSolicitacoesForadoPrazoMta;
        $this->view->noPrazoMtaSolicitacoes = $slaDsvMtaNs->noPrazoMtaSolicitacoes;
        $this->view->mediaAtrasos = $slaDsvMtaNs->mediaAtrasos;
        $this->view->idIndicadorMTA = $slaDsvMtaNs->idIndicadorMTA;
        $this->view->solicitacoesMta = $slaDsvMtaNs->solicitacoesMta;
        if (count($slaDsvMtaNs->solicitacoesMta) > 0) {
            $iMta = 0;
            foreach ($slaDsvMtaNs->solicitacoesMta as $mta) {
                $iMta++;
                $documentoMta[$iMta] = $mta['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoMta = array_unique($documentoMta);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * IDQ – Índice de Soluções das Solicitações no Prazo
         */
        $apfDesenvolvedora = new Application_Model_DbTable_Sosti_SosTbPfdsApfDesenvolvedora();
        $tarefaSolicit = new Tarefa_Model_DataMapper_TarefaSolicitacao();
        $solicitacoesEpa = $slaDsvEpaNs->solicitacoesEpa;
        foreach ($solicitacoesEpa as $i=>$idq) {
            $solicitacoesEpa[$i]['QTDE_DEFEITOS'] = $tarefaSolicit->getDefeitosSolicitacoesSla($idq["SSOL_ID_DOCUMENTO"]);
            $arraySomaDefeitos[] = $solicitacoesEpa[$i]['QTDE_DEFEITOS'];
            try {
                $pfSolicitacao = $apfDesenvolvedora->fetchAll("PFDS_ID_SOLICITACAO = ".$idq["SSOL_ID_DOCUMENTO"]);
                $arrayPfBruto[] = $pfSolicitacao[0]['PFDS_QT_PF_BRUTO'];
            } catch (Exception $ex) {
                $arrayPfBruto[] = 0;
            }
        }
       
        $slaDsvIdqNs = new Zend_Session_Namespace('slaDsvIdqNs');
        $this->view->totalIdqSolicitacoes = $slaDsvIdqNs->totalIdqSolicitacoes;
        $this->view->totalDefeitos = $slaDsvIdqNs->totalDefeitos;
        $this->view->totalPfBruto = $slaDsvIdqNs->totalPfBruto;
        $this->view->totalIdq = $slaDsvIdqNs->totalIdq;
        $this->view->glosaIdq = $slaDsvIdqNs->glosaIdq; 
        $this->view->solicitacoesIdq = $solicitacoesEpa;

        $this->_helper->layout->disableLayout();

        $param = $this->_getParam ('param'); 
        $this->view->param = $param;
        $periodo = $indMin->periodo;
        $p = explode(' ', $periodo);
        $nomeArq = str_replace('/','-',$p[1].'_'.$p[4]);
        /**
         * Gera o Excel
         */
        if ($param == 'xls') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=TRF1 - Análise de SLA.xls");
        } else {     
           /**
             * Gera o PDF
             */
            $this->render();
            $response = $this->getResponse();
            $body = $response->getBody();
            $response->clearBody();

            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
            $mpdf=new mPDF('',    // mode - default ''
                           '',    // format - A4, for example, default ''
                            8,    // font size - default 0
                           '',    // default font family
                           10,    // margin_left
                           10,    // margin right
                           10,    // margin top
                           10,    // margin bottom
                            9,    // margin header
                            9,    // margin footer
                           'L');

            $mpdf->AddPage('P', '', '0', '1');
            $mpdf->WriteHTML($body);
            $name =  'TRF1 - Análise de SLA.pdf';
            $mpdf->Output($name,'D');
        }
    }   
    
    public function relatoriofaturamentoexportacaoAction() 
    {
        ini_set("memory_limit","1024M");
        set_time_limit( 1200 );
        $dados = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $indMin = new Zend_Session_Namespace('Sla_Indices_ns');
        $userNamespace = new Zend_Session_Namespace('userNs');
        $this->view->slaIndices = $indMin->data;
        $this->view->titulo = $indMin->title;
        $this->view->periodo = $indMin->periodo;
        $this->view->horaAtual = $dados->dataHoraAtual();
        $this->view->emissor = strtoupper($userNamespace->matricula).' - '.$userNamespace->nome;
        /**
         * Carrega as variáveis para gerar o indicador:
         * EPA – Volume de ordens de serviço executadas nos prazos acordados
         */
        $slaDsvEpaNs = new Zend_Session_Namespace('slaDsvEpaNs');
        $this->view->totalEpaSolicitacoes = $slaDsvEpaNs->totalEpaSolicitacoes;
        $this->view->countSolicitacoesNoPrazo = $slaDsvEpaNs->countSolicitacoesNoPrazo;
        $this->view->countSolicitacoesUtrapassadas = $slaDsvEpaNs->countSolicitacoesUtrapassadas;
        $this->view->idIndicadorEPA = $slaDsvEpaNs->idIndicadorEPA;
        $this->view->solicitacoesEpa = $slaDsvEpaNs->solicitacoesEpa;
        $this->view->secundarias = $slaDsvEpaNs->secundarias;
        if (count($slaDsvEpaNs->solicitacoesEpa) > 0) {
            $iEpa = 0;
            foreach ($slaDsvEpaNs->solicitacoesEpa as $epa) {
                $iEpa++;
                $documentoEpa[$iEpa] = $epa['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoEpa = array_unique($documentoEpa);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * MTA – Média de tempo de atraso das ordens de serviços do mês
         */
        $slaDsvMtaNs = new Zend_Session_Namespace('slaDsvMtaNs');
        $this->view->totalMtaSolicitacoes = $slaDsvMtaNs->totalMtaSolicitacoes;
        $this->view->countSolicitacoesForadoPrazoMta = $slaDsvMtaNs->countSolicitacoesForadoPrazoMta;
        $this->view->noPrazoMtaSolicitacoes = $slaDsvMtaNs->noPrazoMtaSolicitacoes;
        $this->view->mediaAtrasos = $slaDsvMtaNs->mediaAtrasos;
        $this->view->idIndicadorMTA = $slaDsvMtaNs->idIndicadorMTA;
        $this->view->solicitacoesMta = $slaDsvMtaNs->solicitacoesMta;
        if (count($slaDsvMtaNs->solicitacoesMta) > 0) {
            $iMta = 0;
            foreach ($slaDsvMtaNs->solicitacoesMta as $mta) {
                $iMta++;
                $documentoMta[$iMta] = $mta['SSOL_ID_DOCUMENTO'];
            }
            $this->view->documentoMta = array_unique($documentoMta);
        }
        /**
         * Carrega as variáveis para gerar o indicador:
         * IDQ – Índice de Soluções das Solicitações no Prazo
         */
//        $slaDsvIdqNs = new Zend_Session_Namespace('slaDsvIdqNs');
//        $this->view->totalIdqSolicitacoes = $slaDsvIdqNs->totalIdqSolicitacoes;
//        $this->view->totalErros = $slaDsvIdqNs->totalErros;
//        $this->view->mediaPontosFuncao = $slaDsvIdqNs->mediaPontosFuncao;
//        $this->view->mediaErroPf = $slaDsvIdqNs->mediaErroPf;
//        $this->view->idIndicadorIDQ = $slaDsvIdqNs->idIndicadorIDQ;
//        $this->view->solicitacoesIdq = $slaDsvIdqNs->solicitacoesIdq;
//        if (count($slaDsvIdqNs->solicitacoesIdq) > 0) {
//            $iIdq = 0;
//            foreach ($slaDsvIdqNs->solicitacoesIdq as $idq) {
//                $iIdq++;
//                $documentoIdq[$iIdq] = $idq['SSOL_ID_DOCUMENTO'];
//            }
//            $this->view->documentoIdq = array_unique($documentoIdq);
//        }

        $this->_helper->layout->disableLayout();

        $param = $this->_getParam ('param'); 
        $this->view->param = $param;
        $periodo = $indMin->periodo;
        $p = explode(' ', $periodo);
        $nomeArq = str_replace('/','-',$p[1].'_'.$p[4]);
        /**
         * Gera o Excel
         */
        if ($param == 'xls') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=TRF1 - Análise de SLA.xls");
        } else {     
           /**
             * Gera o PDF
             */
            $this->render();
            $response = $this->getResponse();
            $body = $response->getBody();
            $response->clearBody();

            define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
            define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
            include(realpath(APPLICATION_PATH.'/../library/MPDF53/mpdf.php'));
            $mpdf=new mPDF('',    // mode - default ''
                           '',    // format - A4, for example, default ''
                            8,    // font size - default 0
                           '',    // default font family
                           10,    // margin_left
                           10,    // margin right
                           10,    // margin top
                           10,    // margin bottom
                            9,    // margin header
                            9,    // margin footer
                           'L');

            $mpdf->AddPage('P', '', '0', '1');
            $mpdf->WriteHTML($body);
            $name =  'TRF1 - Análise de SLA.pdf';
            $mpdf->Output($name,'D');
        }
    }   
}
