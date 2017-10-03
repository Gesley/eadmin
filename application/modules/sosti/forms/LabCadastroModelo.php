<?php

class Sosti_Form_LabCadastroModelo extends Zend_Form {

    public function init() {

        /*
          MODE_ID_MODELO
          MODE_ID_GRUPO_MAT_SERV
          MODE_ID_MARCA
          MODE_DS_MODELO
          MODE_CD_MAT_INCLUSAO
          MODE_DT_INCLUSAO
         */

        $this->setAction('')
                ->setMethod('post')
                ->setName('CadastroModelo');

        $marc_id_modelo = new Zend_Form_Element_Hidden('MODE_ID_MODELO');
        $marc_id_modelo->addFilter('Int')
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $marc_ds_modelo = new Zend_Form_Element_Text('MODE_DS_MODELO');
        $marc_ds_modelo->setRequired(true)
                ->setLabel('*Descrição do modelo:')
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->setAttrib('style', 'width: 400px;')
                ->setAttrib('maxLength', 60)
                ->addValidator('StringLength', false, array(5, 60));

        //Busca os grupos
        $OcsTbGrupGrupo = new Application_Model_DbTable_OcsTbGrupGrupo();
        $grupos = $OcsTbGrupGrupo->getGrupos();

        $mode_id_grupo_mat_serv = new Zend_Form_Element_Select('MODE_ID_GRUPO_MAT_SERV');
        $mode_id_grupo_mat_serv->setLabel('*Grupo')
                ->setAttrib('style', 'width: 400px;')
                ->setRequired(true)
                ->addMultiOptions(array("" => "::Selecione um Grupo::"));
        foreach ($grupos as $g) {
            $mode_id_grupo_mat_serv->addMultiOptions(array($g["GRUP_ID_GRUPO_MAT_SERV"] => $g["GRUP_DS_GRUPO_MAT_SERV"]));
        }

        $OcsTbMarcMarca = new Application_Model_DbTable_OcsTbMarcMarca();
        $MarcMarca = $OcsTbMarcMarca->fetchAll(null, 'MARC_DS_MARCA ASC');

        $lhdw_ds_marca = new Zend_Form_Element_Select('MODE_ID_MARCA');
        $lhdw_ds_marca->setLabel('*Marca:')
                ->setAttrib('style', 'width: 400px;')
                ->setRequired(true)
                ->addMultiOptions(array("" => "::Selecione a marca::"));


        $mode_cd_mat_inclusao = new Zend_Form_Element_Hidden('MODE_CD_MAT_INCLUSAO');
        $mode_cd_mat_inclusao->setRequired(false)
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $mode_dt_inclusao = new Zend_Form_Element_Hidden('MODE_DT_INCLUSAO');
        $mode_dt_inclusao->setRequired(false)
                ->removeDecorator('Label')
                ->removeDecorator('HtmlTag');

        $submit = new Zend_Form_Element_Submit('Salvar');
        $submit->setAttrib('class', 'novo');
        
        $obrigatorio = new Zend_Form_Element('OBRIGATORIO');
        $obrigatorio->setLabel('*Campos Obrigatórios.')
                ->setAttrib('style', 'display: none;');

        $this->addElements(array($marc_id_modelo, $mode_id_grupo_mat_serv, $lhdw_ds_marca, $marc_ds_modelo,
            $mode_cd_mat_inclusao, $mode_id_marca, $mode_dt_inclusao, $submit, $obrigatorio));
    }

}