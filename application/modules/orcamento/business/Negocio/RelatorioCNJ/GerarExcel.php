<?php

/**
 * Contém regras negociais específicas desta funcionalidade
 *
 * e-Admin
 * e-Orçamento
 * Business - Negócio
 *
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Contém as regras negociais sobre regra
 *
 * @category Orcamento
 * @package Orcamento_Business_Negocio_RelatorioCNJ_GerarExcel
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Business_Negocio_RelatorioCNJ_GerarExcel {

    private $_excel;

    /**
     * Gera excel do tipo Anexo I
     * 
     * @param arry $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function gerarExcelAnexoI($dados) {

        // --------------------------------------------------------------------

        $arquivo = $this->criarBase();

        $excel = new PHPExcel();
        $this->_excel = $excel;
        
        $excel->getProperties()->setCreator("TRF")
                ->setTitle("Anexo I")
                ->setSubject("TRF - Anexo I")
                ->setDescription("TRF - Anexo I");

        $ativo = $excel->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------

        $dataPublicacao = date("d/m/Y");
        //$mes = str_pad($dados['mes'], 2, "0", STR_PAD_LEFT);

        //$mes = $dados['inciso']['mes'];


        switch ($dados['inciso']['mes']) {
            case 'IMPO_VL_TOTAL_JAN':
                $mes = '01';
                break;
            case 'IMPO_VL_TOTAL_FEV':
                $mes = '02';
                break;
            case 'IMPO_VL_TOTAL_MAR':
                $mes = '03';
                break;
            case 'IMPO_VL_TOTAL_ABR':
                $mes = '04';
                break;
            case 'IMPO_VL_TOTAL_MAI':
                $mes = '05';
                break;
            case 'IMPO_VL_TOTAL_JUN':
                $mes = '06';
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $mes = '07';
                break;
            case 'IMPO_VL_TOTAL_AGO':
                $mes = '08';
                break;
            case 'IMPO_VL_TOTAL_SET':
                $mes = '09';
                break;
            case 'IMPO_VL_TOTAL_OUT':
                $mes = '10';
                break;
            case 'IMPO_VL_TOTAL_NOV':
                $mes = '11';
                break;
            case 'IMPO_VL_TOTAL_DEZ':
                $mes = '12';
                break;
            
        } 

        // ---------------------------------------------------------------------

        $ativo->setCellValue('A1', 'Anexo I - Inciso')
                ->setCellValue('A3', 'Mês/Ano de Referência')
                ->setCellValue('A4', 'Data de Publicação')
                ->setCellValue('A5', 'Inciso-Alínea')
                ->setCellValue('B3', "{$mes}/{$dados['inciso']['ano']}")
                ->setCellValue('B4', $dataPublicacao)
                ->setCellValue('B5', 'Valores (R$ 1,00)');

        unset($dados['inciso']['mes']);
        unset($dados['inciso']['ano']);

        // coloca tamanho automático nos campos
        $this->autoSize(array('A', 'B'));
        $arrayTitulo = array('A1', 'A3', 'A4', 'B3', 'B4');

        // Formata com negrito
        $this->negrito($arrayTitulo);
        $this->fonte($arrayTitulo, 12);

        $this->fonte(array('A5', 'B5'), 10);

        // ---------------------------------------------------------------------
        
        // Formata em monetario
        $this->formatoNumero('B');

        // Formatar o topo da célula A2 com uma borda
        $this->borda('A5:B5');

        // número das linhas onde será inserido as somas dos valores
        $i = 6;

        foreach ($dados['inciso'] as $inciso) {

            foreach ($inciso['alinea'] as $alinea) {

                $ativo->setCellValue("A{$i}",
                        "{$inciso['valor']}-{$alinea['valor']}");

                $total = $alinea['total'] === null ? 0 : $alinea['total'];

                $ativo->setCellValue("B{$i}", $total);
                $this->formatar(array("A{$i}", "B{$i}"));

                $i++;
            }
        }
        
        // ---------------------------------------------------------------------

        // Efetua criação da planilha excel
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($arquivo);

        return $arquivo;
    }

    /**
     * Gera excel do Anexo II
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function gerarExcelAnexoII($dados) {

        // ---------------------------------------------------------------------
        // cria dados iniciais da planilha
        
        $arquivo = $this->criarBase();

        $excel = new PHPExcel();
        $this->_excel = $excel;

        // ---------------------------------------------------------------------
        
        // titulos e posições das planilhas
        $titulos = array(
            "FUNCIONAL_PROGRAMATICA" => "Funcional Programática",
            "PROGRAMA_ACAO" => "Descrição do Programa/Ação",
            "FUNCAO_SUBFUNCAO" => "Função / Subfunção",
            "IMPO_CD_ESFERA" => "Esfera",
            "GND" => "GND",
            "IMPO_CD_FONTE" => "Fonte",
            "VL_DOTACAO" => "Dotação Inicial",
            "VL_SUPLEMENTACAO" => "Suplementação",
            "VL_CANCELAMENTO" => "Cancelamento",
            "VL_CONTINGENCIAMENTO" => "Contingenciamento",
            "VL_DOTACAO_AUTORIAZADA" => "Dotação Autorizada",
            "VL_PROVISAO" => "Provisão",
            "VL_DESTAQUE" => "Destaque",
            "VL_DOTACAO_LIQUIDA" => "Dotação Liquida",
            "VL_EMPENHADO" => "Empenhado",
            "VL_EMPENHADO_PORCENTAGEM" => "%",
            "VL_LIQUIDADO" => "Liquidado",
            "VL_LIQUIDADO_PORCENTAGEM" => "%",
            "VL_PAGO" => "Pago",
            "" => "%");

        // ---------------------------------------------------------------------

        $excel->getProperties()->setCreator("TRF")
                ->setTitle("Anexo I")
                ->setSubject("TRF - Anexo I")
                ->setDescription("TRF - Anexo I");

        $ativo = $excel->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------

        switch ($dados['anexo']['mes']) {
            case 'IMPO_VL_TOTAL_JAN':
                $this->mes = 1;
                break;
            case 'IMPO_VL_TOTAL_FEV':
                $this->mes = 2;
                break;
            case 'IMPO_VL_TOTAL_MAR':
                $this->mes = 3;
                break;
            case 'IMPO_VL_TOTAL_ABR':
                $this->mes = 4;
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $this->mes = 5;
                break;
            case 'IMPO_VL_TOTAL_JUN':
                $this->mes = 6;
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $this->mes = 7;
                break;
            case 'IMPO_VL_TOTAL_AGO':
                $this->mes = 8;
                break;
            case 'IMPO_VL_TOTAL_SET':
                $this->mes = 9;
                break;
            case 'IMPO_VL_TOTAL_OUT':
                $this->mes = 10;
                break;
            case 'IMPO_VL_TOTAL_NOV':
                $this->mes = 11;
                break;
            case 'IMPO_VL_TOTAL_DEZ':
                $this->mes = 12;
                break;
        }        

        $dataPublicacao = date("d/m/Y");
        $mes = $this->mes;
        $ano = $dados['anexo']['ano'];


        $ativo->setCellValueByColumnAndRow(0, 1, "Data Publicação");
        $ativo->setCellValueByColumnAndRow(1, 1, $dataPublicacao);

        //$ativo->setCellValueByColumnAndRow(0, 2, "Data Publicação");
        //$ativo->setCellValueByColumnAndRow(1, 2, $dataPublicacao);

        $ativo->setCellValueByColumnAndRow(0, 3, "Mês/Ano Referência");
        $ativo->setCellValueByColumnAndRow(1, 3, "{$mes}/{$ano}");

        $tituloArray = array("A1", "A2", "B1", "B2");
        $this->borda($tituloArray);
        $this->fonte($tituloArray, 12);

        // ---------------------------------------------------------------------

        $ativo->setCellValueByColumnAndRow(11, 3, "Mov. Líquida de créditos");
        $ativo->mergeCells("L3:M3");
        $ativo->getStyle("L3")->getAlignment()->setHorizontal(
                        PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $this->autoSize('L');
        
        // ---------------------------------------------------------------------
        // popula todos os dados da planilha com base na matriz
        
        // contadores
        $i = 0;
        $linhaPular = 5;
        $totalDados = count($dados['anexo']);
        
        $colunaSize = array();

        foreach ($titulos as $_indice => $titulo) {
            $coluna = PHPExcel_Cell::stringFromColumnIndex($i);
            $colunaSize[] = $coluna;

            $ativo->setCellValueByColumnAndRow($i, 4, $titulo);

            $valorColuna = "{$coluna}4";
            $ativo->getStyle($valorColuna)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $this->fonte($valorColuna, 10);
            $this->borda($valorColuna);

            if (!is_numeric($_indice)) {

                for ($linhaValor = 0; $linhaValor < $totalDados; $linhaValor++) {

                    $colLin = "{$coluna}{$linhaPular}";

                    $this->borda($colLin);
                    $this->fonte($colLin, 10);

                    if (strstr($_indice, "uc_") !== FALSE) {
                        $valInd = str_replace("uc_", "", $_indice);
                        $valor = $dados['anexo'][$linhaValor]['totais'][$valInd];

                        $this->formatoNumero($colLin);
                    } elseif (strstr($_indice, "rn_") !== FALSE) {

                        $valInd = str_replace("rn_", "", $_indice);
                        $lp = $linhaPular;

                        switch ($valInd) {
                            case 1:
                                $valor = "=G{$lp}+H{$lp}-I{$lp}-J{$lp}";
                                $this->formatoNumero($colLin);
                                break;
                            case 2:
                                $valor = "=K{$lp}+L{$lp}+M{$lp}";
                                $this->formatoNumero($colLin);
                                break;
                            case 3:
                                $valor = "=(O{$lp}/N{$lp})*100";
                                $this->formatoPorcentagem($colLin);
                                break;
                            case 4:
                                $valor = "=(Q{$lp}/N{$lp})*100";
                                $this->formatoPorcentagem($colLin);
                                break;
                            case 5:
                                $valor = "=(S{$lp}/N{$lp})*100";
                                $this->formatoPorcentagem($colLin);
                                break;
                            default:
                                $valor = "";
                        }
                    } else {
                        $valor = $dados['anexo'][$linhaValor][$_indice];
                    }

                    $valor = $dados['anexo'][$linhaValor][$_indice];

                    $ativo->setCellValueByColumnAndRow($i, $linhaPular, $valor);

                    $linhaPular++;
                }

                $linhaPular = 5;
            }

            $i++;
        }

        $this->autoSize($colunaSize);

        // Indicação da criação do ficheiro
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($arquivo);

        return $arquivo;
    }

    /**
     * Gera excel da identificação do anexo I
     * 
     * @param array $dados
     * @return array
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function gerarExcelIdentificacaoAnexoI($dados) {

        $arquivo = $this->criarBase();

        $excel = new PHPExcel();
        $this->_excel = $excel;

        // ---------------------------------------------------------------------

        $excel->getProperties()->setCreator("TRF")
                ->setTitle("Anexo I")
                ->setSubject("TRF - Anexo I")
                ->setDescription("TRF - Anexo I");

        $ativo = $excel->setActiveSheetIndex(0);

        // ---------------------------------------------------------------------
        
        $autoSizeArray = array("A", "B", "C", "D");
        $this->autoSize($autoSizeArray);
        
        // ---------------------------------------------------------------------
        
        $dataPublicacao = date("d/m/Y");

        switch ($dados['inciso']['mes']) {
            case 'IMPO_VL_TOTAL_JAN':
                $mes = '01';
                break;
            case 'IMPO_VL_TOTAL_FEV':
                $mes = '02';
                break;
            case 'IMPO_VL_TOTAL_MAR':
                $mes = '03';
                break;
            case 'IMPO_VL_TOTAL_ABR':
                $mes = '04';
                break;
            case 'IMPO_VL_TOTAL_MAI':
                $mes = '05';
                break;
            case 'IMPO_VL_TOTAL_JUN':
                $mes = '06';
                break;
            case 'IMPO_VL_TOTAL_JUL':
                $mes = '07';
                break;
            case 'IMPO_VL_TOTAL_AGO':
                $mes = '08';
                break;
            case 'IMPO_VL_TOTAL_SET':
                $mes = '09';
                break;
            case 'IMPO_VL_TOTAL_OUT':
                $mes = '10';
                break;
            case 'IMPO_VL_TOTAL_NOV':
                $mes = '11';
                break;
            case 'IMPO_VL_TOTAL_DEZ':
                $mes = '12';
                break;
            
        } 

        // $mes = str_pad($dados['mes'], 2, "0", STR_PAD_LEFT);
        $ano = $dados['ano'];
        
        // ---------------------------------------------------------------------

        $tituloArray = array("A1", "A2", "A3", "B2", "B3");

        $this->fonte($tituloArray, 12);
        $this->negrito($tituloArray);

        $ativo->setCellValueByColumnAndRow(0, 1, "Anexo I");
        $ativo->setCellValueByColumnAndRow(0, 2, "Data Publicação");
        $ativo->setCellValueByColumnAndRow(1, 2, $dataPublicacao);
        $ativo->setCellValueByColumnAndRow(0, 3, "Mês/Ano Referência");
        $ativo->setCellValueByColumnAndRow(1, 3, "{$mes}/{$ano}");
        
        // ---------------------------------------------------------------------
        
        $dadosArray = array("A4", "B4", "C4", "D4", "A5", "B5", "C5", "D5");
        $fonteArray = array("A4", "B4", "C4", "D4");

        $this->borda($dadosArray);
        $this->fonte($fonteArray, 12);

        $ativo->setCellValueByColumnAndRow(0, 4, "Sigla");
        $ativo->setCellValueByColumnAndRow(1, 4, "Nome do Órgão");
        $ativo->setCellValueByColumnAndRow(2, 4, "Autoridade Máxima");
        $ativo->setCellValueByColumnAndRow(3, 4, "Responsável pela Informação");
        
        $ativo->setCellValueByColumnAndRow(0, 5, "TRF");
        $ativo->setCellValueByColumnAndRow(1, 5, "Tribunal Regional Federal - 1ª Região");
        $ativo->setCellValueByColumnAndRow(2, 5, "Presidente do TRF1");
        $ativo->setCellValueByColumnAndRow(3, 5, "Secretaria de Planejamento Orçamentário e Financeiro - SECOR / TRF 1º Região");

        // ---------------------------------------------------------------------
        
        // Indicação da criação do ficheiro
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $objWriter->save($arquivo);

        return $arquivo;
    }

    /**
     * Cria base para excel 
     * 
     * @return string
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function criarBase() {
        include_once(realpath(APPLICATION_PATH . '/../library/PHPExcel/Classes/PHPExcel.php'));

        $caminhoBase = APPLICATION_PATH . '/data/ceo/export/';
        $nomeArquivo = "anexo_" . time() . ".xls";
        $arquivo = $caminhoBase . $nomeArquivo;

        return $arquivo;
    }

    /**
     * Formata células por padrão
     * 
     * @param mixed $celula
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function formatar($celula) {

        $this->borda($celula);
        $this->fonte($celula, 10);
    }

    /**
     * Cria borda
     * @param mixed $celula
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function borda($celula) {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->borda($valor);
            }
        } else {
            $this->_excel->getActiveSheet()
                    ->getStyle($celula)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
    }

    /**
     * Deixa em negrito
     * @param mixed $celula
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function negrito($celula) {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->negrito($valor);
            }
        } else {
            $this->_excel->getActiveSheet()
                    ->getStyle($celula)
                    ->getFont()
                    ->setBold(true);
        }
    }

    /**
     * Formata em número
     * 
     * @param mixed $celula
     * @param string $moeda
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function formatoNumero($celula, $moeda = "") {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->formatoNumero($valor);
            }
        } else {
            $this->_excel->getActiveSheet()
                    ->getStyle($celula)
                    ->getNumberFormat()
                    ->setFormatCode("{$moeda} #,##0.00");
        }
    }

    /**
     * Formata fonte
     * 
     * @param mixed $celula
     * @param int $tamanho
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function fonte($celula, $tamanho) {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->fonte($valor, $tamanho);
            }
        } else {
            $this->_excel->getActiveSheet()
                    ->getStyle($celula)
                    ->getFont()
                    ->setName('Arial')
                    ->setSize($tamanho);
        }
    }

    /**
     * Coloca autosize nas celulas
     * @param mixed $celula
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    private function autoSize($celula) {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->autoSize($valor);
            }
        } else {

            $this->_excel
                    ->getActiveSheet()
                    ->getColumnDimension($celula)
                    ->setAutoSize(true);
        }
    }


    /**
     * Deixa formato porcentagem
     * @param mixed $celula
     * 
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
     private function formatoPorcentagem($celula) {

        if (is_array($celula)) {
            foreach ($celula as $valor) {
                $this->formatoPorcentagem($valor);
            }
        } else {
            $this->_excel->getActiveSheet()
                    ->getStyle($celula)
                    ->getNumberFormat()
                    ->setFormatCode(
                            PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
        }
    }

}
