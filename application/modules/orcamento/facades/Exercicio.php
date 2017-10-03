<?php

/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre exercicio, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Exercicio
 * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Exercicio extends Orcamento_Facade_Base {

    /**
     * Método construtor da classe
     *
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     * @var Orcamento_Business_Negocio_Exercicio
     * @var Orcamento_Business_Negocio_FaseAnoExercicio
     */
    public function init () {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Exercicio();

        // Instancia a classe negocial de Fase Ano Exercicio
        $this->_negocioFaseAnoExercicio = new Orcamento_Business_Negocio_FaseAnoExercicio();

        // 
        // Define a controle desta action
        $this->_controle = 'exercicio';
    }

    /**
     * Método para incluir dados
     * O metodo foi alterado devido a necessidade do cliente, com 2 inclusões
     * @var array $dados - dados do exercicio
     * @var array $dadosFaseExercicio - conversao de dados para fase_exercicio
     * 
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     * @var Orcamento_Facade_Exercicio
     */
    public function incluir ($dados) {
        /* monta o array de dados para inserir ( FANE_ID_FASE_EXERCICIO sempre 
          sera status 1 ) */
        $dadosFaseExercicio = array(
            'FANE_NR_ANO' => $dados['ANOE_AA_ANO'],
            'FANE_ID_FASE_EXERCICIO' => 1
        );

        try {
            $this->_negocio->incluir($dados);
            $this->_negocioFaseAnoExercicio->incluir($dadosFaseExercicio);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    /**
     * Método para incluir dados
     * O metodo foi alterado devido a necessidade do cliente, com 2 inclusões
     * @var array $dados - dados do exercicio
     * 
     * @author Gesley Batista Rodrigues <rodrigues.gesley@gmail.com>
     * @var Orcamento_Facade_Exercicio
     */
    public function editar ($dados) {
        // pesquisa a fase pelo ano exercicio a ser editado
        $anoExercicio = new Orcamento_Model_DbTable_FaseAnoExercicio();
        $ano = $anoExercicio->buscarAnoExercicio($dados['ANOE_AA_ANO']);

        // monta o array de dados do exercicio a ser editado
        $dadosExercicio = array(
            'ANOE_AA_ANO' => $dados['ANOE_AA_ANO'],
            'ANOE_DS_OBSERVACAO' => $dados['ANOE_DS_OBSERVACAO'],
            'FASE_ID_EXERCICIO' => $dados['FASE_ID_EXERCICIO'],
            'ANOE_CD_MATRICULA_INCLUSAO' => $dados['ANOE_CD_MATRICULA_INCLUSAO']
        );

        // monta o array da fase para edição
        $dadosFaseExercicio = array(
            'FANE_ID_FASE_ANO_EXERCICIO' => $ano->FANE_ID_FASE_ANO_EXERCICIO,
            'FANE_NR_ANO' => $dados['ANOE_AA_ANO'],
            'FANE_ID_FASE_EXERCICIO' => $dados['FASE_ID_EXERCICIO']
        );

        /* atualiza o exercicio ( somente a descrição e a fase ) nas tabelas de
          exercicio e fase */
        try {
            $this->_negocio->editar($dadosExercicio);
            $this->_negocioFaseAnoExercicio->editar($dadosFaseExercicio);
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

    /**
     * Retorna opções para confecção do objeto grid padrão
     *
     * @return array
     * @author Gesley Rodrigues <rodrigues.gesley@gmail.com>
     */
    public function retornaOpcoesGrid () {
        // Personaliza a exibição dos campos no grid
        $detalhes = array(
            'ANOE_AA_ANO' => array('title' => 'Ano Exercicio', 'abbr' => ''),
            'ANOE_DS_OBSERVACAO' => array('title' => 'Descrição', 'abbr' => ''),
            'FASE_NM_FASE_EXERCICIO' => array('title' => 'Status', 'abbr' => '')
        );

        /*
          // Informa, se houver, os campos a ocultar do grid
          $ocultos = array ( 'PTRS_CD_MATRICULA_EXCLUSAO',
          'PTRS_DH_EXCLUSAO_LOGICA' );
         */

        // Combina as opções num array
        $opcoes ['detalhes'] = $detalhes;
        // $opcoes [ 'ocultos' ] = $ocultos;
        $opcoes ['controle'] = $this->_controle;

        // Devolve o array de opções
        return $opcoes;
    }

    public function retornaComboPerfil () {
        $cache = new Trf1_Orcamento_Cache ();
        $cacheId = $cache->retornaID_Combo('uo');
        $dados = $cache->lerCache($cacheId);

        if ($dados === false) {
            $sessaoOrcamento = new Zend_Session_Namespace('sessaoOrcamento');
            $perfil = $sessaoOrcamento->perfil;

//            $arPerfis = "";
//            if ($perfil == Orcamento_Business_Dados::PERMISSAO_DIPOR) {
//                $arPerfis = "AND FASE_ID_FASE_EXERCICIO IN (4, 5, 6)";

            $sql = "
SELECT
    FASE_ID_FASE_EXERCICIO,
    FASE_NM_FASE_EXERCICIO
FROM
    CEO.CEO_TB_FASE_FASE_EXERCICIO
WHERE
    FASE_DH_EXCLUSAO_LOGICA IS NULL
$arPerfis
ORDER BY
    FASE_ID_FASE_EXERCICIO ASC ";

            $banco = Zend_Db_Table::getDefaultAdapter();

            $dados = $banco->fetchPairs($sql);
        }

        return $dados;
    }

}
