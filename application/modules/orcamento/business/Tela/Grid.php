<?php
/**
 * Contém funcionalidade básicas sobre o grid da aplicação
 * 
 * e-Admin
 * e-Orçamento
 * Business - Tela
 * 
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */

/**
 * Classe para manipulação genérica dos grids do e-Orçamento
 *
 * @category Orcamento
 * @package Orcamento_Business_Tela_Grid
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
 */
class Orcamento_Business_Tela_Grid
{

    /**
     * Define as opções padrão para as ações em massa do grid
     *
     * @var array
     */
    protected $_acoesMassa = array ( 'incluir', 'detalhe', 'editar', 'excluir', 
            'restaurar' );

    /**
     * Método construtor
     *
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function __construct ()
    {
        // faz algo
    }

    /**
     * Cria um grid padrão
     *
     * @param
     *        array Opções Contém diversas opções para confecção da grid
     * @return Bvb_Grid
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    public function criaGrid ( $opcoes )
    {
        // Busca o parâmetro 'dados'
        $dados = $opcoes [ 'dados' ];
        
        // Busca o parâmetro 'chavePrimaria'
        $chavePrimaria = $opcoes [ 'chavePrimaria' ];
        
        // Busca o parâmetro 'detalhes'
        $detalhes = $opcoes [ 'detalhes' ];
        
        // Busca o parâmetro 'ocultos'
        $ocultos = $opcoes [ 'ocultos' ];
        
        if ( $ocultos == null ) {
            $ocultos = array ( 'CAMPO_NAO_EXISTENTE' );
        }
        
        // Busca o parâmetro 'controle'
        $controle = $opcoes [ 'controle' ];
        
        // Busca o parâmetro 'funcionalidade'
        $funcionalidade = $opcoes [ 'funcionalidade' ];
        
        // Busca o parâmetro 'funcionalidade'
        $acoesEmMassa = $opcoes [ 'acoesEmMassa' ];
        
        if ( $acoesEmMassa === null ) {
            // Se parâmetro não for informado, utiliza-se as ações padrão
            $acoesEmMassa = $this->_acoesMassa;
        }
        
        // Objeto grid
        $grid = null;
        
        // Caminho e arquivo de configuração do grid
        $path = APPLICATION_PATH . '/modules/orcamento/business/Tela/configs/';
        $file = 'grid.ini';
        
        // Definições das opções do grid
        $configGrid = new Zend_Config_Ini ( $path . $file, 'production' );
        
        // Cria o grid
        $grid = Bvb_Grid::factory ( 'Table', $configGrid, '' );
        
        // Dados do grid
        $fonteDados = new Bvb_Grid_Source_Array ( $dados );
        $grid->setSource ( $fonteDados );
        
        // Chave primária ou composta
        $fonteDados->setPrimaryKey ( $chavePrimaria );
        
        // Instancia o helper
        $zvhu = new Zend_View_Helper_Url ();
        
        // Define o endereço
        $endereco [ 'module' ] = 'orcamento';
        $endereco [ 'controller' ] = $controle;
        
        // Cria a variável a ser utilizada no grid
        $gridUrl = $zvhu->url ( $endereco, null, true );
        
        // Personalização do grid
        foreach ( $detalhes as $campo => $opcoes ) {
            // Atualiza cada campo com suas respectivas opções
            $grid->updateColumn ( $campo, $opcoes );
        }
        
        // Oculta campos do grid
        $grid->setColumnsHidden ( $ocultos );
        
        // Retorna opções das ações em massa
        $acaoOpcoes = $this->retornaOpcoesAcoesEmMassa ( $gridUrl );
        
        // Define as ações
        $acoes = new Bvb_Grid_Mass_Actions ();
        
        foreach ( array_reverse ( $acoesEmMassa, true ) as $chave => $acao ) {
            // Adiciona cada ação em massa com suas respectivas opções
            $acoes->addMassAction ( $acaoOpcoes [ $acao ] [ 'url' ], 
            $acaoOpcoes [ $acao ] [ 'caption' ], 
            $acaoOpcoes [ $acao ] [ 'confirm' ], 
            $acaoOpcoes [ $acao ] [ 'imagem' ] );
        }
        
        // Define as ações em massa no grid
        $grid->setMassActions ( $acoes );
        
        // Nome padrão dos arquivos
        $nomeArquivo = $this->retornaNomeArquivo ( $funcionalidade );
        $grid->setDeployOption ( 'name', $nomeArquivo );
        
        // Exportação
        $exportacoes = array ( 'print', 'pdf', 'excel', 'csv', 'word' );
        $grid->setExport ( $exportacoes );
        
        // Retorna o objeto grid
        return $grid;
    }

    /**
     * Retorna nome do arquivo para o caso de exportação de dados
     *
     * @param string $funcionalidade
     *        Nome da funcionalida, tipicamente o mesmo utilizado no título da
     *        tela, para compor o nome do arquivo a ser exportado
     * @param boolean $bData
     *        Informe se exibe ou não a data e hora da criação do arquivo
     * @return string
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaNomeArquivo ( $funcionalidade, $bData = true )
    {
        // Sistema
        $nomeArquivo = 'e-Orçamento - ';
        
        // Funcionalidade
        $nomeArquivo .= $funcionalidade;
        
        if ( $bData ) {
            // Define o nome do arquivo a exportar
            $nomeArquivo .= ' - ' . date ( 'Y-m-d-H-i-s' );
        }
        
        // Devolve o nome do arquivo
        return $nomeArquivo;
    }

    /**
     * Retorna opções padrão para montagem do grid
     *
     * @param string $gridUrl
     *        Informa a url atual
     * @return array
     * @author Anderson Sathler M. Ribeiro <asathler@gmail.com>
     */
    private function retornaOpcoesAcoesEmMassa ( $gridUrl )
    {
        // Ação de incluisão de um registro
        $acao [ 'incluir' ] = array ( 'url' => $gridUrl . '/incluir', 
                'caption' => 'Incluir novo registro', 'confirm' => '', 
                'imagem' => 'incluir' );
        
        // Ação para exibição de detalhe
        $acao [ 'detalhe' ] = array ( 'url' => $gridUrl . '/detalhe/cod/', 
                'caption' => 'Visualizar registro selecionado', 'confirm' => '', 
                'imagem' => 'detalhe' );
        
        // Ação de edição de único registro
        $acao [ 'editar' ] = array ( 'url' => $gridUrl . '/editar/cod/', 
                'caption' => 'Editar registro selecionado', 'confirm' => '', 
                'imagem' => 'editar' );
        
        // Ação de exclusão de um ou mais registros
        $acao [ 'excluir' ] = array ( 'url' => $gridUrl . '/excluir/cod/', 
                'caption' => 'Excluir um ou mais registros selecionados', 
                'confirm' => '', 'imagem' => 'excluir' );
        
        // Ação de leitura de um registro
        $acao [ 'leitura' ] = array ( 'url' => $gridUrl . '/leitura/cod/', 
                'caption' => 'Ler o informativo selecionado', 
                'confirm' => '', 'imagem' => 'leitura' );
        
        $msgCaption = 'Restaurar um ou mais registros logicamente excluídos';
        // Ação de restauração de um ou mais registros logicamente excluídos
        $acao [ 'restaurar' ] = array ( 'url' => $gridUrl . '/restaurar/cod/', 
                'caption' => $msgCaption, 'confirm' => '', 
                'imagem' => 'restaurar' );
        
        // Ação de importar
        $acao [ 'importar' ] = array ( 'url' => $gridUrl . '/importar', 
                'caption' => 'Importar dados', 'confirm' => '', 
                'imagem' => 'importar' );
        
        // Devolve as opções das ações em massa
        return $acao;
    }

}
