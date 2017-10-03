<?php
/*
 * Forma que chama todos os campos do add, poar 
 */

class Sisad_Form_ProtocoloAddOrgao extends Zend_Form
{
    public function init()
    {
        $addSession = new Zend_Session_Namespace('addSession');
        $tbPjur = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $tipoEndereco = $tbPjur->getTipoEndereco();
        
        $this->setAction('add')
             ->setMethod('post')
             ->setName('AddProtocolo');
        
        /*
         * Dados do Orgão
         */
        $tipo = new Zend_Form_Element_Hidden('TIPO');
        $tipo->setValue('addOrgão');
        
        $idPjur = new Zend_Form_Element_Hidden('PJUR_ID_PESSOA');
        $idPjur->setValue('NULO');
        
        $razaoSocial = new Zend_Form_Element_Text('PJUR_NO_RAZAO_SOCIAL');
        $razaoSocial->setRequired(true)
                     ->setLabel('*Orgão Destino:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200))
                     ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');
        
        $nrCNPJ = new Zend_Form_Element_Text('PJUR_NR_CNPJ');
        $nrCNPJ->setRequired(true)
                     ->setLabel('*CNPJ:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200));

        $nomeFantasia = new Zend_Form_Element_Text('PJUR_NO_FANTASIA');
        $nomeFantasia->setRequired(true)
                 ->setLabel('*Nome Fantasia:')
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addFilter(new Zend_Filter_StringToUpper())
                 ->setRequired(true)
                 ->setOptions(array('style' => 'width: 350px'))
                 ->addValidator('StringLength', false, array(5, 200));

        $porte = new Zend_Form_Element_Select('PJUR_IC_PORTE');
        $porte->setRequired(true)
                 ->setLabel('*Porte:')
                 ->addMultiOptions(array('S' => 'Microempresa e Empresa de Pequeno Porte','N' => 'Multinacional'));
        
        /*
         * Dados de endereçamento
         */
        $tpEndereco = new Zend_Form_Element_Select('PEND_ID_TP_ENDERECO');
        $tpEndereco->setRequired(true)
                 ->setLabel('*Tipo do Endereço:');
         foreach ($tipoEndereco as $value) {
                 $tpEndereco->addMultiOptions(array($value["PTEN_ID_TP_ENDERECO"] => $value["PTEN_NO_TP_ENDERECO"]));
         }
         
        $endereco = new Zend_Form_Element_Text('PEND_DS_ENDERECO');
        $endereco->setRequired(true)
                     ->setLabel('*Endereço Completo:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(5, 200));
         
        $CEP = new Zend_Form_Element_Text('PEND_NR_CEP');
        $CEP->setRequired(true)
                     ->setLabel('*CEP:')
                     ->addFilter('StripTags')
                     ->addFilter('StringTrim')
                     ->addFilter(new Zend_Filter_StringToUpper())
                     ->setRequired(true)
                     ->addValidator('Alnum', false, true)
                     ->setOptions(array('style' => 'width: 350px'))
                     ->addValidator('StringLength', false, array(8, 200));
        
        $consultar = new Zend_Form_Element_Submit('Consultar');
        
        $submit = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($tipo,
                                $idPjur,
                                $razaoSocial,
                                $nrCNPJ,
                                $nomeFantasia,
                                $porte,
                                $tpEndereco,
                                $endereco,
                                $CEP,
                                $consultar,
                                $submit));
    }
}