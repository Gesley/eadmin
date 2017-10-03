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
 * Contém as funcionalidades disponíveis sobre notas de empenho, através de
 * camada intermediária.
 *
 * @category Orcamento
 * @package Orcamento_Facade_Ne
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Facade_Ne
{

    /**
     * Classe negocial
     *
     * @var object $_classeNegocio
     */
    protected $_negocio = null;

    /**
     * Controller desta facade
     *
     * @var string
     */
    protected $_controle = 'ne';

    /**
     * Método construtor da classe
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ()
    {
        // Instancia a classe negocial
        $this->_negocio = new Orcamento_Business_Negocio_Ne ();
    }

    /**
     * Retorna o objeto grid já com os dados a serem apresentados, bem como
     * demais configurações necessárias para o mesmo
     *
     * @param string $funcionalidade
     *        Informao o nome da funcionalidade, tipicamente a mesma informada
     *        no título da tela para composição do nome do arquivo a exportar
     * @return BvB_Grid $grid
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaListagem ( $funcionalidade )
    {
        // Retorna os dados a serem exibidos
        $dados = $this->_negocio->retornaListagem ();
        
        // Retorna o campo chave primária (ou composta)
        $chavePrimaria = $this->retornaChavePrimaria ();
        
        // Personaliza a exibição dos campos no grid
        $camposDetalhes = array ( 
                'NOEM_CD_UG_FAVORECIDO' => array ( 'title' => 'UG', 
                        'abbr' => '' ), 
                'NOEM_CD_NOTA_EMPENHO' => array ( 'title' => 'Nota de empenho', 
                        'abbr' => '', 'format' => 'Notas' ), 
                'NOEM_CD_NE_REFERENCIA' => array ( 'title' => 'Referência', 
                        'abbr' => '', 'format' => 'Notas' ), 
                'NOEM_NR_DESPESA' => array ( 'title' => 'Despesa', 'abbr' => '' ), 
                'NOEM_CD_ESFERA' => array ( 'title' => 'Esfera', 'abbr' => '' ), 
                'NOEM_CD_FONTE' => array ( 'title' => 'Fonte', 'abbr' => '' ), 
                'NOEM_CD_PT_RESUMIDO' => array ( 'title' => 'PTRES', 
                        'abbr' => '' ), 
                'NOEM_CD_ELEMENTO_DESPESA_SUB' => array ( 
                        'title' => 'Natureza da despesa', 'abbr' => '', 
                        'format' => 'Naturezadespesa' ), 
                'NOEM_DH_NE' => array ( 'title' => 'Emissão', 'abbr' => '' ), 
                'NOEM_DS_OBSERVACAO' => array ( 'title' => 'Descrição', 
                        'abbr' => '' ), 
                'NOEM_CD_EVENTO' => array ( 'title' => 'Evento', 'abbr' => '' ), 
                'NOEM_VL_NE_ACERTADO' => array ( 'title' => 'Valor', 
                        'abbr' => '', 'format' => 'Numerocor', 
                        'class' => 'valorgrid' ) );
        
        // Informa, se houver, os campos a ocultar do grid
        $camposOcultos = array ();
        
        // Define as ações em massa
        $acoes = array ( 'detalhe', 'editar' );
        
        // Instancia o grid
        $classeGrid = new Orcamento_Business_Tela_Grid ();
        
        // Define o grid
        $grid = $classeGrid->criaGrid ( $dados, $chavePrimaria, 
        $camposDetalhes, $camposOcultos, $this->_controle, $funcionalidade, 
        $acoes );
        
        // Devolve o objeto grid
        return $grid;
    }

    /**
     * Retorna um registro, apresentando os campos conforme ação informada
     *
     * @param string $acao
     *        Ação para escolha dos campos
     * @param array $chavePrimaria
     *        Chave primária (ou composta) para identificação do registro
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaRegistro ( $acao, $chavePrimaria )
    {
        // Retorna o registro a ser exibido
        $registro = $this->_negocio->retornaRegistro ( $acao, $chavePrimaria );
        
        // Devolve o registro
        return $registro;
    }

    /**
     * Retorna os campos da chave primária (ou composta)
     *
     * @return unknown
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function retornaChavePrimaria ()
    {
        // Retorna o campo chave primária (ou composta)
        $chavePrimaria = $this->_negocio->chavePrimaria ();
        
        // Devolve a chave primária (ou composta)
        return $chavePrimaria;
    }

    public function editar ( $dados )
    {
        $resultado = $this->_negocio->editar ( $dados );
        
        return $resultado;
    }

}