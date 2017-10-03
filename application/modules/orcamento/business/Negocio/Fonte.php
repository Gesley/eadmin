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
 * Contém as regras negociais sobre FONTE
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Fonte
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_Fonte extends Orcamento_Business_Negocio_Base {

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     *
     */
    public function init ()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_Font();

        // Define a negocio
        $this->_negocio = 'fonte';
    }

    /**
     * AJAX COM AS FONTES DO SISTEMA
     */
    public function getFonteAjax ($texto)
    {
        // Instrução sql
        $sql = "
            SELECT
              UPPER(
                    TRIM(FONT_CD_FONTE) || ' - ' ||
                    TRIM(FONT_DS_FONTE)
              ) AS label

            FROM
              CEO.CEO_TB_FONT_FONTE
            WHERE
              FONT_DH_EXCLUSAO_LOGICA IS NULL AND
              UPPER(
                    TRIM(FONT_CD_FONTE) || ' - ' ||
                    TRIM(FONT_DS_FONTE)
              ) LIKE UPPER ('%$texto%')

            ORDER BY
              FONT_CD_FONTE
              ";

        // Retorna default adapter de banco
        $banco = Zend_Db_Table::getDefaultAdapter();

        // Executa a query informada, retornando os registros conforme $texto
        $dados = $banco->fetchAll($sql);

        // Devolve os dados encontrados
        return $dados;
    }

    /**
     * @param int $ano
     * @param int $ft fonte
     * @param string $d termo de pesquisa
     * @return array
     */
    public function retornaComboDespesaPorAnoFonte ( $ano = 0, $ft = 0, $ptres = '', $ug = '', $d = '' )
    {

        //$ano, $fonte, $ptres, $ug, $term
        $condicaoResponsaveis = CEO_PERMISSAO_RESPONSAVEIS;

        // Busca perfil
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();
        $perfil = $perfilFull [ 'perfil' ];

        $negocio = new Trf1_Orcamento_Negocio_Despesa ();
        $fasesExercicios = $negocio->retornaSqlFaseExercicio ();

        $condicaoFaseExercicio = "";
        if ( $perfil != Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR &&
            $perfil != Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO ) {
            $condicaoFaseExercicio = " AND FANE.FANE_ID_FASE_EXERCICIO <> ";
            $condicaoFaseExercicio .= Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
        }
        
        $sqlptres = "";        
        $sqlug = "";

        if ($ug != ''){
            $sqlug = "DESP_CD_UG = $ug AND";
        }
        if($ptres != ""){
            $sqlptres = "DESP_CD_PT_RESUMIDO = $ptres AND ";
        }

        $sql = "
                SELECT
                    DESP.DESP_NR_DESPESA,
                    /* Descrição para o combo */
                    DESP.DESP_NR_DESPESA || ' - ' ||
                    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
                    DESP.DESP_DS_ADICIONAL || ' [' ||
                    DESP.DESP_AA_DESPESA || '] : ' ||
                    DESP.DESP_CD_UG || ' : ' ||
                    DESP.DESP_CD_PT_RESUMIDO
                     AS DS_COMBO_DESPESA
                FROM
                    CEO_TB_DESP_DESPESA DESP
                Left JOIN
                    CEO_TB_EDSB_ELEMENTO_SUB_DESP EDSB ON
                        EDSB.EDSB_CD_ELEMENTO_DESPESA_SUB = DESP.DESP_CD_ELEMENTO_DESPESA_SUB
                Left JOIN
                    CEO_TB_RESP_RESPONSAVEL					RESP ON
                        RESP.RESP_CD_RESPONSAVEL			= DESP.DESP_CD_RESPONSAVEL
                Left JOIN
                    RH_CENTRAL_LOTACAO						RHCL ON
                        RHCL.LOTA_COD_LOTACAO				= RESP.RESP_CD_LOTACAO					AND
                        RHCL.LOTA_SIGLA_SECAO				= RESP.RESP_DS_SECAO
                Left JOIN
                    (
                    $fasesExercicios
                    )                                       FANE ON
                        FANE.FANE_NR_ANO                    = DESP.DESP_AA_DESPESA
                WHERE
                    DESP_DH_EXCLUSAO_LOGICA IS Null AND
                    DESP_AA_DESPESA = $ano AND
                    DESP_CD_FONTE   = $ft AND
                    $sqlptres
                    $sqlug

                    /* Descrição para o combo */
                    DESP.DESP_NR_DESPESA || ' - ' ||
                    EDSB.EDSB_DS_ELEMENTO_DESPESA_SUB || ' - ' ||
                    DESP.DESP_DS_ADICIONAL || ' [' ||
                    DESP.DESP_AA_DESPESA || '] - ' ||
                    DESP.DESP_CD_UG

                    Like '%$d%'

                $condicaoResponsaveis
                $condicaoFaseExercicio

                ORDER BY
                    DESP.DESP_NR_DESPESA
                ";



        $banco = Zend_Db_Table::getDefaultAdapter ();

        $dados = $banco->fetchAll ( $sql );

        return $dados;
    }

    public function retornaSqlAlteraDespesaFONTE ( $ano, $fOld, $fNovo, array $d )
    {
        if ( !is_array ( $d ) ) {
            $msg = 'Parâmetro $d deve ser um array de despesas.';
            throw new Zend_Exception ( $msg );
        }

        $registros = count($d);
        $sqls = array();

        if( $registros > 1000 ) {
            $arrd = array_chunk($d, 1000);

            foreach ($arrd as $value) {
                try {
                    $despesas = implode ( ', ', $value );
                } catch ( Exception $e ) {
                    $msg = 'Erro ao separar as despesas.';
                    throw new Zend_Exception ( $msg );
                }


                $sqls[] = "
                UPDATE
                    CEO_TB_DESP_DESPESA
                SET
                    DESP_CD_FONTE = $fNovo
                WHERE
                    DESP_DH_EXCLUSAO_LOGICA IS Null AND
                    DESP_AA_DESPESA = $ano AND
                    DESP_CD_FONTE = $fOld AND
                    DESP_NR_DESPESA IN ( $despesas )
                    ";
            }

            return $this->executaQuery ( $sqls, true );

        } else {

            try {
                $despesas = implode ( ', ', $d );
            } catch ( Exception $e ) {
                $msg = 'Erro ao separar as despesas.';
                throw new Zend_Exception ( $msg );
            }



            //
            $sql = "
    UPDATE
        CEO_TB_DESP_DESPESA
    SET
        DESP_CD_FONTE = $fNovo
    WHERE
        DESP_DH_EXCLUSAO_LOGICA IS Null AND
        DESP_AA_DESPESA = $ano AND
        DESP_CD_FONTE = $fOld AND
        DESP_NR_DESPESA IN ( $despesas )
                    ";

            return $this->executaQuery ( $sql, true );
        }
    }

    /**
     * Retorna array contendo as ids para uso no cache
     *
     * @return string array
     */
    public function retornaCacheIds ( $acao = null )
    {
        // Instancia o cache
        $cache = new Trf1_Cache ();

        // Retorna o nome negocial
        $negocio = $this->_negocio;

        // Id para listagem
        $id [ 'index' ] = $cache->retornaID_Listagem ( 'orcamento', $negocio );

        // Id para combo
        $id [ 'combo' ] = $cache->retornaID_Combo ( 'orcamento', $negocio );

        // Determina qual valor será retornado
        $retorno = ( $acao != null ? $id [ $acao ] : $id );

        // Devolve o id, conforme $acao informada
        return $retorno;
    }

}
