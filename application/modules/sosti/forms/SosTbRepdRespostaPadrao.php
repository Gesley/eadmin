<?php

class Sosti_Form_SosTbRepdRespostaPadrao extends Zend_Form {

    public function init() {
        $this->setAction('')
                ->setAttrib('id', 'form_repd')
                ->setMethod('post');

        //FILTRO PARA CAMPO DE TEXTO, ELIMINANDO AS ASPAS SIMPLES
        $Zend_Filter_PregReplace = new Zend_Filter_PregReplace();
        $Zend_Filter_PregReplace->setMatchPattern("/'/");
        $Zend_Filter_PregReplace->setReplacement("''");
        
        $Zend_Filter_HtmlEntities = new Zend_Filter_HtmlEntities();
        $Zend_Filter_HtmlEntities->setQuoteStyle(ENT_QUOTES)->setDoubleQuote(false);
        
        $id_resposta = new Zend_Form_Element_Hidden('REPD_ID_RESPOSTA_PADRAO');
        $id_resposta->removeDecorator('label');
        
        $nome_resposta = new Zend_Form_Element_Text('REPD_NM_RESPOSTA_PADRAO');
        $nome_resposta->setRequired(true)
                ->setLabel('Nome')
                ->addValidator('StringLength', false, array(5, 499))
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter($Zend_Filter_PregReplace)
                ->addFilter('StringTrim');
                
        
        $descricao_resposta = new Zend_Form_Element_Textarea('REPD_DS_RESPOSTA_PADRAO');
        $descricao_resposta->setRequired(true)
                ->setLabel('Descrição')
                ->addValidator('StringLength', false, array(5, 4000))
                ->addValidator('NotEmpty')
                ->addFilter('StripTags')
                ->addFilter('StringTrim');

        $confidencialidade_resposta = new Zend_Form_Element_Checkbox('REPD_IC_CONFIDENCIALIDADE');
        $confidencialidade_resposta->setLabel('Privado')
                ->setDescription('Marque para tornar a resposta como pessoal.');
        
        $idGrupo_resposta = new Zend_Form_Element_Hidden('REPD_ID_GRUPO');
        $idGrupo_resposta->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->removeDecorator('label');
        
        $id_servico_resposta = new Zend_Form_Element_Select('REPD_ID_SERVICO');
        $id_servico_resposta->setLabel('Tipo de Serviço');
                
        
        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class','submitComum');

        $this->addElements(
                array(
                    $id_resposta,
                    $nome_resposta, 
                    $descricao_resposta, 
                    $confidencialidade_resposta,
                    $id_servico_resposta,
                    $idGrupo_resposta, 
                    $submit)
                );
    }

}