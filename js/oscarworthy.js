var Osc = {}; // globject

// jQuery document.ready:
$(function() {

	/***********/
	/* VISUALS */
	/***********/

	// Add inner shadow on header:
	$("header").append("<div id='mask' />");
	

	// Set active nav tab:
	$("nav li a").each(function() {
		if (typeof Osc.tab != 'undefined' && this.text == Osc.tab) {
			$(this).parent().addClass("active");
			return false;
		}
	});

	
	// Zebrafy lists:
	$("#popular #main li:odd").addClass("odd");

	
	/**********/
	/* SEARCH */
	/**********/

	// Hide akas:
	$("#search #main ul span.aka").hide();

	// Highlight search results:
	$("#search #main ul a, #search #main ul span.aka").each(function() {
		// Find:
		var regex = new RegExp(Osc.q, 'gi');	// global, case-insensitive
		var match = regex.exec(this.innerHTML);	// matching string
		// Replace:
		var repl  = "<span class='hilite'>"+match+"</span>";
	    this.innerHTML = this.innerHTML.replace(match, repl);		
	});
		
	// Show some akas:
	var primary = $("#search #main ul a span").parent();		// highlighted links
	var secondary = $("#search #main ul span span").parent();	// highlighted akas
	$("#search #main ul a").not(primary).next(secondary).show();
	

	// Search form behaviour:
	$('#navsearch').focus(function() {
		// Set blank on focus:
		if (this.value == 'Films, actors') this.value = '';
	}).blur(function() {
		// Return default if nothing entered:
		if (this.value == '') this.value = 'Films, actors';	
	}).change(function() {
		// Store query string in buffer:
		Osc.q = this.value;
	});
	
	// Autocomplete:
	var a = $('#navsearch').autocomplete({ 
		serviceUrl:'/ajax/Search.autocomplete.php',
		minChars:3, 
		delimiter: /(,|;)\s*/,	// regex or character
		maxHeight:400,
		width:275,
		zIndex: 999,
		deferRequestBy: 75, 			//milliseconds
		params: {'source': 'local'},	//additional parameters in the GET request
		// callback function:
		onSelect: function(name, data) {
					// Store value as recent search
					Osc.logSearch(Osc.q, name, data, 1);	// NEED AJAX RES
					// Go directly to result page:
					window.location = data;
				  }
	});

	// Logging:
	Osc.logSearch = function(query, res, clicked, dropdown) {
		$.ajax({
	         url: '/ajax/Search.log.php',
	         data: {'query': query,
	         		'res': res,
	         		'clicked': clicked,
	         		'dropdown': dropdown
	         },
	         type: 'POST',
	         success: function (msg) {
				console.info(msg);
	         },
	         error: function (jxhr, msg, err) {
				console.warn(msg);
	         }
		});
	}

	// Logging from PHP results:
	$("#search #main a").click(function() {
		// RegExp to chop href down to size:
		var regex = /\/(film|person)\/[0-9]+/gi;	// global, case-insensitive
	    var url = regex.exec(this.href);
		Osc.logSearch(Osc.q, this.text, url[0], 0);	// NEED AJAX RES
	});


	/*********/
	/* FORMS */
	/*********/
/*
	// Inline login form insertion:
	$("#panel a[href*='login']").click(function() {
		// If no login form, load it:
		if (!$("#login_mini").length) {
			var div = $("<div id='login_inline_target'>");
			div.appendTo($("#wrap960"))
			   .load('/ajax/snippets.php #login_mini', function() {
			   	
					// Bind inline login form behaviour:
					$('#login_mini input:first').focus(function() {
						// Set blank on focus:
						if (this.value == 'Username') {
							this.value = '';
						}
					}).blur(function() {
						// Return default if nothing entered:
						if (this.value == '') {
							this.value = 'Username';
						}	
					});
					
					// Bind close button:
					$("#login_mini a.close_button").click(function() {
						$(this).parent().hide();
						return false;
					});
					
					// Enter must submit:
				    $('#login_mini input').keydown(function(e){
				        if (e.keyCode == 13) {
				            $(this).parents('form').submit();
				            return false;
				        }
				    });

					// Bind submit:
					//$("#login_mini").submit(submitLogin(this));
			   });
		}
		else {	// Show it:
			$("#login_mini").show();		
		}	
		return false;
	});
*/

	/*****************/
	/* FEEDBACK PAGE */
	/*****************/

	// Replying / Upvoting / Flagging:
	$("#comments .comment span, #comments a.delete").click(function() {
		// Get clicked button comment_id:
		var me = $(this);
		var mycom = me.parent().parent();
		var myid = mycom.attr("id").substring(7);
		var myauthor = mycom.find('.author').html();

		switch(me.attr('class')) {

			case 'reply_btn':
				// Delete existing forms:
				$("#feedback_reply").remove();
				// Pop-in reply form:
				$("<div>").insertAfter(mycom)
						  .load('/ajax/snippets.php #feedback_reply', function() {
								// Inject quote into textarea:
								$("#feedback_reply textarea").val("[@"+myauthor+":]\n");
								// Inject id into form hidden input:
								$("#feedback_reply input[name=reply_to]").val(myid);
								console.info('hidden value', myid, 'injected');	
						  });
				break;

			case 'upvote_btn':
				$.ajax({
		             url: '/ajax/Comment.update.php',
		             data: {'cid': myid, 'action': 'upvote'},
		             type: 'POST',
		             beforeSend: function() {
		             	me.beLoading();
		             },
		             success: function (msg) {
						console.info(msg);
						// Disable and remove buttons:
						me.addClass('used').fadeOut().siblings('.flag_btn').fadeOut();
		             },
		             error: function (jxhr, msg, err) {
						console.warn(msg);
		             }
		        });
				break;

			case 'flag_btn':
				$.ajax({
		             url: '/ajax/Comment.update.php',
		             data: {'cid': myid, 'action': 'flag'},
		             type: 'POST',
		             beforeSend: function() {
		             	me.beLoading();
		             },
		             success: function (msg) {
						console.info(msg);
						// Disable and remove buttons:
						me.addClass('used').fadeOut().siblings('.upvote_btn').fadeOut();
						
		             },
		             error: function (jxhr, msg, err) {
						console.warn(msg);
		             }
		        });    	
				break;
			case 'delete':
				$.ajax({
		             url: '/ajax/Comment.update.php',
		             data: {'cid': myid, 'action': 'delete'},
		             type: 'POST',
		             beforeSend: function() {
		             	me.beLoading();
		             },
		             success: function (msg) {
						console.info(msg);
						mycom.remove();
		             },
		             error: function (jxhr, msg, err) {
						console.warn(msg);
		             }
		        });    	
				break;
		} // end switch
	});


	// Enable range input & bind functionality:
	$("#comments_filter input").rangeinput()
								.change(function(event, filterval) {
		console.info("value changed to", filterval);
		$("#comments .comment").each(function() {
			var score = $(this).attr("score");
			if (score < filterval) {
				$(this).hide();
			}
			else if (score >= filterval) {
				$(this).show();
			}
		});
		// Count visible:
		var num = $("#comments .comment:visible").length;
		$("#comment_counter").html(num+' comments displayed')
	});


	/************/
	/* LISTINGS */
	/************/

	// Legend adder:
	$("#film #listing table:first").each(function() {
		$("<div>").insertBefore(this)
				  .load("/ajax/snippets.php #starlegend")
				  .show();
	});

	// Long table row hider:
	$("#listing table").each(function() {
		$(this).find("tr:gt(4)").hide();
		// Table expand link adder:
		if ($(this).find('tr').length > 4) {
			$("<a>Show all</a>").insertBefore(this)
								.addClass("expander").attr("href", "#");
		}
	});
	
	// Long table expander:
	$("a.expander").click(function() {
		// Find the next table, toggle later rows
		$(this).next("table").find("tr:gt(4)").toggle();
		// Toggle link text:
		var t = this.text;
		$(this).text(t == 'Hide' ? 'Show all' : 'Hide');
		return false;
	});


	// While loading cast, crew, filmog:
	$("#cast_box, #crew_box, #filmography_box").filter(":empty").beLoading();


	/**********/
	/* RATING */
	/**********/

	// RateIt voting:
    $(".rateit").bind('rated', function (event, value) {
    	var rater = $(this);
    	var target_hash = $(this).attr("id");
		var target_url = '/ajax/Role.rate.php';

    	// Send vote via Ajax:    	
		$.ajax({
             url: target_url,
             data: {'hash': target_hash, 'vote': value},
             type: 'GET',
             beforeSend: function() {
				// Loading spinner:
				rater.append(Osc.elems.loader);
             },
             success: function () {
				// Set stars as readonly:
				rater.rateit('readonly', 'true');
				// Replace loading spinner with tick, & fade:
				rater.find('.loader').hide();
				$(Osc.elems.tick).appendTo(rater);
             },
             error: function (jxhr, msg, err) {
				console.log(msg);
             }
         });    	
    });

	// Set member-rated stars readonly:
	$("#listing .rating.mem").rateit('readonly', true);


	/********/
	/* AJAX */
	/********/

	// AJAX call to delete film (admin only):
    $("#film .delete").click(function () {
		var fid = $(this).attr("id").substring(3);

    	// Call Film.drop via Ajax:    	
		$.ajax({
             url: '/ajax/Film.drop.php',
             data: {'id': fid},
             type: 'POST',
             success: function (msg_id) {
				displayMessage(msg_id);
				// Remove the icon:
				$(this).hide();
             },
             error: function (jxhr, msg, err) {
				console.error(msg);
             }
         });    	
    });

	// AJAX call to rename film (admin only):
    $("#rename a").hover(function() {
    	$(this).prev("input").show();    
    }).click(function () {
		var input = $(this).prev('input');
		var fid = input.attr("id").substring(3);
		var val = input.val();
		console.info('Rename', fid, val);
		
    	// Call Film.rename via Ajax:    	
		$.ajax({
             url: '/ajax/Film.rename.php',
             data: {'id': fid, 'title': val},
             type: 'POST',
             beforeSend: function() {input.beLoading()},
             success: function (msg_id) {
				displayMessage(msg_id);
             },
             error: function (jxhr, msg, err) {
				console.error(msg);
             }
        });    	
		return false;
    });


	/************/
	/* MESSAGES */
	/************/

	// Message autofade:
	$('.message.autofade').fadeOut(2000);

	// Message close button:
	$('.close_button').click(function() {
		$(this).parent().slideUp();
		var msg_id = $(this).parent().attr('id').substr(7,10);
		console.log(msg_id, ' clicked');
		deleteMessage(msg_id);
	});

	// AJAX call to delete a message from session:
	var deleteMessage = function(msg_id) {
		$.ajax({
             url: '/ajax/MessageHandler.php',
             data: {'msg_id': msg_id, 'action': 'delete'},
             type: 'POST',
             success: function (data) {
				console.info(data);
             }
		});
	}

	// AJAX call to retrieve and display a message from session:
	var displayMessage = function(msg_id) {
		$.ajax({
             url: '/ajax/MessageHandler.php',
             data: {'msg_id': msg_id, 'action': 'display'},
             type: 'POST',
             success: function (data) {
				console.log(data);
				var el = $(data);
				el.appendTo('#messages').fadeOut(2000);
				deleteMessage(msg_id);
             }
		});
	}



}); // end jQuery.ready


/***********/
/* CONTENT */
/***********/
Osc.elems = {};
Osc.elems.loader = "<img src='/img/ajax_loader.gif' class='loader' />";
Osc.elems.tick = "<img src='/img/icons/tick.png' class='tick' />";

// Loading GIF function:
$.fn.beLoading = function() {
	return this.html(Osc.elems.loader);
};


// Generalised Ajax-getter:
Osc.lazyLoad = function(id, dataname) {
	console.time('lazyLoad()');

	// Prep Ajax param:
	var resource;
	switch(dataname) {
		case 'cast':
		case 'crew':
			resource = '/ajax/Film.fetchCast.php';
			break;
		case 'filmography':
			resource = '/ajax/Person.fetchFilmography.php';
			break;
		default:
			console.error('Bad data');
			return false;
	}

	// Ajax-request JSONP data:
	$.ajax({
		// Hit an ajax subdomain so other requests are not blocked:
		url: 'http://ajax.oscarworthy.local:8888'+resource,
		data: {id: id, type: dataname},
		dataType: 'jsonp',					//
		jsonp: 'callback',					//
		jsonpCallback: 'lazyLoadCB',		// adds callback=lazyLoadCB to url
		cache: true,
	});	
}	// end lazyLoad()


// JSONP callback (currently does nothing)
lazyLoadCB = function(data) {
	console.timeEnd('lazyLoad()');
//	console.log('JSONP:', data);
}


// Ratings populator:
Osc.starFiller = function(id, page) {
	console.time('starFiller()');
	// Ajax-request the data:
	$.ajax({
		url: '/ajax/Stars.fetch.php',
		data: {id: id, type: page},
		datatype: 'json',
		type: 'POST',
		success: function (responseString, textStatus, jqXHR) {
			// Convert to js array:
			Osc.data = $.parseJSON(responseString);
//			console.log('starData:', Osc.data);
			// Insert star values into correct widgets:
			for (var hash in Osc.data) {
				var vals = Osc.data[hash];
				if (vals.memvote > 0) {
					// Set member's votes:
					$('#'+hash).rateit('value', vals.memvote)
							   .rateit('readonly', true)
							   .rateit('ispreset', true)
							   .addClass('mem');
					console.info(hash+' set to memvote '+vals.memvote);
				}
				else if (vals.avgrate > 0) {
					// Set average ratings:
					$('#'+hash).rateit('value', vals.avgrate)
							   .rateit('ispreset', true)
							   .addClass('avg');
					console.info(hash+' set to avgrate '+vals.avgrate);
				}
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			console.error(textStatus);
		},
		complete: function (jqXHR, textStatus) {
//			console.info('Finished starFiller():', textStatus);
			console.timeEnd('starFiller()');
		}
	});	
}	// end starFiller()


/***********/
/* UTILITY */
/***********/

$.fn.invert = function() {
  return this.end().not(this);
};
