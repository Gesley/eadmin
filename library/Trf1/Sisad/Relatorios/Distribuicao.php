<?php

/**
 * @category	TRF1
 * @package		Trf1_Sisad_Relatorios_Distribuicao
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * @tutorial	Tutorial abaixo
 * 
 * TRF1, Classe de relatórios da distribuição de processos administrativos
 * 
 * ====================================================================================================
 * LICENÇA
 * ====================================================================================================
 * Este código é livre para uso dentro do TRF!
 * 
 * Fora do TRF pode apenas servir como fonte de estudo ou base para futuros códigos-fonte sem nenhuma 
 * restrição, salvo pelas informações de @copyright e @author que devem ser mantidas inalteradas.
 * 
 * Sugestão para futura adoção da Licença Creative Commons Atribuição 3.0 Unported
 * => http://creativecommons.org/licenses/by/3.0/deed.pt_BR
 * 
 */
class Trf1_Sisad_Relatorios_Distribuicao {

    /**
     * Armazena o valor da URL
     *
     * @var String $_baseUrl
     */
    protected $_baseUrl;

    /**
     * Função construtora
     * 
     * @param	none
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function __construct() {
        //Carrega as classes do MPDF
        define("_MPDF_TEMP_PATH", realpath(APPLICATION_PATH . '/../temp'));
        define("_MPDF_TTFONTDATAPATH", realpath(APPLICATION_PATH . '/../temp'));
        include(realpath(APPLICATION_PATH . '/../library/MPDF53/mpdf.php'));

        //pega o valor do baseUrl
        $fc = Zend_Controller_Front::getInstance();
        $this->_baseUrl = $fc->getBaseUrl();
    }

    /**
     * Gera o pdf da ata de distribuição de um processo administrativo
     * 
     * @param	
     * @author	Leidison Siqueira Barbosa [leidison_14@hotmail.com]
     */
    public function gerarAtaDeDistribuicao($idProcesso, $nrDocumento, $idOrgao, $matricula, $formaDistribuicao, $dataHoraMili) {

        $bd_distribuicao = new Trf1_Sisad_Bd_Distribuicao();
        $dadosAta = $bd_distribuicao->montaDadosAtaDistribuicao($idProcesso, $matricula, $idOrgao, $formaDistribuicao);

        $zend_date = new Zend_Date($dataEhoraMili['DATA'], 'dd/MM/YY HH:mm:ss');
        $dia = $zend_date->get(Zend_Date::DAY);
        $mes = $zend_date->get(Zend_Date::MONTH_NAME);
        $ano = $zend_date->get(Zend_Date::YEAR);
        $dateTime = $zend_date->get(Zend_Date::DATETIME);

        $arrayModalidade = array(
            'DA' => $dadosAta[0]['QTD_DISTRIBUICAO'] + 1 . ' ª DISTRIBUIÇÃO AUTOMÁTICA'
            , 'RA' => $dadosAta[0]['QTD_DISTRIBUICAO'] + 1 . ' ª REDISTRIBUIÇÃO AUTOMÁTICA'
            , 'DM' => $dadosAta[0]['QTD_DISTRIBUICAO'] + 1 . ' ª DISTRIBUIÇÃO MANUAL'
        );

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $body = $viewRenderer->view->partial('_partials/ata_distribuicao.phtml', array(
            'datahora' => $dateTime
            , 'dataExtenso' => "$dia de $mes do ano de $ano"
            , 'processo' => $nrDocumento
            , 'orgaoJulgador' => $dadosAta[0]['ORGJ_NM_ORGAO_JULGADOR']
            , 'modalidadeDistribuicao' => $arrayModalidade[$formaDistribuicao]
            , 'dataDistribuicao' => $dateTime
            , 'relator' => $dadosAta[0]['PNAT_NO_PESSOA']));

        $mpdf = new mPDF('utf-8', // mode - default ''
                        'A4', // format - A4, for example, default ''
                        '', // font size - default 0
                        '', // default font family
                        5, // margin_left
                        20, // margin right
                        40, // margin top
                        16, // margin bottom	 
                        0, // margin header
                        5, // margin footer
                        'L');  // L - landscape, P - portrait
        $mpdf->SetDisplayMode('fullpage');
        $cabecalho = '
            <table id="header">
                <tr valign="top">
                    <td class="brasao">
                    </td>
                    <td class="date-time">
                        ' . $dateTime . '
                    </td>
                </tr>
            </table>';

        $mpdf->showWatermarkImage = true;
        $mpdf->watermarkImgBehind = true;
        $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
        $mpdf->SetHTMLHeader($cabecalho);
        $mpdf->AddPage();
        $mpdf->WriteHTML($body);
        $dest = realpath(APPLICATION_PATH . '/../temp/');
        $name = 'SISAD_TEMP_DOC_ATA_DISTRIBUICAO_VISUALIZAR_DOCUMENTO' . date('dmYHisu') . '.pdf';

        $caminho = $dest . DIRECTORY_SEPARATOR . $name;
        $mpdf->Output($caminho, 'F');

        try {
            $nrDocsRed = null;
            if (file_exists($caminho)) {
                $app_Multiupload_Upload = new App_Multiupload_Upload();
                try {
                    $nrDocsRed = $app_Multiupload_Upload->anexarAoDocumento($caminho);
                } catch (Exception $e) {
                    throw new Exception('Não foi possível gerar a ata de distribuição.', 1, null);
                }
            } else {
                throw new Exception('Não foi possível encontrar a ata de distribuição.', 1, null);
            }
        } catch (Exception $e) {
            throw $e;
        }
        if($nrDocsRed == null){
            throw new Exception('Não foi possível gerar a ata de distribuição.', 1, null);
        }
        return $nrDocsRed;
    }

}