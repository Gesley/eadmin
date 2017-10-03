<?php

class Sosti_Form_ImportaPlanilha extends Zend_Form {

    
    /******************************************************************
     * Definições
     *****************************************************************/
    const TIPO_ENTRADA = 'TIPO_ENTRADA';
    const TIPO_ENTRADA_PERIODO = 'TIPO_ENTRADA_PERIODO';
    const TIPO_ENTRADA_IMPORTACAO = 'TIPO_ENTRADA_IMPORTACAO';
    const TIPO_ENTRADA_FATURAMENTO = 'TIPO_ENTRADA_FATURAMENTO';
    
    public $_tipos_entrada =
    array(
        Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO => 
            array(
                "label" =>'Informar Período',
                "elements" => 
                array(    
                        "TIPO_ENTRADA" => "",
                        "DATA_INICIAL" => "",
                        "DATA_FINAL"  => "",
                        "CONTA_NAO_CATEGORIZADO"  => "",
//                        "TIRA_FILTRO"=>"",
                        "Gerar" => "",
                        "OBRIGATORIO" => ""
                    )
                ),
        Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO => 
            array(
                "label" =>'Importar Excel',
                "elements" =>
                array(
                        "TIPO_ENTRADA" => "",
                        "CELULA_INICIAL" => "",
                        "CELULA_FINAL" => "",
                        "CELULA_TOTAL_PF" => "",
                        "SEPARADOR_MULTIPLO_NUMEROS" => "",
                        "PLANILHA_ARQUIVO" => "",
//                        "TIRA_FILTRO"=>"",
                        "Gerar" => "",
                        "OBRIGATORIO" => ""
                )
                ),
    );
    

    
    public function init() {
        $this
                ->setAttrib('enctype', 'multipart/form-data')
                ->setMethod('post')
                ->setName('ImportarPlanilha');

        $tipo_entrada = new Zend_Form_Element_Radio(Sosti_Form_ImportaPlanilha::TIPO_ENTRADA);
        $tipo_entrada->setLabel('Forma de Geração:')
                ->setRequired(true)
                ->setMultiOptions(
                array(
                    Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO => $this->_tipos_entrada[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO]["label"],
                    Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO => $this->_tipos_entrada[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO]["label"]
                ))->setValue(Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO);

        $data_inicial = new Zend_Form_Element_Text('DATA_INICIAL');
        $data_inicial
                ->setRequired(true)
                ->setValue('')
                ->setLabel('*Data inicial Encaminhamento:')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyyHH:mm:ss')))
                ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');

        $data_final = new Zend_Form_Element_Text('DATA_FINAL');
        $data_final
                ->setRequired(true)
                ->setValue('')
                ->setLabel('*Data final Encaminhamento:')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd/MM/yyyyHH:mm:ss')))
                ->setDescription('Fomato de data/hora deve ser dd/mm/yyyy hh:mm:ss');
        ;
        
        $conta_nao_categorizado = new Zend_Form_Element_Checkbox('CONTA_NAO_CATEGORIZADO');
        $conta_nao_categorizado->setLabel('Contabilizar Solicitações não Categorizadas')
                        ->setCheckedValue('S')
                        ->setUncheckedValue('N')
                        ->setValue('S')
                        ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
                        ->removeDecorator('HtmlTag', array('tag' => 'dt'))
                        ->addDecorator('HtmlTag', array('tag' => 'dt', 'style' => 'clear:both;', 'id' => 'CONTA_NAO_CATEGORIZADO-label'))
                        ->setAttribs(array('style' => 'float: left;'));
        
//        $tira_filtro = new Zend_Form_Element_Checkbox('TIRA_FILTRO');
//        $tira_filtro->setLabel('Relatório com validação')
//                        ->setCheckedValue('S')
//                        ->setUncheckedValue('N')
//                        ->setValue('N')
//                        ->setDecorators(array('ViewHelper', 'Errors', 'Label'))
//                        ->removeDecorator('HtmlTag', array('tag' => 'dt'))
//                        ->addDecorator('HtmlTag', array('tag' => 'dt', 'style' => 'clear:both;', 'id' => 'TIRA_FILTRO-label'))
//                        ->setAttribs(array('style' => 'float: left;'));

        $celula_inicial = new Zend_Form_Element_Text('CELULA_INICIAL');
        $celula_inicial->setLabel('*Celula inícial de números de solicitações.')->addFilter('StringToUpper')
                ->setRequired(true)
                ->setAttrib('style', 'width: 40px; text-transform: uppercase;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('Alnum')
                ->addValidator('StringLength', false, array(1, 5))
                ->setDescription('Exemplo: B2');

        $celula_final = new Zend_Form_Element_Text('CELULA_FINAL');
        $celula_final->setLabel('*Celula final de números de solicitações.')->addFilter('StringToUpper')
                ->setRequired(true)
                ->setAttrib('style', 'width: 40px; text-transform: uppercase;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(1, 5))
                ->addValidator('Alnum')
                ->setDescription('Exemplo: B120');
        
        
        
        
        
        $celula_total_pf = new Zend_Form_Element_Text('CELULA_TOTAL_PF');
        $celula_total_pf->setLabel('*Celula Referente ao Total de Pontos de Função.')->addFilter('StringToUpper')
                ->setRequired(true)
                ->setAttrib('style', 'width: 40px; text-transform: uppercase;')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(1, 5))
                ->addValidator('Alnum')
                ->setDescription('Exemplo: I121');

        $separador_multiplo_numeros = new Zend_Form_Element_Text('SEPARADOR_MULTIPLO_NUMEROS');
        $separador_multiplo_numeros->setLabel('*Separador para células com mais de um número de solicitação.')
                ->setRequired(true)
                ->setAttrib('style', 'width: 10px;')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', false, array(1, 1))
                ->setDescription('Exemplo: ; |  -  ,');

        $planilha_arquivo = new Zend_Form_Element_File('PLANILHA_ARQUIVO');
        $planilha_arquivo->setLabel('*Arquivo de Planilha:')
                ->setRequired(true)
                ->addValidator(new Zend_Validate_File_Extension(array('xls', 'xlsx')))
//                        ->addValidator('Size', false, 52428800) // limit to 50m
//                        ->setMaxFileSize(52428800)
                ->addValidator('Count', false, array('min' => 0, 'max' => 1))
                ->setDestination(APPLICATION_PATH . '/../temp')
                ->setDescription('Somente serão aceitos arquivos com o formato xls e xlsx. Com tamanho máximo de 50 Megas.')
                ;
        
        
        
        $submit = new Zend_Form_Element_Submit('Gerar');
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios')
                ->setAttrib('style', 'display: none;');

        $this->addElements(array(
            $tipo_entrada,
            $data_inicial,
            $data_final,
            $celula_inicial,
            $celula_final,
            $conta_nao_categorizado,
            $celula_total_pf,
            $separador_multiplo_numeros,
            $planilha_arquivo,
//            $tira_filtro,
            $submit,
            $obrigatorio));
    }
    
    public function switchTipoEntrada($tipo_entrada){
        if ($tipo_entrada == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO) {
            foreach ($this->getElements() as $element) {
                if (in_array($element->getName(), array_keys($this->_tipos_entrada[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_PERIODO]["elements"]))) {
                    if($element->isRequired()){
                        $element->setRequired(true);
                    }
                } else {
                    if($element->isRequired()){
                        $element->setRequired(false);
                    }
                }
            }
        } else if ($tipo_entrada == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO) {
            foreach ($this->getElements() as $element) {
                if (in_array($element->getName(), array_keys($this->_tipos_entrada[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO]["elements"]))) {
                    if($element->isRequired()){
                        $element->setRequired(true);
                    }
                } else {
                    if($element->isRequired()){
                        $element->setRequired(false);
                    }
                }
            }
        }
        else if ($tipo_entrada == Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_FATURAMENTO) {
            foreach ($this->getElements() as $element) {
                if (in_array($element->getName(), array_keys($this->_tipos_entrada[Sosti_Form_ImportaPlanilha::TIPO_ENTRADA_IMPORTACAO]["elements"]))) {
                    if($element->isRequired()){
                        $element->setRequired(false);
                    }
                } else {
                    if($element->isRequired()){
                        $element->setRequired(false);
                    }
                }
            }
        }
        return $this;
    }

}
