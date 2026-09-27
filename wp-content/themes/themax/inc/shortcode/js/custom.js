(function() {  
 
	//Insert Button Shortcode
    tinymce.create('tinymce.plugins.block_learn_more', {  
        init : function(ed, url) {  
            ed.addButton('block_learn_more', {  
                title : 'Create New Block Learn More',  
                image : url + '/../images/insert_shortcode_tinymce.png',  
                onclick : function() {  
                    ed.selection.setContent('[block_learn_more]' + ed.selection.getContent() + '[/block_learn_more]');  
				}  
            }); 

        },  
        createControl : function(n, cm) {  
            return null;  
        },  
    });  
    tinymce.PluginManager.add('block_learn_more', tinymce.plugins.block_learn_more);  
})();