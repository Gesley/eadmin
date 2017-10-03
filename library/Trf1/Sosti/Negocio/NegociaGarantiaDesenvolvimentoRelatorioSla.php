<?php
/**
 * @category	TRF1
 * @package		Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimentoRelatorioSla
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leonan Alves dos Anjos
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Garantia dos serviços do desenvolvimento Relacionamento Garantia com o Relatório SLA
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
class Trf1_Sosti_Negocio_NegociaGarantiaDesenvolvimentoRelatorioSla
{
	public function __construct() {

	}
    /**
     * @abstract Verifica se um conjunto do tipo array de números de solicitação é 
     * considerada garantia, tais como 
     * Garantia: Aceita
     * Garantia: Recusada; garantia nao aceita.
     * Garantia: Recusada a garantia e não foi avaliada.
     * @param array $numerosSolicSomente
     * @return bollean
     */
    public function getVerificaGarantia($numerosSolicSomente) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
        $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
        
        $stmt = "";
        $stmt .= " 
                SELECT COUNT(*) COUNT
                ";
        $stmt .= "
        FROM
        -- documento
        SAD_TB_DOCM_DOCUMENTO DOCM
        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
        --garantia
        INNER JOIN SOS_TB_NEGA_NEGOCIA_GARANTIA NEGA
        ON NEGA.NEGA_ID_MOVIMENTACAO = MODO_MOVI.MODO_ID_MOVIMENTACAO
        WHERE 
        (
            NEGA_IC_ACEITE = 'A' /*Garantia Aceita*/ 
            OR 
            NEGA_IC_CONCORDANCIA = 'D' /*Garantia: Recusa garantia nao aceita.*/
            OR
            (NEGA_IC_ACEITE = 'R' AND NEGA_IC_CONCORDANCIA IS NULL) /*Garantia: Recusa garantia e não avaliada.*/
        )
        AND $clausulaInDocm
        ORDER BY MODO_ID_MOVIMENTACAO DESC
        ";
        $count = $db->query($stmt)->fetch();
        if($count != false){
            if($count["COUNT"] > 0){
                return true;
            }else{
                return false;
            }
        }
    }
    
    /**
     * @abstract Verifica se um conjunto do tipo array de números de solicitação é 
     * considerada garantia, tais como 
     * Garantia: Aceita
     * Garantia: Recusada; garantia nao aceita.
     * Garantia: Recusada a garantia e não foi avaliada.
     * e RETORNA os registros em garantia
     * @param array $numerosSolicSomente
     * @return array
     */
    public function getGarantias($numerosSolicSomente) {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $ClausulaIN = new App_Utilidades_Consultas_ClausulaIN();
        $clausulaInDocm = $ClausulaIN->condicaoIN_para_muitos_valores($numerosSolicSomente, 'SAD_TB_DOCM_DOCUMENTO', 'DOCM_NR_DOCUMENTO', ',');
        
        $stmt = "";
        $stmt .= "
                SELECT DOCM_NR_DOCUMENTO, NEGA.*
                ";
        $stmt .= "
        FROM
        -- documento
        SAD_TB_DOCM_DOCUMENTO DOCM
        -- documento movimentacao
        INNER JOIN SAD_TB_MODO_MOVI_DOCUMENTO MODO_MOVI
        ON  DOCM.DOCM_ID_DOCUMENTO     = MODO_MOVI.MODO_ID_DOCUMENTO
        --garantia
        INNER JOIN SOS_TB_NEGA_NEGOCIA_GARANTIA NEGA
        ON NEGA.NEGA_ID_MOVIMENTACAO = MODO_MOVI.MODO_ID_MOVIMENTACAO
        WHERE 
        (
            NEGA_IC_ACEITE = 'A' /*Garantia Aceita*/ 
            OR 
            NEGA_IC_CONCORDANCIA = 'D' /*Garantia: Recusa garantia nao aceita.*/
            OR
            (NEGA_IC_ACEITE = 'R' AND NEGA_IC_CONCORDANCIA IS NULL) /*Garantia: Recusa garantia e não avaliada.*/
        )
        AND $clausulaInDocm
        ORDER BY MODO_ID_MOVIMENTACAO DESC
        ";
        return $db->query($stmt)->fetchAll();
    }
	
}