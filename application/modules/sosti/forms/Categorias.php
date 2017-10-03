<?php
class Sosti_Form_Categorias extends Zend_Form
{
    public function init()
    {
        $this->setAction('nova')
             ->setMethod('post');
        
        $cate_no_categoria = new Zend_Form_Element_Text('CATE_NO_CATEGORIA');
        $cate_no_categoria->setLabel('*Nome da Categoria')
                          ->setRequired(true)
                          ->addFilter('StripTags')
                          ->addValidator('StringLength', false, array(1, 50))
                          ->addFilter('StringTrim')
                          ->addValidator('NotEmpty');
                          
        $cate_id_grupo = new Zend_Form_Element_Hidden('CATE_ID_GRUPO');
        $cate_id_grupo->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $cate_id_nivel = new Zend_Form_Element_Hidden('CATE_ID_NIVEL');
        $cate_id_nivel->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $cate_cd_matricula_categoria = new Zend_Form_Element_Hidden('CATE_CD_MATRICULA_CATEGORIA');
        $cate_cd_matricula_categoria->setRequired(false)
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag');
        
        $cate_ds_observacao = new Zend_Form_Element_Textarea('CATE_DS_OBSERVACAO');
        $cate_ds_observacao->setRequired(true)
                            ->setLabel('*Descrição da Categoria:')
                            ->setOptions(array('style' => 'width:300px'))
                            ->addValidator('StringLength', false, array(5, 500))
                            ->addValidator('NotEmpty')
                            ->addFilter('StripTags')
                            ->addFilter('StringTrim')
                            ->addFilter('HtmlEntities',array('quotestyle' => ENT_QUOTES));
        
        $cate_ds_descricao_cor = new Zend_Form_Element_Hidden('CATE_DS_DESCRICAO_COR');
        $cate_ds_descricao_cor->setRequired(false)
                              ->removeDecorator('Label')
                              ->removeDecorator('HtmlTag');
        
        $cate_ic_ativo = new Zend_Form_Element_Select('CATE_IC_ATIVO');
        $cate_ic_ativo->setLabel('*Categoria ativa')
                      ->setRequired(true)
                      ->setMultiOptions(array('S' => 'Sim', 
                                              'N' => 'Não')); 
        
        $cate_categorias = new Zend_Form_Element_Select('CATE_CATEGORIAS');
        $cate_categorias->setRequired(false)
                       ->setLabel('Categorias:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty');
        
        $cate_id_categoria = new Zend_Form_Element_Multiselect('CATE_ID_CATEGORIA');
        $cate_id_categoria
                        ->setRequired(false)
                        ->setLabel('Categorias:');
        
        $criar = new Zend_Form_Element_Submit('Salvar');
        $alterar = new Zend_Form_Element_Submit('Alterar');
        
        $this->addElements(array($cate_id_categoria,
                                     $cate_no_categoria,
                                     $cate_ds_observacao,
                                     $cate_ic_ativo,
                                     $cate_id_grupo,
                                     $cate_ds_descricao_cor,
                                     $cate_id_nivel,
                                     $cate_cd_matricula_categoria,
                                     $criar,
                                     $cate_categorias,
                                     $alterar));
    }
}