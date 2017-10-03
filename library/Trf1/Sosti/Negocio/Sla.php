<?php
/**
 * Classe para obtenção da dados para uso no SLA - Service Level of Agreement
 * 
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_Sla
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Sosti_Negocio_Sla {
	/**
	 * Retorna o percentual de ligações perdidas (consultado no Asternic)
	 * 
	 * @param	date		$dataIni	formato: yyyy-mm-dd
	 * @param	date		$dataFim	formato: yyyy-mm-dd
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaPercentualLigacoesPerdidas($dataIni, $dataFim) {
		$sql_LigacoesAtendidas = "
SELECT
	Count(*) AS Qtde
FROM		
	queue_stats AS qs,
	qname AS q,
	qagent AS ag,
	qevent AS ac
WHERE
	qs.qname = q.qname_id AND
	qs.qagent = ag.agent_id AND
	qs.qevent = ac.event_id AND
	qs.datetime >= '$dataIni 00:00:00' and
	qs.datetime <= '$dataFim 23:59:59' and
	q.queue IN (600, 650) AND
	ac.event IN ('COMPLETECALLER', 'COMPLETEAGENT')
				";
		
		$sql_LigacoesPerdidas = "
SELECT
	Count(*) AS Qtde
FROM
	queue_stats AS qs,
	qname AS q,
	qagent AS ag,
	qevent AS ac
WHERE
	qs.qname = q.qname_id AND
	qs.qagent = ag.agent_id AND
	qs.qevent = ac.event_id AND
	qs.datetime >= '$dataIni 00:00:00' and
	qs.datetime <= '$dataFim 23:59:59' and
	q.queue IN (600, 650) AND
	ac.event IN ('ABANDON', 'EXITWITHTIMEOUT')
				";
		
		try {
			// Verifica quem é a anterior conexão com banco.
			$bancoAnterior = Zend_Db_Table_Abstract::getDefaultAdapter ();
			
			// Estabelece conexão com o Asternic
			$resource = Zend_Controller_Front::getInstance ()->getParam ( 'bootstrap' )->getPluginResource ( 'multidb' );
			$banco = $resource->getDb ( 'asternic' );
			$banco = Zend_Db_Table::setDefaultAdapter ( $banco );
			$banco = Zend_Db_Table_Abstract::getDefaultAdapter ();
			
			$qtdeLigacoesAtendidas = $banco->fetchOne ( $sql_LigacoesAtendidas );
			$qtdeLigacoesPerdidas = $banco->fetchOne ( $sql_LigacoesPerdidas );
			
			// Reestabelece conexão com o banco anterior
			$banco = Zend_Db_Table::setDefaultAdapter ( $bancoAnterior );
			$banco = Zend_Db_Table_Abstract::getDefaultAdapter ();
		} catch (Exception $e) {
			throw new Zend_Exception($e);
		}
		
		$qtdeLigacoesTotal = $qtdeLigacoesAtendidas + $qtdeLigacoesPerdidas;
		
		$percLigacoesPerdidas = 0;
		if ($qtdeLigacoesTotal > 0) {
			$percLigacoesPerdidas = ($qtdeLigacoesPerdidas / $qtdeLigacoesTotal) * 100;
		}
		
		/*
		 * TESTES
		echo 'Lig. atendidas: ' . $qtdeLigacoesAtendidas . '<br />';
		echo 'Lig. perdidas: ' . $qtdeLigacoesPerdidas . '<br />';
		echo 'Lig. total: ' . $qtdeLigacoesTotal . '<br />';
		echo 'Percentual: ' . $percLigacoesPerdidas . '<br />';
		exit;
		*/
		
		return $percLigacoesPerdidas;
	}
	
	
	/**
	 * Verifica se permite ou não a exibição do SLA
	 * 
	 * @return	array	permissao = (true ou false); mensagem = 'texto' 
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function permiteSla() {
		// Mensagem antiga
		$mensagem = "Atenção, devido ao crescente uso do sistema, o que está causando uma sobrecarga no banco de dados, a visualização do Dashboard somente estará disponível antes das 10:00 e após às 19:00.";
		$mensagem = "Para visualizar o relatório acesse: <a href='http://relatorios.trf1.jus.br/app/e-Admin/login' target='_black'>http://relatorios.trf1.jus.br/app/e-Admin/login</a>.";
		$permissao = false;
		
		try {
			$urlCompleta = strtolower ( getenv ( 'HTTP_REFERER' ) );
			$urlSLA = strtolower ( 'relatorios.trf1.jus.br/app/e-admin' );
			
			$permissao = strrpos ( $urlCompleta, $urlSLA ) > 0;
		} catch ( Zend_Exception $e ) {
			return array ('permissao' => false, 'mensagem' => 'Erro na validação da permissão ou não da exibição das funcionalidades do SLA.<br />' . $mensagem );
		}
		
		return array ('permissao' => $permissao, 'mensagem' => $mensagem );
	}
	
	public static function getSysDate(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $date = $db->fetchRow(new Zend_Db_Expr('SELECT  to_char(CURRENT_TIMESTAMP,\'DD/MM/YYYY HH24:MI:SS\') AS DT_SYS FROM dual'));
        return current($date);


    }
	
}