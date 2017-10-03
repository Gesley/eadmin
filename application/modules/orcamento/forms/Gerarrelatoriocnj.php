<?php
/**
 * Contém formulário do relatório regra cnj
 *
 * e-Admin
 * e-Orçamento
 * Form
 *
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */

/**
 * Disponibiliza o formulário para entrada de dados sobre esfera.
 *
 * @category Orcamento
 * @package Orcamento_Form_Gerarrelatoriocnj
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 * @license Creative Commons Atribuição 3.0 não adaptada
 *          <http://creativecommons.org/licenses/by/3.0/deed.pt_BR>
 * @ignore Este código é livre para uso dentro do TRF! Fora do TRF pode apenas
 *         servir como fonte de estudo ou base para futuros códigos-fonte sem
 *         nenhuma restrição, salvo pelas informações de @copyright e @author
 *         que devem ser mantidas inalteradas.
 * @copyright Tribunal Regional Federal ©2007-2014 <http://www.trf1.jus.br>
 */
class Orcamento_Form_Gerarrelatoriocnj extends Orcamento_Form_Base {
    
    /**
     * Cria o formulário negocial para entrada de dados
     *
     * @see Zend_Form::init()
     * @author Ricardo Ramon <rrsantos2@stefanini.com>
     */
    public function init () {

        $mesDefault = date("n", strtotime("last month"));
        $mesAtual = date("n");
        $anoAtual = date("Y");
        
        
        $parametro = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $tipo_relatorio = $parametro['TIPO_RELATORIO'];
        
        // Cria o campo REGC_AA_REGRA
        $txtAno = new Zend_Form_Element_Text ( 'REGC_AA_REGRA' );
        
        // Define opções o controle ano
        $txtAno->setLabel ( 'Ano:' );
        $txtAno->setAttrib ( 'size', 7 );
        $txtAno->setAttrib ( 'maxlength', 4);
        $txtAno->addFilter ( 'StringTrim' );
        $txtAno->setValue(date('Y'));
        $txtAno->setRequired ( true );
        
        // Cria o campo IMPA_IC_MES
        $slctMes = new Zend_Form_Element_Select('IMPA_IC_MES');

        // Define opções o controle $slctMes
        $slctMes->setLabel('Mês:')
                ->setRequired(true)
                ->addValidator('NotEmpty', true)
                ->addFilter('StringTrim')
                ->setRequired(true)
                ->setValue($mesDefault);
        
        // Retorna meses em pt-br para preenchimento do select
        /* $meses = Zend_Locale::getTranslationList('Months', 'pt_br');
        $mesesFormat = $meses['format']['wide'];
        array_walk($mesesFormat, 'primeiraMaiuscula');
        */

        $meses = array( 
            '' => 'Selecione',
            'IMPO_VL_TOTAL_JAN' => 'JANEIRO',
            'IMPO_VL_TOTAL_FEV' => 'FEVEREIRO', 
            'IMPO_VL_TOTAL_MAR' => 'MARÇO',
            'IMPO_VL_TOTAL_ABR' => 'ABRIL', 
            'IMPO_VL_TOTAL_MAI' => 'MAIO', 
            'IMPO_VL_TOTAL_JUN' => 'JUNHO', 
            'IMPO_VL_TOTAL_JUL' => 'JULHO',
            'IMPO_VL_TOTAL_AGO' => 'AGOSTO', 
            'IMPO_VL_TOTAL_SET' => 'SETEMBRO', 
            'IMPO_VL_TOTAL_OUT' => 'OUTUBRO', 
            'IMPO_VL_TOTAL_NOV' => 'NOVEMBRO',
            'IMPO_VL_TOTAL_DEZ' => 'DEZEMBRO'
            );
        
        // adiciona os meses
        $slctMes->addMultiOptions($meses);
        
        // Tipo de anexo
        $TipoAnexo = new Zend_Form_Element_Radio('TIPO_ANEXO');
        $TipoAnexo->setLabel('Tipo de Anexo:')
            ->setValue(1)
            ->setRequired(true)
            ->addMultiOptions(array('1' => 'Anexo I','2' => 'Anexo II')
        ); 
        $TipoAnexo->setSeparator(' ');
   
        // Tipo de relatório
        $TipoRelatorio = new Zend_Form_Element_Radio('TIPO_RELATORIO');
        $TipoRelatorio->setLabel('Tipo de relatório:')
            ->setValue(1)
            ->setRequired(true)
            ->addMultiOptions(array('1' => 'HTML','2' => 'Excel')
        ); 
        $TipoRelatorio->setSeparator(' ');
                
        // Tipo de Anexo html
        $tipoAnexoHtml = new Zend_Form_Element_Select('TIPO_ANEXO_HTML');
        $tipoAnexoHtml->setLabel('Tipo Anexo I:')
            ->addFilter('StripTags')
            ->addMultiOptions(array(
                '' => 'Selecione',
                Orcamento_Business_Negocio_Gerarrelatoriocnj::
                    ANEXOI_RP 
                    => 'Anexo I - Restos a Pagar',
                Orcamento_Business_Negocio_Gerarrelatoriocnj::
                    ANEXOI_ORCAMENTARIO 
                    => 'Anexo I - Orçamentário',
                Orcamento_Business_Negocio_Gerarrelatoriocnj::
                    ANEXOI_FINANCEIRO 
                    => 'Anexo I - Financeiro',
                )
            );
        
        if($tipo_relatorio === '1'){
            $tipoAnexoHtml->setRequired ( true );
        }

        // Tipo de Anexo Excel
        $tipoAnexoExcel = new Zend_Form_Element_Select('TIPO_ANEXO_EXCEL');
        $tipoAnexoExcel->setLabel('Tipo Anexo I:')
            ->addFilter('StripTags')
            ->addMultiOptions(array(
                '' => 'Selecione',
                Orcamento_Business_Negocio_Gerarrelatoriocnj::
                    ANEXOI_INCISOS_EXCEL
                    => 'Anexo I - Incisos',
                Orcamento_Business_Negocio_Gerarrelatoriocnj::
                    ANEXOI_IDENTIFICACAO
                    => 'Anexo I - Identificação',
                )
            );
        
        $unidadeGestora = new Zend_Form_Element_Select('UNIDADE_GESTORA');
        $unidadeGestora->setLabel('UG:')
            ->addFilter('StripTags')
            ->addMultiOptions(array(
                '' => 'Selecione'
                )
            );
        
        $bussRelCNJ = new Orcamento_Business_Negocio_Gerarrelatoriocnj();
        
        $unidadeGestora = new Zend_Form_Element_Select('UNIDADE_GESTORA');
        $unidadeGestora->setLabel('UG:');

        $unidadeGestora->addMultiOptions ( array ( '' => 'Selecione' ) );
        $unidadeGestora->addMultiOptions ( $bussRelCNJ->retornaComboUG() );
        $unidadeGestora->setAttribs(array( 'style' => 'max-width:350px; display: inline') );

        $unidadeTodas = new Zend_Form_Element_Checkbox("UG_TODAS");
        $unidadeTodas->setLabel("Todas");
        $unidadeTodas->getDecorator('label')->setOptions(array('placement' => 'APPEND'));

        if ($tipo_relatorio === '2' ){
            $tipoAnexoExcel->setRequired ( true );
        }
        
        // Cria o botão de enviar
        $cmdEnviar = new Zend_Form_Element_Submit('Enviar');
        
        // Define opções do controle $cmdEnviar
        $classeSubmit = Orcamento_Business_Dados::CLASSE_RELATORIO;
        $cmdEnviar->setLabel ( 'Gerar Relatório' );
        $cmdEnviar->setAttrib ( 'type', 'submit' );
        $cmdEnviar->setAttrib ( 'class', $classeSubmit );

        // Adiciona os controles no formulário
        $this->addElement($txtAno);
        $this->addElement($slctMes);
        $this->addElement($TipoAnexo);
        $this->addElement($TipoRelatorio);
        $this->addElement($tipoAnexoHtml);
        $this->addElement($tipoAnexoExcel);
        $this->addElement($unidadeGestora);
        $this->addElement($unidadeTodas);
        $this->addElement($cmdEnviar);
    }

}

/**
 * 
 * Função para deixar a primeira letra em maiusculo em um array.
 * Usado no array_walk.
 * 
 * @param String $item
 * 
 * @author Ricardo Ramon <rrsantos2@stefanini.com>
 */
function primeiraMaiuscula(&$item) {
    $item = ucfirst($item);
}