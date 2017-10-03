<?php
/**
 * @category	TRF1
 * @package	Trf1_Sosti_Negocio_SolicitacaoServicosGraficos
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Dayane Oliveira Freire
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Solicitações de serviços graficos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Soseg_Negocio_SolicitacaoServicosGraficos
{
    
	/**
	 * Classe init
	 * 
	 * @param	none
	 * @author	Dayane Oliveira Freire
	 */
	public function _init () {
            
	}
	
	
	
	/**
	 * Metodo que retorna os dados de uma solicitacao
	 *
	 * @params	int	$params['ids']	- ids das solicitações
	 * @params	int	$params['tipo']	- tipo de solicitação
	 * @return	array	
	 * @author	Dayane Oliveira Freire
	 */
	public function getDadosSolicitacao($params) {
		
            $CaixasQuerys = new App_Sosti_CaixasQuerys();

            $stmt = "";
            $stmt .= $CaixasQuerys->selectCaixa(4);
            $stmt .= $CaixasQuerys->from();
            $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
            $stmt .= $CaixasQuerys->leftJoinFaseServico();
            $stmt .= $CaixasQuerys->leftJoinFaseNivel();
            $stmt .= $CaixasQuerys->leftJoinFaseEspera();
            $stmt .= $CaixasQuerys->leftJoinFasePrazo();
            $stmt .= $CaixasQuerys->leftJoinLotacaoGeradora();
            $stmt .= $CaixasQuerys->innerJoinMovimentacaoDestinatarioCaixaDeEntrada();
            $stmt .= $CaixasQuerys->innerJoinCaixaDeEntradaGrupoServico();
            $stmt .= $CaixasQuerys->where();
            $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false, $params['tipo']);
            $stmt .= $CaixasQuerys->whereUltimoServico();
            $stmt .= $CaixasQuerys->whereUltimoNivel();
            $stmt .= $CaixasQuerys->whereUltimaEspera();
            $stmt .= $CaixasQuerys->whereUltimoPrazo();
            $stmt .= $CaixasQuerys->whereTipoSolicitacao(true, $params['tipo']);
            $stmt .= $CaixasQuerys->whereIdSolicitacaoIN(true, $params['ids']);
            //Zend_Debug::dump($stmt,  'getDadosSolicitacao - negocio');
            //exit;
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $statement = $db->query($stmt);

            return $statement->fetchAll();
            
        }
        
        /**
	 * Metodo que encaminha uma ou várias solicitações para um grupo de serviço diferente
	 *
	 * @params	array	$params	
	 * @return	array	
	 * @author	Dayane Oliveira Freire
	 */
        public function encaminhaSolicitacaoServGraficoGrupo($params){
            
        $SosTbSsolSolicitacao= new Application_Model_DbTable_SosTbSsolSolicitacao(); 
        $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
        $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
        $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
        $userNs = new Zend_Session_Namespace('userNs'); 
        $appAnexo = new App_Sosti_Anexo();
        
        //Zend_Debug::dump($params); exit;
        
        $anexos = $params['anexos'];
        $dadosForm = $params['dados'];
        $dadosFormDecode = Zend_Json::decode($dadosForm["SGRS_ID_GRUPO"]);
        $explodeServico = explode("|", $dadosForm["SSER_ID_SERVICO"]);
        $idServico = $explodeServico[0];
        $documentos = ""; 
        $solicitacoes = $this->getDadosSolicitacao($params);
        if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO'] = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); 
        }
        
        
        try{
             
             $db = Zend_Db_Table_Abstract::getDefaultAdapter();
             $db->beginTransaction();
             
                foreach($solicitacoes as $sol){
                    $sysdate = $SosTbSsolSolicitacao->sysdate();
                    
                    $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $sysdate;
                    $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $sol['MODE_SG_SECAO_UNID_DESTINO'];
                    $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $sol["MODE_CD_SECAO_UNID_DESTINO"];
                    $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                    $dataMoviMovimentacao["MOVI_ID_CAIXA_ENTRADA"] = $dadosFormDecode["CXEN_ID_CAIXA_ENTRADA"];
                    //Zend_Debug::dump($dataMoviMovimentacao, ' dataMoviMovimentacao');
                    $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
                    $idMoviMovimentacao = $rowMoviMovimentacao->save();
                    
                    $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $sol['SSOL_ID_DOCUMENTO'];
                    //Zend_Debug::dump($dataModoMoviDocumento, 'dataModoMoviDocumento');
                    $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
                    $rowModoMoviDocumento->save();
                    
                    $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $dadosFormDecode['LOTA_SIGLA_SECAO'];
                    $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $dadosFormDecode["LOTA_COD_LOTACAO"];
                    $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
                    $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $dadosFormDecode["CXEN_ID_CAIXA_ENTRADA"];
                    //Zend_Debug::dump($dataModeMoviDestinatario, 'dataModeMoviDestinatario');
                    $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
                    $rowModeMoviDestinatario->save();
                    
                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataMofaMoviFase["MOFA_DH_FASE"] =  $sysdate;
                    $dataMofaMoviFase["MOFA_ID_FASE"] = Trf1_Sosti_Definicoes::FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO; 
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dadosForm["MOFA_DS_COMPLEMENTO"] . "'");
                    //Zend_Debug::dump($dataMofaMoviFase, 'dataMofaMoviFase');
                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                    $rowMofaMoviFase->save();
                    
                    $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                    $rowUltima_fase = $SadTbDocmDocumento->find($sol['SSOL_ID_DOCUMENTO'])->current();
                    $rowUltima_fase->setFromArray($dataUltima_fase);
                    //Zend_Debug::dump($rowUltima_fase->toArray(), 'rowUltima_fase');
                    $rowUltima_fase->save();
                    
                    $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataSsesServicoSolic["SSES_DH_FASE"] = $sysdate;
                    $dataSsesServicoSolic["SSES_ID_SERVICO"] = $idServico;
                    $dataSsesServicoSolic["SSES_ID_DOCUMENTO"] = $sol['SSOL_ID_DOCUMENTO'];
                    //Zend_Debug::dump($dataSsesServicoSolic);
                    $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
                    $rowSsesServicoSolic->save();
                    
                    /* Retira do atendente */
                    $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
                    $rowSolicitacao = $SosTbSsolSolicitacao->find($sol['SSOL_ID_DOCUMENTO'])->current();
                    $rowSolicitacao->setFromArray($dataSsolSolicitacao);
                    $rowSolicitacao->save();
                    
                    //anexos
                    if ( count($nrDocsRed) > 0 ) {
                        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                        $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($sol["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                    }
                    
                    
                    $documentos .= $sol['DOCM_NR_DOCUMENTO']. '<br/>';
                    
                }
                $retorno = $documentos;
                //exit;
                $db->commit();
                return $retorno;
                
        }catch(Exception $e){
            
            $db->rollBack();
            return false;
            //throw $e;
            
        }
         
     }
		
     public function encaminhaSolicitacaoServGraficoAtendente($params){
         
         
        $userNs = new Zend_Session_Namespace('userNs');
        $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $email = new Application_Model_DbTable_EnviaEmail();
        $appAnexo = new App_Sosti_Anexo();
        $documentos = "";
        //Zend_Debug::dump($params, '$params'); 
        
        $dadosForm = $params['dados'];
        $anexos = $params['anexos'];
        
        $solicitacoes = $this->getDadosSolicitacao($params); 
        if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO'] = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); exit;
        }
      
        try
        {
             
             $db = Zend_Db_Table_Abstract::getDefaultAdapter();
             $db->beginTransaction();
             
                foreach($solicitacoes as $sol){
                    
                    $sysdate = $SosTbSsolSolicitacao->sysdate();
                    
                   
                    $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $sol['MOVI_ID_MOVIMENTACAO'];
                    $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
                    $dataMofaMoviFase["MOFA_DH_FASE"] = $sysdate;
                    $dataMofaMoviFase["MOFA_ID_FASE"] = Trf1_Sosti_Definicoes::FASE_ENCAMINHAMENTO_SOLICITACAO_SERVICO_CX_PESSOAL; 
                    $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dadosForm["MOFA_DS_COMPLEMENTO"] . "'");

                    $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                    $idMoviMovimentacao = $rowMofaMoviFase->save();
                    $idMoviMovimentacao = $idMoviMovimentacao["MOFA_ID_MOVIMENTACAO"];

                    $dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"] = $dadosForm["APSP_ID_PESSOA"];
                    //$dataSsolSolicitacao["SSOL_CD_MATRICULA_ATENDENTE"] = $dadosForm["SSOL_CD_MATRICULA_ATENDENTE"];
                    $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $sol["SSOL_ID_DOCUMENTO"];
                    
                    $rowSsolSolicitacao = $SosTbSsolSolicitacao->find($sol['SSOL_ID_DOCUMENTO'])->current();
                    $rowSsolSolicitacao->setFromArray($dataSsolSolicitacao);
                    $rowSsolSolicitacao->save();

                    //Ultima Fase do lançada na Solicitação.//
                    /*----------------------------------------------------------------------------------------*/
                    $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
                    $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                    $rowUltima_fase = $SadTbDocmDocumento->find($sol['SSOL_ID_DOCUMENTO'])->current();
                    $rowUltima_fase->setFromArray($dataUltima_fase);
                //    Zend_Debug::dump($rowUltima_fase->toArray());
                    $rowUltima_fase->save();
                    
                    
                    if ( count($nrDocsRed) > 0 ) {
                        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                        $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($sol["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                    }
                   
                     /*----------------------------------------------------------------------------------------*/
                    $sistema = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais';
                    $remetente = 'noreply@trf1.jus.br';
                    $destinatario = $dadosForm["APSP_ID_PESSOA"].'@trf1.jus.br';
                    $assunto = 'Encaminhamento de Solicitação';
                    $corpo = "Uma solicitação foi encaminhada para sua Caixa Pessoal.</p>
                              Número da Solicitação: ".$sol['DOCM_NR_DOCUMENTO']." <br/>
                              Data da Solicitação: ".$sol["DOCM_DH_CADASTRO"]." <br/>
                              Encaminhado por: ".$userNs->nome." <br/>
                              Tipo de Serviço Solicitado: ".$sol['SSER_DS_SERVICO']."<br/>
                              Descrição do Encaminhamento: ".nl2br($dadosForm["MOFA_DS_COMPLEMENTO"])."<br/>";
                              $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);
            
                   $documentos .= $sol['DOCM_NR_DOCUMENTO'] . "<br/>";
           }
          
            //exit;
            $db->commit();
            //$logAcesso = new Trf1_Guardiao_Log ();
            //$logAcesso->gravaLog ('encaminhamento');
            return $documentos;
        } catch (Exception $exc) {
            $db->rollBack();
            return false;
        }
        
       
     }
     
     public function parecerSolicitacao($params){
         
       
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao(); 
        $appEmail = new App_Email();
        $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_parecer');
        $userNs = new Zend_Session_Namespace('userNs');
        $appAnexo = new App_Sosti_Anexo();
        $sysdate = $SosTbSsolSolicitacao->sysdate();
        
        $anexos = $params['anexos'];
        $faseParecer = Trf1_Sosti_Definicoes::FASE_PARECER_SOLICITACAO_SERVICO;
        $solicitacoes = $ns->solicitacoes;
        
        
        $dadosParecer = array( 'MOFA_ID_MOVIMENTACAO' => '',
                               'MOFA_CD_MATRICULA' => $userNs->matricula,
                               'MOFA_DS_COMPLEMENTO' => $params['dados']["MOFA_DS_COMPLEMENTO"]
        );
        
        $dadosEmail = array(    'solicitacao' => '',
                                'dataSolicitacao' => '',
                                'tipoServico' => '',
                                'destinatario' => '',
                                'descricaoParecer' => $params['dados']["MOFA_DS_COMPLEMENTO"]);

         try{                       
            foreach( $ns->solicitacoes as $solicitacao ){

                $dadosParecer['MOFA_ID_MOVIMENTACAO'] = $solicitacao["MOVI_ID_MOVIMENTACAO"];
               // Zend_Debug::dump($dadosParecer, 'dadosParecer');

                $retorno = $SosTbSsolSolicitacao->parecerSolicitacao($dadosParecer, $solicitacao['SSOL_ID_DOCUMENTO'], $faseParecer);
              //  Zend_Debug::dump($DtHoraParecer, 'DtHoraParecer');

                $dadosEmail['solicitacao'] = $solicitacao['DOCM_NR_DOCUMENTO'];
                $dadosEmail['dataSolicitacao'] = $solicitacao['DOCM_DH_CADASTRO'];
                $dadosEmail['tipoServico'] = $solicitacao['SSER_DS_SERVICO'];


                /* Envia e-mail para o Atendente da Solicitação se tiver */
                if ($solicitacao['ATENDENTE'] != ' - ' && $solicitacao['ATENDENTE'] != '') {
                    $matriculaDestinatario = explode(' - ', $solicitacao['ATENDENTE']);
                    if(count($matriculaDestinatario) > 0){
                        $dadosEmail['destinatario'] = $matriculaDestinatario[0];
                    }else{
                        $dadosEmail['destinatario'] = $solicitacao['ATENDENTE'];
                    }
                    $appEmail->parecerSolicitacao($dadosEmail);
                }
                
                /*if ( count($nrDocsRed) > 0 ) {
                        $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                        $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($solicitacao["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                }*/
                
                //Envia email para o cadastrante da solicitacao
                $dadosEmail['destinatario'] = $solicitacao['DOCM_CD_MATRICULA_CADASTRO'];
                $appEmail->parecerSolicitacao($dadosEmail);
                
            }
         }catch(Exception $e){
             throw new Exception('Não foi possível salvar o parecer. '.$e);
         }
      
        return true;
     }
     
     public function baixarSolicitacao($params){
         
         $userNs = new Zend_Session_Namespace('userNs');
         $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
         $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
         $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_baixar');
         $appEmail = new App_Email();
         $appAnexo = new App_Sosti_Anexo();
         
         $dadosBaixa = array( 'MOFA_ID_MOVIMENTACAO' => '',
                              'MOFA_CD_MATRICULA' => $userNs->matricula,
                              'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr("'" . $params['dados']["MOFA_DS_COMPLEMENTO"] . "'"),
                              'MOFA_ID_FASE' => Trf1_Sosti_Definicoes::FASE_BAIXA_SOLICITACAO_SERVICO
                 
                        );
         
         $anexos = $params['anexos'];
         $solicitacoes = $ns->solicitacoes;
         $msg = "";
         
         if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO'] = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); 
        }
        
         $db = Zend_Db_Table_Abstract::getDefaultAdapter();
         $db->beginTransaction();
         
         try{                       
            foreach( $ns->solicitacoes as $solicitacao ){
                        
                        $sysdate = $SosTbSsolSolicitacao->sysdate();
                        
                        $dadosBaixa['MOFA_ID_MOVIMENTACAO'] = $solicitacao['MOVI_ID_MOVIMENTACAO'];
                        $dadosBaixa['MOFA_DH_FASE'] = $sysdate;
                        
                        $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dadosBaixa);
                        $idMoviMovimentacao = $rowMofaMoviFase->save();
                        $idMoviMovimentacao = $idMoviMovimentacao['MOFA_ID_MOVIMENTACAO'];
                        
                        if ( count($nrDocsRed) > 0 ) {
                            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                            $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($solicitacao["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                        }
                        
                        if($idMoviMovimentacao){
                            $arrayDados = array(
                                        'destinatario' => $solicitacao["DOCM_CD_MATRICULA_CADASTRO"]
                                        , 'solicitacao' => $solicitacao['DOCM_NR_DOCUMENTO']
                                        , 'dataSolicitacao' => $solicitacao['DOCM_DH_CADASTRO']
                                        , 'tipoServico' => $solicitacao['SSER_DS_SERVICO']
                                        , 'descricaoBaixa' => nl2br($params['dados']["MOFA_DS_COMPLEMENTO"])
                                        , 'descricaoSolicitacao' => $solicitacao["DOCM_DS_ASSUNTO_DOC"]);

                                        $appEmail->baixarSolicitacao($arrayDados, false);
                        }
                        
                        $msg .= $solicitacao['DOCM_NR_DOCUMENTO'] . "<br />";
                        
            }
            
            $db->commit();
            return $msg;
         }catch(Exception $e){
             return false;
             $db->rollBack();
             //throw $e;
         }
          
         return true;   
     }
     
     public function cancelarSolicitacao($params){
         
         $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_cancelar');
         $userNs = new Zend_Session_Namespace('userNs');
         $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
         $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
         $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
         $email = new Application_Model_DbTable_EnviaEmail();
         $solicitacoesCanceladas = "";
         
          try{     
              $db = Zend_Db_Table_Abstract::getDefaultAdapter();
              $db->beginTransaction();
              
              //Zend_debug::dump($ns->solicitacoes);
             
              foreach( $ns->solicitacoes as $solicitacao ){
                
                  $sysdate = $SosTbSsolSolicitacao->sysdate();
                  $dadosCancelar = array(
                      'MOFA_ID_MOVIMENTACAO' => $solicitacao['MOVI_ID_MOVIMENTACAO'],
                      'MOFA_CD_MATRICULA' => $userNs->matricula,
                      'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr("'" .$params['dados']['MOFA_DS_COMPLEMENTO'] . "'"),
                      'MOFA_DH_FASE' => $sysdate,
                      'MOFA_ID_FASE' => Trf1_Sosti_Definicoes::FASE_CANCELAMENTO_SOLICITACAO_SERVICO
                  ); 
                  
                 $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dadosCancelar);
                 $rowMofaMoviFase->save();
                  
                 //Zend_debug::dump($dadosCancelar);
                 
                 $dataSsolSolicitacao['SSOL_CD_MATRICULA_ATENDENTE'] = '';
		 $rowSolicitacao = $SosTbSsolSolicitacao->find($solicitacao['SSOL_ID_DOCUMENTO'])->current();
		 $rowSolicitacao->setFromArray($dataSsolSolicitacao);
		 $rowSolicitacao->save();
                
                $nrDocumento = $solicitacao["DOCM_NR_DOCUMENTO"];
		$solicitacoesCanceladas .= $nrDocumento . "<br/>";
		
                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $solicitacao['MOVI_ID_MOVIMENTACAO'];
                $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                $rowUltima_fase = $SadTbDocmDocumento->find($solicitacao['SSOL_ID_DOCUMENTO'])->current();
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
						
                
                $sistema = 'e-Sisad - Sistema de Gerenciamento e Controle de Documentos e Processos Administrativos Digitais ';
                $assunto = 'Cancelamento de Solicitação';
                $remetente = 'noreply@trf1.jus.br';
                $destinatario = $solicitacao['DOCM_CD_MATRICULA_CADASTRO'] . '@trf1.jus.br';
                $corpo = "A seguinte solicitação foi cancelada.</p>
                                Número da Solicitação: " . $nrDocumento . " <br/>
                                Data do Cancelamento: " . date('d/m/Y H:i:s') . " <br/>
                                Responsavél: " . $userNs->nome . " <br/>
                                Tipo de Serviço : Cancelamento de Solicitação <br/>
                                Descrição do Cancelamento: " . $dadosCancelar["MOFA_DS_COMPLEMENTO"] . "<br/>";

                $email->setEnviarEmail($sistema, $remetente, $destinatario, $assunto, $corpo);

                  
              }
            
              $db->commit();
              return $solicitacoesCanceladas;
              
          }catch(Exception $e){
            // Zend_debug::dump($e);
            
            $db->rollback();  
            return false;
            
          }
         
     }
     
     
     public function trocaServicoSolicitacao($params){
         
         $userNs = new Zend_Session_Namespace('userNs');
         $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
         $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
         $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
         $SosTbSserServico = new Application_Model_DbTable_SosTbSserServico();
         $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
         $appEmail = new App_Email();
         $appAnexo = new App_Sosti_Anexo();
         $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_trocarservico');
         
        //Zend_Debug::dump( $ns->solicitacoes, ' ns->solicitacoes');
        // exit;
         //Zend_Debug::dump($params, '$params'); exit;
         $anexos = $params['anexos'];
         $solicitacoes = $ns->solicitacoes;
         
         if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO'] = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); 
         }
         
          try{     
              $db = Zend_Db_Table_Abstract::getDefaultAdapter();
              $db->beginTransaction();
              
            foreach( $ns->solicitacoes as $solicitacao ){
                
                $sysdate = $SosTbSsolSolicitacao->sysdate();
                
                $dataMofaMoviFase = array(
                    'MOFA_ID_MOVIMENTACAO' => $solicitacao["MOVI_ID_MOVIMENTACAO"],
                    'MOFA_CD_MATRICULA' => $userNs->matricula, 
                    'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr( " CAST( '". $params['dados']["MOFA_DS_COMPLEMENTO"] ."' AS VARCHAR(4000)) " ),
                    'MOFA_DH_FASE' => $sysdate,
                    'MOFA_ID_FASE' => Trf1_Sosti_Definicoes::FASE_TROCA_SERVICO
                );
                
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                $rowMofaMoviFase->save();
                
                $dataSsesServicoSolic = array(
                    'SSES_ID_MOVIMENTACAO' => $solicitacao["MOVI_ID_MOVIMENTACAO"],
                    'SSES_DH_FASE' => $sysdate,
                    'SSES_ID_DOCUMENTO' => $solicitacao["SSOL_ID_DOCUMENTO"], 
                    'SSES_ID_SERVICO' => $params['dados']["SSER_ID_SERVICO"]
                ); 
               
                $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
                $rowSsesServicoSolic->save();

                $descricaoServico = $SosTbSserServico->find($params['dados']["SSER_ID_SERVICO"])->toArray();

                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $solicitacao["MOVI_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                
                $rowUltima_fase = $SadTbDocmDocumento->find($solicitacao["SSOL_ID_DOCUMENTO"])->current();
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
                
                if ( count($nrDocsRed) > 0 ) {
                            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                            $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($solicitacao["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                }
                
                $arrayEmail = array(
                        'nomeSistema' => 'e-Sisad'
                        ,'destinatario' => $solicitacao["DOCM_CD_MATRICULA_CADASTRO"]
                        , 'solicitacao' => $solicitacao['DOCM_NR_DOCUMENTO']
                        , 'dataSolicitacao' => $solicitacao['DOCM_DH_CADASTRO']
                        , 'descricaoServico' => $descricaoServico[0]['SSER_DS_SERVICO']
                        , 'motivoTroca' => nl2br($params['dados']["MOFA_DS_COMPLEMENTO"])
                    );

                $appEmail->trocaServico($arrayEmail, false);
                $solic .= $solicitacao['DOCM_NR_DOCUMENTO']. "<br/>";
                
            }
            $db->commit();
            
            return $solic;
            
          }catch(Exception $e){
              
            $db->rollback();  
             return false;
          }
                        
     }
     
     public function solicitarInformacao( $params, $nrDocsRed = null){
         
         $userNs = new Zend_Session_Namespace('userNs');
         $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
         $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
         $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
         $appEmail = new App_Email();
         $appAnexo = new App_Sosti_Anexo();
         $ns = new Zend_Session_Namespace('Ns_solicservicosgraficos_solicitarinfo');
         
         $anexos = $params['anexos'];
         $solicitacoes = $ns->solicitacoes;
        // Zend_Debug::dump($anexos);
        
         if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO']         = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); 
         }
         //exit;
         try{     
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
              
            foreach( $ns->solicitacoes as $solicitacao ){
                
                $sysdate = $SosTbSsolSolicitacao->sysdate();
                
                $dataMofaMoviFase = array(
                    'MOFA_ID_MOVIMENTACAO' => $solicitacao["MOVI_ID_MOVIMENTACAO"],
                    'MOFA_CD_MATRICULA' => $userNs->matricula, 
                    'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr( " CAST( '". $params['dados']["MOFA_DS_COMPLEMENTO"] ."' AS VARCHAR(4000)) " ),
                    'MOFA_DH_FASE' => $sysdate,
                    'MOFA_ID_FASE' => Trf1_Soseg_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_SERVICO
                );
                
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                $rowMofaMoviFase->save();
                
                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $solicitacao["MOVI_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                
                $rowUltima_fase = $SadTbDocmDocumento->find($solicitacao["SSOL_ID_DOCUMENTO"])->current();
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
                
                if ( count($nrDocsRed) > 0 ) {
                            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                            $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($solicitacao["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                }
                
                $arrayEmail = array(
                        'nomeSistema' => 'e-Soseg'
                        ,'destinatario' => $solicitacao["DOCM_CD_MATRICULA_CADASTRO"]
                        , 'solicitacao' => $solicitacao['DOCM_NR_DOCUMENTO']
                        , 'dataSolicitacao' => $solicitacao['DOCM_DH_CADASTRO']
                        , 'descricaoServico' => $solicitacao['SSER_DS_SERVICO']
                        , 'descricao' => nl2br($params['dados']["MOFA_DS_COMPLEMENTO"])
                    );

                $appEmail->solicitarInformacao($arrayEmail, false);
                $solic .= $solicitacao['DOCM_NR_DOCUMENTO']. "<br/>";
                
            }
            $db->commit();
            
            return $solic;
            
          }catch(Exception $e){
              
            $db->rollback();  
             return false;
          }
         
         
     }
     
     public function incluirInformacao($params, $nrDocsRed = null ){
         
         $userNs = new Zend_Session_Namespace('userNs');
         $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
         $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
         $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
         $appEmail = new App_Email();
         $appAnexo = new App_Sosti_Anexo();
         $ns = new Zend_Session_Namespace('Ns_minhassolicitacoes_incluirInfo');
         
         $anexos = $params['anexos'];
         $solicitacoes = $ns->solicitacoes;
        // Zend_Debug::dump($anexos);
        
         if( count($solicitacoes) > 0 ){
            
             $dadosForm['DOCM_ID_CONFIDENCIALIDADE'] = $solicitacoes[0]['DOCM_ID_CONFIDENCIALIDADE'];
             $dadosForm['DOCM_NR_DOCUMENTO']         = $solicitacoes[0]['DOCM_NR_DOCUMENTO'];
             $nrDocsRed = $appAnexo->incluiAnexosRED($anexos, $dadosForm);
             //Zend_Debug::dump($nrDocsRed, 'nrDocsRed'); 
         }
         //exit;
         try{     
            $db = Zend_Db_Table_Abstract::getDefaultAdapter();
            $db->beginTransaction();
            
           // Zend_Debug::dump($ns->solicitacoes);exit;
            
            foreach( $ns->solicitacoes as $solicitacao ){
                
                $sysdate = $SosTbSsolSolicitacao->sysdate();
                
                $dataMofaMoviFase = array(
                    'MOFA_ID_MOVIMENTACAO' => $solicitacao["MOVI_ID_MOVIMENTACAO"],
                    'MOFA_CD_MATRICULA' => $userNs->matricula, 
                    'MOFA_DS_COMPLEMENTO' => new Zend_Db_Expr( " CAST( '". $params['dados']["MOFA_DS_COMPLEMENTO"] ."' AS VARCHAR(4000)) " ),
                    'MOFA_DH_FASE' => $sysdate,
                    'MOFA_ID_FASE' => Trf1_Soseg_Definicoes::FASE_INCLUSAO_INFORMACAO_SOLICITACAO_SERVICO
                );
                
                $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
                $rowMofaMoviFase->save();
                
                $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $solicitacao["MOVI_ID_MOVIMENTACAO"];
                $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
                
                $rowUltima_fase = $SadTbDocmDocumento->find($solicitacao["SSOL_ID_DOCUMENTO"])->current();
                $rowUltima_fase->setFromArray($dataUltima_fase);
                $rowUltima_fase->save();
                
                if ( count($nrDocsRed) > 0 ) {
                            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
                            $incluiAnexos = $SadTbAnexAnexo->incluiAnexos($solicitacao["SSOL_ID_DOCUMENTO"], $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
                }
                
                $arrayEmail = array(
                         'nomeSistema' => 'e-Soseg'
                        ,'destinatario' => $solicitacao["MOFA_CD_MATRICULA"]
                        , 'solicitacao' => $solicitacao['DOCM_NR_DOCUMENTO']
                        , 'dataSolicitacao' => $solicitacao['DOCM_DH_CADASTRO']
                        , 'descricaoServico' => $solicitacao['SSER_DS_SERVICO']
                        , 'descricao' => nl2br($params['dados']["MOFA_DS_COMPLEMENTO"])
                    );

                $appEmail->incluirInformacao($arrayEmail, false);
                $solic .= $solicitacao['DOCM_NR_DOCUMENTO']. "<br/>";
                
            }
            $db->commit();
            
            return $solic;
            
          }catch(Exception $e){
              
            $db->rollback();  
             return false;
          }
         
     }
     
     public function cadastraSolicitacao( $data, $nrDocsRed = null) 
    {
        $SosTbSsolSolicitacao = new Application_Model_DbTable_SosTbSsolSolicitacao();
        $SadTbDocmDocumento = new Application_Model_DbTable_SadTbDocmDocumento();
        $sysdate = $SosTbSsolSolicitacao->sysdate(); 
        $userNs = new Zend_Session_Namespace('userNs');


        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction();
        try {
            
            /* ---------------------------------------------------------------------------------------- */
            /* Primeira tabela a ser inserida */
            
            $unidade = explode(' - ', $data['UNIDADE']);

            $data['DOCM_SG_SECAO_REDATORA'] = $unidade[3];
            $data['DOCM_CD_LOTACAO_REDATORA'] = $unidade[0];

            $data['DOCM_SG_SECAO_GERADORA'] = $unidade[3];
            $data['DOCM_CD_LOTACAO_GERADORA'] = $unidade[0];
            
             
             $dataDocmDocumento['DOCM_CD_MATRICULA_CADASTRO'] = $userNs->matricula;
             $dataDocmDocumento['DOCM_ID_TIPO_DOC'] = Trf1_Sosti_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO;
             $dataDocmDocumento['DOCM_SG_SECAO_GERADORA'] = $data['DOCM_SG_SECAO_GERADORA'];
             $dataDocmDocumento['DOCM_CD_LOTACAO_GERADORA'] = $data['DOCM_CD_LOTACAO_GERADORA'];
             $dataDocmDocumento['DOCM_SG_SECAO_REDATORA'] = $data['DOCM_SG_SECAO_REDATORA'];
             $dataDocmDocumento['DOCM_CD_LOTACAO_REDATORA'] = $data['DOCM_CD_LOTACAO_REDATORA'];
             $dataDocmDocumento['DOCM_ID_PCTT'] = 2531;
             $dataDocmDocumento['DOCM_DS_ASSUNTO_DOC'] = $data['DOCM_DS_ASSUNTO_DOC'];
             $dataDocmDocumento['DOCM_ID_TIPO_SITUACAO_DOC'] = 1;
             $dataDocmDocumento['DOCM_ID_CONFIDENCIALIDADE'] = 0;
             $dataDocmDocumento['DOCM_DS_PALAVRA_CHAVE'] = substr($data['DOCM_DS_ASSUNTO_DOC'], 0, 100);
               
            
            $dataDocmDocumento["DOCM_NR_SEQUENCIAL_DOC"] = $SadTbDocmDocumento->getNumeroSequencialDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], Trf1_Sosti_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO);
            $dataDocmDocumento["DOCM_NR_DOCUMENTO"] = $SadTbDocmDocumento->getNumeroDCMTO($data['DOCM_SG_SECAO_REDATORA'], $data['DOCM_CD_LOTACAO_REDATORA'], $data['DOCM_CD_LOTACAO_GERADORA'], Trf1_Sosti_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO, $data['DOCM_NR_SEQUENCIAL_DOC']);
            $dataDocmDocumento["DOCM_DH_CADASTRO"] = $sysdate;
            $dataDocmDocumento["DOCM_DS_ASSUNTO_DOC"] = new Zend_Db_Expr("'" . $dataDocmDocumento['DOCM_DS_ASSUNTO_DOC'] . "'");
           
            
            unset($dataDocmDocumento["DOCM_ID_DOCUMENTO"]);
            
            $rowDocmDocumento = $SadTbDocmDocumento->createRow($dataDocmDocumento);
            $idDocmDocumento = $rowDocmDocumento->save();
            /* ---------------------------------------------------------------------------------------- */
            
            /* ---------------------------------------------------------------------------------------- */
            /* Segunda tabela */
            
            $dataSsolSolicitacao["SSOL_ID_TIPO_CAD"] = 1;
            $dataSsolSolicitacao["SSOL_ED_LOCALIZACAO"] = $data["SSOL_ED_LOCALIZACAO"];
            $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = $data["SSOL_DS_OBSERVACAO"];
            unset($dataSsolSolicitacao['SSOL_NM_USUARIO_EXTERNO']);
            unset($dataSsolSolicitacao['SSOL_NR_CPF_EXTERNO']);
            $dataSsolSolicitacao['SSOL_DS_EMAIL_EXTERNO'] = $data['SSOL_DS_EMAIL_EXTERNO'];
            $dataSsolSolicitacao['SSOL_NR_TELEFONE_EXTERNO'] = str_replace(array("(", "-", ")"), "", $data['SSOL_NR_TELEFONE_EXTERNO']);
            
            $dataSsolSolicitacao["SSOL_ID_DOCUMENTO"] = $idDocmDocumento;
            $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] = new Zend_Db_Expr("'" . $dataSsolSolicitacao["SSOL_DS_OBSERVACAO"] . "'");
            unset($dataSsolSolicitacao["SSOL_HH_INICIO_ATEND"]);
            unset($dataSsolSolicitacao["SSOL_HH_FINAL_ATEND"]);
            $dataSsolSolicitacao["SSOL_QT_ITEM_PEDIDO"] = $data['SSOL_QT_ITEM_PEDIDO'];
            $rowSsolSolicitacao = $SosTbSsolSolicitacao->createRow($dataSsolSolicitacao);
            $idSsolSolicitacao = $rowSsolSolicitacao->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* terceira tabela */
            $dataMoviMovimentacao["MOVI_SG_SECAO_UNID_ORIGEM"] = $unidade[3];
            $dataMoviMovimentacao["MOVI_CD_SECAO_UNID_ORIGEM"] = $unidade[0];
            $dataMoviMovimentacao["MOVI_CD_MATR_ENCAMINHADOR"] = $userNs->matricula;
                
                
            $SadTbMoviMovimentacao = new Application_Model_DbTable_SadTbMoviMovimentacao();
            unset($dataMoviMovimentacao["MODO_ID_MOVIMENTACAO"]);
            $dataMoviMovimentacao["MOVI_DH_ENCAMINHAMENTO"] = $sysdate;
            $rowMoviMovimentacao = $SadTbMoviMovimentacao->createRow($dataMoviMovimentacao);
            $idMoviMovimentacao = $rowMoviMovimentacao->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* quarta tabela */
           
                
            $SadTbModoMoviDocumento = new Application_Model_DbTable_SadTbModoMoviDocumento();
            $dataModoMoviDocumento["MODO_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataModoMoviDocumento["MODO_ID_DOCUMENTO"] = $idDocmDocumento;
            $rowModoMoviDocumento = $SadTbModoMoviDocumento->createRow($dataModoMoviDocumento);
            $rowModoMoviDocumento->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* quinta tabela */
            $destino = Zend_Json::decode($data["SGRS_ID_GRUPO"]);

            $dataModeMoviDestinatario["MODE_SG_SECAO_UNID_DESTINO"] = $destino['LOTA_SIGLA_SECAO'];
            $dataModeMoviDestinatario["MODE_CD_SECAO_UNID_DESTINO"] = $destino['LOTA_COD_LOTACAO'];
            $dataModeMoviDestinatario["MODE_IC_RESPONSAVEL"] = 'N';
            $dataModeMoviDestinatario["MODE_ID_CAIXA_ENTRADA"] = $destino['CXEN_ID_CAIXA_ENTRADA']; //Caixa de atendimento 
            
            $SadTbModeMoviDestinatario = new Application_Model_DbTable_SadTbModeMoviDestinatario();
            $dataModeMoviDestinatario["MODE_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            unset($dataModeMoviDestinatario["MODE_DH_RECEBIMENTO"]);
            unset($dataModeMoviDestinatario["MODE_CD_MATR_RECEBEDOR"]);
            $rowModeMoviDestinatario = $SadTbModeMoviDestinatario->createRow($dataModeMoviDestinatario);
            $rowModeMoviDestinatario->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* sexta tabela */
            $SadTbMofaMoviFase = new Application_Model_DbTable_SadTbMofaMoviFase();
            $dataMofaMoviFase["MOFA_ID_FASE"] = Trf1_Sosti_Definicoes::FASE_CADASTRO_SOLICITACAO_SERVICO; 
            $dataMofaMoviFase["MOFA_CD_MATRICULA"] = $userNs->matricula;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = "Cadastro da Solicitação.";
                
            $dataMofaMoviFase["MOFA_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataMofaMoviFase["MOFA_DH_FASE"] = $sysdate;
            $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] = new Zend_Db_Expr("'" . $dataMofaMoviFase["MOFA_DS_COMPLEMENTO"] . "'");
            $rowMofaMoviFase = $SadTbMofaMoviFase->createRow($dataMofaMoviFase);
            $rowMofaMoviFase->save();
            /* ---------------------------------------------------------------------------------------- */

            //Ultima Fase do lançada na Solicitação.//
            /* ---------------------------------------------------------------------------------------- */
            $dataUltima_fase["DOCM_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataUltima_fase["DOCM_DH_FASE"] = $sysdate;
            $rowUltima_fase = $SadTbDocmDocumento->find($idDocmDocumento)->current();
            $rowUltima_fase->setFromArray($dataUltima_fase);
            $rowUltima_fase->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* setima tabela */
            $SosTbSsesServicoSolic = new Application_Model_DbTable_SosTbSsesServicoSolic();
            $id_servico = explode('|', $data["SSER_ID_SERVICO"]);
            $dataSsesServicoSolic["SSES_ID_SERVICO"] = $id_servico[0];
                
            $dataSsesServicoSolic["SSES_ID_MOVIMENTACAO"] = $idMoviMovimentacao;
            $dataSsesServicoSolic["SSES_DH_FASE"] = $sysdate;
            $dataSsesServicoSolic['SSES_ID_DOCUMENTO'] = $idDocmDocumento;
            $rowSsesServicoSolic = $SosTbSsesServicoSolic->createRow($dataSsesServicoSolic);
            $rowSsesServicoSolic->save();
            /* ---------------------------------------------------------------------------------------- */

            /* ---------------------------------------------------------------------------------------- */
            /* nona tabela */
            $SadTbAnexAnexo = new Application_Model_DbTable_SadTbAnexAnexo();
            $anexos = $SadTbAnexAnexo->incluiAnexos($idDocmDocumento, $sysdate, $idMoviMovimentacao, $nrDocsRed, false);
            
            /* ---------------------------------------------------------------------------------------- */
            
            $db->commit();
            $dataRetorno["DOCM_ID_DOCUMENTO"] = $idDocmDocumento;
            $dataRetorno["DOCM_NR_DOCUMENTO"] = $dataDocmDocumento["DOCM_NR_DOCUMENTO"];
            return $dataRetorno;
        } catch (Exception $exc) {
            $db->rollBack();
            echo $exc->getMessage();
        }
    }

    /*
     * Método que retorna as solicitações que estão com pedido de informação
     * @params
     * 
     * @return
     * 
     */
    public function getSolicitacoesPedidoInformacao( $params ){
     
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql  = "SELECT DISTINCT
                    SSOL_ID_DOCUMENTO,
                    SSOL_CD_MATRICULA_ATENDENTE||' - '||SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) ATENDENTE,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(SSOL_CD_MATRICULA_ATENDENTE) NOME_ATENDENTE,

                    --documento sad_tb_docm_documento
                    DOCM_NR_DOCUMENTO,
                    DOCM_CD_MATRICULA_CADASTRO,
                    DOCM_DS_ASSUNTO_DOC,
                    DOCM_DH_CADASTRO,
                    SAD_PKG_NR_DOCUMENTO.MASCARA_DOCUMENTO (DOCM_NR_DOCUMENTO) MASCARA_DOCM,
                    SAD_PG_RETORNO_PESSOA.SAD_FU_RETORNA_NOME(DOCM_CD_MATRICULA_CADASTRO) NOME_USARIO_CADASTRO,

                    --fase sad_tb_mofa_movi_fase
                    MOFA_ID_MOVIMENTACAO,
                    TO_CHAR(MOFA_DH_FASE,'DD/MM/YYYY HH24:MI:SS')MOFA_DH_FASE,
                    MOFA_ID_FASE,
                    
                    --sysdate dual
                    TO_CHAR(SYSDATE,'DD/MM/YYYY HH24:MI:SS') DATA_ATUAL, 

                    --movimentacao origem sad_tb_movi_movimentacao
                    TO_CHAR(MOVI_DH_ENCAMINHAMENTO,'DD/MM/YYYY HH24:MI:SS')MOVI_DH_ENCAMINHAMENTO,
                    TRUNC((SYSDATE - MOVI_DH_ENCAMINHAMENTO)*24*60,2) TEMPO_TOTAL,

                    --movimentacao destino sad_tb_mode_movi_destinatario
                    MODE_ID_CAIXA_ENTRADA, 
                    MODE_SG_SECAO_UNID_DESTINO,
                    MODE_CD_SECAO_UNID_DESTINO,
                    TO_CHAR(PKG_SLA.DATA_MOVIMENTACAO(MOFA_ID_MOVIMENTACAO), 'DD/MM/YYYY HH24:MI:SS') MOVIMENTACAO,

                    --servico sos_tb_sser_servico
                    SSER_ID_SERVICO, 
                    SSER_DS_SERVICO
			    
                    FROM
                        -- solicitacao    
                        SOS_TB_SSOL_SOLICITACAO SSOL

                        -- documento
                        INNER JOIN SAD_TB_DOCM_DOCUMENTO DOCM
                        ON SSOL.SSOL_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO

                        -- documento movimentacao
                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
                        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO

                        -- movimentacao origem
                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI
                        ON  MODO_MOVI.MODO_ID_MOVIMENTACAO  = MOVI.MOVI_ID_MOVIMENTACAO

                        -- movimentacao destino
                        INNER JOIN SAD_TB_MODE_MOVI_DESTINATARIO MODE_MOVI
                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MODE_MOVI.MODE_ID_MOVIMENTACAO

                        --fase
                        INNER JOIN SAD_TB_MOFA_MOVI_FASE MOFA
                        ON  MOVI.MOVI_ID_MOVIMENTACAO  = MOFA.MOFA_ID_MOVIMENTACAO

                        --descricao fase
                        INNER JOIN SAD_TB_FADM_FASE_ADM FADM
                        ON MOFA.MOFA_ID_FASE = FADM.FADM_ID_FASE

                        --servico

                        LEFT JOIN SOS_TB_SSES_SERVICO_SOLIC SSES
                        ON  MOFA.MOFA_ID_MOVIMENTACAO  = SSES.SSES_ID_MOVIMENTACAO
                        LEFT JOIN SOS_TB_SSER_SERVICO SSER
                        ON  SSES.SSES_ID_SERVICO       = SSER.SSER_ID_SERVICO 

                       WHERE

                        --Ultima movimentacao

                        (MOFA.MOFA_DH_FASE,MOFA_ID_MOVIMENTACAO) = (SELECT MAX(MOFA_2.MOFA_DH_FASE),MAX(MOFA_2.MOFA_ID_MOVIMENTACAO)
                        FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                        WHERE  MOFA_2.MOFA_ID_MOVIMENTACAO = (SELECT MAX(MOVI_1.MOVI_ID_MOVIMENTACAO)
                                                        FROM SAD_TB_DOCM_DOCUMENTO DOCM_1
                                                        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI_1
                                                        ON  DOCM_1.DOCM_ID_DOCUMENTO     = MODO_MOVI_1.MODO_ID_DOCUMENTO
                                                        INNER JOIN  SAD_TB_MOVI_MOVIMENTACAO MOVI_1
                                                        ON  MODO_MOVI_1.MODO_ID_MOVIMENTACAO  = MOVI_1.MOVI_ID_MOVIMENTACAO
                                                        WHERE  DOCM_1.DOCM_ID_DOCUMENTO = DOCM.DOCM_ID_DOCUMENTO
                                                        AND DOCM_1.DOCM_ID_TIPO_DOC = :tipoDoc )
                        )

                            AND 

                        --Ultimo servico                                                  
                        (SSER.SSER_ID_SERVICO = (SELECT SSES_1.SSES_ID_SERVICO 
                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_1
                                                            INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_1
                                                            ON  MOFA_1.MOFA_ID_MOVIMENTACAO  = SSES_1.SSES_ID_MOVIMENTACAO
                                                            AND MOFA_1.MOFA_DH_FASE          = SSES_1.SSES_DH_FASE
                                                    WHERE  (MOFA_1.MOFA_ID_MOVIMENTACAO, MOFA_1.MOFA_DH_FASE) = (SELECT MAX(MOFA_2.MOFA_ID_MOVIMENTACAO),MAX(MOFA_2.MOFA_DH_FASE)
                                                                                                                    FROM   SAD_TB_MOFA_MOVI_FASE MOFA_2
                                                                                                                    INNER JOIN SOS_TB_SSES_SERVICO_SOLIC SSES_2
                                                                                                                    ON  MOFA_2.MOFA_ID_MOVIMENTACAO  = SSES_2.SSES_ID_MOVIMENTACAO
                                                                                                                    AND MOFA_2.MOFA_DH_FASE          = SSES_2.SSES_DH_FASE                                                                                         
                                                                                                                    WHERE    MOFA_2.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO)
                                                    AND MOFA_1.MOFA_ID_MOVIMENTACAO = MOFA.MOFA_ID_MOVIMENTACAO) OR SSER.SSER_ID_SERVICO IS NULL)
                        
                        -- 1061 baixa
                        -- 1062 cancelada
                        AND   MOFA.MOFA_ID_FASE NOT IN ( 1061, 1062 )                                                                                  

                        -- fase pedido de informacao
                        AND MOFA.MOFA_ID_FASE = " . Trf1_Soseg_Definicoes::FASE_PEDIDO_INFORMACAO_SOLICITACAO_SERVICO. " 
                        
                        -- escolhe caixa
                        AND  MODE_MOVI.MODE_ID_CAIXA_ENTRADA IN  (". Trf1_Soseg_Definicoes::CAIXA_ATENDIMENTO_SERVICO_DIEDI . " , 
                                                                  ". Trf1_Soseg_Definicoes::CAIXA_ATENDIMENTO_SERVICO_DIGRA . ")    
                            
                        -- tipo documento solicitacao
                        AND  DOCM.DOCM_ID_TIPO_DOC = :tipoDoc
                        
                        -- matricula do solicitante 
                        AND DOCM.DOCM_CD_MATRICULA_CADASTRO = :matricula 

                        ";                                                                        
        
        
        //Zend_Debug::dump($sql);
        //exit;
        $stmt = $db->query($sql, array('matricula' => $params['matricula'],
                                       'tipoDoc' => Trf1_Soseg_Definicoes::ID_TIPO_SOLICITACAO_SERVICO_GRAFICO));	

        $resultado = $stmt->fetchAll();
        return $resultado;
        
        
}



}








































