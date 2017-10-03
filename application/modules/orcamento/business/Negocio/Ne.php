<?php
/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as regras negociais sobre notas de empenho
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Ne
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Ne extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Noem ();

        // Define a negocio
        $this->_negocio = 'ne';
    }

    /**
     * Sobrescreve a classe incluir da business
     */
    public function incluirDadosImportados(array $dados, $txtName = null)
    {
        // negocio importacao
        $negocioImportarne = new Orcamento_Business_Negocio_Importarne();
        // $sql = "BEGIN";

        $atualizados = 0;
        $inseridos = 0;
        $excluidos = 0;

        foreach ( $dados as $n ){

            $arrayNE[] = array(
                            'IMPD_TX_LINHA'          => $n['IMPD_TX_LINHA'],
                            'NOEM_CD_NOTA_EMPENHO'   => $n['NOEM_CD_NOTA_EMPENHO'],
                            'NOEM_CD_UG_OPERADOR'    => (int)$n['NOEM_CD_UG_OPERADOR'],
                            'NOEM_CD_NE_REFERENCIA'  => $n['NOEM_CD_NE_REFERENCIA'],
                            'NOEM_CD_PT_RESUMIDO'    => $n['NOEM_CD_PT_RESUMIDO'],
                            'NOEM_CD_EVENTO'         => $n['NOEM_CD_EVENTO'],
                            'NOEM_CD_FONTE'          => $n['NOEM_CD_FONTE'],
                            'NOEM_CD_VINCULACAO'     => $n['NOEM_CD_VINCULACAO'],
                            'NOEM_VL_NE'             => $n['NOEM_VL_NE'],
                            'NOEM_CD_ELEMENTO_DESPESA_SUB' => $n['NOEM_CD_ELEMENTO_DESPESA_SUB'],
                            'NOEM_CD_CATEGORIA'      => $n['NOEM_CD_CATEGORIA'],
                            'NOEM_NR_DESPESA'        => $n['NOEM_NR_DESPESA'],
                            'NOEM_DH_NE'             => $n['NOEM_DH_NE'],
                            'NOEM_DT_EMISSAO'        => $n['NOEM_DT_EMISSAO'],
                            'NOEM_DS_OBSERVACAO'     => $n['NOEM_DS_OBSERVACAO'],
                            'NOEM_VL_NE_ACERTADO'    => $n['NOEM_VL_NE_ACERTADO'],
                            'NOEM_CD_UG_FAVORECIDO'  => (int)$n['NOEM_CD_UG_FAVORECIDO'],
                            'NOEM_CD_ESFERA'         => $n['NOEM_CD_ESFERA'],
                            'NOEM_IC_ACERTADO_MANUALMENTE' => $n['NOEM_IC_ACERTADO_MANUALMENTE'],
                            'NOEM_NR_PROCESSO'       => $n['NOEM_NR_PROCESSO']
                            );

        }

        foreach ( $arrayNE as $n) {
            // tratamentos
            $existeNem = $this->existeNE( $n["NOEM_CD_NOTA_EMPENHO"] );
            $existeExec = $this->existeExecucao($n["NOEM_CD_NOTA_EMPENHO"]);
            $negocioDespesa = new Trf1_Orcamento_Negocio_Despesa ();
            // verifica se existe a despesa
            if( $n['NOEM_NR_DESPESA'] ){
                $despesa = $negocioDespesa->retornaDespesa( $n['NOEM_NR_DESPESA'] );

                // se não existe grava o campo como nulo
                if (!$despesa){
                    $n['NOEM_NR_DESPESA'] = '';
                }
            }

            $linha = $n['IMPD_TX_LINHA'];
            unset($n['IMPD_TX_LINHA']);
            unset($n['NOME_DO_ARQUIVO']);

             // atualiza ne que não pode ser apagada por vinculo na tabela de execução
            if($existeExec) {
                $edit = parent::editar ( $n );
                //Zend_Debug::dump( $edit ); die;
                $atualizados += 1;
            }

            // se não tem execução mas ja existe, apaga a ne existente
            if( !$existeExec && $existeNem["NOEM_CD_NOTA_EMPENHO"] != "" ){
                $delete = $this->deleteNE($n["NOEM_CD_NOTA_EMPENHO"]);
                //Zend_Debug::dump( $delete ); die;
                $excluidos += 1;
            }

            // finalmente salva
            $insert = parent::incluir ( $n );
            Zend_Debug::dump( $insert );

            //Zend_Debug::dump( $insert ); die;
            $inseridos += 1;

            $this->gravaImportacao( $txtName, $n, $linha );
        }

            // Atualiza o campo valor acertado das NE'S
            $this->updateValorAcertadoNe();

            $resultado = array(
                'atualizados' => $atualizados,
                'inseridos' => $inseridos,
                'excluidos' => $excluidos,
            );

            return $resultado;
    }

    public function gravaImportacao( $name, $ne, $linha ) {
        $modelImportacao = new Orcamento_Business_Negocio_Importarne();
        return $modelImportacao->incluir( $name, $ne, $linha );
    }

    /*
     * Atualiza o campo acertado
     */
    public function updateValorAcertadoNe(){
        $sql = "
        UPDATE
            CEO.CEO_TB_NOEM_NOTA_EMPENHO
        SET
            NOEM_VL_NE_ACERTADO = NOEM_VL_NE * (-1)
        WHERE
            NOEM_CD_EVENTO IN (
                SELECT EVEN_CD_EVENTO FROM CEO.CEO_TB_EVEN_EVENTO_NE WHERE EVEN_IC_SINAL_EVENTO = 1
            )
        ";
        $this->executaQuery($sql, true);
    }

    public function trataValor( $valor ) {
        $util = new Trf1_Orcamento_Valor ();
        return $util->formataMoedaOrcamento( $valor );
    }

    /**
     * Verifica se a NE já existe
     * @param int $ne Numero da Nota de Empenho
     * @param int $ne Numero da Nota de Empenho
     * @return bool
     */
    public function existeNE($ne)
    {
        $banco = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                SELECT
                    NOEM_CD_NOTA_EMPENHO
                FROM CEO_TB_NOEM_NOTA_EMPENHO
                WHERE NOEM_CD_NOTA_EMPENHO = '$ne'";

        return $banco->fetchRow($sql);
    }

    // Verifica se a execução já existe
    public function existeExecucao($ne, $op)
    {
        $banco = Zend_Db_Table::getDefaultAdapter();
        $sql = "
            SELECT
                EXEC_CD_NOTA_EMPENHO
                FROM CEO_TB_EXEC_EXECUCAO_NE
                WHERE EXEC_CD_NOTA_EMPENHO = '$ne'";

        return $banco->fetchRow($sql);
    }

    /**
     * Verifica se a Referencia existe
     * @param int $ne Numero da Nota de Empenho
     * @return bool
     */
    public function existeReferencia($ne)
    {
        $banco = Zend_Db_Table::getDefaultAdapter();
        $sql = "
                SELECT
                CASE WHEN COUNT(NOEM_CD_NE_REFERENCIA) IS NULL
                    THEN 'true'
                    ELSE 'false'
                    END AS NOTA
                FROM CEO_TB_NOEM_NOTA_EMPENHO
                WHERE NOEM_CD_NOTA_EMPENHO = '$ne' ";

        return $banco->fetchRow($sql);
    }

    /**
     * Exclui uma NE
     * @param int $ne Numero da Nota de Empenho
     * @return bool
     */
    public function deleteNE($ne)
    {
        $where = $this->_model->getAdapter()->quoteInto('NOEM_CD_NOTA_EMPENHO = ?', $ne);
        $this->_model->delete($where);
    }
}