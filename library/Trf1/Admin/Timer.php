<?php
/**
 * TRF1, Classe auxiliar para contagem de tempo
 * 
 * @category	TRF1
 * @package		Trf1_Admin_Timer
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
 * 1- Deve-se instanciar esta classe:
 * $variavelDeTempo = new Trf1_Admin_Timer ();
 * 
 * 2- Deve-se iniciar o contador de tempo:
 * $variavelDeTempo->Inicio ();
 * 
 * 3- Por padrão, o tempo máximo de resposta é de 3 segundos.
 * Caso se deseje um tempo (em segundos) diferente como tempo máximo para resposta, basta informar na função MostraMensagemTempo ()
 * 
 * 4- Deve-se terminar o contador de tempo:
 * $variavelDeTempo->MostraMensagemTempo ();
 * 
 * 4b- Caso se deseje apenas o tempo em segundos deve-se terminar o contador de tempo com a função retornaTempo ()
 * $variavelDeTempo->retornaTempo ();
 * 
 * @example
 * public function init() {
 * // Timer para mensuração do tempo de carregamento da página
 * $this->_tempo = new Trf1_Admin_Timer ();
 * $this->_tempo->Inicio ();
 * }
 * 
 * public function postDispatch() {
 * // Apresenta o tempo de carregamento da página
 * $this->view->tempoResposta = $this->_tempo->MostraMensagemTempo ();
 * }
 * 
 */
class Trf1_Admin_Timer {
	/**
	 * Valor definido como máximo para o tempo de resposta de uma página.
	 * 
	 * @var		TEMPO_MAXIMO_RESPOSTA int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	protected $TEMPO_MAXIMO_RESPOSTA = 3;
	
	/**
	 * Inicia o contador
	 * @return	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function Inicio() {
		$this->st = $this->retornaMicroTime ();
	}
	
	/**
	 * Retorna tempo decorrido desde o início do contador até o momento da solicitação
	 *
	 * @return	float Tempo, em micro segundos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaTempo() {
		$this->et = $this->retornaMicroTime ();
		return round ( ($this->et - $this->st), 3 );
	}
	
	/**
	 * Mostra mensagem contendo o tempo decorrido desde o início do contador até o momento da solicitação
	 *
	 * @return	string Mensagem contendo o tempo decorrido entre $this->Inicio até o momento
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function MostraMensagemTempo($tempoMaximoResposta = null) {
		if (! $tempoMaximoResposta) {
			$tempoMaximoResposta = $this->TEMPO_MAXIMO_RESPOSTA;
		}
		
		$tempo = self::retornaTempo ();
		
		// 
		$this->registraTempoResposta ( $tempo );
		
		if ($tempo < $tempoMaximoResposta) {
			$texto = 'Consulta respondida em ' . $tempo . ' segundos.';
		} else {
			$texto = 'Consulta respondida em <font color="red">' . $tempo . '</font> segundos.';
		}
		
		return $texto;
	}
	
	/**
	 * Retorna o microtime do momento da solicitação
	 *
	 * @return	float Tempo, em micro segundos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function retornaMicroTime() {
		list ( $usec, $sec ) = explode ( " ", microtime () );
		return (( float ) $usec + ( float ) $sec);
	}
	
	/**
	 * Grava no banco o log do tempo de duração de dada requisição do sistema
	 *
	 * @param	float Tempo, em micro segundos
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function registraTempoResposta($tempo) {
		try {
			// Busca usuário logado
			$sessao = new Zend_Session_Namespace ( 'userNs' );
			$matricula = strtoupper ( $sessao->matricula );
			
			// Busca o endereço IP
			$ip = getenv ( 'HTTP_X_FORWARDED_VARNISH' );
			
			if (! $ip) {
				$ip = 'não identificado';
			}
			
			// Busca dados da requisição
			$helper = new Zend_Controller_Action_Helper_Redirector ();
			$requisicao = $helper->getRequest ();
			
			$sistema = 'e-Admin';
			$modulo = $requisicao->getModuleName ();
			$controle = $requisicao->getControllerName ();
			$acao = $requisicao->getActionName ();
			$parametros = $this->isolaParametros ( $requisicao->getParams () );
			
			$sql = "
INSERT INTO
	OCS.OCS_TB_LOGT_TEMPO_RESPOSTA
(
	LOGT_NR_REQUISICAO,
	LOGT_DH_REQUISICAO,
	LOGT_DS_MATRICULA_USUARIO,
	LOGT_DS_IP_ACESSO,
	LOGT_NM_SISTEMA,
	LOGT_NM_MODULO,
	LOGT_NM_CONTROLE,
	LOGT_NM_ACAO,
	LOGT_TX_PARAMETROS,
	LOGT_NR_RESPOSTA_MILISEGUNDOS
) VALUES (
	SEQ_LOGT_NR_REQUISICAO.NEXTVAL,
	SYSDATE, /* TO_DATE(SYSDATE, 'YYYY-MM-DD-HH24:MI:SS'), */
	'$matricula',
	'$ip',
	'$sistema',
	'$modulo',
	'$controle',
	'$acao',
	'$parametros',
	$tempo * 1000
)
					";
			
			/*
			Zend_Debug::dump ( $sql );
			*/
			
			$banco = Zend_Db_Table::getDefaultAdapter ();
			
			$banco->query ( $sql );
		} catch (Exception $e) {
			// não faz nada; apenas não grava o log de tempo da requisição
			//Zend_Debug::dump($e);
		}
	}
	
	/**
	 * Gera a string contendo apenas os parâmetros, se for o caso, do endereço visitado, baseado na requisicao getRequest()
	 *
	 * @param	array	$requisicao
	 * @return	string
	 * @see		Trf1_Orcamento_Log
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	private function isolaParametros($requisicao) {
		$txtParametros = '';
		
		// remove o módulo
		unset ( $requisicao ['module'] );
		
		// remove o controle
		unset ( $requisicao ['controller'] );
		
		// remove a ação
		unset ( $requisicao ['action'] );
		
		// retorna em string formatada os parâmetros informados, se for o caso
		foreach ( $requisicao as $id => $valor ) {
			$txtParametros .= '/' . $id . '/' . $valor;
		}
		
		return $txtParametros;
	}

}