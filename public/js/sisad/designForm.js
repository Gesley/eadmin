/**
 * @category    SIDAD
 * @copyright	Copyright (c) 2010-2014 Tribunal Regional Federal BRZ (http://www.trf1.jus.br)
 * @author		Leidison Siqueira Barbosa [leidison_14@hotmail.com]
 * @license		FREE, keep original copyrights
 * @version		controlada pelo SVN
 * 
 * Script de ações relativas a personalização de formularios
 */
$(function() {
    $("input:text").addClass("x-form-text");
    $("select").addClass("x-form-text");
    $("input.datepicker").datepicker($.datepicker.regional["pt_BR"]);
    $("textarea").addClass("x-form-field");
});