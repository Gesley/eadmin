<?php
class Sosti_Form_Conformidadeform extends Zend_Form
{
    public function init()
    {
       		$userNamespace = new Zend_Session_Namespace('userNs'); 
    		$SadTbCxgsGrupoServico = new Application_Model_DbTable_SadTbCxgsGrupoServico();  
    		$SgrsGrupoServicoRows = $SadTbCxgsGrupoServico->getTodasCaixas();
       		
    		$indicadorNivel =  new Zend_Form_Element_Select('SOTC_ID_INDICADOR');
       		$indicadorNivel->setLabel('Indicador:');
       		$indicadorNivel->addMultiOptions(array(''=>'::Selecione o grupo de serviço primeiro::'));
       		
       		$gruposervico =  new Zend_Form_Element_Select('SOTC_ID_GRUPO');
       		$gruposervico->setLabel('Grupo de Serviço:');
       		$gruposervico->addMultiOptions(array('' => '::SELECIONE::'));
       		$gruposervico->setRequired(true);
       		
       		foreach ($SgrsGrupoServicoRows as $SgrsGrupoServico_p):
            $gruposervico->addMultiOptions(array(Zend_Json::encode($SgrsGrupoServico_p) => substr($SgrsGrupoServico_p["CXEN_DS_CAIXA_ENTRADA"], 9)));
        	endforeach; 
       		
       		$matriculaInclusao = new Zend_Form_Element_Hidden('SOTC_CD_MATRICULA_INCLUSAO');
       		$matriculaInclusao->removeDecorator('Label');
            $matriculaInclusao->removeDecorator('HtmlTag');
            
            $descricao = new Zend_Form_Element_Text('SOTC_DS_CONFORMIDADE');
            $descricao->setLabel('Descrição da não Conformidade');
            $descricao->setRequired(true);
            $descricao->setAttrib('size', 80);
            
            $dataInicio = new Zend_Form_Element_Hidden('SOTC_DH_INICIO_CONFORMIDADE');
            $dataInicio->removeDecorator('Label');
            $dataInicio->removeDecorator('HtmlTag');
            
            $dataFim = new Zend_Form_Element_Text('SOTC_DH_FIM_CONFORMIDADE');
            $dataFim->setLabel('Data Fim conformidade');
            
			$confId = new Zend_Form_Element_Hidden('CONFORMIDADE_ID');
            $confId->removeDecorator('Label');
            $confId->removeDecorator('HtmlTag');
            
            $submit = new Zend_Form_Element_Submit('Salvar');
             
            
            $this->addElements(array($gruposervico,$indicadorNivel,$descricao,$matriculaInclusao,$dataInicio,$dataFim,$submit,$confId));
            
    }
}