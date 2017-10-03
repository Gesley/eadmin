<?php

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license
 * It is  available through the world-wide-web at this URL:
 * http://www.petala-azul.com/bsd.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to geral@petala-azul.com so we can send you a copy immediately.
 *
 * @package    Bvb_Grid
 * @copyright  Copyright (c)  (http://www.petala-azul.com)
 * @license    http://www.petala-azul.com/bsd.txt   New BSD License
 * @version    $Id: Table.php 1796 2011-07-01 22:54:02Z bento.vilas.boas@gmail.com $
 * @author     Bento Vilas Boas <geral@petala-azul.com >
 */
class Bvb_Grid_Template_Table {

    public $hasExtraRow = 0;
    public $hasFilters = 1;
    public $i = 0;
    public $insideLoop;
    public $options;
    public $export;

    public $buildAbstract = false;

    public $result = array();

    public function buildAttr($class, $value)
    {
        if (strlen($value) == 0) {
            return '';
        }

        // classe de cada linha de registro
        return $class . "='$value'";
    }

    public function getClass($name)
    {

        if (isset($this->options['userDefined']['cssClass'][$name])) {
            return ' class="' . $this->options['userDefined']['cssClass'][$name] . '" ';
        }

        return '';
    }

    public function globalStart()
    {
        if($this->buildAbstract)
                return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= PHP_EOL;
        $strRetorno .= PHP_EOL;
        $strRetorno .= "<div id='gridLinhas'>" . PHP_EOL;
        $strRetorno .= "<table ";
        $strRetorno .= $this->getClass('table') . " ";
        $strRetorno .= "align=\"center\" ";
        $strRetorno .= "cellspacing=\"0\" ";
        $strRetorno .= "cellpadding=\"0\" >" . PHP_EOL;

        return $strRetorno;
    }

    public function extra($value)
    {
        // Botões de filtro e ordenação
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= PHP_EOL;
        $strRetorno .= "<div style='text-align: right;'>" . PHP_EOL;
        $strRetorno .= "    $value" . PHP_EOL;
        $strRetorno .= "</div>" . PHP_EOL;
        $strRetorno .= "<br />" . PHP_EOL;
        $strRetorno .= PHP_EOL;

        return $strRetorno;
        // return "";
    }

    public function globalEnd()
    {

        if($this->buildAbstract)
                return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "</table>" . PHP_EOL;
        $strRetorno .= "</div>" . PHP_EOL;

        return $strRetorno;
    }

    public function titlesStart()
    {
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "    <thead>" . PHP_EOL;
        $strRetorno .= "        <tr>" . PHP_EOL;

        return $strRetorno;
    }

    public function titlesLoop($title, $colspan)
    {
        // Cabeçalho de cada campo
        $this->result['titles'][] = func_get_args();

        if($this->buildAbstract)
            return;

        $strRetorno = "";
        $strRetorno .= "            <th";
        $strRetorno .= $this->buildAttr('colspna', $colspan) . " ";
        $strRetorno .= ">";
        $strRetorno .= $title;
        $strRetorno .= "</th> " . PHP_EOL;

        return $strRetorno;
    }

    public function titlesEnd()
    {
        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "        </tr>" . PHP_EOL;
        $strRetorno .= PHP_EOL;

        return $strRetorno;
    }

    public function filtersStart()
    {
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        // Azul - header TRF
        $strRetorno = "";
        $strRetorno .= "        <tr style='background: #313F59;'>" . PHP_EOL;

        return $strRetorno;
    }

    public function filtersLoop($value, $colspan)
    {
        $this->result['filters'][] = func_get_args();

        if($this->buildAbstract)
        	return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "            <th";
        $strRetorno .= $this->buildAttr('colspan', $colspan);
        $strRetorno .= $this->getClass('filters');
        $strRetorno .= ">";
        $strRetorno .= $value;
        $strRetorno .= "</th>" . PHP_EOL;

        return $strRetorno;
    }

    public function filtersEnd()
    {
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "        </tr>" . PHP_EOL;
        $strRetorno .= "    </thead>" . PHP_EOL;
        $strRetorno .= PHP_EOL;
        $strRetorno .= "    <tbody>" . PHP_EOL;

        return $strRetorno;
    }

    public function noResults($message)
    {
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        // $strRetorno .= "    <tbody>" . PHP_EOL;
        $strRetorno .= "        <tr>" . PHP_EOL;
        $strRetorno .= "            <td colspan=";
        $strRetorno .= $this->options['colspan'] . " ";
        $strRetorno .= $this->getClass('noRecords');
        $strRetorno .= ">";
        $strRetorno .= $message;
        $strRetorno .= "</td>" . PHP_EOL;
        $strRetorno .= "        </tr>" . PHP_EOL;
        // $strRetorno .= "    </tbody>" . PHP_EOL;

        return $strRetorno;
    }

    public function loopStart($class, $style)
    {
        $this->i++;
        $this->insideLoop = 1;

        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "        <tr ";
        $strRetorno .= $this->buildAttr('class', $class);
        $strRetorno .= $this->buildAttr('style', $style);
        $strRetorno .= ">" . PHP_EOL;

        return $strRetorno;
    }

    public function loopLoop($value, $class, $style, $rowspan, $colspan)
    {
        // Cada campo de cada registro
        $this->result['loop'][$this->i][] = func_get_args();

        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "            <td ";
        $strRetorno .= $this->buildAttr('class', $class);
        $strRetorno .= $this->buildAttr('style', $style);
        $strRetorno .= $this->buildAttr('rowspan', $rowspan);
        $strRetorno .= $this->buildAttr('colspan', $colspan);
        $strRetorno .= ">";
        $strRetorno .= $value;
        $strRetorno .= "</td>" . PHP_EOL;

        return $strRetorno;
    }

    public function loopEnd()
    {
        if($this->buildAbstract)
            return;

        /* ASmR - Tradução ou alteração para compatibilização */
        $strRetorno = "";
        $strRetorno .= "        </tr>" . PHP_EOL;
        $strRetorno .= PHP_EOL;

        return $strRetorno;
    }

    public function hRow($value)
    {
        if($this->buildAbstract)
            return;

        // ...
        return "        <td  colspan=\"{$this->options['colspan']}\"  " .
               $this->getClass('hBar') . "><div>$value</div></td>" . PHP_EOL;
    }

    public function formMessage($sucess, $message)
    {
        if ($sucess) {
            $class = $this->getClass('formMessageOk');
        } else {
            $class = $this->getClass('formMessageError');
        }

        // Mensagem do formulário
        return "<div $class >$message</div>";
    }

    public function sqlExpStart()
    {
        if($this->buildAbstract)
            return;

        // Linha de totalização (ou outras operações estatísticas)
        return "    <tr>" . PHP_EOL;
    }

    public function sqlExpLoop($value, $class)
    {
        $this->result['sql'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return  "     <td " . $this->buildAttr('class', $class) . "" .
                $this->getClass('sqlExp') . ">$value</td>" . PHP_EOL;
    }

    public function sqlExpEnd()
    {
        if($this->buildAbstract)
                return;
        return "    </tr>" . PHP_EOL;
    }

    public function pagination($pagination, $numberRecords, $perPage, $pageSelect, $botoesAcao)
    {
		/* ASmR - Tradução ou alteração para compatibilização */
    	/*
        $this->result['pagination'] = func_get_args();
        if($this->buildAbstract)
                return;
        return "    <tfoot><tr>" . PHP_EOL . "     <td " . $this->getClass('tableFooter') .
                " colspan=\"{$this->options['colspan']}\"><div>
                <div " . $this->getClass('tableFooterExport') . ">" . $this->export . "</div>
                <div " . $this->getClass('tableFooterPagination') . "> <em>$numberRecords</em> $pagination  $perPage
                        $pageSelect</div>
                </div>
                </td>" . PHP_EOL . "</tr></tfoot>" . PHP_EOL;
        */
		$this->result['pagination'] = func_get_args();

		if($this->buildAbstract)
		    return;

		/* ASmR - Tradução ou alteração para compatibilização */
		// versão 9 - Anderson
		$strRetorno = "";
		$strRetorno .= "    </tbody>" . PHP_EOL;
		$strRetorno .= PHP_EOL;
		$strRetorno .= "        <tr>" . PHP_EOL;
		$strRetorno .= "            <td>";
		$strRetorno .= "</td>" . PHP_EOL;
		$strRetorno .= "        </tr>" . PHP_EOL;
		$strRetorno .= PHP_EOL;
		$strRetorno .= "    <tfoot>" . PHP_EOL;
		$strRetorno .= "        <tr>" . PHP_EOL;
		$strRetorno .= "            <td>";
		$strRetorno .= "</td>" . PHP_EOL;
		$strRetorno .= "        </tr>" . PHP_EOL;
		$strRetorno .= "    </tfoot>" . PHP_EOL;
		$strRetorno .= PHP_EOL;

		echo PHP_EOL . $strRetorno;
		// return $strRetorno;




		// versão 8 - Anderson
		return	"" .
		        "    </tbody>" . PHP_EOL . PHP_EOL .
				"<tr>" .
				"	<td " . $this->getClass('tableFooter') . " colspan=\"{$this->options['colspan']}\">" . PHP_EOL .
				"		<div " . $this->getClass('tableFooterPagination') . " style='text-align: center;'>" .
				"			$pagination  " .
				"		</div>" .
				"	</td>" .
				"</tr>" . PHP_EOL .
				"<tfoot>" .
				"	<tr>" .
				"		<td " . $this->getClass('tableFooter') . " colspan=\"{$this->options['colspan']}\">" . PHP_EOL .
				"			<div " . $this->getClass('tableFooterPagination') . " style='float: left;'>" . PHP_EOL .
				"				$botoesAcao " . ($botoesAcao == null ? "" : "&nbsp;" . PHP_EOL) . " $this->export " .
				"			</div>" .
				"			<div " . $this->getClass('tableFooterPagination') . " style='float: right; padding-top: 7px;'>" .
				"				$numberRecords $perPage $pageSelect " .
				"			</div>" .
				"		</td>" .
				"	</tr>" .
				"</tfoot>" . PHP_EOL;

		/* versão 8 - puro */
		/*
		return	"<tfoot>" .
				"	<tr>" . PHP_EOL .
				"		<td " . $this->getClass('tableFooter') . " colspan=\"{$this->options['colspan']}\">" .
				"			<div>" .
				"				<div " . $this->getClass('tableFooterExport') . ">" . $this->export . "</div>" .
				"				<div>Anderson Sathler #$%#$</div" .
				"				<div " . $this->getClass('tableFooterPagination') . "> <em>$numberRecords</em> $pagination  $perPage $pageSelect</div>" .
				"			</div>" .
				"		</td>" . PHP_EOL .
				"	</tr>" .
				"</tfoot>" . PHP_EOL;
		*/

		/* versão 7 - anderson */
		/*
		return	"    <tr>" . PHP_EOL .
		"      <td ".$this->getClass('tableFooter')." colspan=\"{$this->options['colspan']}\">" . PHP_EOL .
		"        <div ".$this->getClass('tableFooterPagination').">" .
		$this->export . " {{numberRecords}} " .
		"          {{pagination}} " .
		"          {{perPage}} {{pageSelect}} " .
		"        </div>" .
		"      </td>" . PHP_EOL .
		"    </tr>" . PHP_EOL;
		*/
    }

    public function images($url)
    {
    	/* ASmR - Tradução ou alteração para compatibilização */
    	/*
    	return array('asc' => "<img src=\"" . $url . "arrow_up.gif\" border=\"0\" />",
                     'desc' => "<img src=\"" . $url . "arrow_down.gif\" border=\"0\" />",
                     'delete' => "<img src=\"" . $url . "delete.png\" border=\"0\" />",
                     'detail' => "<img src=\"" . $url . "detail.png\" border=\"0\" />",
                     'edit' => "<img src=\"" . $url . "edit.png\"  border=\"0\" />");
    	*/
		return array(	'asc'			 => "<img src=\"" . $url . "arrow_up.gif\" border=\"0\" alt=\"Ordenação ascendente\" title=\"Ordenação ascendente\" />",
						'desc'			 => "<img src=\"" . $url . "arrow_down.gif\" border=\"0\" alt=\"Ordenação descendente\" title=\"Ordenação descendente\" />",
						'delete'		 => "<img src=\"" . $url . "delete.png\" border=\"0\" alt=\"Exclui este registro\" title=\"Exclui este registro\" />",
						'detail'		 => "<img src=\"" . $url . "detail.png\" border=\"0\" alt=\"Exibe detalhes deste registro\" title=\"Exibe detalhes deste registro\" />",
						'edit'			 => "<img src=\"" . $url . "edit.png\"  border=\"0\" alt=\"Edita este registro\" title=\"Edita este registro\" />",
						'incluir'		 => "<img src=\"" . $url . "zf_incluir.png\" border=\"0\" alt=\"\" title=\"\" />",
						'importar'		 => "<img src=\"" . $url . "zf_importar.png\" border=\"0\" alt=\"\" title=\"\" />",
						'detalhe'		 => "<img src=\"" . $url . "zf_visualizar.png\" border=\"0\" alt=\"\" title=\"\" />",
						'editar'		 => "<img src=\"" . $url . "zf_editar.png\" border=\"0\" alt=\"\" title=\"\" />",
						'editarcontrato' => "<img src=\"" . $url . "zf_editarcontrato.png\" border=\"0\" alt=\"\" title=\"\" />",
						'excluir'		 => "<img src=\"" . $url . "zf_excluir.png\" border=\"0\" alt=\"\" title=\"\" />",
						'leitura'		 => "<img src=\"" . $url . "zf_leitura.png\" border=\"0\" alt=\"\" title=\"\" />",
						'restaurar'		 => "<img src=\"" . $url . "zf_restaurar.png\" border=\"0\" alt=\"\" title=\"\" />",
						'inverter'		 => "<img src=\"" . $url . "inverter.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Inverter seleção\" title=\"Inverter seleção\" style=\"float: left; margin-top: 1px; margin-right: 2px; \" />",
						'tudo'			 => "<img src=\"" . $url . "tudo.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Marcar todos\" title=\"Marcar todos\" style=\"float: left; margin-top: 1px; margin-right: 2px; \" />",
						'nada'			 => "<img src=\"" . $url . "nada.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Desmarcar todos\" title=\"Desmarcar todos\" style=\"float: left; margin-top: 1px; margin-right: 2px; \" />",
						'fundo'			 => "" . $url . "fundo.png"
						/*
						'inverter'		=> "<img src=\"" . $url . "inverter.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Inverter seleção\" title=\"Inverter seleção\" style=\"float: left; margin-top: 1px;\" />",
						'tudo'			=> "<img src=\"" . $url . "tudo.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Marcar todos\" title=\"Marcar todos\" style=\"float: left; margin-top: 1px;\" />",
						'nada'			=> "<img src=\"" . $url . "nada.png\" height=\"16\" width=\"16\" border=\"0\" alt=\"Desmarcar todos\" title=\"Desmarcar todos\" style=\"float: left; margin-top: 1px;\" />"
						*/
						);
    }

    public function startDetail($title)
    {
        if($this->buildAbstract)
        	return;

        return "    <tr>" . PHP_EOL . "     <th colspan='2' " . $this->getClass('detailLeft') . ">$title</th>"
                . PHP_EOL . "</tr>" . PHP_EOL;
    }

    public function detail($field, $value)
    {
        $this->result['detail'][] = func_get_args();

        if($this->buildAbstract)
            return;
        return "    <tr>" . PHP_EOL . "     <td " . $this->getClass('detailLeft') . ">$field</td><td  " .
                $this->getClass('detailRight') . ">$value</td>" . PHP_EOL . "</tr>" . PHP_EOL;
    }

    public function detailEnd($url, $text)
    {
        $this->result['detailEnd'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return "    <tr>" . PHP_EOL . "     <td colspan='2' class='detailEnd'><button type='button' class='detailReturn'
               onclick='window.location=\"$url\"';>$text</button></td>" . PHP_EOL . " </tr>" . PHP_EOL;
    }

    public function detailDelete($button)
    {
        $this->result['detailDelete'][] = func_get_args();
        if($this->buildAbstract)
                return;
        return "<tr><td colspan='2'>$button</td></tr>" . PHP_EOL;
    }

    public function export($exportDeploy, $images, $url, $gridId)
    {
        $exp = '';
        foreach ($exportDeploy as $export) {
            /* ASmR - Tradução ou alteração para compatibilização */
        	/* $caption = sprintf(Bvb_Grid_Translator::getInstance()->__('Export to %s format'), $export['caption']); */
        	$caption = sprintf(Bvb_Grid_Translator::getInstance()->__('Exportar para %s'), $export['caption']);

        	// Trocar 'exportar para print' para Imprimir
        	if ($caption == 'Exportar para print') $caption = 'Imprimir';


            $export['newWindow'] = isset($export['newWindow']) ? $export['newWindow'] : true;
            $class = isset($export['cssClass']) ? 'class="' . $export['cssClass'] . '"' : '';

            $blank = $export['newWindow'] == false ? '' : "target='_blank'";

            if (strlen($images) > 1) {
                $export['img'] = $images . $export['caption'] . '.gif';
            }

            if (isset($export['img'])) {
                $exp .= "<a title='$caption' $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>
                        <img alt='{$export['caption']}' src='{$export ['img']}' border='0'></a>";
            } else {
                $exp .= "<a title='$caption'  $class $blank href='$url/_exportTo$gridId/{$export['caption']}'>" .
                      $export['caption'] . "</a>";
            }
        }

        $this->exportWith = 25 * count($exportDeploy);
        $this->paginationWith = 630 + (10 - count($exportDeploy)) * 20;

        $this->export = $exp;

        return $exp;
    }

    public function scriptOnAjaxOpen($element)
    {
        return "document.getElementById(ponto).innerHTML= '<div style=\"width:'+(document.getElementById('" . $element . "').offsetWidth - 2)+'px;height:'+(document.getElementById('" . $element . "').offsetHeight - 2)+'px;\" " . $this->getClass('gridLoading') . ">&nbsp;</div>'";
    }

    public function scriptOnAjaxResponse($element)
    {
        return 'document.getElementById(ponto).innerHTML=xmlhttp.responseText';
    }

    public function scriptOnAjaxStateChange($element)
    {
        return '';
    }

}