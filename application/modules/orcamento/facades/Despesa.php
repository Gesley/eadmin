<?php
/**
 * Contém classe de fachada para chamadas vindas, tipicamente, das controllers
 * 
 * e-Admin
 * e-Orçamento
 * Facade
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Contém as funcionalidades disponíveis sobre despesa, através de camada
 * intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Despesa
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Despesa extends Orcamento_Facade_Base
{

    public function __construct ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Trf1_Orcamento_Negocio_Despesa ();
        
        // Define a controle desta action
        $this->_controle = 'despesa';
    }

    public function retornaListaResumida ( $funcionalidade )
    {
        // Função chamada no index da controller
        
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemSimplificada ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DS_DESPESA' => array ( 'title' => 'Descrição', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'SG_FAMILIA_RESPONSAVEL' => array ( 'title' => 'Responsável', 
                        'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'TIDE_DS_TIPO_DESPESA' => array ( 
                        'title' => 'Caráter da despesa', 'abbr' => '' ), 
                'VL_DESPESA_SECOR' => array ( 'title' => 'Valor aprovado', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaOrcamentaria ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemOrcamento ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'ESFE_DS_ESFERA' => array ( 'title' => 'Esfera', 'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'TIDE_DS_TIPO_DESPESA' => array ( 
                        'title' => 'Caráter da despesa', 'abbr' => '' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaFinanceira ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemFinanceiro ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'incluir', 'detalhe', 'editar', 'excluir' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DESP_CD_FONTE' => array ( 'title' => 'Fonte', 'abbr' => '' ), 
                'DESP_CD_VINCULACAO' => array ( 'title' => 'Vinculação', 
                        'abbr' => '' ), 
                'DESP_CD_CATEGORIA' => array ( 'title' => 'Categoria', 
                        'abbr' => '' ), 
                'TREC_DS_TIPO_RECURSO' => array ( 'title' => 'Tipo recurso', 
                        'abbr' => '' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaPlanejamentoEstrategico ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemPlanejamento ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'incluir', 'detalhe', 'editar', 'excluir' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'PORC_DS_TIPO_ORCAMENTO' => array ( 
                        'title' => 'Tipo de orçamento', 'abbr' => '' ), 
                'POBJ_DS_OBJETIVO' => array ( 'title' => 'Objetivo estratégico', 
                        'abbr' => '' ), 
                'PPRG_DS_PROGRAMA' => array ( 'title' => 'Programa', 
                        'abbr' => '' ), 
                'POPE_DS_TIPO_OPERACIONAL' => array ( 
                        'title' => 'Tipo operacional', 'abbr' => '' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaContrato ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemContrato ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'incluir', 'detalhe', 'editar', 'excluir' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DS_DESPESA' => array ( 'title' => 'Descrição', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'CTRD_ID_CONTRATO_DESPESA' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'CTRD_NR_CONTRATO' => array ( 'title' => 'Contrato', 
                        'abbr' => '' ), 
                'CTRD_NM_EMPRESA_CONTRATADA' => array ( 'title' => 'Empresa', 
                        'abbr' => '' ), 
                'CTRD_DT_INICIO_VIGENCIA' => array ( 
                        'title' => 'Início vigência', 'abbr' => '' ), 
                'CTRD_DT_TERMINO_VIGENCIA' => array ( 
                        'title' => 'Término vigência', 'abbr' => '' ) );
        
        $camposOcultos = array ( 'CTRD_ID_CONTRATO_DESPESA', 
                'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaValor ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemRecursos ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'incluir', 'detalhe', 'editar', 'excluir' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DS_DESPESA' => array ( 'title' => 'Descrição', 'abbr' => '' ), 
                'VL_DESPESA_RESPONSAVEL' => array ( 
                        'title' => 'Proposta inicial', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ), 
                'VL_DESPESA_DIPLA' => array ( 'title' => 'Ajuste setorial', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_DESPESA_CONGRESSO' => array ( 'title' => 'Ajuste ao limite', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_DESPESA_SECOR' => array ( 'title' => 'Orçamento aprovado', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_MAX_MENSAL_AUTORIZADO' => array ( 
                        'title' => 'Limite mensal máximo', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaSaldoEmpenho ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        // @todo Ver o código da listagem a seguir
        $dados = $negocio->ListagemEmpenho ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'detalhe' );
        $camposDetalhes = array ( 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DESP_DS_ADICIONAL' => array ( 'title' => 'Descrição', 
                        'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'NOEM_CD_NOTA_EMPENHO' => array ( 'title' => 'Nota de empenho', 
                        'abbr' => '', 'format' => 'Notas' ), 
                'NOEM_DS_OBSERVACAO' => array ( 'title' => 'Descrição da NE', 
                        'abbr' => '', 'format' => 'Naturezadespesa' ), 
                'VR_EMPENHADO' => array ( 'title' => 'Valor', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ), 
                'VR_A_EXECUTAR' => array ( 'title' => 'Saldo da NE', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ) );
        
        $camposOcultos = array ();
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaDistribuicao ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemDistribuicao ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'detalhe' );
        $camposDetalhes = array ( 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DESP_DS_ADICIONAL' => array ( 'title' => 'Descrição', 
                        'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'SG_FAMILIA_RESPONSAVEL' => array ( 'title' => 'Responsável', 
                        'abbr' => '' ), 
                'DESP_CD_FONTE' => array ( 'title' => 'Fonte', 'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'VR_PROPOSTA_APROVADA' => array ( 
                        'title' => 'Proposta aprovada', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ), 
                'VR_PROPOSTA_RECEBIDA' => array ( 
                        'title' => 'Proposta recebida', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ), 
                'VR_PROPOSTA_A_RECEBER' => array ( 
                        'title' => 'Proposta a receber', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ) );
        
        $camposOcultos = array ();
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaListaCompleta ( $funcionalidade )
    {
        // Busca os dados a serem exibidos
        $negocio = new $this->_classeNegocio ();
        $dados = $negocio->retornaListagemCompleta ();
        $chavePrimaria = $negocio->chavePrimaria ();
        
        // Geração do grid
        $acoes = array ( 'incluir', 'detalhe', 'editar', 'excluir' );
        $camposDetalhes = array ( 
                'DESP_AA_DESPESA' => array ( 'title' => 'Ano', 'abbr' => '' ), 
                'DESP_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'DS_DESPESA' => array ( 'title' => 'Descrição', 'abbr' => '' ), 
                'DESP_CD_UG' => array ( 'title' => 'UG', 'abbr' => '' ), 
                'SG_FAMILIA_RESPONSAVEL' => array ( 'title' => 'Responsável', 
                        'abbr' => '' ), 
                'ESFE_DS_ESFERA' => array ( 'title' => 'Esfera', 'abbr' => '' ), 
                'DESP_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'DESP_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'TIDE_DS_TIPO_DESPESA' => array ( 
                        'title' => 'Caráter da despesa', 'abbr' => '' ), 
                'DESP_CD_FONTE' => array ( 'title' => 'Fonte de recursos', 
                        'abbr' => '' ), 
                'DESP_CD_VINCULACAO' => array ( 'title' => 'Vinculação', 
                        'abbr' => '' ), 
                'DESP_CD_CATEGORIA' => array ( 
                        'title' => 'Categoria de recursos', 'abbr' => '' ), 
                'TREC_DS_TIPO_RECURSO' => array ( 'title' => 'Tipo de recurso', 
                        'abbr' => '' ), 
                'PORC_DS_TIPO_ORCAMENTO' => array ( 
                        'title' => 'Tipo de orçamento', 'abbr' => '' ), 
                'POBJ_DS_OBJETIVO' => array ( 'title' => 'Objetivo estratégico', 
                        'abbr' => '' ), 
                'PPRG_DS_PROGRAMA' => array ( 'title' => 'Programa estratégico', 
                        'abbr' => '' ), 
                'POPE_DS_TIPO_OPERACIONAL' => array ( 
                        'title' => 'Tipo operacional', 'abbr' => '' ), 
                'CTRD_NR_CONTRATO' => array ( 'title' => 'Número do contrato', 
                        'abbr' => '' ), 
                'CTRD_NM_EMPRESA_CONTRATADA' => array ( 
                        'title' => 'Nome da contratada' ), 
                'VL_DESPESA_RESPONSAVEL' => array ( 
                        'title' => 'Proposta inicial', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ), 
                'VL_DESPESA_DIPLA' => array ( 'title' => 'Ajuste setorial', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_DESPESA_CONGRESSO' => array ( 'title' => 'Ajuste ao limite', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_DESPESA_SECOR' => array ( 'title' => 'Orçamento aprovado', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ), 
                'VL_MAX_MENSAL_AUTORIZADO' => array ( 
                        'title' => 'Limite mensal máximo', 'abbr' => '', 
                        'format' => 'Numerocor', 'class' => 'valorgrid' ) );
        
        $camposOcultos = array ( 'DESP_CD_PROGRAMA', 'DESP_CD_OBJETIVO', 
                'DESP_CD_TIPO_ORCAMENTO', 'DESP_VL_MAX_MENSAL_AUTORIZADO', 'VL_SOLICITACAO_ACRESCIMO_SOLI', 
            'VL_SOLICITACAO_ACRESCIMO_SOLI' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade );
        
        return $grid;
    }

    public function retornaRegistro ( $chavePrimaria )
    {
        $negocio = new $this->_classeNegocio ();
        $registro = $negocio->retornaDespesa ( $chavePrimaria );
        
        return $registro;
    }

    public function copiaDespesas ( $ano, $faseJuste = null )
    {
        //$resultado = $this->_negocio->copiaDespesas ( $ano );
        
        $negocioNovo = new Orcamento_Business_Negocio_Despesa();
        $resultado = $negocioNovo->copiaDespesas ( $ano, $faseJuste );
        
        return $resultado;
    }

}