<?php
/*
 * Forma que chama todos os campos do add, poar 
 */

class Sisad_Form_ProtocoloAddMalote extends Zend_Form
{
    public function init()
    {
        $userNamespace = new Zend_Session_Namespace('userNs');
        
        $this->setAction('add')
             ->setMethod('post')
             ->setName('AddProtocolo');
        
        /*
         * Dados do Orgão
         */
        $tipo = new Zend_Form_Element_Hidden('TIPO');
        $tipo->setValue('addMalote');
        
        if ($this->getAttrib('tipo') == 'addMalote') {

            $destino = new Zend_Form_Element_Text('MAPO_SG_SECSUBSEC_DESTINO');
            $destino->setRequired(true)
                     ->setLabel('*Orgão Destino:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->setOptions(array('style' => 'width: 500px','disabled' => 'disabled'))
                     ->addValidator('StringLength', false, array(5, 200));

            $externo = new Zend_Form_Element_Hidden('MAPO_ID_ORGAO_EXTERNO');

            $descExterno = new Zend_Form_Element_Text('MAPO_DESC_ORGAO_EXET');
            $descExterno->setRequired(true)
                 ->setLabel('*Descrição Orgão Externo:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addFilter(new Zend_Filter_StringToUpper())
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 350px','disabled' => 'disabled'))
                 ->addValidator('StringLength', false, array(5, 200));
        }else {
            $origem = new Zend_Form_Element_Text('MAPO_SG_SECSUBSEC_ORIGEM');
            $origem->setRequired(true)
                     ->setLabel('*Orgão Origem:')
                     ->setValue("$userNamespace->codsecsubsec - $userNamespace->descrisaosecsubsec")
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->setOptions(array('style' => 'width: 500px','disabled' => 'disabled'))
                     ->addValidator('StringLength', false, array(5, 200));

            $destino = new Zend_Form_Element_Text('MAPO_SG_SECSUBSEC_DESTINO');
            $destino->setRequired(true)
                     ->setLabel('*Orgão Destino:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->setOptions(array('style' => 'width: 500px'))
                     ->addValidator('StringLength', false, array(5, 200))
                     ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');

            $externo = new Zend_Form_Element_Hidden('MAPO_ID_ORGAO_EXTERNO');

            $descExterno = new Zend_Form_Element_Text('MAPO_DESC_ORGAO_EXET');
            $descExterno->setRequired(true)
                 ->setLabel('*Descrição Orgão Externo:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addFilter(new Zend_Filter_StringToUpper())
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 350px'))
                 ->addValidator('StringLength', false, array(5, 200))
                 ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        }
        $matricula = new Zend_Form_Element_Text('MAPO_CD_MATRICULA');
        $matricula->setRequired(true)
             ->setLabel('*Matricula Incluínte:')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter(new Zend_Filter_StringToUpper())
             ->setRequired(true)
             ->setOptions(array('style' => 'width: 350px'))
             ->addValidator('StringLength', false, array(5, 200))
             ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        
        $nrMalote = new Zend_Form_Element_Text('MAPO_NR_MALOTE');
        $nrMalote->setRequired(true)
             ->setLabel('*Número Malote:')
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addFilter(new Zend_Filter_StringToUpper())
             ->setRequired(true)
             ->setOptions(array('style' => 'width: 150px'))
             ->addValidator('StringLength', false, array(5, 200));
        
        $tipoMalote = new Zend_Form_Element_Select('MAPO_IC_TIPO_MALOTE');
        $tipoMalote->setRequired(true)
                 ->setLabel('*Externo:');
         
        $ativo = new Zend_Form_Element_Checkbox('MAPO_IC_ATIVO');
        $ativo->setRequired(true)
                 ->setLabel('*Ativo:');
        
        $consultar = new Zend_Form_Element_Submit('Consultar');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tipo,
                                $origem,
                                $destino,
                                $descExterno,
                                $matricula,
                                $tipoMalote,
                                $ativo,
                                $nrMalote,
                                $consultar,
                                $submit));
    }
}