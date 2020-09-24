(function() {
    tinymce.PluginManager.add('erf_fields_button', function( editor, url ) { 
        if(erf_form_fields.length>0){
            if(editor.id=='user_act_msg' || editor.id=='user_acc_verification_msg')
                return;
            editor.addButton( 'erf_fields_button', {
            text: 'ERF Fields',
            icon: false,
            type: 'listbox',
            onselect: function (e) {
                editor.insertContent('{{' + this.value() + '}}');
            },
            values: erf_form_fields
            });
        }
    });
})();