function verEsconder(id){
	$(id).toggle('slow');
	return false;
}

function toogle_newsletter_categoria( categoria_id, subscriber_id, ele ) {
	$.ajax({
		url: '../inc/ajax.php?act=toogle_news_cat&categoria_id='+categoria_id+'&subscriber_id='+subscriber_id,
	}).done(function(msg){
		if( msg == 'true' ) {
			$(ele).addClass('label-success');
		}else if( msg === 'false' ){
			$(ele).removeClass('label-success');
		}
	});
	return false;
}


function details_in_popup(link, div_id){

    $.ajax({
        url: "../inc/ajax.php?act=get_subscriber_details&id="+link,
        success: function(response){
            $('#'+div_id).html(response);
            $(".ajax-button-go button").click(function(){
            	event.preventDefault();
				var selected = $(this).parent().prev().prev().find("option:selected").map(function(){ return this.value }).get();
				
				//params
				var group_url = "";
				var id = $('#'+div_id).data("email_id");
				
				for (var i = selected.length - 1; i >= 0; i--) {
					group_url += "&group[]=" + selected[i];
				};
				
				//request
				$.ajax({
					url: "../inc/ajax.php?act=update_subscriber_group&id=" + id + group_url,
					success: function(response){
						$('#'+div_id).find(".ajax-response").html(response);
					}
				})
								
            });
        }
    });
    return '<div data-email_id="'+link+'" id="'+ div_id +'">A carregar...</div>'
}


//Loading jQuery
$(document).ready(function(){

	//confirm no delete de links
	$(".link-confirm").click( function(){

		$("#multiple-actions-confirmation").modal();
		event.preventDefault();
		var form = $(this);
		$("#confirm").click( function(){
			window.location = form.attr("href");
		});
	} );

	var counter_input = 0;
	$(".btn-sender-add").click(function(){

		counter_input ++;
		var parent_row = $(this).parent().parent(); //tr
		var full_row = $("table.senders-table").find("tr:nth-child(2)").clone();

		//clear inputs
		var input = full_row.find("input");

		input.each(function(index){
			
			$(input[index]).attr("value", "");
			$(input[index]).attr("name", $(input[index]).attr("name").replace(/\[([0-9]*)\]/, "[new]["+counter_input+"]"));
			$(input[index]).removeClass("seamless-input");
		})

		
		
		full_row.insertBefore($(parent_row));
	})

	$(".seamless-input").click(function(){
		var inputs = $(".seamless-input");
		for (var i = inputs.length - 1; i >= 0; i--) {
			$(inputs[i]).removeClass("seamless-input");
		};
	})

	$(".auto-submit").change(function(){
		$(this).parent().submit();	
	})

	
	$("button.flip-widget-open").click(function(){
		if($(this).parent().parent().parent().hasClass("hover")){
			$(this).parent().parent().parent().removeClass("hover");
			//console.log("add it");
		}
		else{
			$(this).parent().parent().parent().addClass("hover");
			//console.log("Remove it");
		}
			
	})
	
	var recebidas = $("#morris-recebidas-nao-lidas").html();
	var recebidas_lidas = $("#morris-recebidas-lidas").html();
	var bounces = $("#morris-bounces").html();
	
	var total = (parseInt(recebidas) + parseInt(recebidas_lidas) + parseInt(bounces));
	var recebidas_percent = ((recebidas * 100) / total);
	var bounces_percent = ((bounces * 100) / total);
	recebidas_percent = Math.round(recebidas_percent * 100) / 100;
	bounces_percent = Math.round(bounces_percent * 100) / 100;
	var recebidas_lidas_percent = Math.round((100 - recebidas_percent) * 100) / 100;
	
		
	//morris
	try{
		//morris DONUT
		var morris_donut_aberturas = Morris.Donut({
		  element: 'morris-pie-aberturas',
		  data: [
		    {label: "Entregues", value: recebidas_percent},
		    {label: "Lidas", value: recebidas_lidas_percent},
		    {label: "Devolvidos", value: bounces_percent}
		  ],
		  colors: [
		  	'#0b62a4',
		    '#0BA462',
		    '#bd1e2d'   
		  ],
		  formatter: function (y) { return y + "%" }
		});
		
		morris_donut_aberturas.select(1);
		
	
		var source_table = $("table#table-aberturas-dia");
		var timeline = [];
		
		source_table.find("th.data-header").each(function(index){
			
			var key = $(this).html();
			key = key.replace("&gt;", ">");
			
			var value = $(source_table.find("td.data-value")[index]).html();
			
			timeline.push({"year":key, "value": value});
		});
	}
	
	catch(e){
		console.log("morries-pie-aberturas not found")
	}
		
	try{
		//morris LINE
		new Morris.Line({
		  // ID of the element in which to draw the chart.
		  element: 'morries-line-aberturas',
		  // Chart data records -- each entry in this array corresponds to a point on
		  // the chart.
		  data: timeline,
		  // The name of the data record attribute that contains x-values.
		  xkey: 'year',
		  // A list of names of data record attributes that contain y-values.
		  ykeys: ['value'],
		  // Labels for the ykeys -- will be displayed when you hover over the
		  // chart.
		  labels: ['Visualizações'],
		  parseTime: false
		});
		
	}
	catch(e){
		console.log("morris-line-aberturas not found");
	}
	
	try{
		
		var source_table = $("table#evo-subscritores");
		var timeline = [];
		
		source_table.find("th.data-header").each(function(index){
			
			var key = $(this).html();
			key = key.replace("&gt;", ">");
			
			var value = $(source_table.find("td.data-value")[index]).html();
			
			timeline.push({"year":key, "value": value});
		});
		
		Morris.Bar({
		  element: 'morris-bars-subscritores',
		  data: timeline,
		  xkey: 'year',
		  ykeys: ['value'],
		  labels: ['Subscritores']
		});
	}
	catch(e){
		console.log("morris bars subscritores not found");
	}
	
	try{
		
		//morris DONUT
		
		//dashboard - 1
		var morris_donut_demo_1 = Morris.Donut({
		  element: 'morris-graph-demo-1',
		  data: [
		    {label: "Entregues", value: $("input[name=pie_graph_delivered_last_time_interval]").val()},
		    {label: "Lidas", value: $("input[name=pie_graph_opened_last_time_interval]").val()},
		    {label: "Devolvidos", value: $("input[name=pie_graph_bounced_last_time_interval]").val()}
		  ],
		  colors: [
		  	'#0b62a4',
		    '#0BA462',
		    '#bd1e2d'   
		  ],
		  formatter: function (y) { return y + "%" }
		});
		
		morris_donut_demo_1.select(1);

		var month_totals = [];
		$("input.subscriber_month_totals").each(function(){
			var total = $(this).val();
			var month = $(this).attr("name").replace("month_", "");

			month_totals.push({"year":month, "value": total});

		})
		
		//dashboard - 2
		Morris.Bar({
		  element: 'morris-graph-demo-2',
		  data: month_totals,
		  xkey: 'year',
		  ykeys: ['value'],
		  parseTime: false,
		  labels: ['Subscritores'],
			xLabelAngle: 90,
			hideHover: 'auto'
		});
	
	}
	catch(e){
		
	}

	//end morris
	
	$(".mandril-btn").click(function(){
		$("form#send_test").attr("action", "?mod=mass_email&mandrill=true");
		$("form#send_test").submit();
	})

	$("form").validate();

	//select todos os grupos
	$("div.multiple-checkboxes .select-all").click(function(){
		var siblings = $(this).parent().parent().find("input");
		var current_status = $(this).attr("checked");

		//se existir quer dizer que está checked
		if(current_status)
			siblings.attr("checked", "checked");
		else
			siblings.removeAttr("checked");
	})

	$(".collapse").collapse("hide");

	//tooltips
	$('.tooltip2, .tip').tooltip();
	$('a[rel=tooltip]').tooltip({placement: "left"});
	$('a[rel=tooltip-top]').tooltip({placement: "top"});

	$('#tabThis a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	})

	//adsense
	$("#select_type_direita").change(function(){
		check_adsense();
	});

	check_adsense();

	$(".alert").alert();

	$(".bars_graph").visualize({width: "700px", height:"300px" });

	$(".lines_graph").visualize({width: "700px", type: "line"});
	$(".pie_graph").visualize({width: "300px", height: "300px", type: "pie"});


	$("#projecto_tabs").tabs();
	$("#data").datepicker({
		changeMonth: true,
		changeYear: true,
		dateFormat: 'yy-mm-dd'
	});


	$("#sort_this_items").sortable({
		placeholder: "ui-state-highlight",
		stop: function(event, ui){
			var ar = $("#sort_this_items").sortable('serialize');
			$.ajax({
				url: '../inc/ajax.php?act=sort_media_items&'+ar,
			}).done(function(msg){
			});
		}
	});

	//destaques
	$("#destaques-sortable").sortable({
		placeholder: "ui-state-highlight",
		stop: function(event, ui){
			var ar = $("#destaques-sortable").sortable('serialize');
			$.ajax({
				url: '../inc/ajax.php?act=sort_destaques&'+ar,
			}).done(function(msg){
			});
		}
	});


	function showPreview(coords)
	{
		var rx = 250 / coords.w;
		var ry = 250 / coords.h;

		$('#jcrop_preview').css({
			width: Math.round(rx*60) + 'px',
			height: Math.round(ry*60) + 'px',
			marginLeft: '-' + Math.round(rx * coords.x) + 'px',
			marginTop: '-' + Math.round(ry * coords.y) + 'px'
		});
	}


	$("#link_externo_slider").click(function(){
		if($("#slider_pagina").css("display")=='none'){
			$('input[name="link"]').val("");
		}else{
			$('input[name="link"]').val("http://");
		}

		$("#slider_pagina").toggle("fast");
	})


	///EDITED BY HUGO
	$("#tipo_conteudo").change(function(){
		var valor = $("#tipo_conteudo option:selected").val();
		$("fieldset").hide("fast");
		$("#type_"+valor).show("fast");
	});

	$("#tipo_conteudo_update").change(function(){
		var valor = $("#tipo_conteudo_update option:selected").val();
		$("fieldset").hide("fast");
		$("#type_update_"+valor).show("fast");
	});


	$("#media_tabs").tabs();

	//END Sortables






$("#tabs").tabs({
	select: function(event, ui) {                   
	   window.location.replace(ui.tab.hash);
	},
});

	$("#verTratadas").click(function(){
		$(".tratado").toggle('fast');
		return false;
	});
	$("#faqs_sort").sortable({
		stop: function(event, ui){
			//console.log($(this).html());
			var inputs = this.getElementsByTagName('input');
			var i = 0;
			var newOrder = '';
			while(i<inputs.length){
				var aux = inputs[i].name.split("_");
				newOrder = newOrder + '_' + aux[1];
				i++;
			}
			console.log(newOrder);
			$.ajax({
				url: "../inc/ajax.php?act=faqs_sort",
				type: "post",
				data: {order: newOrder}
			})

		}
	});
	$("#imprensa_sort").sortable({
		stop: function(event, ui){
			//console.log($(this).html());
			var inputs = this.getElementsByTagName('input');
			var i = 0;
			var newOrder = '';
			while(i<inputs.length){
				if(inputs[i].type=='checkbox'){
					var aux = inputs[i].name.split("_");
					newOrder = newOrder + '_' + aux[1];
				}
				i++;
			}
			console.log(newOrder);
			$.ajax({
				url: "../inc/ajax.php?act=imprensa_sort",
				type: "post",
				data: {order: newOrder}
			})

		}
	});

	$("#videos_sort").sortable({
		stop: function(event, ui){
			//console.log($(this).html());
			var inputs = this.getElementsByTagName('input');
			var i = 0;
			var newOrder = '';
			while(i<inputs.length){
				if(inputs[i].type=='checkbox'){
					var aux = inputs[i].name.split("_");
					newOrder = newOrder + '_' + aux[1];
				}
				i++;
			}
			console.log(newOrder);
			$.ajax({
				url: "../inc/ajax.php?act=videos_sort",
				type: "post",
				data: {order: newOrder}
			})

		}
	});

	$("#images_sort").sortable({
		stop: function(event, ui){
			//console.log($(this).html());
			var inputs = this.getElementsByTagName('input');
			var i = 0;
			var newOrder = '';
			while(i<inputs.length){
				if(inputs[i].type=='checkbox'){
					var aux = inputs[i].name.split("_");
					newOrder = newOrder + '_' + aux[1];
				}
				i++;
			}
			console.log(newOrder);
			$.ajax({
				url: "../inc/ajax.php?act=images_sort",
				type: "post",
				data: {order: newOrder}
			})

		}
	});

	$(".newsletter_toggle").click(function(){
		$(".newsletter_expand").slideToggle();
		return false;
	})


	$(".toogleActive_faqs").click(function(){
		var input = this.parentNode.getElementsByTagName("input")[0].name.split("_");
		$.ajax({
			url: "../inc/ajax.php?act=faqs_toogle",
			type: "post",
			data: {id: input[1]},
			success: function(data, textStatus, jqXHR){
			}
		});
		if($(this).html() == "inativo" || $(this).html() == "inactive" || $(this).html() == "Inativo" || $(this).html() == "Inactive"){
			$(this).html("Ativo");
		}else{
			$(this).html("Inativo");
		}
		
	});
	$(".toogleActive_media").click(function(){
		var input = this.parentNode.getElementsByTagName("input")[0].name.split("_");
		$.ajax({
			url: "../inc/ajax.php?act=media_toogle",
			type: "post",
			data: {id: input[1]},
			success: function(data, textStatus, jqXHR){
			}
		});
		if($(this).html() == "inativo" || $(this).html() == "inactive" || $(this).html() == "Inativo" || $(this).html() == "Inactive"){
			$(this).html("Ativo");
		}else{
			$(this).html("Inativo");
		}
		
	});
	$(".toogleActive_cart").click(function(){
		var input = this.parentNode.getElementsByTagName("input")[0].name.split("_");
		$.ajax({
			url: "../inc/ajax.php?act=cart_toogle",
			type: "post",
			data: {id: input[1]},
			success: function(data, textStatus, jqXHR){
			}
		});
		if($(this).html() == "inativo" || $(this).html() == "inactive" || $(this).html() == "Inativo" || $(this).html() == "Inactive"){
			$(this).html("Ativo");
		}else{
			$(this).html("Inativo");
		}
		
	});


	/// END EDITED BY HUGO

	var fixHelper = function(e, ui) {
		ui.children().each(function() {
			$(this).width($(this).width());
		});
		return ui;
	};

	$('#datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		changeYear: true
	});

	try{
		$('#datasort').dataTable({
		"bJQueryUI": true,
		"iDisplayLength": 20,
		"oLanguage": {
			"sZeroRecords": "Sem Resultados",
			"sSearch": "<i class=\"icon-search\"></i> Pesquisa:",
			"sProcessing": "A processar...",
			"sLoadingRecords": "A processar...",
			"sLengthMenu": '<i class=\"icon-eye-open\"></i> Mostrar <select>'+
				'<option value="10">10</option>'+
				'<option value="20">20</option>'+
				'<option value="50">50</option>'+
				'<option value="100">100</option>'+
				'<option value="-1">Todos</option>'+
				'</select>',
			"sInfoFiltered": " - a filtrar de _MAX_ registos",
			"sInfoEmpty": "Não existem registos",
			"sInfo": "A mostrar _TOTAL_ registos (_START_ a _END_)",
			"sEmptyTable": "Não existem registos."
		},
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bSearchable": false, "aTargets": [ 'nosearch' ] },
			{ "bSortable": false, "aTargets": [ 'nosort' ] }
		]
	});
	}
	catch(e){
		if(console) console.log(e);
	}

	try{
		$('#clicks-table').dataTable({
		"bJQueryUI": true,
		"iDisplayLength": 20,
		"oLanguage": {
			"sZeroRecords": "Sem Resultados",
			"sSearch": "<i class=\"icon-search\"></i> Pesquisa:",
			"sProcessing": "A processar...",
			"sLoadingRecords": "A processar...",
			"sLengthMenu": '<i class=\"icon-eye-open\"></i> Mostrar <select>'+
				'<option value="10">10</option>'+
				'<option value="20">20</option>'+
				'<option value="50">50</option>'+
				'<option value="100">100</option>'+
				'<option value="-1">Todos</option>'+
				'</select>',
			"sInfoFiltered": " - a filtrar de _MAX_ registos",
			"sInfoEmpty": "Não existem registos",
			"sInfo": "A mostrar _TOTAL_ registos (_START_ a _END_)",
			"sEmptyTable": "Não existem registos."
		},
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bSearchable": false, "aTargets": [ 'nosearch' ] },
			{ "bSortable": false, "aTargets": [ 'nosort' ] }
		]
	});
	}
	catch(e){
		if(console) console.log(e);
	}
	

	try{
		$('.subscribers-table').dataTable({
		"bJQueryUI": true,
		"bProcessing": true,
		"bServerSide": true,
		"bStateSave": true,
		"iDisplayLength": 20,
		"fnServerParams": function(aoData){
			aoData.push({"name" : "group_id", "value" : this.data("group-id")})
		}, 
		"sAjaxSource": "../inc/ajax.php?act=get_subscribers",
		"fnDrawCallback" : function() {
			$('a.popup-ajax').popover({
		        "html": true,
		        "content": function(){
		            var div_id =  "div-id-" + $.now();
		            return details_in_popup($(this).data('id'), div_id)
		        }}).click(function(e){e.preventDefault()});
		},
		"oLanguage": {
			"sZeroRecords": "Sem Resultados",
			"sSearch": "<i class=\"icon-search\"></i> Pesquisa:",
			"sProcessing": "A processar...",
			"sLoadingRecords": "A processar...",
			"sLengthMenu": '<i class=\"icon-eye-open\"></i> Mostrar <select>'+
				'<option value="10">10</option>'+
				'<option value="20">20</option>'+
				'<option value="50">50</option>'+
				'<option value="100">100</option>'+
				'<option value="-1">Todos</option>'+
				'</select>',
			"sInfoFiltered": " - a filtrar de _MAX_ registos",
			"sInfoEmpty": "Não existem registos",
			"sInfo": "A mostrar _TOTAL_ registos (_START_ a _END_)",
			"sEmptyTable": "Não existem registos."
		},
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bSearchable": false, "aTargets": [ 'nosearch' ] },
			{ "bSortable": false, "aTargets": [ 'nosort' ] }
		]
	});
	}
	catch(e){
		if(console) console.log(e);
	}

	try{
		$('.exclusions-table').dataTable({
		"bJQueryUI": true,
		"bProcessing": true,
		"bServerSide": true,
		"bStateSave": true,
		"iDisplayLength": 20,
		"fnServerParams": function(aoData){
			aoData.push({"name" : "group_id", "value" : this.data("group-id")})
		}, 
		"sAjaxSource": "../inc/ajax.php?act=get_exclusions",
		"oLanguage": {
			"sZeroRecords": "Sem Resultados",
			"sSearch": "<i class=\"icon-search\"></i> Pesquisa:",
			"sProcessing": "A processar...",
			"sLoadingRecords": "A processar...",
			"sLengthMenu": '<i class=\"icon-eye-open\"></i> Mostrar <select>'+
				'<option value="10">10</option>'+
				'<option value="20">20</option>'+
				'<option value="50">50</option>'+
				'<option value="100">100</option>'+
				'<option value="-1">Todos</option>'+
				'</select>',
			"sInfoFiltered": " - a filtrar de _MAX_ registos",
			"sInfoEmpty": "Não existem registos",
			"sInfo": "A mostrar _TOTAL_ registos (_START_ a _END_)",
			"sEmptyTable": "Não existem registos."
		},
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bSearchable": false, "aTargets": [ 'nosearch' ] },
			{ "bSortable": false, "aTargets": [ 'nosort' ] }
		]
	});
	}
	catch(e){
		if(console) console.log(e);
	}
	

	$('#datepicker').change(function(){
		$('#datepicker2').datepicker( "destroy" );
		$('#datepicker2').datepicker({
			dateFormat: 'yy-mm-dd',
			minDate: getMinDate(),
			changeMonth: true,
			changeYear: true
		});
	});

	$("a.fancybox").fancybox();

	$("a.fancybox-special").fancybox();

	$("a.ttip").tooltip({
		placement: "left"
	});

	if(getMinDate() !== 0){
		$('#datepicker2').datepicker({
			dateFormat: 'yy-mm-dd',
			minDate: getMinDate()
		});
	}
	else
		$('#datepicker2').datepicker({
			dateFormat: 'yy-mm-dd'
		});

	$( "#sortable" ).sortable();
	$( "#sortable" ).disableSelection();
	$(".gallery_sort tbody").sortable({
		helper: fixHelper,
		update : function () {
			var serial = $('table.gallery_sort tbody tr input').sortable('serialize');
			$.ajax({
				url: "../inc/ajax.php?act=save_gallery_sort",
				type: "post",
				data: serial
			})
		}
	}).disableSelection();

	$(".testimony_sort tbody").sortable({
		helper: fixHelper,
		update : function () {
			var serial = $('table.testimony_sort tbody tr input').sortable('serialize');
			$.ajax({
				url: "../inc/ajax.php?act=save_testimony_sort",
				type: "post",
				data: serial
			})
		}
	}).disableSelection();

	$(".slides_sort tbody").sortable({
		helper: fixHelper,
		update : function () {
			var serial = $('table.slides_sort tbody tr input').sortable('serialize');
			$.ajax({
				url: "../inc/ajax.php?act=save_slides_sort",
				type: "post",
				data: serial
			})
		}
	}).disableSelection();


	if($('#media_browser').lenght != 0){
		 $('#media_browser').html('<iframe name="kcfinder_iframe" src="../inc/libs/kcfinder/browse.php?type=images" ' +
		'frameborder="0" width="100%" height="100%" marginwidth="0" marginheight="0" scrolling="no" />');
		$('#media_browser').css('height','84%');
		$('#media_browser').css('display','block');
	}

	//$('ul.sfmenu').superfish();



	$('form#showMenu').submit(function(){
		var action = $('select[name="action"] option:selected').val();
		switch (action) {
			case 'delete':
				$('input[type=checkbox]').each(function () {
					if (this.checked) {
						//console.log($(this).closest('li').children('ul'));
						var pid = $(this).closest('li').find('.pid_hidden')[0].value;
						var id = $(this).closest('li').find('.id_hidden')[0].value;
						var toDel = $(this).closest('li');
						$(this).closest('li').children('ul').each(function(){
							$(this).children('li').each(function(){
							//	console.log("--->");
							//	console.log(pid);
							//	console.log("--->");
								//$(this).find('.id_hidden')[0].value;
								$(this).find('.pid_hidden')[0].value = pid;
							//	console.log(toDel.find('.id_hidden'));


								//apagar registo bd
							});
						});
						var e = toDel.find('.id_hidden')[0];
					//	console.log(e);
						$(e).remove();

						var html = $.ajax({
							type: "POST",
							url: "../inc/ajax.php?act=remove-menu-node",
							data: "id=" + id,
							async: true
						}).responseText;
						drag(0,0);
					}
				});
				break;

			case 'activate':
				$('input[type=checkbox]').each(function () {
					if (this.checked) {
						var id = $(this).closest('li').find('.id_hidden')[0].value;
						var html = $.ajax({
							type: "POST",
							url: "../inc/ajax.php?act=change-menu-status",
							data: "id=" + id + "&nstat=1",
							async: false,
							success: location.reload()
						}).responseText;
					}
				});
				break;

			case 'deactivate':
				$('input[type=checkbox]').each(function () {
					if (this.checked) {
						var id = $(this).closest('li').find('.id_hidden')[0].value;
						var html = $.ajax({
							type: "POST",
							url: "../inc/ajax.php?act=change-menu-status",
							data: "id=" + id + "&nstat=0",
							async: false,
							success: location.reload()
						}).responseText;
					}
				});
				break;
		}
		//return false;
		setTimeout(function(){
			location.reload();
		},2000);
	});

	$("#sorter").tablesorter({
		headers: {
			0: {
				sorter: false
			},
			4: {
				sorter: false
			}
		}
	}).tablesorterPager({
		container: $(".pager")
		});

	$('input[name=select_items]').bind('change',function(){
		$('input[type=checkbox]').each(function(){
			if($(this).attr('name')=='items[]'){
				if($(this).attr('checked'))
					$(this).attr('checked', false);
				else
					$(this).attr('checked', true);
			};
		});
	});


});

// Proc


//functions start
function createHighlight(obj){
	obj.addClass('ui-state-highlight ui-corner-all');
	obj.html('<p class="forcecenter"><span class="ui-icon ui-icon-alert" style="float: left; margin-right:.3em;"></span>'+obj.html()+'</p>');
}

function createError(obj){
	obj.addClass('ui-state-error ui-corner-all');
	obj.html('<p class="forcecenter"><span class="ui-icon ui-icon-alert" style="float: left; margin-right:.3em;"></span>'+obj.html()+'</p>');
}

function drag(id,parent_id){
	setTimeout(function(){
		var res = new Array();
		$('.id_hidden').each(function(index,element){
		//	console.log(this);
		//	console.log(parent_id);
			if(this.value == id){
				//console.log(id);
				//console.log(parent_id);
				$(this).next()[0].value = parent_id;
			}
			var sub_array = new Array(this.value, $(this).next()[0].value);
			//console.log($(this).next());
			res[index] = new Array(sub_array);
		});
			//}

		//console.log(res);
		var html = $.ajax({
			type: "POST",
			url: "../inc/ajax.php?act=ordering",
			data: {myJson:  res},
			async: false
		}).responseText;
	}, 1000);
}

// INLINE EDITING

function activeSave(content){
	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_active",
		data: "value=" + content.current + "&id=" + this.attr('id') + "&field=menus",
		async: true
	}).responseText;
	activeImg(content, this);
}

function nameSave(content){
	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_name",
		data: "value=" + content.current + "&id=" + this.attr('id'),
		async: true
	}).responseText;

}

function activeSaveMenu(content){

	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_active_menu",
		data: "value=" + content.current + "&id=" + this.attr('id'),
		async: true
	}).responseText;
	activeImg(content, this);
}

function nameSaveMenu(content){
	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_active",
		data: "value=" + content.current + "&id=" + this.attr('id') + "&field=sub_menus",
		async: true
	}).responseText;
}

function activeSaveHome(content){
	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_active",
		data: "value=" + content.current + "&id=" + this.attr('id') + "&field=home",
		async: true
	}).responseText;
	activeImg(content, this);
}

function activeSaveNews(content){
	var html = $.ajax({
		type: "POST",
		url: "../inc/ajax.php?act=save_active",
		data: "value=" + content.current + "&id=" + this.attr('id') + "&field=news",
		async: true
	}).responseText;
	activeImg(content, this);
}

function activeImg(content, elem){

	if(content.current == 'Activo')
		$(elem).html('<img src="../inc/img/admin/yes.png"/>');
	else if(content.current == 'Inactivo')
		$(elem).html('<img src="../inc/img/admin/no.png"/>');
}

function openKCFinder(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#image img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", '');
			img.onload = function() {
				$('.loading').remove();
				$('<img id="img" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="photo"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder_new_image(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#image img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", '');
			img.onload = function() {
				console.log(this.width);
				$('.loading').remove();
				$('<img id="img" style="display:none;" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="photo"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";

				jcrop_api.destroy();
				$('#jcrop_target').attr("src", '../media' + url2.replace("/2012novovisual/", ""));
				$('#jcrop_preview').attr("src", '../media' + url2.replace("/2012novovisual/", ""));
				$("#jcrop_group").show("fast");
				$('#jcrop_target').attr("style", 'display:block;');
				
				function showPreview(coords)
				{	
					$("#x").val(coords.x);
					$("#y").val(coords.y);
					$("#x2").val(coords.x2);
					$("#y2").val(coords.y2);
					$("#w").val(coords.w);
					$("#h").val(coords.h);
					var rx = 250 / coords.w;
					var ry = 250 / coords.h;
					$('#jcrop_preview').css({
						width: Math.round(rx*this.width) + 'px',
						height: Math.round(ry*this.width) + 'px',
						marginLeft: '-' + Math.round(rx * coords.x) + 'px',
						marginTop: '-' + Math.round(ry * coords.y) + 'px'
					});
				}



			$('#jcrop_target').Jcrop({
				onChange: showPreview,
				onSelect: showPreview,
				aspectRatio: 1
			},function(){
			  jcrop_api = this;
			});

			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder_new_image_update(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#image_update img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", '');
			img.onload = function() {
				console.log(this.width);
				$('.loading').remove();
				$('<img id="img_update" style="display:none;" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="photo"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('img_update');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";

				jcrop_api_update.destroy();
				$('#jcrop_target_update').attr("src", '../media' + url2.replace("/2012novovisual/", ""));
				$('#jcrop_preview_update').attr("src", '../media' + url2.replace("/2012novovisual/", ""));
				$("#jcrop_group_update").show("fast");
				$('#jcrop_target_update').attr("style", 'display:block;');
				
				function showPreview(coords)
				{	
					$("#x_update").val(coords.x);
					$("#y_update").val(coords.y);
					$("#x2_update").val(coords.x2);
					$("#y2_update").val(coords.y2);
					$("#w_update").val(coords.w);
					$("#h_update").val(coords.h);
					var rx = 250 / coords.w;
					var ry = 250 / coords.h;
					$('#jcrop_preview_update').css({
						width: Math.round(rx*this.width) + 'px',
						height: Math.round(ry*this.width) + 'px',
						marginLeft: '-' + Math.round(rx * coords.x) + 'px',
						marginTop: '-' + Math.round(ry * coords.y) + 'px'
					});
				}



			$('#jcrop_target_update').Jcrop({
				onChange: showPreview,
				onSelect: showPreview,
				aspectRatio: 1
			},function(){
			  jcrop_api_update = this;
			});

			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}


function openKCFinder_thumb_pdf(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#thumb_pdf img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			img.onload = function() {
				$('.loading').remove();
				$('<img id="thumb_pdf_img" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="thumb_pdf"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('thumb_pdf_img_1');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder_thumb_pdf_1(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#thumb_pdf_1 img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			img.onload = function() {
				$('.loading').remove();
				$('<img id="thumb_pdf_img_1" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="thumb_pdf_1"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('thumb_pdf_img_1');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}




function openKCFinder_news(div) {
	window.KCFinder = {
		callBack: function(url) {
			$('#image img').remove();
			window.KCFinder = null;
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", '');
			img.onload = function() {
				$('.loading').remove();
				$('<img id="img" src="../media' + url2.replace("/2012novovisual/", "") + '" />').insertBefore('div.add-image-button');
				$('input[name="photo"]').val(url2.replace("/2012novovisual//", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}

function openKCFinder2(div) {

	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#thumb img').remove();
			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button2');
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("/2012novovisual/", '');
			img.onload = function() {
				$('.loading').remove();
				$('<img id="img" src="../media' + url2 + '" />').insertBefore('div.add-image-button2');
				$('input[name="thumb"]').val(url2);
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
			'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}

function openKCFinder3(div) {
	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#image2 img').remove();
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", ''); 
			img.onload = function() {
				$('.loading').remove();
				$('<img id="full_img2" src="../media' + url2.replace("2012novovisual/", "") + '" style="width:150px; height:115px;" />').insertBefore('div.add-image-button2');
				$('input[name="full_photo"]').val(url2.replace("2012novovisual", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder4(div) {
	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#image4 img').remove();
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", ''); 
			img.onload = function() {
				$('.loading').remove();
				$('<img src="../media' + url2.replace("2012novovisual/", "") + '" style="width:218px; height:208px;" />').insertBefore('div.add-image-button4');
				$('input[name="photo4"]').val(url2.replace("2012novovisual", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder5(div) {
	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#image5 img').remove();
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", ''); 
			img.onload = function() {
				$('.loading').remove();
				$('<img src="../media' + url2.replace("2012novovisual/", "") + '" style="width:60px; height:60px;" />').insertBefore('div.add-image-button5');
				$('input[name="thumb1"]').val(url2.replace("2012novovisual", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}
function openKCFinder6(div) {
	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#image6 img').remove();
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", ''); 
			img.onload = function() {
				$('.loading').remove();
				$('<img src="../media' + url2.replace("2012novovisual/", "") + '" style="width:60px; height:60px;" />').insertBefore('div.add-image-button6');
				$('input[name="thumb2"]').val(url2.replace("2012novovisual", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}

function openKCFinder7(div) {
	window.KCFinder = {
		callBack: function(url) {
			window.KCFinder = null;
			$('#image7 img').remove();
			var img = new Image();
			img.src = url;
			url2 = url.replace(/^.*\/\/[^\/]+/, '');
			url2 = url2.replace("media/", '');
			url2 = url2.replace("dev/", '');
			url2 = url2.replace("meiostec/", ''); 
			img.onload = function() {
				$('.loading').remove();
				$('<img src="../media' + url2.replace("2012novovisual/", "") + '" style="width:60px; height:60px;" />').insertBefore('div.add-image-button7');
				$('input[name="thumb3"]').val(url2.replace("2012novovisual", ""));
				var img = document.getElementById('img');
				var o_w = img.offsetWidth;
				var o_h = img.offsetHeight;
				var f_w = div.offsetWidth;
				var f_h = div.offsetHeight;
				if ((o_w > f_w) || (o_h > f_h)) {
					if ((f_w / f_h) > (o_w / o_h))
						f_w = parseInt((o_w * f_h) / o_h);
					else if ((f_w / f_h) < (o_w / o_h))
						f_h = parseInt((o_h * f_w) / o_w);
					img.style.width = f_w + "px";
					img.style.height = f_h + "px";
				} else {
					f_w = o_w;
					f_h = o_h;
				}
				img.style.marginLeft = parseInt((div.offsetWidth - f_w) / 2) + 'px';
				img.style.marginTop = parseInt((div.offsetHeight - f_h) / 2) + 'px';
				img.style.visibility = "visible";
			}
		}
	};
	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_image', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=1, resizable=1, scrollbars=0, width=800, height=600'
	);
}

function openKCFinderGalery(div) {
	window.KCFinder = {
		callBackMultiple: function(files) {
			window.KCFinder = null;

			$('<div style="margin:5px" class="loading">Loading...</div>').insertBefore('div.add-image-button');
			var list_img = Array();
			for (var i = 0; i < files.length; i++){

				var url = files[i];
				url2 = url.replace(/^.*\/\/[^\/]+/, '');
				var control = 0;
				$('#sortable li').each(function (){
					if($(this).find('img').attr('src') == url2){
						control = 1;
						return false;
					}
				})

				if(control)
					continue;

				list_img[i] = new Image();

				list_img[i].src = url2;
				url2 = url2.replace("media/", '');

				list_img[i].onload = function() {
					var array_title = $(this).attr('src').substring($(this).attr('src').indexOf('/') +1);
					alert($(this).attr('src'));
					$('.loading').remove();
					$('#sortable').append('<li class="ui-state-default">'+
							'<img class="img_gallery" src="' + $(this).attr('src') + '" />'+
							'<div class="img_detais_gallery">'+
								'<input type="hidden" value="' + $(this).attr('src').replace("2012novovisual", '').replace("/media/", "") + '" name="photo['+$(this).attr('src')+'][url]"/>'+
								'<label><span>Título: </span><input type="text" name="photo['+$(this).attr('src')+'][img_titulo]"/></label>'+
								'<label><span>Texto: </span><input type="text" name="photo['+$(this).attr('src')+'][caption]"/></label>'+
								'<label><span>Link: </span><input type="text" name="photo['+$(this).attr('src')+'][link]"/></label>'+
								'<label><span>Activo? </span><select name="photo['+$(this).attr('src')+'][active_photo]">'+
									'<option value="0">Não</option>'+
									'<option value="1" selected="selected">Sim</option>'+
								'</select></label>'+
							'</div>'+
							'<input type="button" value="X" onclick="$(this).parent().remove();"/>'+
							'<div class="clear"></div>'+
						'</li>'
						);
				}
			}
		}
	};

	window.open('../inc/libs/kcfinder/browse.php?type=images',
		'kcfinder_multiple', 'status=0, toolbar=0, location=0, menubar=0, ' +
		'directories=0, resizable=1, scrollbars=0, width=800, height=600'
	);
}


function showAdvanced(obj,str1,str2){
	$('.advanced_gallery_options').toggle();
	if($(obj).html().length > 23)
		$(obj).html(str1);
	else
		$(obj).html(str2);
}

function getMinDate(){
	var obj = $('#datepicker');
	var value = obj.val();

	if(!value || value === 0 || value === ''){
		return 0;
	}else{
		var date = new Date(value.substring(0,4), parseInt(value.substring(5,7)) -1, value.substring(8,10.));
		return date;
	}

	return 0;
}

function check_adsense(){
	
	//caso seja adsense executar: 1 - mostrar a box que está escondida, 2 - remover os inputs de ficheiros
	if($("#select_type_direita").val() == "adsense"){
		$("li.adsense_conditional").fadeIn();
		$("li.ad_file").fadeOut();
		$("li.ad_file input").attr("disabled", "disabled");
		$("li.adsense_conditional input, li.adsense_conditional textarea").removeAttr("disabled");
	}

	//caso contrário executar: 1 - esoncer o 
	else{
		$("li.adsense_conditional").fadeOut();
		$("li.adsense_conditional textarea, li.adsense_conditional input").attr("disabled", "disabled")
		$("li.ad_file").fadeIn();
		$("li.ad_file input, li.ad_file textarea").removeAttr("disabled");
	}

}