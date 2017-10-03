<?php
class Sisad_Form_EncaUnidadePessoa extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
                ->setName('Encaminhar Pessoa');
        
        $acao = new Zend_Form_Element_Hidden('acao');
        $acao->setValue('EncaminharPessoaForm');
        $aNamespace = new Zend_Session_Namespace('userNs');
        
        $encaminhamento = new Zend_Form_Element_Radio('ENCAMINHAMENTO');
        $encaminhamento->setLabel('Encaminhar para:')
                       ->setRequired(true)
                       ->setValue('daunidade')
                       ->addMultiOption("responsaveis", "Responsáveis pela caixa")
                       ->addMultiOption("daunidade", "Pessoa da Unidade")
                       ->addMultiOption("outraunidade", "Outras Unidades");
        
        $mode_cd_secao_unid_destino = new Zend_Form_Element_Hidden('MODE_CD_SECAO_UNID_DESTINO');
        $mode_cd_secao_unid_destino->addFilter('Int')
                          ->removeDecorator('Label')
                          ->removeDecorator('HtmlTag')
                          ->addFilter('StripTags')
                          ->addFilter('StringTrim');       
        
        $mofa_ds_complemento = new Zend_Form_Element_Textarea('MOFA_DS_COMPLEMENTO');
        $mofa_ds_complemento->setRequired(true)
                 ->setLabel('Descrição do Encaminhamento:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('NotEmpty')
                 ->addValidator('StringLength', false, array(5, 4000))
                ->setAttrib('style', 'width: 628px;');
        
        $mode_cd_matr_recebedor = new Zend_Form_Element_Select('MODE_CD_MATR_RECEBEDOR');
        $mode_cd_matr_recebedor->setRequired(false)
                       ->setLabel('Pessoa da Unidade:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty');

        $mode_cd_matr_recebedor_unidades = new Zend_Form_Element_Text('MODE_CD_MATR_RECEBEDOR_UNIDADES');
        $mode_cd_matr_recebedor_unidades->setLabel('Pessoa de outra unidade: ')
                                   ->setAttrib('style', 'text-transform: uppercase; width: 500px;');
        
        $caixas_disponiveis = new Zend_Form_Element_Select('CAIXAS_DISPONIVEIS');
        $caixas_disponiveis->setRequired(false)
                       ->setLabel('Caixas Disponíveis:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty');
        
        $mode_cd_matr_recebedor_responsavel = new Zend_Form_Element_Select('MODE_CD_MATR_RECEBEDOR_RESPONSAVEL');
        $mode_cd_matr_recebedor_responsavel->setRequired(false)
                       ->setLabel('Responsáves pela Caixa:')
                       ->setAttrib('style', 'text-transform: uppercase; width: 500px;')
                       ->addFilter('StripTags')
                       ->addFilter('StringTrim')
                       ->addValidator('NotEmpty');
        
        $submit = new Zend_Form_Element_Submit('Encaminhar');
        
        $this->addElements(array($acao,
                                $encaminhamento,
                                $mode_cd_matr_recebedor,
                                $mode_cd_matr_recebedor_unidades,
                                $caixas_disponiveis,
                                $mode_cd_matr_recebedor_responsavel,
                                $mofa_ds_complemento,
                                $submit));
    }
}