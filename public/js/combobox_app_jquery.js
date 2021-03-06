/**
 * Construtor do combobox da jquery
 * Como modificações.
 * @example 	$(function() {
                	$( "#DOCM_ID_PCTT" ).combobox();
            		$( "#combobox-input-text-DOCM_ID_PCTT" ).attr('style','width: 500px;');
                        $( "#combobox-input-button-DOCM_ID_PCTT" ).attr('style','display: none;');
                })
 * 
 **/
(function( $ ) {
        $.widget( "ui.combobox", {
                _create: function() {
                        var self = this,
                                select = this.element.hide(),
                                selected = select.children( ":selected" ),
                                value = selected.val() ? selected.text() : "";
                        var input = this.input = $( "<input>" )
                                .insertAfter( select )
                                .val( value )
                                .autocomplete({
                                        delay: 500,
                                        minLength: 3,
                                        source: function( request, response ) {
                                                var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                                                response( select.children( "option" ).map(function() {
                                                        var text = $( this ).text();
                                                        if ( this.value && ( !request.term || matcher.test(text) ) )
                                                                return {
                                                                        label: text.replace(
                                                                                new RegExp(

                                                                                        "(?![^&;]+;)(?!<[^<>]*)(" +
                                                                                        $.ui.autocomplete.escapeRegex(request.term) +
                                                                                        ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                                                                ), "<strong>$1</strong>" ),
                                                                        value: text,
                                                                        option: this
                                                                };


                                                }) );
                                        },
                                        select: function( event, ui ) {
                                                ui.item.option.selected = true;
                                                self._trigger( "selected", event, {
                                                        item: ui.item.option
                                                });
                                        },
                                        change: function( event, ui ) {
                                                if ( !ui.item ) {
                                                        var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
                                                                valid = false;
                                                        select.children( "option" ).each(function() {
                                                                if ( $( this ).text().match( matcher ) ) {
                                                                        this.selected = valid = true;
                                                                        return false;
                                                                }
                                                        });
                                                        if ( !valid ) {
                                                                // remove invalid value, as it didn't match anything
                                                                $( this ).val( "" );
                                                                select.val( "" );
                                                                input.data( "autocomplete" ).term = "";
                                                                return false;
                                                        }
                                                }
                                        }
                                })
                                .addClass( "x-form-text" )
                                /*nomeia o input gerado pelo autocomplete como a palavra combobox seguido de input seguido de text seguido do id do select separado por -*/
                                .attr('id', 'combobox-input-text-'+select.attr('id'));

                        input.data( "autocomplete" )._renderItem = function( ul, item ) {
                                return $( "<li></li>" )
                                        .data( "item.autocomplete", item )
                                        .append( "<a>" + item.label + "</a>" )
                                        .appendTo( ul ); 
                        };

                        this.button = $( "<button type='button'>&nbsp;</button>" )
                                .attr( "tabIndex", -1 )
                                .attr( "title", "Mostrar todos os itens." )
                                .insertAfter( input )
                                .button({
                                        icons: {
                                                primary: "ui-icon-triangle-1-s"
                                        },
                                        text: false
                                })
                                .removeClass( "ui-corner-all" )
                                .attr('style', 'width: 20px; height: 19px; top: 4px; left: -20px;')
                                /*nomeia o input gerado pelo autocomplete como a palavra combobox seguido de input seguido de button seguido do id do select separado por -*/
                                .attr('id', 'combobox-input-button-'+select.attr('id'))
                                .click(function() {
                                        // close if already visible
                                        if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                                                input.autocomplete( "close" );
                                                return;
                                        }

                                        // work around a bug (likely same cause as #5265)
                                        $( this ).blur();

                                        // pass empty string as value to search for, displaying all results
                                        input.autocomplete({
                                            delay: 500,
                                            minLength: 0
                                        });
                                        input.autocomplete( "search", "" );
                                        input.autocomplete({
                                            delay: 500,
                                            minLength: 3
                                        });
                                        input.focus();

                                });
                },

                destroy: function() {
                        this.input.remove();
                        this.button.remove();
                        this.element.show();
                        $.Widget.prototype.destroy.call( this );
                }
        });

})( jQuery );

function init_combobox_app_jquery(){
    
    (function( $ ) {
        $.widget( "ui.combobox", {
                _create: function() {
                        var self = this,
                                select = this.element.hide(),
                                selected = select.children( ":selected" ),
                                value = selected.val() ? selected.text() : "";
                        var input = this.input = $( "<input>" )
                                .insertAfter( select )
                                .val( value )
                                .autocomplete({
                                        delay: 500,
                                        minLength: 3,
                                        source: function( request, response ) {
                                                var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
                                                response( select.children( "option" ).map(function() {
                                                        var text = $( this ).text();
                                                        if ( this.value && ( !request.term || matcher.test(text) ) )
                                                                return {
                                                                        label: text.replace(
                                                                                new RegExp(

                                                                                        "(?![^&;]+;)(?!<[^<>]*)(" +
                                                                                        $.ui.autocomplete.escapeRegex(request.term) +
                                                                                        ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                                                                ), "<strong>$1</strong>" ),
                                                                        value: text,
                                                                        option: this
                                                                };


                                                }) );
                                        },
                                        select: function( event, ui ) {
                                                ui.item.option.selected = true;
                                                self._trigger( "selected", event, {
                                                        item: ui.item.option
                                                });
                                        },
                                        change: function( event, ui ) {
                                                if ( !ui.item ) {
                                                        var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
                                                                valid = false;
                                                        select.children( "option" ).each(function() {
                                                                if ( $( this ).text().match( matcher ) ) {
                                                                        this.selected = valid = true;
                                                                        return false;
                                                                }
                                                        });
                                                        if ( !valid ) {
                                                                // remove invalid value, as it didn't match anything
                                                                $( this ).val( "" );
                                                                select.val( "" );
                                                                input.data( "autocomplete" ).term = "";
                                                                return false;
                                                        }
                                                }
                                        }
                                })
                                .addClass( "x-form-text" )
                                /*nomeia o input gerado pelo autocomplete como a palavra combobox seguido de input seguido de text seguido do id do select separado por -*/
                                .attr('id', 'combobox-input-text-'+select.attr('id'));

                        input.data( "autocomplete" )._renderItem = function( ul, item ) {
                                return $( "<li></li>" )
                                        .data( "item.autocomplete", item )
                                        .append( "<a>" + item.label + "</a>" )
                                        .appendTo( ul ); 
                        };

                        this.button = $( "<button type='button'>&nbsp;</button>" )
                                .attr( "tabIndex", -1 )
                                .attr( "title", "Show All Items" )
                                .insertAfter( input )
                                .button({
                                        icons: {
                                                primary: "ui-icon-triangle-1-s"
                                        },
                                        text: false
                                })
                                .removeClass( "ui-corner-all" )
                                .attr('style', 'width: 20px; height: 19px; top: 4px; left: -20px;')
                                /*nomeia o input gerado pelo autocomplete como a palavra combobox seguido de input seguido de button seguido do id do select separado por -*/
                                .attr('id', 'combobox-input-button-'+select.attr('id'))
                                .click(function() {
                                        // close if already visible
                                        if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
                                                input.autocomplete( "close" );
                                                return;
                                        }

                                        // work around a bug (likely same cause as #5265)
                                        $( this ).blur();

                                        // pass empty string as value to search for, displaying all results
                                        input.autocomplete({
                                            delay: 500,
                                            minLength: 0
                                        });
                                        input.autocomplete( "search", "" );
                                        input.autocomplete({
                                            delay: 500,
                                            minLength: 3
                                        });
                                        input.focus();

                                });
                },

                destroy: function() {
                        this.input.remove();
                        this.button.remove();
                        this.element.show();
                        $.Widget.prototype.destroy.call( this );
                }
        });

    })( jQuery );

}
