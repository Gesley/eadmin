<?php

/**
 * Classe para manipulação genérica dos grids do e-Orçamento
 * 
 * @category	TRF1
 * @package		Trf1_Orcamento_Grid
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Anderson Sathler M. Ribeiro [asathler@gmail.com]
 * @license		FREE, keep original copyrights
 * ====================================================================================================
 * LICENSA (português)
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 * @tutorial
 * a descrever...
 */
class Trf1_Orcamento_Grid {

    /**
     * Classe construtora
     *
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function __construct () {
        //
    }

    /**
     * Cria um grid padrão
     * 
     * @param	string	$controller		Nome da controller que chama o grid
     * @param	array	$dados			Contém o array (recordset) com os registros desejados
     * @param	array	$chavePrimaria	Chave primária - ou composta
     * @param	string	$nomeArquivo	Nome do arquivo (sem extensão) para exportação
     * @param	array	$acoesMassa		Contém as ações em massa a serem exibidas no grid
     * @return	object	Grid
     * @author	Anderson Sathler M. Ribeiro [asathler@gmail.com]
     */
    public function criaGrid ($controller, $dados, $chavePrimaria, $nomeArquivo, $acoesMassa) {
        // Objeto grid
        $grid = null;

        // Definições e criação do grid
        $configGrid = new Zend_Config_Ini(APPLICATION_PATH . '/configs/grid.ini', 'production');
        $grid = Bvb_Grid::factory('Table', $configGrid, '');

        // Dados do grid
        $fonteDados = new Bvb_Grid_Source_Array($dados);
        $grid->setSource($fonteDados);

        // Chave primária ou composta
        $fonteDados->setPrimaryKey($chavePrimaria);

        $zvhu = new Zend_View_Helper_Url ();
        $endereco = array('module' => 'orcamento', 'controller' => $controller);
        $gridUrl = $zvhu->url($endereco, null, true);

        $fc = Zend_Controller_Front::getInstance();

        // Ações em massa
        $acaoOpcoes = array('incluir' => array('url' => $gridUrl . '/incluir', 'caption' => 'Incluir novo registro', 'confirm' => '', 'imagem' => 'incluir'),
            'importar' => array('url' => $gridUrl . '/importar', 'caption' => 'Importar dados', 'confirm' => '', 'imagem' => 'importar'),
            'detalhe' => array('url' => $gridUrl . '/detalhe/cod/', 'caption' => 'Visualizar registro selecionado', 'confirm' => '', 'imagem' => 'detalhe'),
            'editar' => array('url' => $gridUrl . '/editar/cod/', 'caption' => 'Editar registro selecionado', 'confirm' => '', 'imagem' => 'editar'),
            'editarcontrato' => array('url' => $fc->getBaseUrl() . '/orcamento/contrato/index/CTRD_NR_DESPESA/', 'caption' => 'Editar contrato selecionado', 'confirm' => '', 'imagem' => 'editarcontrato'),
            'excluir' => array('url' => $gridUrl . '/excluir/cod/', 'caption' => 'Excluir um ou mais registros selecionados', 'confirm' => '', 'imagem' => 'excluir')
        );
        $acoes = new Bvb_Grid_Mass_Actions ();

        foreach (array_reverse($acoesMassa, true) as $chave => $acao) {
            $acoes->addMassAction($acaoOpcoes [$acao] ['url'], $acaoOpcoes [$acao] ['caption'], $acaoOpcoes [$acao] ['confirm'], $acaoOpcoes [$acao] ['imagem']);
        }

        $grid->setMassActions($acoes);

        // Nome padrão dos arquivos
        $grid->setDeployOption('name', 'e-Orçamento - ' . $nomeArquivo . ' - ' . date('Y-m-d-H-i-s'));

        // Exportação
        $exportacoes = array('print', 'pdf', 'excel', 'csv', 'word');
        $grid->setExport($exportacoes);

        // Retorna o objeto grid
        return $grid;
    }

}
