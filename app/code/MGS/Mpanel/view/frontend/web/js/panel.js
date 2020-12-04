require([
	"jquery",
	"jquery/ui"
], function($){
	$(document).ready(function(){
		initPanelPopup();
		setSectionPanelPosition($);
		
		$('body').addClass('active_mgs_builder');
		
		if($("#sortable_home").length){
			$("#sortable_home").sortable({handle: '.sort-handle'});
		}
		
		if($(".edit-panel.parent-panel").length){
			$('.edit-panel.parent-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.container-panel.hover').removeClass('hover');
			});
		}
		
		if($(".static-can-edit .edit-panel").length){
			$('.static-can-edit .edit-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.static-can-edit.hover').removeClass('hover');
			});
		}
		
		if($(".child-panel").length){
			$('.child-panel').mouseover(function(){
				$(this).parent().addClass('hover');
			}).mouseout(function(){
				$('.child-builder.hover').removeClass('hover');
			});
		}
		
		if($(".moveuplink").length){
			$(".moveuplink").click(function() {
				$(this).parents(".sort-item").insertBefore($(this).parents(".sort-item").prev());
				sendOrderToServer();   
			});
		   
			$(".movedownlink").click(function() {
				$(this).parents(".sort-item").insertAfter($(this).parents(".sort-item").next());
				sendOrderToServer();
			});
		}
		
		if($(".sort-block-container").length){
			$(".sort-block-container").sortable({
				handle: '.sort-handle',
				update: function (event, ui) {
					var data = $(this).sortable('serialize');

					$.ajax({
						data: data,
						type: 'POST',
						url: WEB_URL+'mpanel/index/sortblock'
					});
				}
			});
		}
	});
});

function sendOrderToServer(){
	require([
		'jquery',
		'jquery/ui'
	], function(jQuery){
		(function($) {
			var order = $("#sortable_home").sortable('serialize');
			$.ajax({
				type: "POST", dataType: "json", url: WEB_URL+'mpanel/index/sortsection',
				data: order,
				success: function(response) {}
			});
		})(jQuery);
	});		
}

function initPanelPopup(){
	require([
		"jquery",
		"mgs_quickview"
	], function($){
		var magnificPopup = $('.popup-link').magnificPopup({
			type: 'iframe',
			iframe: {
				markup: '<div class="mfp-iframe-scaler builder-iframe">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
						'</div>'
			}, 
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: false
		});
	});
}

function loadAjaxByAction(action, additionalData){
	require([
		"jquery"
	], function($){
		var url = WEB_URL+'mpanel/index/'+action;
		if(additionalData){
			url +=additionalData;
		}
		$.ajax(url, {
			success: function(data) {
				if(data!=''){
					switch(action) {
						case 'newsection':
							$('#sortable_home').append(data);
							$('#new-section-load img').hide();
							$('#new-section-load .fa').show();
							initPanelPopup();
							$('.edit-panel.parent-panel').mouseover(function(){
								$(this).parent().addClass('hover');
							}).mouseout(function(){
								$('.container-panel.hover').removeClass('hover');
							});
							
							if($(".moveuplink").length){
								$(".moveuplink").click(function() {
									$(this).parents(".sort-item").insertBefore($(this).parents(".sort-item").prev());
									sendOrderToServer();   
								});
							   
								$(".movedownlink").click(function() {
									$(this).parents(".sort-item").insertAfter($(this).parents(".sort-item").next());
									sendOrderToServer();
								});
							}
							break;
						case 'removesection':
							$('#panel-section-'+data).remove();
							break;
					} 
				}
			}
	   });
	});
}
	
function addNewSection(page_id){
	additionalData = '/page_id/'+page_id;
	loadAjaxByAction('newsection', additionalData);
}

function removeSection(sectionId){
	additionalData = '/id/'+sectionId;
	loadAjaxByAction('removesection', additionalData);
}

function closeColorTable(el){
	require([
		"jquery"
	], function($){
		$(el).slideUp('normal');
	});
}

function openColorTable(el){
	require([
		"jquery"
	], function($){
		$(el).slideToggle('normal');
	});
}

function changeInputColor(name, input, el, wrapper){
	require([
		"jquery"
	], function($){
		$('#'+input).val(name);
		$('#'+wrapper+' ul li a').removeClass('active');
		$(el).addClass('active');
		divwrapper = wrapper.replace('colour-content','color');
		$('.'+divwrapper+' .remove-color').show();
	});
	
}

function removeColor(input, el){
	require([
		"jquery"
	], function($){
		$('#'+input).val('');
		$(el).hide();
	});
	
}

function removeBlock(url, blockId){
	require([
		"jquery"
	], function($){
		$.ajax(url, {
			success: function(data) {
				if(isNaN(data)){
					alert(data);
				}else{
					$('#block-'+data).remove();
				}
			}
	   });
	});
}

function changeBlockCol(url, oldCol, blockId){
	require([
		"jquery"
	], function($){
		$.ajax(url, {
			success: function(data) {
				if(isNaN(data)){
					alert(data);
				}else{
					for(i=1; i<=12; i++){
						if($('#block-'+blockId).hasClass('col-md-'+i)){
							$('#block-'+blockId).removeClass('col-md-'+i);
						}
					}
					
					newClass = 'col-md-'+data;
					$('#block-'+blockId).addClass(newClass);
					
					$('#block-'+blockId+' .edit-panel .change-col a').removeClass('active');
					$('#changecol-'+blockId+'-'+data).addClass('active');
				}
			}
	   });
	});
}

function setSectionPanelPosition($){
	if($(".section-builder").length){
		$(".section-builder").each(function() {
			padding = $(this).css('padding-top');
			$(this).find($('.parent-panel')).css('top', padding);
		});
	}
}

function setLocation(url){
		require([
			"jquery",
			"mage/mage"
		], function($){
			$($.mage.redirect(url, "assign", 0));
		});
	}