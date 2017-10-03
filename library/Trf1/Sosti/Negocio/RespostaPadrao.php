<?php

/**
 * @category	TRF1
 * @package	Trf1_Sosti_Negocio_RespostaPadrao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author	Daniel Rodrigues
 * @license	FREE, keep original copyrights
 * @version	controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe negocial sobre o SOSTI - Respostas Padrões do Sistema
 * 
 * ====================================================================================================
 * LICENSA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 */
class Trf1_Sosti_Negocio_RespostaPadrao {

    protected $db;
    protected $tb_resposta_padrao;
    protected $tb_resposta_padrao_auditoria;

    function __construct() {
        //ADAPTADOR DO BANCO DE DADOS
        $this->db = Zend_Db_Table::getDefaultAdapter();
        $this->db->query("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY HH24:MI:SS'")->execute();

        //INSTANCIA DA TABELA DE RESPOSTA PADRÃO
        $this->tb_resposta_padrao = new Application_Model_DbTable_Sosti_SosTbRepdRespostaPadrao();

        //INSTANCIA DA TABELA DE RESPOSTA PADRÃO
        $this->tb_resposta_padrao_auditoria = new Application_Model_DbTable_Sosti_SosTbRepdAuditoria();
    }

    /**
     * Função para inserção de Resposta Padrão no banco de dados
     * @param type $data
     * @author Daniel Rodrigues
     */
    public function addRespostaPadrao(array $data) {

        try {

            $this->db->beginTransaction();

            //TRATAMENTO PARA ASPAS
            $data['REPD_DS_RESPOSTA_PADRAO'] = str_replace("'", "''", $data['REPD_DS_RESPOSTA_PADRAO']);
            //TRATAMENTO PARA O ZEND ACEITAR O LIMITE MÁXIMO DE CARACTERES
            $data['REPD_DS_RESPOSTA_PADRAO'] = new Zend_Db_Expr("'" . $data['REPD_DS_RESPOSTA_PADRAO'] . "'");
            $newRow = $this->tb_resposta_padrao->createRow($data);
            $newRow->save();

            //********************* AUDITORIA *****************************************************

            $repd = $newRow->toArray();
            $idRepd = $repd['REPD_ID_RESPOSTA_PADRAO'];

            //MONTANDO O ARRAY DA AUDITORIA
            $data_audit = array();
            $data_audit['REPD_TS_OPERACAO'] = $data['REPD_DH_CADASTRO'];
            $data_audit['REPD_IC_OPERACAO'] = 'I';
            $data_audit['REPD_CD_MATRICULA_OPERACAO'] = $data['REPD_CD_MATRICULA_CADASTRO'];
            $data_audit['REPD_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $data_audit['REPD_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
            $data_audit['OLD_REPD_ID_RESPOSTA_PADRAO'] = null;
            $data_audit['NEW_REPD_ID_RESPOSTA_PADRAO'] = $idRepd;
            $data_audit['OLD_REPD_CD_MATRICULA_CADASTRO'] = null;
            $data_audit['NEW_REPD_CD_MATRICULA_CADA'] = $data['REPD_CD_MATRICULA_CADASTRO'];
            $data_audit['OLD_REPD_DH_CADASTRO'] = null;
            $data_audit['NEW_REPD_DH_CADASTRO'] = $data['REPD_DH_CADASTRO'];
            $data_audit['OLD_REPD_ID_GRUPO'] = null;
            $data_audit['NEW_REPD_ID_GRUPO'] = $data['REPD_ID_GRUPO'];
            $data_audit['OLD_REPD_ID_SERVICO'] = null;
            $data_audit['NEW_REPD_ID_SERVICO'] = $data['REPD_ID_SERVICO'];
            $data_audit['OLD_REPD_NM_RESPOSTA_PADRAO'] = null;
            $data_audit['NEW_REPD_NM_RESPOSTA_PADRAO'] = $data['REPD_NM_RESPOSTA_PADRAO'];
            $data_audit['OLD_REPD_DS_RESPOSTA_PADRAO'] = null;
            $data_audit['NEW_REPD_DS_RESPOSTA_PADRAO'] = $data['REPD_DS_RESPOSTA_PADRAO'];
            $data_audit['OLD_REPD_IC_CONFIDENCIALIDADE'] = null;
            $data_audit['NEW_REPD_IC_CONFIDENCIALIDADE'] = $data['REPD_IC_CONFIDENCIALIDADE'];

            $audit = $this->tb_resposta_padrao_auditoria->createRow($data_audit);
            $audit->save();
            //********************* AUDITORIA *****************************************************

            $this->db->commit();
        } catch (Exception $e) {

            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Função que busca as Respostas Padrões do sistema de acordo com o parâmetro de busca
     * @param String $matricula Matricula do usuário
     * @param Int $idGrupo Id do Grupo da caixa
     * @return Array Array de valores
     * @author Daniel Rodrigues
     */
    public function listRespostaPadrao(array $data) {
        
        $sql = "
            SELECT * FROM SOS_TB_REPD_RESPOSTA_PADRAO
            INNER JOIN SOS_TB_SETP_TIPO_SERVICO
            ON REPD_ID_SERVICO = SETP_ID_SERVICO
            WHERE
            REPD_CD_MATRICULA_CADASTRO = ?  OR
            (REPD_ID_GRUPO = ? AND REPD_IC_CONFIDENCIALIDADE = 'G')
            ORDER BY REPD_ID_RESPOSTA_PADRAO DESC
        ";
        return $this->db->fetchAll($sql, $data);
    }

    /**
     * Função que busca uma Resposta Padrão de acordo com o ID
     * @param Int $idResposta Id da Resposta Padrão
     * @return Array Array de valores
     * @author Daniel Rodrigues
     */
    public function buscaRespostaPadrao($idResposta) {

        $sql = "
            SELECT *FROM SOS_TB_REPD_RESPOSTA_PADRAO
            INNER JOIN SOS_TB_SETP_TIPO_SERVICO
            ON REPD_ID_SERVICO = SETP_ID_SERVICO
            WHERE
            REPD_ID_RESPOSTA_PADRAO = ?
        ";
        return $this->db->fetchRow($sql, $idResposta);
    }

    /**
     * Função que busca uma Resposta Padrão de acordo com o ID
     * @param Int $idResposta Id da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function editRespostaPadrao($data) {

        //OBTENDO A DH DA ALTERAÇÃO
        $dh_alteracao = $data['REPD_DH_ALTERACAO'];
        unset($data['REPD_DH_ALTERACAO']);

        //VERIFICA SE EXISTE O REGISTRO NO BANCO DE DADOS
        $resposta = $this->tb_resposta_padrao->find($data['REPD_ID_RESPOSTA_PADRAO'])->current();
        //OBTENDO OS DAODS ANTES DA ALTERAÇÃO
        $repd_old = $resposta->toArray();

        if (!is_null($resposta)) {

            //INICIANDO A TRANSAÇÃO NO BANCO DE DADOS
            $this->db->beginTransaction();

            //TRATAMENTO PARA ASPAS
            $data['REPD_DS_RESPOSTA_PADRAO'] = str_replace("'", "''", $data['REPD_DS_RESPOSTA_PADRAO']);
            //TRATAMENTO PARA O ZEND ACEITAR O LIMITE MÁXIMO DE CARACTERES
            $data['REPD_DS_RESPOSTA_PADRAO'] = new Zend_Db_Expr("'" . $data['REPD_DS_RESPOSTA_PADRAO'] . "'");
            //ALTERA O REGISTRO
            $resposta->setFromArray($data);
            $resposta->save();

            //********************* AUDITORIA ********************
            //OBTENDO OS DADOS DEPOIS DA ALTERAÇÃO
            $repd = $resposta->toArray();

            //TRATAMENTO PARA ASPAS
            $repd['REPD_DS_RESPOSTA_PADRAO'] = str_replace("'", "''", $repd['REPD_DS_RESPOSTA_PADRAO']);
            $repd_old['REPD_DS_RESPOSTA_PADRAO'] = str_replace("'", "''", $repd_old['REPD_DS_RESPOSTA_PADRAO']);
            //TRATAMENTO PARA O ZEND ACEITAR O LIMITE MÁXIMO DE CARACTERES
            $repd['REPD_DS_RESPOSTA_PADRAO'] = new Zend_Db_Expr("'" . $repd['REPD_DS_RESPOSTA_PADRAO'] . "'");
            $repd_old['REPD_DS_RESPOSTA_PADRAO'] = new Zend_Db_Expr("'" . $repd_old['REPD_DS_RESPOSTA_PADRAO'] . "'");
            
            //MONTANDO O ARRAY DA AUDITORIA
            $data_audit = array();
            $data_audit['REPD_TS_OPERACAO'] = $dh_alteracao;
            $data_audit['REPD_IC_OPERACAO'] = 'A';
            $data_audit['REPD_CD_MATRICULA_OPERACAO'] = $data['REPD_CD_MATRICULA_CADASTRO'];
            $data_audit['REPD_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
            $data_audit['REPD_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
            $data_audit['OLD_REPD_ID_RESPOSTA_PADRAO'] = $repd['REPD_ID_RESPOSTA_PADRAO'];
            $data_audit['NEW_REPD_ID_RESPOSTA_PADRAO'] = $repd['REPD_ID_RESPOSTA_PADRAO'];
            $data_audit['OLD_REPD_CD_MATRICULA_CADASTRO'] = $repd['REPD_CD_MATRICULA_CADASTRO'];
            $data_audit['NEW_REPD_CD_MATRICULA_CADA'] = $repd['REPD_CD_MATRICULA_CADASTRO'];
            $data_audit['OLD_REPD_DH_CADASTRO'] = $repd['REPD_DH_CADASTRO'];
            $data_audit['NEW_REPD_DH_CADASTRO'] = $repd['REPD_DH_CADASTRO'];
            $data_audit['OLD_REPD_ID_GRUPO'] = $repd['REPD_ID_GRUPO'];
            $data_audit['NEW_REPD_ID_GRUPO'] = $repd['REPD_ID_GRUPO'];
            $data_audit['OLD_REPD_ID_SERVICO'] = $repd_old['REPD_ID_SERVICO'];
            $data_audit['NEW_REPD_ID_SERVICO'] = $repd['REPD_ID_SERVICO'];
            $data_audit['OLD_REPD_NM_RESPOSTA_PADRAO'] = $repd_old['REPD_NM_RESPOSTA_PADRAO'];
            $data_audit['NEW_REPD_NM_RESPOSTA_PADRAO'] = $repd['REPD_NM_RESPOSTA_PADRAO'];
            $data_audit['OLD_REPD_DS_RESPOSTA_PADRAO'] = $repd_old['REPD_DS_RESPOSTA_PADRAO'];
            $data_audit['NEW_REPD_DS_RESPOSTA_PADRAO'] = $repd['REPD_DS_RESPOSTA_PADRAO'];
            $data_audit['OLD_REPD_IC_CONFIDENCIALIDADE'] = $repd_old['REPD_IC_CONFIDENCIALIDADE'];
            $data_audit['NEW_REPD_IC_CONFIDENCIALIDADE'] = $repd['REPD_IC_CONFIDENCIALIDADE'];

            $audit = $this->tb_resposta_padrao_auditoria->createRow($data_audit);
            $audit->save();

            //********************* AUDITORIA ********************

            $this->db->commit();
        } else {
            $this->db->rollBack();
            throw new Exception('Violação de parâmetro.');
        }
    }

    /**
     * Função que busca uma Resposta Padrão de acordo com o ID
     * @param Int $idResposta Id da Resposta Padrão
     * @author Daniel Rodrigues
     */
    public function deleteRespostaPadrao(array $data) {

        //OBTENDO OS DADOS NECESSARIOS PARA A EXCLUSAO
        $idResposta = $data['REPD_ID_RESPOSTA_PADRAO'];
        $idGrupo = $data['REPD_ID_GRUPO'];
        //MONTANDO A CLAUSULA WHERE
        $where = "REPD_ID_RESPOSTA_PADRAO = $idResposta AND REPD_ID_GRUPO = $idGrupo";
        //OBTENDO OS DADOS DO REGISTRO ANTES DA EXCLUSÃO
        $resposta = $this->tb_resposta_padrao->find($idResposta)->current();
        $repd = $resposta->toArray();

        //INICIANDO A TRANSAÇÃO NO BANCO DE DADOS
        $this->db->beginTransaction();

        //EXCLUI O REGISTRO DO BANCO
        $rowsAfected = $this->tb_resposta_padrao->delete($where);

        //********************* AUDITORIA ********************
        
        //TRATAMENTO PARA ASPAS
        $repd['REPD_DS_RESPOSTA_PADRAO'] = str_replace("'", "''", $repd['REPD_DS_RESPOSTA_PADRAO']);
        $repd['REPD_NM_RESPOSTA_PADRAO'] = str_replace("'", "''", $repd['REPD_DS_RESPOSTA_PADRAO']);
        $repd['REPD_DS_RESPOSTA_PADRAO'] = new Zend_Db_Expr("'" . $repd['REPD_DS_RESPOSTA_PADRAO'] . "'");
            
        //MONTANDO O ARRAY DA AUDITORIA
        $data_audit = array();
        $data_audit['REPD_TS_OPERACAO'] = $data['REPD_DH_EXCLUSAO'];
        $data_audit['REPD_IC_OPERACAO'] = 'E';
        $data_audit['REPD_CD_MATRICULA_OPERACAO'] = $data['REPD_CD_MATRICULA_CADASTRO'];
        $data_audit['REPD_CD_MAQUINA_OPERACAO'] = substr($_SERVER['REMOTE_ADDR'], 0, 50);
        $data_audit['REPD_CD_USUARIO_SO'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 50);
        $data_audit['OLD_REPD_ID_RESPOSTA_PADRAO'] = $repd['REPD_ID_RESPOSTA_PADRAO'];
        $data_audit['NEW_REPD_ID_RESPOSTA_PADRAO'] = null;
        $data_audit['OLD_REPD_CD_MATRICULA_CADASTRO'] = $repd['REPD_CD_MATRICULA_CADASTRO'];
        $data_audit['NEW_REPD_CD_MATRICULA_CADA'] = null;
        $data_audit['OLD_REPD_DH_CADASTRO'] = $repd['REPD_DH_CADASTRO'];
        $data_audit['NEW_REPD_DH_CADASTRO'] = null;
        $data_audit['OLD_REPD_ID_GRUPO'] = $repd['REPD_ID_GRUPO'];
        $data_audit['NEW_REPD_ID_GRUPO'] = null;
        $data_audit['OLD_REPD_ID_SERVICO'] = $repd['REPD_ID_SERVICO'];
        $data_audit['NEW_REPD_ID_SERVICO'] = null;
        $data_audit['OLD_REPD_NM_RESPOSTA_PADRAO'] = $repd['REPD_NM_RESPOSTA_PADRAO'];
        $data_audit['NEW_REPD_NM_RESPOSTA_PADRAO'] = null;
        $data_audit['OLD_REPD_DS_RESPOSTA_PADRAO'] = $repd['REPD_DS_RESPOSTA_PADRAO'];
        $data_audit['NEW_REPD_DS_RESPOSTA_PADRAO'] = null;
        $data_audit['OLD_REPD_IC_CONFIDENCIALIDADE'] = $repd['REPD_IC_CONFIDENCIALIDADE'];
        $data_audit['NEW_REPD_IC_CONFIDENCIALIDADE'] = null;

        $audit = $this->tb_resposta_padrao_auditoria->createRow($data_audit);
        $audit->save();

        //********************* AUDITORIA ********************
        $this->db->commit();

        if ($rowsAfected <= 0) {
            $this->db->rollBack();
            throw new Exception('O registro não foi encontrado na base de dados.');
        }
    }

    /**
     * Função que busca uma Resposta Padrão de acordo com os parâmetros de busca
     * @param array $data Dados necessários para a pesquisa
     * @param String $idGrupos Uma String com os IDs dos Grupos separados por vírgula
     * @return Array Array de valores
     * @author Daniel Rodrigues
     */
    public function pesquisaRespostaPadrao(array $data, $idGrupos) {

        //VARIAVEIS
        //TRATAMENTO PARA ASPAS
        $nome = str_replace("'", "''", $data['REPD_NM_RESPOSTA_PADRAO']);
        $idServico = $data["REPD_ID_SERVICO"];
        $descricao = str_replace("'", "''", $data['REPD_DS_RESPOSTA_PADRAO']);
        $matricula = $data['REPD_CD_MATRICULA_CADASTRO'];
        $where = "";

        //VERIFICANDO OS CRITÉRIOS DE BUSCA
        if($data['REPD_ID_SERVICO'] != 0){
            $where .= "SETP.SETP_ID_SERVICO = $idServico AND";
        }
        if ($data['REPD_NM_RESPOSTA_PADRAO'] != "") {
            $where .= " UPPER(REPD.REPD_NM_RESPOSTA_PADRAO) LIKE UPPER('%$nome%') AND ";
        }
        if ($data['REPD_DS_RESPOSTA_PADRAO'] != "") {
            $where .= " UPPER(REPD_DS_RESPOSTA_PADRAO) LIKE UPPER('%$descricao%') AND ";
        }
   
        //MONSTANDO A QUERY DE PESQUISA
        $sql = "
            SELECT *FROM SOS_TB_REPD_RESPOSTA_PADRAO REPD
            INNER JOIN SOS_TB_SETP_TIPO_SERVICO SETP
            ON REPD.REPD_ID_SERVICO = SETP.SETP_ID_SERVICO
            WHERE
            $where
            (REPD_ID_GRUPO IN ($idGrupos) OR
            REPD.REPD_CD_MATRICULA_CADASTRO = '$matricula')
        ";

        return $this->db->fetchAll($sql);
    }

}

?>
