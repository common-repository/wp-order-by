jQuery(document).ready(function($){
	
	var parentPadding = parseInt($('#form_container').parent().css('padding-bottom'));
	var adminbar = $('#wpadminbar');

	var adminbarHeight = adminbar ? $('#wpadminbar').height() : 0;
	var offset = (parentPadding !== 'NaN') ? parentPadding : 0;
	var extraOffset = 10;
	$('#form_container').height($(window).height()-offset-adminbarHeight-extraOffset);

 	$( window ).resize(function() {
 		$('#form_container').height(0);
		$('#form_container').height($(window).height()-offset-adminbarHeight-extraOffset);
 	})
	
	/*******************************option panels*******************************************/
	//change name of other field groups then the selected one on setting pages, that it won't run over the actual selection
	var fieldsOrgName = $('#order_custom_fields').attr('name');
	if($('input[name^="'+fieldsOrgName+'"]:checked').attr('value')) {
		$('#order_custom_fields').attr('name',$('#order_custom_fields').attr('name')+'-not_active');
		$('input[name="wpob-cf-type"]').prop('disabled',true);
		$('input[name="wpob_other_posts"]').prop('disabled',true);
		//$('input[name="wpob_cf_order"]').prop('disabled',true);
	} else {	
		if($('#order_custom_fields option:selected').val() == "") {
			$('#order_date_desc').attr('checked','true'); //set the default option when no option is selected
			$('input[name="wpob-cf-type"]').prop('disabled',true);
			$('input[name="wpob_other_posts"]').prop('disabled',true);
			//$('input[name="wpob_cf_order"]').prop('disabled',true);
		}
	}
	
	//init custom type radio buttons
	if($('#order_custom_fields').attr('value') == '') {
		//$('#cf_desc').attr('checked',true)
	}
	
	//form elements on change event
	$('input[name^="'+fieldsOrgName+'"]').change(function(){
		$('input[name^="'+fieldsOrgName+'"]').attr('name',fieldsOrgName);
		$('#order_custom_fields').attr('name',$('#order_custom_fields').attr('name')+'-not_active');
		$('#order_custom_fields option').prop('selected',false);
		$('input[name="wpob-cf-type"]').prop('checked',false);
		$('input[name="wpob-cf-type"]').prop('disabled',true);
		$('input[name="wpob_other_posts"]').prop('checked',false);
		$('input[name="wpob_other_posts"]').prop('disabled',true);
		//$('input[name="wpob_cf_order"]').prop('disabled',true);
		
	})
	$('#order_custom_fields').change(function(){
		$('#order_custom_fields').attr('name',fieldsOrgName);
		$('input[name^="'+fieldsOrgName+'"]').attr('name',$('#order_custom_fields').attr('name')+'-not_active');
		$('input[name^="'+fieldsOrgName+'"]').prop('checked',false);
		$('input[name="wpob-cf-type"]').prop('disabled',false);
		$('input[name="wpob_other_posts"]').prop('disabled',false);
		//$('input[name="wpob_cf_order"]').prop('disabled',false);
	})
	
	//no custom fields
	if($('#order_custom_fields option').length == 1) { //no options, only the default one
		$('#order_custom_fields').attr('disabled','disabled');
		$('#order_custom_fields_remark').show();
	} else {
		$('#order_custom_fields_remark').hide();
	}
	
	//form validation
	function getUrlParam(pname) {
		var vars = [], hash;
		var q = document.URL.split('?')[1];
		if(q != undefined){
			q = q.split('&');
			for(var i = 0; i < q.length; i++){
				hash = q[i].split('=');
				vars.push(hash[1]);
				vars[hash[0]] = hash[1];
			}
		}
		return vars[pname]
	}
	
	$('form').submit(function() {
		
		var checked = false;
		var pt = getUrlParam('post_type');
		var page = getUrlParam('page');
		pt = pt ? pt : (page=='wpob-settings-wpob_general' ? '' : 'post');
		$('input[name^="wpob-options-'+pt+'"]').each(function(){
			if($(this).prop('checked')) checked = true;
		})
		if(!checked && $('input[value="numeric"]').prop('checked') == false && $('input[value="string"]').prop('checked') == false ) {
			alert('Please choose the type of the custom type');
			return false;
		}
		
	})
	
})

