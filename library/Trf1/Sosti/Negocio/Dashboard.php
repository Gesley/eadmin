<?php
/**
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_Dashboard
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre Orçamento - Despesas
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
class Trf1_Sosti_Negocio_Dashboard
{
	/* ************************************************************
	 * Definições iniciais
	 *********************************************************** */
	
	/**
	 * Código da caixa de atendimento ao usuário do TRF
	 *
	 * @var		CAIXA_HELP_DESK						int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const CAIXA_HELP_DESK							= 1;
	
	/**
	 * Código da caixa de desenvolvimento e sustentação do TRF
	 *
	 * @var		CAIXA_DESENVOLVIMENTO_SUSTENTACAO	int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const CAIXA_DESENVOLVIMENTO_SUSTENTACAO			= 2;
	
	/**
	 * Código da caixa da Infraestrutura do TRF
	 *
	 * @var		CAIXA_INFRAESTRUTURA				int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const CAIXA_INFRAESTRUTURA						= 3;
	
	/**
	 * Código da caixa NOC do TRF
	 *
	 * @var		CAIXA_NOC							int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const CAIXA_NOC									= 4;
	
	/**
	 * Código da fase de baixa de uma solicitação
	 *
	 * @var		FASE_BAIXADA						int (constant)
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	const FASE_BAIXADA								= 1000;
	
	/**
	 * Classe construtora
	 * 
	 * @param	none
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function __construct() {
		//
	}
	
	/* ************************************************************
	 * Funções específicas
	 *********************************************************** */
	
	/**
	 * Busca quantitativo de solicitações da Infra em conformidade ou não
	 *
	 * @param	int		$caixa		código da caixa desejada
	 * @param	string	$periodo	valores válidos: {'mes', '7dias', 'hoje'}
	 * @return	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaConformidade($periodo, $caixa) {
		// Necessita parâmetro $caixa
		if (!$caixa) {
			throw new Zend_Exception ( 'Favor informar o código da caixa desejada.' );
		}
		
		// Retorna datas ajustadas conforme parâmetro $periodo
		$filtroData = $this->defineIntervaloDatas($periodo);
		
		$sql = "
-- Busca quantitativo de solicitações da Infra em conformidade ou não
SELECT
	STATUS,
	COUNT(STATUS)	QTDE
FROM
	(
	-- Busca movimentações...
	SELECT
		MOVI.MOVI_ID_MOVIMENTACAO,
		CASE NVL(MVCO_ID_NAO_CONFORMIDADE, 0)
			WHEN 0 THEN 'CONFORME'
			ELSE 'NÃO CONFORME'
		END									STATUS
	FROM
		SAD_TB_MOVI_MOVIMENTACAO			MOVI
	Left JOIN
		-- ...verificando se está conforme ou não
		SOS_TB_MVCO_MOVIM_N_CONFORM			MVCO ON
			MVCO.MVCO_ID_MOVIMENTACAO		= MOVI.MOVI_ID_MOVIMENTACAO		AND
			MVCO.MVCO_IC_ATIVO_INATIVO		= 'S'
	WHERE
		MOVI.MOVI_ID_CAIXA_ENTRADA			= " . $caixa . "						AND
		MOVI.MOVI_DH_ENCAMINHAMENTO			BETWEEN to_date('" . $filtroData['dataInicio'] . " 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('" . $filtroData['dataTermino'] . " 23:59:59','DD/MM/YYYY HH24:MI:SS')
	)
GROUP BY
	STATUS
ORDER BY
        QTDE Desc
				";
		
		$banco = Zend_Db_Table::getDefaultAdapter();
		
		//return $sql;
		return $banco->fetchPairs($sql);
	}
	
	/**
	 * Busca quantitativo de solicitações por tipo de avaliação
	 *
	 * @param	int		$caixa		código da caixa desejada
	 * @param	string	$periodo	valores válidos: {'mes', '7dias', 'hoje'}
	 * @return	array	$dados
	 * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
	 */
	public function retornaAvaliacoes($periodo, $caixa) {
		// Necessita parâmetro $caixa
		if (!$caixa) {
			throw new Zend_Exception ( 'Favor informar o código da caixa desejada.' );
		}
		
		// Retorna datas ajustadas conforme parâmetro $periodo
		$filtroData = $this->defineIntervaloDatas($periodo);
		
		$sql = "
SELECT
	AVALIACAO,
	COUNT(AVALIACAO)									QTDE
FROM
(
	SELECT
		BASE.MOFA_ID_MOVIMENTACAO,
		NVL(SERV.SAVS_ID_TIPO_SAT, 99)                  ID_AVALIACAO,
        NVL(AVAL.STSA_DS_TIPO_SAT, 'NÃO AVALIADA')		AVALIACAO
	FROM
		(
		SELECT DISTINCT
			MOFA.MOFA_ID_MOVIMENTACAO
			-- MIN(MOFA.MOFA_DH_FASE)					PRIMEIRA_AVALIACAO,
			-- MAX(MOFA.MOFA_DH_FASE)					ULTIMA_AVALIACAO
		FROM
			SAD_TB_MOFA_MOVI_FASE MOFA
		WHERE
			MOFA.MOFA_ID_FASE IN (" . self::FASE_BAIXADA . ") 	AND
			MOFA.MOFA_ID_MOVIMENTACAO IN (
											SELECT
												MOVI_ID_MOVIMENTACAO
											FROM
												SAD_TB_MOVI_MOVIMENTACAO
											WHERE
												MOVI_ID_CAIXA_ENTRADA	= " . $caixa . "	AND
												MOVI_DH_ENCAMINHAMENTO	BETWEEN to_date('" . $filtroData['dataInicio'] . " 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('" . $filtroData['dataTermino'] . " 23:59:58','DD/MM/YYYY HH24:MI:SS')
										)
		-- GROUP BY
		--	MOFA.MOFA_ID_MOVIMENTACAO
	)													BASE
Left JOIN
	SOS_TB_SAVS_AVALIACAO_SERVICO						SERV ON
	SERV.SAVS_ID_MOVIMENTACAO = BASE.MOFA_ID_MOVIMENTACAO
Left  JOIN
	SOS_TB_STSA_TIPO_SATISFACAO							AVAL ON
	AVAL.STSA_ID_TIPO_SAT = SERV.SAVS_ID_TIPO_SAT
)
GROUP BY
	ID_AVALIACAO,
	AVALIACAO
ORDER BY
        QTDE Desc
		";
		
		$banco = Zend_Db_Table::getDefaultAdapter();
		
		//return $sql;
		return $banco->fetchPairs($sql);
	}
	
	/**
	 * 
	 * @param unknown_type $periodo
	 */
	private function defineIntervaloDatas($periodo = 'mes') {
		// Acerta string para uso padronizado
		$periodo = strtolower($periodo);
		
		// Define $periodo padrão, caso não seja informado
		if (!$periodo) {
			/*
			throw new Zend_Exception ('
										Favor informar o período de tempo desejado.<br />
										<br />
										As opções atuais são:<br />
										[mes] para o mês corrente;<br />
										[7dias] para os últimos 7 (sete) dias;<br />
										[hoje] para o dia atual.<br />
									');
			*/
			$periodo = 'mes';
		}
		
		// Cálculos de data
		$mesIni = date('d-M-Y', mktime(0, 0, 0, date('m'), 1, date('Y')));
		$mesFim = date('d-M-Y', mktime(0, 0, 0, date('m') +1, 1 -1, date('Y')));
		$diaHoje = date('d-M-Y');
		$dia7ant = date('d-M-Y', mktime(0, 0, 0, date('m'), date('d') -7, date('Y')));
		
		// Definição do filtro de data conforme parâmetro
		// formato: 01-JAN-2012
		$datas = array(	'mes'	=> array('dataInicio' => $mesIni, 'dataTermino' => $mesFim),
						'7dias'	=> array('dataInicio' => $dia7ant, 'dataTermino' => $diaHoje),
						'hoje'	=> array('dataInicio' => $diaHoje, 'dataTermino' => $diaHoje)
		);
		
		//Zend_Debug::dump($periodo);
		return $datas[$periodo];
	}
	
}








































