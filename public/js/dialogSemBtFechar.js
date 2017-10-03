/**
 * Extende a Classe Dialog do dojo e retira o botão fechar da Dialog
 *
 */
    dojo.provide("dijit.Dialog");

    dojo.declare
    (
    "dijit.DialogSemBtFechar",
    [dijit.Dialog],
    {
        // summary:
        // extended version of the dojo Dialog widget with the option to disable
        // the close button and supress the escape key.
        disableCloseButton: true,
        /* postCreate */
        postCreate: function()
        {
            this.inherited(arguments);
            this._updateCloseButtonState();
        },
        /*  _onKey */
        _onKey: function(evt)
        {
            if(this.disableCloseButton && evt.charOrCode == dojo.keys.ESCAPE) return;
            this.inherited(arguments);
        },
        /*  setCloseButtonDisabled*/
        setCloseButtonDisabled: function(flag)
        {
            this.disableCloseButton = flag;
            this._updateCloseButtonState();
        },
        /*  _updateCloseButtonState */
        _updateCloseButtonState: function()
        {
            dojo.style(this.closeButtonNode,
            "display",this.disableCloseButton ? "none" : "block");
        }
    }
    );

