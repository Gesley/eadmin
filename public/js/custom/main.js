dojo.provide("custom.main");

(function(){
dojo.registerModulePath("custom", "/base/public/js/custom/");
dojo.require("dojox.data.QueryReadStore");
dojo.require("dojox.grid.EnhancedGrid");
dojo.require("dojox.grid.enhanced.plugins.IndirectSelection");
dojo.require("dijit.MenuBarItem");
dojo.require("dijit.MenuBar");
dojo.require("custom.main");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.AccordionContainer");
dojo.require("dijit.Tree");
dojo.require("dojox.widget.Toaster");
})();