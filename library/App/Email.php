<?php
/**
 * @category            App
 * @package		App_Email
 * @copyright           Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author              Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 */

class App_Email {
    
    private $userNs;
    private $remetente;
    private $arraySistemas;
    
    public function __construct() {
        $this->userNs    = new Zend_Session_Namespace('userNs');
        $this->remetente = 'noreply@trf1.jus.br';
        $this->arraySistemas = array (
            'e-Sosti' => 'e-Sosti - Sistema de Ordem de Serviço de TI'
            );
    }
    
   /**
    * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
    * @param array  $arrayDados ('destinatario'=>'', 'solicitacao'=>'', 'dataSolicitacao'=>'', 'dataPrazo'=>'', 'descricao'=>'')
    * @param boolean $autoCommit = true
    * @throws Exception 
    */
    public function extensaoPrazo(
            array $arrayDados,
            $autoCommit=true){
        
        $arrayCorpo = array();
        $arrayCorpo['destinatario'] = $arrayDados[destinatario].'@trf1.jus.br';
        $arrayCorpo['assunto']      = 'Solicitação de extensão de prazo';
        $arrayCorpo['corpo']        = "Foi encaminhada uma solicitação de extensão de prazo.
                                       <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                       <br/>Atendente: {$this->userNs->nome} 
                                       <br />Data da Solicitação: $arrayDados[dataSolicitacao]
                                       <br />Prazo Solicitado: $arrayDados[dataPrazo]
                                       <br />Descrição da extensão do prazo: $arrayDados[descricao].<br />";
        try {
            if($autoCommit){
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
            }
            
            $Application_Model_DbTable_EnviaEmail = new Application_Model_DbTable_EnviaEmail();
            $Application_Model_DbTable_EnviaEmail->setEnviarEmail(
                    $this->arraySistemas['e-Sosti'],
                    $this->remetente,
                    $arrayCorpo['destinatario'],
                    $arrayCorpo['assunto'],
                    $arrayCorpo['corpo']);
            
            if($autoCommit){
                $adapter->commit();
            }
        } catch (Exception $e) {
            if($autoCommit){
                $adapter->rollBack();
            }
            throw $e;
        }
    }
   /**
    * @author           Leidison Siqueira Barbosa [leidison_14@hotmail.com]
    * @param array      $arrayDados ('destinatario'=>'', 'solicitacao'=>'', 'dataSolicitacao'=>'', 'secao'=>'','descricao'=>'')
    * @param boolean    $autoCommit = true
    * @throws Exception 
    */
    public function encaminharSolicitacao(
            array $arrayDados,
            $autoCommit=true){
        
        $arrayCorpo = array();
        $arrayCorpo['destinatario'] = $arrayDados[destinatario].'@trf1.jus.br';
        $arrayCorpo['assunto']      = 'Encaminhamento de solicitação';
        $arrayCorpo['corpo']        = "Foi encaminhado uma solicitação para a caixa da seção ($arrayDados[secao]).</p>
                                        <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                        <br />Data da Solicitação: $arrayDados[dataSolicitacao]
                                        <br />Encaminhado por: {$this->userNs->nome} 
                                        <br />Descrição do Encaminhamento: $arrayDados[descricao]<br/>";
        try {
            if($autoCommit){
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
            }
            
            $Application_Model_DbTable_EnviaEmail = new Application_Model_DbTable_EnviaEmail();
            $Application_Model_DbTable_EnviaEmail->setEnviarEmail(
                    $this->arraySistemas['e-Sosti'],
                    $this->remetente,
                    $arrayCorpo['destinatario'],
                    $arrayCorpo['assunto'],
                    $arrayCorpo['corpo']);
            
            if($autoCommit){
                $adapter->commit();
            }
        } catch (Exception $e) {
            if($autoCommit){
                $adapter->rollBack();
            }
            throw $e;
        }
    }
   /**
    * @author           Leidison Siqueira Barbosa [leidison_14@hotmail.com]
    * @param array      $arrayDados ('destinatario'=>'', 'solicitacao'=>'', 'dataSolicitacao'=>'', 'tipoServico'=>'','descricaoBaixa'=>'','descricaoSolicitacao'=>'')
    * @param boolean    $autoCommit = true
    * @throws Exception 
    */
    public function baixarSolicitacao(
            array $arrayDados,
            $autoCommit=true){
        
        $arrayCorpo = array();
        if (isset($arrayDados["acompanhante"])) {
        $arrayCorpo['destinatario'] = $arrayDados["destinatario"].'@trf1.jus.br';
            $arrayCorpo['assunto'] = 'Baixa de Solicitação em Acompanhamento';
            $arrayCorpo['corpo'] = "Uma solicitação que você acompanhava, foi baixada</p>
                                        <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                        <br />Data da Solicitação: ".$arrayDados["dataSolicitacao"]."
                                        <br />Atendente: {$this->userNs->nome} 
                                        <br />Tipo de Serviço Solicitado: ".$arrayDados["tipoServico"]."
                                        <br />Descrição da Baixa: ".$arrayDados["descricaoBaixa"]."<br />
                                        <br />Descrição da Solicitação: ".$arrayDados["descricaoSolicitacao"]."<br/>";
        }else{
            $arrayCorpo['destinatario'] = $arrayDados["destinatario"].'@trf1.jus.br';
        $arrayCorpo['assunto']      = 'Baixa de Solicitação';
        $arrayCorpo['corpo']        = "Uma solicitação foi baixada, será necessário acessar o sistema para avaliação.</p>
                                        <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                        <br />Data da Solicitação: ".$arrayDados["dataSolicitacao"]."
                                        <br />Atendente: {$this->userNs->nome} 
                                        <br />Tipo de Serviço Solicitado: ".$arrayDados["tipoServico"]."
                                        <br />Descrição da Baixa: ".$arrayDados["descricaoBaixa"]."<br />
                                        <br />Descrição da Solicitação: ".$arrayDados["descricaoSolicitacao"]."<br/>";
        }
        
        try {
            if($autoCommit){
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
            }
            
            $Application_Model_DbTable_EnviaEmail = new Application_Model_DbTable_EnviaEmail();
            $Application_Model_DbTable_EnviaEmail->setEnviarEmail(
                    $this->arraySistemas['e-Sosti'],
                    $this->remetente,
                    $arrayCorpo['destinatario'],
                    $arrayCorpo['assunto'],
                    $arrayCorpo['corpo']);
            
            if($autoCommit){
                $adapter->commit();
            }
        } catch (Exception $e) {
            if($autoCommit){
                $adapter->rollBack();
            }
            throw $e;
        }
    }
    
   /**
    * @author           Leidison Siqueira Barbosa [leidison_14@hotmail.com]
    * @param array      $arrayDados ('destinatario'=>'', 'solicitacao'=>'', 'dataSolicitacao' => '', 'tipoServico'=>'','descricaoParecer'=>'')
    * @param boolean    $autoCommit = true
    * @throws Exception 
    */
    public function parecerSolicitacao(
            array $arrayDados,
            $autoCommit=true){

        $arrayCorpo = array();

        $arrayCorpo['destinatario'] = $arrayDados[destinatario] . '@trf1.jus.br';
        $arrayCorpo['assunto'] = 'Parecer em Solicitação';
        $arrayCorpo['corpo'] = "Foi realizado um parecer na solicitação abaixo:</p>
                                        <br />Número da Solicitação: <a href=\"http://sistemas.trf1.jus.br/app/e-Admin/sosti/pesquisarsolicitacoes/formpesquisa/nSosti/".$arrayDados["solicitacao"]."\"><b>".$arrayDados["solicitacao"]."</b> </a>
                                        <br />Data da Solicitação: $arrayDados[dataSolicitacao]
                                        <br />Por: {$this->userNs->nome} 
                                        <br />Tipo de Serviço: $arrayDados[tipoServico]
                                        <br />Descrição do Parecer: $arrayDados[descricaoParecer]<br />";

        try {
            if($autoCommit){
                $adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
                $adapter->beginTransaction();
}
            
            $Application_Model_DbTable_EnviaEmail = new Application_Model_DbTable_EnviaEmail();
            $Application_Model_DbTable_EnviaEmail->_setEnviarEmailThrowExceptionFlag = true;
            $Application_Model_DbTable_EnviaEmail->setEnviarEmail(
                    $this->arraySistemas['e-Sosti'],
                    $this->remetente,
                    $arrayCorpo['destinatario'],
                    $arrayCorpo['assunto'],
                    $arrayCorpo['corpo']);
            
            if($autoCommit){
                $adapter->commit();
            }
        } catch (Exception $e) {
            if($autoCommit){
                $adapter->rollBack();
            }
            throw $e;
        }
    }
}