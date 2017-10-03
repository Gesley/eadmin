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
 * Contém as regras negociais sobre esfera
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_Esfera
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_FaseAnoExercicio extends Orcamento_Business_Negocio_Base
{

    /**
     * Iniciador chamado no final do método construtor {@link __construct()}
     * 
     * @var Orcamento_Model_DbTable_FaseAnoExercicio
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function init()
    {
        // Instancia a classe model
        $this->_model = new Orcamento_Model_DbTable_FaseAnoExercicio();

        // Define a negocio
        $this->_negocio = 'faseano';
    }

    /* deprecated
    public function retornaAnoExercicio($anoExercicio)
    {
        return $this->_model->buscarAnoExercicio($anoExercicio);
    }

    public function setStatusAnoExercicio($exercicio, $status)
    {
        return $this->_model->setStatusAnoExercicio($exercicio, $status);
    }
    */
    
    
    public function retornaFasePorExercicio($exercicio)
    {
        $sql = "
                SELECT 

        -- ANOE.ANOE_AA_ANO,
        FASE_ID_FASE_EXERCICIO
        -- ,FASE_NM_FASE_EXERCICIO
FROM CEO_TB_ANOE_ANO_EXERCICIO ANOE

LEFT JOIN CEO_TB_FANE_FASE_ANO_EXERCICIO FANE ON FANE.FANE_NR_ANO = ANOE.ANOE_AA_ANO
LEFT JOIN CEO_TB_FASE_FASE_EXERCICIO FASE ON FASE.FASE_ID_FASE_EXERCICIO = FANE.FANE_ID_FASE_EXERCICIO

WHERE ANOE.ANOE_AA_ANO = '$exercicio'
                    ";
        $banco = Zend_Db_Table::getDefaultAdapter ();
        
        return $banco->fetchOne($sql);
    }
    
    /**
     * Realiza a exclusão fisica de um exercicio
     *
     * @param array $chaves
     *        Chaves primárias (ou composta) para exclusão de um ou mais
     *        registros
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function exclusaoFisica($exercicio)
    {
        // Exclui um ou mais registros
        $sql = "
                    DELETE FROM CEO.CEO_TB_FANE_FASE_ANO_EXERCICIO
                    WHERE FANE_NR_ANO = '$exercicio'
                ";
        return $this->executaQuery($sql);
    }
    
    /**
     * ...
     * 
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaSqlFaseExercicio ()
    {
        $sql = "
SELECT
    FANE.FANE_NR_ANO,
    FANE.FANE_ID_FASE_EXERCICIO,
    FASE.FASE_NM_FASE_EXERCICIO
FROM
    CEO_TB_FANE_FASE_ANO_EXERCICIO FANE
INNER JOIN
    (
    SELECT
        FANE_NR_ANO,
        MAX(FANE_ID_FASE_ANO_EXERCICIO) ULTIMA_FASE
    FROM
        CEO_TB_FANE_FASE_ANO_EXERCICIO
    GROUP BY
        FANE_NR_ANO
    ) ULTF ON
        ULTF.FANE_NR_ANO = FANE.FANE_NR_ANO AND
        ULTF.ULTIMA_FASE = FANE.FANE_ID_FASE_ANO_EXERCICIO
Left JOIN
    CEO_TB_FASE_FASE_EXERCICIO FASE ON
        FASE.FASE_ID_FASE_EXERCICIO = FANE.FANE_ID_FASE_EXERCICIO
	            ";
         
        // Devolve a instrução sql
        return $sql;
    }
    
    /**
     * ...
     * 
     * @param unknown $ano
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaFaseAtualPorExercicio ( $ano )
    {
        $sqlBase = $this->retornaSqlFaseExercicio ();
        
        $sql = "
SELECT
    FANE_ID_FASE_EXERCICIO
FROM
    ( $sqlBase )
WHERE
    FANE_NR_ANO = $ano
                ";
        
        $banco = Zend_Db_Table::getDefaultAdapter ();
        
        $fase = $banco->fetchOne ( $sql );
        
        return $fase;
    }
    
    /**
     * Verifica qual perfil pode alterar quais campos de valores da despesa
     *  
     * @param numeric $ano
     * @param string $campoValor
     * @return boolean
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function matrizAcesso ( $ano, $campoValor )
    {
        if ( !$ano ) {
            return false;
        }
        
        if ( !$campoValor ) {
            return false;
        }
        
        // Sessão
        $sessao = new Orcamento_Business_Sessao ();
        $perfilFull = $sessao->retornaPerfil ();
        $perfil = $perfilFull [ 'perfil' ];
        
        // Retorna fase atual do exercício
        $fase = $this->retornaFaseAtualPorExercicio ( $ano );
        
        // Teste - Início
        // $perfil = Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR;
        // $perfil = Orcamento_Business_Dados::PERMISSAO_DIPOR;
        // $perfil = Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO;
        // $perfil = Orcamento_Business_Dados::PERMISSAO_SECCIONAL;
        // $perfil = Orcamento_Business_Dados::PERMISSAO_SECRETARIA;
        
        // $fase = Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
        // $fase = Orcamento_Business_Dados::FASE_EXERCICIO_RESPONSAVEL;
        // $fase = Orcamento_Business_Dados::FASE_EXERCICIO_CONSOLIDACAO;
        // $fase = Orcamento_Business_Dados::FASE_EXERCICIO_LIBERADA;
        // $fase = Orcamento_Business_Dados::FASE_EXERCICIO_EXECUCAO;
        
        // Zend_Debug::dump ( $ano );
        // Zend_Debug::dump ( $fase );
        // Zend_Debug::dump ( $perfil );
        // Teste - Término
        
        if ( $perfil == Orcamento_Business_Dados::PERMISSAO_DESENVOLVEDOR ) {
            return true;
        }
        
        // Perfis envolvidos em edição de campos de valor
        $dipla = Orcamento_Business_Dados::PERMISSAO_PLANEJAMENTO;
        $secretaria = Orcamento_Business_Dados::PERMISSAO_SECRETARIA;
        $secao = Orcamento_Business_Dados::PERMISSAO_SECCIONAL;
        $dipor = Orcamento_Business_Dados::PERMISSAO_DIPOR;
        
        // Fases aplicáveis
        $definicao = Orcamento_Business_Dados::FASE_EXERCICIO_DEFINICAO;
        $responsavel = Orcamento_Business_Dados::FASE_EXERCICIO_RESPONSAVEL;
        $consolidacao = Orcamento_Business_Dados::FASE_EXERCICIO_CONSOLIDACAO;
        $liberada = Orcamento_Business_Dados::FASE_EXERCICIO_LIBERADA;
        $execucao = Orcamento_Business_Dados::FASE_EXERCICIO_EXECUCAO;
        
        // Campos
        $cAjuste = 'VL_DESPESA_AJUSTE_DIPLA';
        $cResponsavel = 'VL_DESPESA_SOLIC_RESPONSAVEL';
        $cDipla = 'VL_DESPESA_DIPLA';
        $cLimite = 'VL_DESPESA_CONGRESSO';
        $cDipor = 'VL_DESPESA_SECOR';
        
        // Permissionamento de edição por campo
        $acesso [ $dipla ] [ $definicao ] [ $cAjuste ] = true; 
        $acesso [ $dipla ] [ $definicao ] [ $cDipla ] = true;
        $acesso [ $dipla ] [ $definicao ] [ $cLimite ] = true;
        
        $acesso [ $dipla ] [ $consolidacao ] [ $cAjuste ] = true;
        $acesso [ $dipla ] [ $consolidacao ] [ $cDipla ] = true;
        $acesso [ $dipla ] [ $consolidacao ] [ $cLimite ] = true;
        
        $acesso [ $secretaria ] [ $responsavel ] [ $cResponsavel ] = true;
        $acesso [ $secao ] [ $responsavel ] [ $cResponsavel ] = true;
        
        $acesso [ $dipor ] [ $liberada ] [ $cDipor ] = true;
        $acesso [ $dipor ] [ $execucao ] [ $cDipor ] = true;
        
        $bPodeEditar = $acesso [ $perfil ] [ $fase ] [ $campoValor ];
        
        if ( ! $bPodeEditar ) {
            $bPodeEditar = false;
        }
        
        // Devolve true ou false sobre a permissão de edição do campo
        return $bPodeEditar;
    }
    
    /**
     * Apresenta dados (código e descrição) para montagem de combos
     * 
     * @return  array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaCombo ()
    {       
            
            // Retorna instrução sql para listagem dos dados
            $sql = "
SELECT 
    FASE_ID_FASE_EXERCICIO,
    FASE_NM_FASE_EXERCICIO
FROM CEO_TB_FASE_FASE_EXERCICIO
    WHERE FASE_CD_MATRICULA_EXCLUSAO IS NULL
        AND FASE_DH_EXCLUSAO_LOGICA IS NULL
                    ";
            
            // Retorna default adapter de banco
            $banco = Zend_Db_Table::getDefaultAdapter ();
        
            // Retorna todos os registros e campos da instrução sql
            $dados = $banco->fetchPairs ( $sql );        
        
        // Devolve os dados
        return $dados;
    }


}