<?php

/**
 * @category	TRF1
 * @package	Trf1_Sosti_Negocio_TipoServico
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Daniel Rodrigues
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Tipos de Serviço do Sistema
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 */
class Trf1_Sosti_Negocio_TipoServico {

    protected $db;
    protected $tb_tipo_servico;

    function __construct() {
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->tb_tipo_servico = new Application_Model_DbTable_Sosti_SosTbSetpTipoServico();
    }

    /**
     * Função que busca os tipos de serviço do sistema
     * @param int $idGrupo Define o ID do grupo para a busca dos valores
     * @return Array Dados selecionados da base de dados
     */
    public function getTipoServicoByGrupo($idGrupo) {

        $sql = $this->db->query("
            SELECT DISTINCT 
            SETP.SETP_ID_SERVICO,
            SETP.SETP_DS_SERVICO
            FROM SOS_TB_SETP_TIPO_SERVICO  SETP 
            LEFT JOIN SOS_TB_SSER_SERVICO  SSER ON 
            SETP.SETP_ID_SERVICO = SSER.SSER_ID_TIPO_SERVICO
            WHERE SSER.SSER_ID_TIPO_SERVICO IS NOT NULL
            AND SSER.SSER_ID_GRUPO = $idGrupo
        ");

        return $sql->fetchAll();
    }

    /**
     * Função que busca os tipos de serviço do sistema que estão contidos em um arranjo de Grupos
     * @param array $idGrupos Define os IDs dos grupos para a busca dos valores
     * @return Array Dados selecionados da base de dados
     */
    public function getTipoServicoByGrupos($idGrupos) {

        //VERIFICA SE O PARÂMETRO É UM ARRAY DE GRUPOS OU APENAS UM GRUPO
        if (is_array($idGrupos)) {
            //CRIANDO GRUPO DE IDS
            $clausula = implode($idGrupos, ' , ');
        }else{
            //RECEBENDO UNICO ID GRUPO PARA A PESQUISA
            $clausula = $idGrupos;
        }

        $sql = $this->db->query("
            SELECT DISTINCT 
            SETP.SETP_ID_SERVICO,
            SETP.SETP_DS_SERVICO
            FROM SOS_TB_SETP_TIPO_SERVICO  SETP 
            LEFT JOIN SOS_TB_SSER_SERVICO  SSER ON 
            SETP.SETP_ID_SERVICO = SSER.SSER_ID_TIPO_SERVICO
            WHERE SSER.SSER_ID_TIPO_SERVICO IS NOT NULL
            AND SSER.SSER_ID_GRUPO IN ($clausula)
        ");

        return $sql->fetchAll();
    }

}

?>
