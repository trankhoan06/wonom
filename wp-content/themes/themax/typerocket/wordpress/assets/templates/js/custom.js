( function($) {
  'use strict';

  //library_detail
  	function triggerChangeFormLibrary() {
			var v = jQuery("#library_detail #tr_field_librarytype").val();
			$("#library_detail .typerocket-container > .control-section:not(.typerocket-elements-fields-select)").css('display', "none");
			$("#library_detail [name='tr["+v+"]']").closest('.control-section').css('display', 'block');
		}

		function triggerChangeFormProgress() {
			var v = jQuery("#progress_detail #tr_field_librarytype").val();
			$("#progress_detail .typerocket-container > .control-section:not(.typerocket-elements-fields-select)").css('display', "none");
			$("#progress_detail [name='tr["+v+"]']").closest('.control-section').css('display', 'block');
		}

  	$(function(e) {
  		triggerChangeFormLibrary();
  		jQuery("#library_detail #tr_field_librarytype").change(function(event) {
  			triggerChangeFormLibrary();
  		});

  		triggerChangeFormProgress();
  		jQuery("#progress_detail #tr_field_librarytype").change(function(event) {
  			triggerChangeFormProgress();
  		});
  	});

  	$(".btn-toogle-feature").click(function(event) {
    	var ethis = $(this);
    	var v = $(this).data("value");
    	var id = $(this).data("id");
    	var field = $(this).data("field");
    	var icon ="tr-icon-star-empty";
    	if(v=="0"){
    		v= 1;
    		icon ="tr-icon-star-full";
    	}
    	else{
    		v=0;
    	}
    	$.ajax({
	        url: ajaxurl,
	        type: 'POST',
	        data: "action=updatepost&id="+id+"&value="+v+"&field="+field,
	        success: function(data) {
	        	//console.log(data);
	          	var jObject = jQuery.parseJSON(data);
	          	if(jObject && jObject.status==1){
		            $(ethis).data("value",v);
		            $("i",ethis).attr("class",icon);
	          	}

	        },
	        complete:function(){

	        }
	    });
	    return false;


    });

    TypeRocket.redactorSettings = {
		    formattingAdd: [{
					tag:'span',
					title:'No-wrap',
					//clear:true,
					style:'white-space: nowrap;',
					class:'no-wrap'
				}]

		}

})(jQuery);
