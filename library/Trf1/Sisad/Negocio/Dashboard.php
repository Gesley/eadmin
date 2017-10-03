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
class Trf1_Sisad_Negocio_Dashboard
{
	/* ************************************************************
	 * Definições iniciais
	 *********************************************************** */
	
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
	 * @param	string	$periodo	valores válidos: {'mes', '7dias', 'hoje'}
	 * @return	array	$dados
	 * @author	Luiz Mendes de Moraes Junior [luiz.moraes@trf1.jus.br]
	 */
	public function retornaDadosDocumentos($params) {
        
		// Necessita parâmetro $caixa
		if (!$params["per"]) {
			throw new Exception('Favor informar o período desejado.');
		}
		// Retorna datas ajustadas conforme parâmetro $periodo
		$filtroData = $this->defineIntervaloDatas($params["per"]);
        
        if($params["secao"] == 'TRF1'){
            $sql = "SELECT  DOCM_SG_SECAO_GERADORA SIGLA,
                        COUNT(*) TOTAL 
                   FROM SAD_TB_DOCM_DOCUMENTO DOCM
                  WHERE DOCM_ID_TIPO_DOC NOT IN(152,160)
                    AND DOCM_IC_ATIVO = 'S'
                    AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
               GROUP BY DOCM_SG_SECAO_GERADORA
               ORDER BY 1";
        }else if($params["secao"] == 'PROC'){
            $sql = "SELECT  DOCM_SG_SECAO_GERADORA SIGLA,
                        COUNT(*) TOTAL 
                   FROM SAD_TB_DOCM_DOCUMENTO DOCM
                  WHERE DOCM_ID_TIPO_DOC = 152
                    AND DOCM_IC_ATIVO = 'S'
                    AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
               GROUP BY DOCM_SG_SECAO_GERADORA
               ORDER BY 1";
        }else{
            $sql = "SELECT AQAT.AQAT_DS_ATIVIDADE TIPO,
                           COUNT(*) QTDE
                      FROM SAD_TB_DOCM_DOCUMENTO DOCM
                INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                        ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                     INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                        ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                     WHERE DOCM_SG_SECAO_GERADORA = '".$params["secao"]."'
                       AND DOCM_ID_TIPO_DOC = 152    
                       AND DOCM_IC_ATIVO = 'S'
                       AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
                  GROUP BY AQAT_DS_ATIVIDADE                
                  ORDER BY 1";
        }
		
		$banco = Zend_Db_Table::getDefaultAdapter();
                
		return $banco->fetchPairs($sql);
	}
    
    public function getTotalDocumentos(){
        // Retorna datas ajustadas conforme parâmetro $periodo
		$filtroData = $this->defineIntervaloDatas('inicio');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SUM(COUNT(*)) TOTAL
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM
                           WHERE DOCM_ID_TIPO_DOC NOT IN(152,160)
                             AND DOCM_IC_ATIVO = 'S'
                             AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
                           GROUP BY DOCM_SG_SECAO_GERADORA");
        return $stmt->fetchAll();
    }
    
    public function getTotalProcs(){
        // Retorna datas ajustadas conforme parâmetro $periodo
		$filtroData = $this->defineIntervaloDatas('inicio');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SUM(COUNT(*)) TOTAL
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM
                           WHERE DOCM_ID_TIPO_DOC = 152
                             AND DOCM_IC_ATIVO = 'S'
                             AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
                           GROUP BY DOCM_SG_SECAO_GERADORA");
        return $stmt->fetchAll();
    }
    
    public function getTotalProcessos($params){
        $filtroData = $this->defineIntervaloDatas('inicio');
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $stmt = $db->query("SELECT SUM(COUNT(*)) TOTAL
                            FROM SAD_TB_DOCM_DOCUMENTO DOCM
                      INNER JOIN SAD_TB_AQVP_VIA_PCTT AQVP
                              ON AQVP.AQVP_ID_PCTT = DOCM.DOCM_ID_PCTT
                           INNER JOIN  SAD_TB_AQAT_ATIVIDADE AQAT
                              ON AQVP.AQVP_ID_AQAT = AQAT.AQAT_ID_ATIVIDADE
                           WHERE DOCM_SG_SECAO_GERADORA = '$params'
                             AND DOCM_ID_TIPO_DOC = 152    
                             AND DOCM_IC_ATIVO = 'S'
                             AND DOCM.DOCM_DH_CADASTRO BETWEEN to_date('". $filtroData['dataInicio'] ." 00:00:00','DD/MM/YYYY HH24:MI:SS') AND to_date('". $filtroData['dataTermino'] ." 23:59:59','DD/MM/YYYY HH24:MI:SS')
                        GROUP BY AQAT_DS_ATIVIDADE ");
        return $stmt->fetchAll();
    }
    
    
	/**
	 * 
	 * @param unknown_type $periodo
	 */
        
	public function defineIntervaloDatas($periodo) {
		// Acerta string para uso padronizado
		$periodo = strtolower($periodo);

        // Define $periodo padrão, caso não seja informado
		if (!$periodo) {
			/*
			throw new Exception('
								Favor informar o período de tempo desejado.<br />
								<br />
								As opções atuais são:<br />
								[mes] para o mês corrente;<br />
								[mensal] para o mês anterior;<br />
								[7dias] para os últimos 7 (sete) dias;<br />
								[hoje] para o dia atual.<br />
								');
			*/
			$periodo = 'mes';
		}
		
		// Cálculos de data
		$mesIni     = date('d/m/Y', mktime(0, 0, 0, date('m'), 1, date('Y')));
		$mesFim     = date('d/m/Y', mktime(0, 0, 0, date('m') +1, 1 -1, date('Y')));
		$mensal      = date('d/m/Y', mktime(0, 0, 0, date('m') -1, date('d') , date('Y')));
		$diaHoje    = date('d/m/Y');
		$dia7ant    = date('d/m/Y', mktime(0, 0, 0, date('m'), date('d') -7, date('Y')));
		$inicio     = date('d/m/Y', mktime(0, 0, 0, 01, 01, 2011));
        
		// Definição do filtro de data conforme parâmetro
		// formato: 01-JAN-2012
		$datas = array(	'mes'       => array('dataInicio' => $mesIni, 'dataTermino' => $mesFim),
						'mensal'       => array('dataInicio' => $mensal, 'dataTermino' => $diaHoje),
						'7dias'     => array('dataInicio' => $dia7ant, 'dataTermino' => $diaHoje),
						'inicio'    => array('dataInicio' => $inicio, 'dataTermino' => $diaHoje),
						'hoje'      => array('dataInicio' => $diaHoje, 'dataTermino' => $diaHoje)
		);
		
		//Zend_Debug::dump($periodo);
		return $datas[$periodo];
	}
	
}
