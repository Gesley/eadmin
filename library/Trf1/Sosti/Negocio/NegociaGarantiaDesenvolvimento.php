<?php
/**
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leonan Alves dos Anjos
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Garantia dos serviços do desenvolvimento
 * 
 * ====================================================================================================
 * LICENSA
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
class Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento
{
	/* ************************************************************
	 * Definições iniciais
	 *********************************************************** */
    const ENTRADA_DADOS_METODO_CHAVE				= 'ENTRADA_DADOS_METODO_CHAVE';
    const IGNORE_DADOS_METODO_CHAVE				    = 'IGNORE_DADOS_METODO_CHAVE';
    const IGNORE_PARA_FORM_USUARIO				    = 'IGNORE_PARA_FORM_USUARIO';
    const CONFIGURACAO_FORM				            = 'CONFIGURACAO_FORM';
    const CHAVES_FORM				                = 'CHAVES_FORM';
    const ACAO_CONCORDANCIA				            = 'setConcordaGarantia';
    
    
    /**
     * Padroes arrays chaves dos metodos
     */
    const PADRAO_METODO_IGNORE			        = 'PADRAO_METODO_IGNORE';
    const PADRAO_METODO_TODAS_AS_CHAVES			= 'PADRAO_METODO_IGNORE_NOME_DATA';
    
    
    /**
     * @abstract Classe do modelo de dados do Zend Db
     * @var Class 
     */
    public $_Bd_modelo_garantia;
    public $_Bd_modelo_garantia_auditoria;
    public $_Bd_Dual;
    public $_Bd_form_garantia;
    public $_Bd_Adaptador;
    public $_Bd_TransacaoAtiva = false;
    public $_Bd_auto_commit_instantaneo = false;
    public $_Bd_auto_commit_persistente = false;
    private $_Bd_Comum_Date = null;
    public $_userNs = null;
    
    
            
    public $_ChavesArrayMetodos = array(
                                'setSolicitaGarantia' => array(
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE => array("NEGA_ID_MOVIMENTACAO" => "",
                                                            "NEGA_DH_SOLIC_GARANTIA" => "",
                                                            "NEGA_CD_MATR_SOLIC" => "",
                                                            "NEGA_DS_JUSTIFICATIVA_PEDIDO" => ""),
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_DADOS_METODO_CHAVE => array('NEGA_CD_MATR_SOLIC'=> "",'NEGA_DH_SOLIC_GARANTIA'=> ""),
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_PARA_FORM_USUARIO => array('NEGA_ID_MOVIMENTACAO'=> "",'NEGA_DH_SOLIC_GARANTIA'=> "",'NEGA_CD_MATR_SOLIC'=> "",'NEGA_DH_SOLIC_GARANTIA'=> ""),
                                                        ),
        
                                'setAceitaGarantia' => array(
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE => array("NEGA_ID_MOVIMENTACAO" => "",
                                                                         "NEGA_IC_ACEITE" => "",
                                                                         "NEGA_DH_ACEITE_RECUSA" => "",
                                                                         "NEGA_CD_MATR_ACEITE_RECUSA" => "",
                                                                         "NEGA_DS_JUST_ACEITE_RECUSA"  => ""),
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_DADOS_METODO_CHAVE => array('NEGA_DH_ACEITE_RECUSA'=> "",'NEGA_CD_MATR_ACEITE_RECUSA'=> ""),
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_PARA_FORM_USUARIO =>  array('NEGA_ID_MOVIMENTACAO'=> "",'NEGA_DH_ACEITE_RECUSA'=> "",'NEGA_CD_MATR_ACEITE_RECUSA'=> "",'NEGA_CD_MATR_ACEITE_RECUSA'=> "")),
        
                                'setConcordaGarantia' => array(
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE => array("NEGA_ID_MOVIMENTACAO" => "",
                                                                         "NEGA_IC_CONCORDANCIA" => "",
                                                                         "NEGA_DH_CONCORDANCIA" => "",
                                                                         "NEGA_CD_MATR_CONCORDANCIA" => "",
                                                                         "NEGA_DS_JUSTIFICATIVA_CONCOR" => ""),
                                                       Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_DADOS_METODO_CHAVE => array("NEGA_DH_CONCORDANCIA" => "","NEGA_CD_MATR_CONCORDANCIA" => "")
                                                        )
                                );
    
	public function __construct() {
        $this->_Bd_Dual = new Application_Model_DbTable_Dual();
        $this->setBd_Comum_DataHora($this->_Bd_Dual->sysdate());
        $this->_userNs = new Zend_Session_Namespace('userNs');
        $this->inicializaValoresPadroesDosMetodos();
        
        $this->_Bd_modelo_garantia = new Application_Model_DbTable_SosTbNegaNegociaGarantia();
        $this->_Bd_modelo_garantia_auditoria = new Application_Model_DbTable_SosTbNegaAuditoria();
        $this->_Bd_form_garantia = new Sosti_Form_SosTbNegaNegociaGarantia();
        $this->_Bd_Adaptador = Zend_Db_Table_Abstract::getDefaultAdapter();
	}
    
    
    
    /* ************************************************************
	 * Funções específicas
	 *********************************************************** */
    
    public function inicializaValoresPadroesDosMetodos(){
                        $this->_ValoresPadraoArrayMetodos = array(
                            'setSolicitaGarantia' =>array("NEGA_ID_MOVIMENTACAO" => "",
                                                            "NEGA_DH_SOLIC_GARANTIA" => $this->_Bd_Comum_Date,
                                                            "NEGA_CD_MATR_SOLIC" => $this->_userNs->matricula,
                                                            "NEGA_DS_JUSTIFICATIVA_PEDIDO" => ""),

                            'setAceitaGarantia' => array("NEGA_ID_MOVIMENTACAO" => "",
                                                            "NEGA_IC_ACEITE" => "",
                                                            "NEGA_DH_ACEITE_RECUSA" => $this->_Bd_Comum_Date,
                                                            "NEGA_CD_MATR_ACEITE_RECUSA" => $this->_userNs->matricula,
                                                            "NEGA_DS_JUST_ACEITE_RECUSA" => "" ),

                            'setConcordaGarantia' =>  array("NEGA_ID_MOVIMENTACAO" => "",
                                                                "NEGA_IC_CONCORDANCIA" => "",
                                                                "NEGA_DH_CONCORDANCIA" => $this->_Bd_Comum_Date,
                                                                "NEGA_CD_MATR_CONCORDANCIA" => $this->_userNs->matricula,
                                                                "NEGA_DS_JUSTIFICATIVA_CONCOR" => "")
                            );
    }
    
    /**
     * Realiza o merge da array de entrada com as posições da array padrão de entrada do método com os respectivos valores padrões
     * A preferencia de valores é concedida a array de entrada
     * @param string $nm_metodo
     * @param array $arrayEntrada
     * @return array
     */
    public function mergeValoresPadroes($nm_metodo,$arrayEntrada){
        return array_merge($this->_ValoresPadraoArrayMetodos[$nm_metodo] , $arrayEntrada);
    }
    
    /**
     * Permite setar os valores padrões de um método.
     * @param string $nm_metodo
     * @param array $arrayEntrada
     */
    public function setPadraoArrayEntradaMetodo($nm_metodo,$arrayEntrada){
        $this->_ValoresPadraoArrayMetodos[$nm_metodo] = $arrayEntrada;
    }
    
    /**
     * 
     * @param string $nm_metodo
     * @return array
     * @throws Exception Array de dados do método informado não foi encontrada.
     */
    public function getChavesPadraoMetodoIgnore($nm_metodo){
        if (!array_key_exists($nm_metodo, $this->_ChavesArrayMetodos)) {
            throw new Exception("Array de dados do método informado não foi encontrada.");
        }
        return array_diff_key($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE], $this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_DADOS_METODO_CHAVE]);
    }
    /**
     * 
     * @param array $array_post
     * @param string $nm_metodo
     * @param string $padrao
     * @param array $array_base
     * @return array
     */
    public function prepareEntradaMetodoFromArray($array_post, $nm_metodo, $padrao, $array_base = array()) {
        if ($padrao == Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::PADRAO_METODO_IGNORE) {
            if(empty($array_base)){
                return array_merge($this->getChavesPadraoMetodoIgnore($nm_metodo), array_intersect_key($array_post, $this->getChavesPadraoMetodoIgnore($nm_metodo)));
            }
            return array_merge($array_base,array_merge($this->getChavesPadraoMetodoIgnore($nm_metodo), array_intersect_key($array_post, $this->getChavesPadraoMetodoIgnore($nm_metodo))));
        } else if ($padrao == Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::PADRAO_METODO_TODAS_AS_CHAVES) {
            if(empty($array_base)){
                return array_merge($array_base,array_merge($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE], array_intersect($array_post, $this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE])));
            }
            return array_merge($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE], array_intersect($array_post, $this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE]));
        }
    }
    /**
     * 
     * @param array $dadosArraySolicitaGarantia
     * @throws Exception
     */
    public function setSolicitaGarantia($dadosArraySolicitaGarantia) {
        if($this->isAutoCommit()){
            $this->AtiveTransacao();
        }
        try {
            $this->validaChavesDosDadosDeEntradaMedodos(__FUNCTION__, array_flip(array_keys($dadosArraySolicitaGarantia)));
            $dadosArraySolicitaGarantia = $this->mergeValoresPadroes(__FUNCTION__, $dadosArraySolicitaGarantia);
            $dadosArraySolicitaGarantia["NEGA_DS_JUSTIFICATIVA_PEDIDO"] = new Zend_Db_Expr("'" . $dadosArraySolicitaGarantia["NEGA_DS_JUSTIFICATIVA_PEDIDO"] . "'");
            $row = $this->_Bd_modelo_garantia->createRow($dadosArraySolicitaGarantia);
            $this->audit("I",$row);
            $row->save();
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->commit();
                $this->InativeTransacao();
            }
        }
        catch (Exception $exc) {
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->rollBack();
                $this->InativeTransacao();
            }
            throw $exc;
       }
	}
    /**
     * 
     * @param array $dadosArrayAceitaGarantia
     * @throws Exception
     */
    public function setAceitaGarantia($dadosArrayAceitaGarantia) {
	    if($this->isAutoCommit()){
            $this->AtiveTransacao();
        }
        try {
            $this->validaChavesDosDadosDeEntradaMedodos(__FUNCTION__, $dadosArrayAceitaGarantia);
            $dadosArrayAceitaGarantia = $this->mergeValoresPadroes(__FUNCTION__, $dadosArrayAceitaGarantia);
            
            $dadosArrayAceitaGarantia["NEGA_DS_JUST_ACEITE_RECUSA"] = new Zend_Db_Expr("'" . $dadosArrayAceitaGarantia["NEGA_DS_JUST_ACEITE_RECUSA"] . "'");
            $row = $this->_Bd_modelo_garantia->find($dadosArrayAceitaGarantia["NEGA_ID_MOVIMENTACAO"])->current()->setFromArray($dadosArrayAceitaGarantia);
            $this->audit("A",$row);
            $row->save();
            
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->commit();
                $this->InativeTransacao();
            }
        }
        catch (Exception $exc) {
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->rollBack();
                $this->InativeTransacao();
            }
            throw $exc;
       }
    }
    /**
     * 
     * @param array $dadosArrayConcordaGarantia
     * @throws Exception
     */
    public function setConcordaGarantia($dadosArrayConcordaGarantia) {
        if($this->isAutoCommit()){
            $this->AtiveTransacao();
        }
        try {
            $this->validaChavesDosDadosDeEntradaMedodos(__FUNCTION__, $dadosArrayConcordaGarantia);
            $dadosArrayConcordaGarantia = $this->mergeValoresPadroes(__FUNCTION__, $dadosArrayConcordaGarantia);
            
            $dadosArrayConcordaGarantia["NEGA_DS_JUSTIFICATIVA_CONCOR"] = new Zend_Db_Expr("'" . $dadosArrayConcordaGarantia["NEGA_DS_JUSTIFICATIVA_CONCOR"] . "'");
            $row = $this->_Bd_modelo_garantia->find($dadosArrayConcordaGarantia["NEGA_ID_MOVIMENTACAO"])->current()->setFromArray($dadosArrayConcordaGarantia); 
            $this->audit("A",$row);
            $row->save();
            
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->commit();
                $this->InativeTransacao();
            }
        }
        catch (Exception $exc) {
            if($this->estaAtivaTransacao()){
                $this->_Bd_Adaptador->rollBack();
                $this->InativeTransacao();
            }
            throw $exc;
       }
	}
    /**
     * 
     * @param type $nm_metodo
     * @param type $arrayChavesEntrada
     * @throws Exception
     */
    private function validaChavesDosDadosDeEntradaMedodos($nm_metodo,$arrayChavesEntrada){
        if (!array_key_exists($nm_metodo, $this->_ChavesArrayMetodos)) {
            throw new Exception("Array de dados do método informado não foi encontrada.");
        }
        foreach ($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE] as $key => $value) {
            if ( !array_key_exists($key,  $arrayChavesEntrada) ) {
                if ( !array_key_exists($key, $this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_DADOS_METODO_CHAVE]) ) {
                    throw new Exception("A chave obrigatória: $key não foi encontrada nos dados de entrada do método $nm_metodo.");
                }
            }
        }
    }
    /**
     * 
     * @param string $operacao
     * @param Zend_Db_Row_Object $row
     */
    public function audit($operacao,$row){
        $dual = new Application_Model_DbTable_Dual();
        $dh_ts = $dual->localtimestampDb();
        $auditArr = array();
        $auditArr["NEGA_TS_OPERACAO"] = $dh_ts['DATA'];
        $auditArr["NEGA_CD_MATRICULA_OPERACAO"] = $this->_userNs->matricula;
        $auditArr["NEGA_CD_MAQUINA_OPERACAO"] = substr($_SERVER['REMOTE_ADDR'],0,50); 
        $auditArr["NEGA_CD_USUARIO_SO"] = substr($_SERVER['HTTP_USER_AGENT'],0,50);       
        $auditArr["NEGA_IC_OPERACAO"] = $operacao;
        
        switch ($operacao) {
            case "I":
                
                $rowNova = $row->toArray();
                $auditArr["OLD_NEGA_ID_MOVIMENTACAO"] = NULL;
                $auditArr["NEW_NEGA_ID_MOVIMENTACAO"] = $rowNova["NEGA_ID_MOVIMENTACAO"];     
                $auditArr["OLD_NEGA_DH_SOLIC_GARANTIA"] = NULL;     
                $auditArr["NEW_NEGA_DH_SOLIC_GARANTIA"] = ( !empty($rowNova["NEGA_DH_SOLIC_GARANTIA"]) && !( $rowNova["NEGA_DH_SOLIC_GARANTIA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowNova["NEGA_DH_SOLIC_GARANTIA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowNova["NEGA_DH_SOLIC_GARANTIA"]);
                $auditArr["OLD_NEGA_CD_MATR_SOLIC"] = NULL;         
                $auditArr["NEW_NEGA_CD_MATR_SOLIC"] = $rowNova["NEGA_CD_MATR_SOLIC"];         
                $auditArr["OLD_NEGA_DS_JUSTIFICATIVA_PED"] = NULL;  
                $auditArr["NEW_NEGA_DS_JUSTIFICATIVA_PED"] = (!( $rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"] . "'") ):($rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"]);   
                
                break;
            case "A":
                $rowNova = $row->toArray();
                $rowAntiga = $this->_Bd_modelo_garantia->find($rowNova["NEGA_ID_MOVIMENTACAO"])->current()->toArray();
                
                $auditArr["OLD_NEGA_ID_MOVIMENTACAO"] = $rowAntiga["NEGA_ID_MOVIMENTACAO"]; 
                $auditArr["NEW_NEGA_ID_MOVIMENTACAO"] = $rowNova["NEGA_ID_MOVIMENTACAO"];
                $auditArr["OLD_NEGA_DH_SOLIC_GARANTIA"] = ( !empty($rowAntiga["NEGA_DH_SOLIC_GARANTIA"]) && !( $rowAntiga["NEGA_DH_SOLIC_GARANTIA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowAntiga["NEGA_DH_SOLIC_GARANTIA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowAntiga["NEGA_DH_SOLIC_GARANTIA"]);
                $auditArr["NEW_NEGA_DH_SOLIC_GARANTIA"] = ( !empty($rowNova["NEGA_DH_SOLIC_GARANTIA"]) && !( $rowNova["NEGA_DH_SOLIC_GARANTIA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowNova["NEGA_DH_SOLIC_GARANTIA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowNova["NEGA_DH_SOLIC_GARANTIA"]);
                $auditArr["OLD_NEGA_CD_MATR_SOLIC"] = $rowAntiga["NEGA_CD_MATR_SOLIC"];          
                $auditArr["NEW_NEGA_CD_MATR_SOLIC"] = $rowNova["NEGA_CD_MATR_SOLIC"];         
                $auditArr["OLD_NEGA_DS_JUSTIFICATIVA_PED"] = (!( $rowAntiga["NEGA_DS_JUSTIFICATIVA_PEDIDO"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowAntiga["NEGA_DS_JUSTIFICATIVA_PEDIDO"] . "'") ):($rowAntiga["NEGA_DS_JUSTIFICATIVA_PEDIDO"]);   
                $auditArr["NEW_NEGA_DS_JUSTIFICATIVA_PED"] = (!( $rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"] . "'") ):($rowNova["NEGA_DS_JUSTIFICATIVA_PEDIDO"]);   
                
                $auditArr["OLD_NEGA_IC_ACEITE"] = $rowAntiga["NEGA_IC_ACEITE"];
                $auditArr["NEW_NEGA_IC_ACEITE"] = $rowNova["NEGA_IC_ACEITE"];
                $auditArr["OLD_NEGA_DH_ACEITE_RECUSA"] = ( !empty($rowAntiga["NEGA_DH_ACEITE_RECUSA"])  && !( $rowAntiga["NEGA_DH_ACEITE_RECUSA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowAntiga["NEGA_DH_ACEITE_RECUSA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowAntiga["NEGA_DH_ACEITE_RECUSA"]);
                $auditArr["NEW_NEGA_DH_ACEITE_RECUSA"] = ( !empty($rowNova["NEGA_DH_ACEITE_RECUSA"])  && !( $rowNova["NEGA_DH_ACEITE_RECUSA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowNova["NEGA_DH_ACEITE_RECUSA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowNova["NEGA_DH_ACEITE_RECUSA"]);
                $auditArr["OLD_NEGA_CD_MATR_ACEITE_RECUSA"] = $rowAntiga["NEGA_CD_MATR_ACEITE_RECUSA"];
                $auditArr["NEW_NEGA_CD_MATR_ACEITE_RECUSA"] = $rowNova["NEGA_CD_MATR_ACEITE_RECUSA"];
                $auditArr["OLD_NEGA_DS_JUST_ACEITE_RECUSA"] = (!( $rowAntiga["NEGA_DS_JUST_ACEITE_RECUSA"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowAntiga["NEGA_DS_JUST_ACEITE_RECUSA"] . "'") ):($rowAntiga["NEGA_DS_JUST_ACEITE_RECUSA"]);   
                $auditArr["NEW_NEGA_DS_JUST_ACEITE_RECUSA"] = (!( $rowNova["NEGA_DS_JUST_ACEITE_RECUSA"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowNova["NEGA_DS_JUST_ACEITE_RECUSA"] . "'") ):($rowNova["NEGA_DS_JUST_ACEITE_RECUSA"]);   
                
                $auditArr["OLD_NEGA_IC_CONCORDANCIA"] = $rowAntiga["NEGA_IC_CONCORDANCIA"];
                $auditArr["NEW_NEGA_IC_CONCORDANCIA"] = $rowNova["NEGA_IC_CONCORDANCIA"];
                $auditArr["OLD_NEGA_DH_CONCORDANCIA"] = ( !empty($rowAntiga["NEGA_DH_CONCORDANCIA"])  && !( $rowAntiga["NEGA_DH_CONCORDANCIA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowAntiga["NEGA_DH_CONCORDANCIA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowAntiga["NEGA_DH_CONCORDANCIA"]);
                $auditArr["NEW_NEGA_DH_CONCORDANCIA"] = ( !empty($rowNova["NEGA_DH_CONCORDANCIA"])  && !( $rowNova["NEGA_DH_CONCORDANCIA"] instanceof Zend_Db_Expr ) ) ? (new Zend_Db_Expr("TO_DATE('".$rowNova["NEGA_DH_CONCORDANCIA"]."','dd/mm/yyyy HH24:MI:SS')")) : ($rowNova["NEGA_DH_CONCORDANCIA"]);
                $auditArr["OLD_NEGA_CD_MATR_CONCORDANCIA"] = $rowAntiga["NEGA_CD_MATR_CONCORDANCIA"];
                $auditArr["NEW_NEGA_CD_MATR_CONCORDANCIA"] = $rowNova["NEGA_CD_MATR_CONCORDANCIA"];
                $auditArr["OLD_NEGA_DS_JUSTIFICATIVA_CONC"] = (!( $rowAntiga["NEGA_DS_JUSTIFICATIVA_CONCOR"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowAntiga["NEGA_DS_JUSTIFICATIVA_CONCOR"] . "'") ):($rowAntiga["NEGA_DS_JUSTIFICATIVA_CONCOR"]);   
                $auditArr["NEW_NEGA_DS_JUSTIFICATIVA_CONC"] = (!( $rowNova["NEGA_DS_JUSTIFICATIVA_CONCOR"] instanceof Zend_Db_Expr )) ? ( new Zend_Db_Expr("'" . $rowNova["NEGA_DS_JUSTIFICATIVA_CONCOR"] . "'") ):($rowNova["NEGA_DS_JUSTIFICATIVA_CONCOR"]);   
                
                break;
            default:
                break;
        }
        $this->_Bd_modelo_garantia_auditoria->createRow($auditArr)->save();
    }
    
    /*********************************
     * Integração com o ZendDb
     *********************************/
    private function AtiveTransacao(){
        $this->_Bd_Adaptador->beginTransaction();
        $this->_Bd_TransacaoAtiva = true;
        $this->_Bd_auto_commit_instantaneo = false;
    }
    
    private function InativeTransacao(){
        $this->_Bd_TransacaoAtiva = false;
    }
    
    private function estaAtivaTransacao(){
        return $this->_Bd_TransacaoAtiva;
    }
    
    private function isAutoCommit(){
        if($this->_Bd_auto_commit_instantaneo){
            return true; 
        }else if($this->_Bd_auto_commit_persistente){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     *  Data do Banco de dados
     */
    public function getBd_Comum_DataHora(){
        return $this->_Bd_Comum_Date;
    }
    
    public function setBd_Comum_DataHora($dataHora){
        $this->_Bd_Comum_Date = $dataHora;
    }
    
    /*****************************************
     * Integração com Forms
     * ***************************************/
    
    /**
     *
     * @param type $nm_metodo
     * @param type $form
     * @return type 
     */
    public function addNoform($nm_metodo,$form){
            $chavesForm = $this->getChavesPadraoMetodoParaForm($nm_metodo);
            foreach ($chavesForm as $key => $value) {
                $form->addElement($this->_Bd_form_garantia->getElement($key));
            }
            return $form;
    }
    /**
     * 
     * @param string constante $nm_metodo
     * @return array
     */
    public function getChavesPadraoMetodoParaForm($nm_metodo){
            return array_diff_key($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE], $this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::IGNORE_PARA_FORM_USUARIO]);
    }
    /**
     * 
     * @param string constante $nm_metodo
     */
    public function confForm($nm_metodo){
        $elements = $this->_Bd_form_garantia->getElements();
        foreach ($elements as $element) {
            if(!in_array($element->getName(), array_keys($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE]))){
                $this->_Bd_form_garantia->removeElement($element->getName());
            }
        }
        $this->_Bd_form_garantia->addDisplayGroup(array_keys($this->_ChavesArrayMetodos[$nm_metodo][Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimento::ENTRADA_DADOS_METODO_CHAVE]),$nm_metodo);
    }
    
    /**************************
     * Consultas no banco 
     **************************/
    /**
     * 
     * @param array $idsMovimentacao
     * @return boolean
     */
    public function existeGarantiaPorMovimentacoes($idsMovimentacao){
        if( $this->_Bd_modelo_garantia->fetchAll("NEGA_ID_MOVIMENTACAO IN (".implode(',',$idsMovimentacao).")")->count() > 0 ){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 
     * @param array $idsMovimentacao
     * @return null or array
     */
    public function getMovimentacoesComGarantia($idsMovimentacao = array()){
        $movimentacoes = $this->_Bd_modelo_garantia->fetchAll("NEGA_ID_MOVIMENTACAO IN (".implode(',',$idsMovimentacao).")");
        $movimentacoesRetorno = array();
        if(!is_null($movimentacoes)){
            $movimentacoes = $movimentacoes->toArray();
            foreach ($movimentacoes as $value) {
                $movimentacoesRetorno[] = $value["NEGA_ID_MOVIMENTACAO"];
            }
            return $movimentacoesRetorno;
        }else{
            return null;
        }
    }
    /**
     * 
     * @param array $params
     * @param string $order
     * @return array
     */
    public function getCaixaGarantiaDivergPesq($params, $order) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $CaixasQuerys = new App_Sosti_CaixasQuerys();
        
        $stmt = "";
        $stmt .= $CaixasQuerys->selectCaixa(13);
        $stmt .= $CaixasQuerys->from();
        $stmt .= $CaixasQuerys->innerJoinSolicitacaoDocumentoMovimentacaoFase();
        $stmt .= $CaixasQuerys->leftJoinFaseServico();
        $stmt .= $CaixasQuerys->innerJoinGarantia();
        $stmt .= $CaixasQuerys->leftJoinFechamento();
        $stmt .= $CaixasQuerys->where();
        $stmt .= $CaixasQuerys->whereUltimaMovimentacao(false);
        $stmt .= $CaixasQuerys->whereUltimoServico();
        $stmt .= $CaixasQuerys->whereTipoSolicitacao();
        $stmt .= $CaixasQuerys->whereNaoFechadoSla();
        
        /* Serviço */
        if (is_array($params['SSER_ID_SERVICO'])) {
            //Remove valores vazios da array
            if (array_search("", $params['SSER_ID_SERVICO']) !== false) {
                unset($params['SSER_ID_SERVICO'][array_search("", $params['SSER_ID_SERVICO'])]);
            }
            //Verifica se a array não é vazia
            if (count($params['SSER_ID_SERVICO']) > 0) {
                //Concatena os valores separados por vírgula
                $value_query = implode(',', $params['SSER_ID_SERVICO']);
                // Retira a utima virgula
                $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO IN( " . $value_query . ") ") : ('');
            }
        } else {
            $stmt .= ( $params['SSER_ID_SERVICO']) ? (" AND SSER_ID_SERVICO = " . $params['SSER_ID_SERVICO'] . " ") : ('');
        }
        $stmt .= ( $params['SSER_DS_SERVICO']) ? (" AND SSER_DS_SERVICO LIKE UPPER('%" . $params['SSER_DS_SERVICO'] . "%')") : ('');
        /* Data da Ultima fase */
        (($params['DATA_INICIAL'] == "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND NEGA_DH_ACEITE_RECUSA BETWEEN TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] == "")) ? ($stmt .= "AND NEGA_DH_ACEITE_RECUSA BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");
        (($params['DATA_INICIAL'] != "") && ($params['DATA_FINAL'] != "")) ? ($stmt .= "AND NEGA_DH_ACEITE_RECUSA BETWEEN TO_DATE('" . $params['DATA_INICIAL'] . "', 'DD/MM/YYYY') AND TO_DATE('" . $params['DATA_FINAL'] . "', 'DD/MM/YYYY')+1-1/24/60/60 ") : ("");

        /* Número da solicitação */
        $docm_nr_documento = $params['DOCM_NR_DOCUMENTO'];
        $stmt .= ($docm_nr_documento) ? ("AND TO_CHAR (DOCM_DH_CADASTRO,'YYYY') = SUBSTR($docm_nr_documento,0,4)
                                        AND DOCM_NR_DOCUMENTO LIKE '%'|| SUBSTR($docm_nr_documento,5)") : ('');


        switch ($params['NEGA_IC_CONCORDANCIA']) {
            case "C":
                $stmt .= " AND NEGA_IC_CONCORDANCIA = 'C' ";
                break;
            case "D":
                $stmt .= " AND NEGA_IC_CONCORDANCIA = 'D' ";
                break;
            case "AV":
                $stmt .= " AND NEGA_IC_ACEITE = 'R' AND NEGA_IC_CONCORDANCIA IS NOT NULL ";
                break;
            case "NAV":
                $stmt .= " AND NEGA_IC_ACEITE = 'R' AND NEGA_IC_CONCORDANCIA IS NULL";
                break;
            default:
                break;
        }
        /* Ordem */
        $stmt .= $CaixasQuerys->ordemCaixa($order);
        $stmt = $db->query($stmt);
        $solics_cx = $stmt->fetchAll();

        if ($params['SOMENTE_PRINCIPAL'] == 'N') {
            //esconde as solicitações filhas vinculadas
            $solics_cx = App_UtilArray::retiraposicaoarray2dby($solics_cx, "MOSTRA_VINCULACAO", "0");
        }
        return $solics_cx;
    }
	
}