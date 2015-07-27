<?php

$js = <<<JS
(function() {
    tinymce.create('tinymce.plugins.EnvoyConnectMergeTags', {
        init : function(ed, url) {
            ed.addButton('bbconnect_merge_tags_button', {
                title : 'Insert Tag',
                cmd : 'bbconnect_merge_tags_button',
                image : url + '/favicon.png'
            });
 
            ed.addCommand('bbconnect_merge_tags_button', function() {
                tb_show(null,bbconnectAdminAjax.mergeref+'&KeepThis=true&TB_iframe=true&height=400&width=600',null);
            });
            
        },
        // ... Hidden code
    });
    
    tinymce.PluginManager.add( "EnvoyConnectMergeTags", tinymce.plugins.EnvoyConnectMergeTags);
})();

JS;

header("Content-type: text/javascript");
echo $js;
exit();
?>