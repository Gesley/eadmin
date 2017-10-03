<?php
/**
 * Monta a combo de pesquisa por Grupo e por Serviço
 */
class Sosti_Form_PesqGrupoServico extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post');
        
        $SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico(); 
        $SgrsGrupoServico = $SadTbCxgsGrupoServico->getTodasCaixas();
        $sgrs_id_grupo = new Zend_Form_Element_Select('CXEN_ID_CAIXA_ENTRADA');
        $sgrs_id_grupo->setRequired(true)
                      ->setLabel('Grupo de Serviço:');
        $sgrs_id_grupo->addMultiOptions(array('' => '::SELECIONE::'));
        foreach ($SgrsGrupoServico as $SgrsGrupoServico_p):
            $sgrs_id_grupo->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        endforeach;        

        $sser_id_servico = new Zend_Form_Element_Select('SSER_ID_SERVICO');
        $sser_id_servico->setRequired(true)
                        ->setLabel('Serviço:')
                        ->setAttrib('style', 'width: 650PX;');
        
        $this->addElements(array($sgrs_id_grupo, $sser_id_servico));

    }
}