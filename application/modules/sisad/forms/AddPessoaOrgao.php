<?php

/*
 * Forma que chama todos os campos do add, poar 
 */

class Sisad_Form_AddPessoaOrgao extends Zend_Form {

    public function init() {
        $addSession = new Zend_Session_Namespace('addSession');
        $userNs = new Zend_Session_Namespace('userNs');
        $tbPjur = new Application_Model_DbTable_OcsTbPjurPessoaJuridica();
        $tipoEndereco = $tbPjur->getTipoEndereco();

        $tbCapitalUf = new Application_Model_DbTable_CapitalUF();
        $ufs = $tbCapitalUf->getCapitalUF();
        
        $this->setAction('add')
                ->setMethod('post')
                ->setName('addPessoa');

        /**
         * 001:Dados Coletivos
         */
        $ID_PESSOA = new Zend_Form_Element_Hidden('ID_PESSOA');
        $ID_PESSOA->setValue(null);

        $ID_TP_ENDERECO = new Zend_Form_Element_Select('PEND_ID_TP_ENDERECO');
        $ID_TP_ENDERECO->setRequired(false)
                ->setLabel('*Tipo do Endereço:');
        foreach ($tipoEndereco as $value) {
            $ID_TP_ENDERECO->addMultiOptions(array($value["PTEN_ID_TP_ENDERECO"] => $value["PTEN_NO_TP_ENDERECO"]));
        }

        $DS_ENDERECO = new Zend_Form_Element_Text('PEND_DS_ENDERECO');
        $DS_ENDERECO->setRequired(false)
                ->setLabel('*Endereço Completo:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->setOptions(array('style' => 'width: 350px'))
                ->addValidator('StringLength', false, array(5, 200));

        $NR_CEP = new Zend_Form_Element_Text('PEND_NR_CEP');
        $NR_CEP->setRequired(false)
                ->setLabel('*CEP:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->addValidator('Alnum', false, true)
                ->setOptions(array('style' => 'width: 350px'))
                ->addValidator('StringLength', false, array(5, 9));
        //001

        /**
         * Dados Pessoa JURIDICA
         */
        $NO_RAZAO_SOCIAL = new Zend_Form_Element_Text('PJUR_NO_RAZAO_SOCIAL');
        $NO_RAZAO_SOCIAL->setRequired(false)
                ->setLabel('*Orgão Destino:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->setOptions(array('style' => 'width: 500px'))
                ->addValidator('StringLength', false, array(5, 200))
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');

        $NR_CNPJ = new Zend_Form_Element_Text('PJUR_NR_CNPJ');
        $NR_CNPJ->setRequired(false)
                ->setLabel('*CNPJ:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->addValidator(new App_Validate_Cnpj())
                ->setOptions(array('style' => 'width: 350px'))
                ->addValidator('StringLength', false, 18);

        $NO_FANTASIA = new Zend_Form_Element_Text('PJUR_NO_FANTASIA');
        $NO_FANTASIA->setRequired(false)
                ->setLabel('*Nome Fantasia:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->setOptions(array('style' => 'width: 350px'))
                ->addValidator('StringLength', false, array(5, 200));

        $IC_PORTE = new Zend_Form_Element_Select('PJUR_IC_PORTE');
        $IC_PORTE->setRequired(false)
                ->setLabel('*Porte:')
                ->addMultiOptions(array('S' => 'Microempresa e Empresa de Pequeno Porte', 'N' => 'Multinacional'));
        // FIM JURIDICA

        /**
         * Cadastro Pessoa FISICA
         */
        $NR_CPF = new Zend_Form_Element_Text('PNAT_NR_CPF');
        $NR_CPF->setRequired(false)
                ->setLabel('*CPF:')
                //->addFilter('StringTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->setOptions(array('style' => 'width: 200px'))
                ->addValidator('StringLength', false, 11)
                ->addValidator(new App_Validate_Cpf());

        $NO_PESSOA = new Zend_Form_Element_Text('PNAT_NO_PESSOA');
        $NO_PESSOA->setRequired(false)
                ->setLabel('*Nome:')
                //->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addFilter(new Zend_Filter_StringToUpper())
                ->setOptions(array('style' => 'width: 500px'))
                ->addValidator('StringLength', false, array(5, 100))
                ->setDescription('A lista será carregada após digitar no mínimo três caracteres. Selecione uma unidade  da lista.');

        $DT_NASCIMENTO = new Zend_Form_Element_Text('PNAT_DT_NASCIMENTO');
        $DT_NASCIMENTO->setRequired(false)
                ->setLabel('Nascimento')
                ->addValidator('StringLength', false, 12);

        $CD_LOCAL_NASCIMENTO = new Zend_Form_Element_Select('PNAT_CD_LOCAL_NASCIMENTO');
        $CD_LOCAL_NASCIMENTO->setRequired(false)
                ->setLabel('Naturalidade')
                ->addMultiOptions(array('0' => ':: Selecione ::'));
        foreach ($ufs as $value) {
            $CD_LOCAL_NASCIMENTO->addMultiOptions(array($value["CAP_UF"] => $value["NOME"]));
        }

        $NR_IDENTIDADE = new Zend_Form_Element_Text('PNAT_NR_IDENTIDADE');
        $NR_IDENTIDADE->setRequired(false)
                ->setLabel('Identidade')
                ->addValidator('StringLength', false, array(5, 15));

        $SG_ORGAO_EMISSOR_ID = new Zend_Form_Element_Text('PNAT_SG_ORGAO_EMISSOR_ID');
        $SG_ORGAO_EMISSOR_ID->setRequired(false)
                ->setLabel('Orgão Emissor')
                ->addValidator('StringLength', false, array(5, 15));

        $DH_EMISSAO_ID = new Zend_Form_Element_Text('PNAT_DH_EMISSAO_ID');
        $DH_EMISSAO_ID->setRequired(false)
                ->setLabel('Emissão')
                ->addValidator('StringLength', false, 12);

        $SG_UF_EMISSOR_ID = new Zend_Form_Element_Select('PNAT_SG_UF_EMISSOR_ID');
        $SG_UF_EMISSOR_ID->setRequired(false)
                ->setLabel('UF')
                ->addMultiOptions(array('0' => ':: Selecione ::'));
        foreach ($ufs as $value) {
            $SG_UF_EMISSOR_ID->addMultiOptions(array($value["CAP_UF"] => $value["NOME"]));
        }

        // FISICA

        
        $SUBMIT = new Zend_Form_Element_Submit('Salvar');

        $this->addElements(array($ID_PESSOA,
            $ID_TP_ENDERECO,
            $DS_ENDERECO,
            $NR_CEP,
            $NO_RAZAO_SOCIAL,
            $NR_CNPJ,
            $NO_FANTASIA,
            $IC_PORTE,
            $NR_CPF,
            $NO_PESSOA,
            $DT_NASCIMENTO,
            $CD_LOCAL_NASCIMENTO,
            $NR_IDENTIDADE,
            $SG_ORGAO_EMISSOR_ID,
            $DH_EMISSAO_ID,
            $SG_UF_EMISSOR_ID,
            $SUBMIT));
    }

}