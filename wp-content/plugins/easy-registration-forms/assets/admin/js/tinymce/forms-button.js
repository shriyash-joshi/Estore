(function() {
    tinymce.PluginManager.add('erf_forms_button', function( editor, url ) {
        if(erf_form_names.length>0){
            editor.addButton( 'erf_forms_button', {
            text: 'Insert ERF Shortcodes',
            icon: false,
            type: 'listbox',
            onselect: function (e) {
                if(this.value()=='login_form')
                    editor.insertContent('[erforms_login]');
                else
                    editor.insertContent('[erforms id="' + this.value() + '"]');
            },
            values: erf_form_names
            });
        }
        
    });
})();