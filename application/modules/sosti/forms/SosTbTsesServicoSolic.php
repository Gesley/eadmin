<?php
class Sosti_Form_SosTbTsesServicoSolic extends Zend_Form
{
    public function init()
    {
        $this->setAction('')
             ->setMethod('post')
             ->setName('ServicoSolic');
         
        $sses_ic_video_realizada = new Zend_Form_Element_Checkbox('SSES_IC_VIDEO_REALIZADA');
        $sses_ic_video_realizada->setLabel('*Videoconferência(s) realizada(s):')
                        ->addFilter('StripTags')
                        ->addFilter('StringTrim')
                        ->addValidator('NotEmpty')
                        ->setCheckedValue('S')
                        ->setUncheckedValue('N');
        
        $this->addElements(array($sses_ic_video_realizada));
    }
}