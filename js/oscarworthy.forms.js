// jQuery document.ready:
$(function() {

	/*********/
	/* FORMS */
	/*********/

	// Enter MUST submit:
	$('input').keydown(function(e) {
		if (e.keyCode == 13) {
			$(this).closest('form').find('input[type=submit]').focus().click();	// hackish
		}
	});


	/**************/
	/* VALIDATION */
	/**************/

	// Live validation:
	$("form#register input#username").bind('keyup blur focus change', function() {
		var me = $(this);
		me.data("validity1", me.validateRequired() ? true : false);
		me.data("validity2", me.validateLength(2,20) ? true : false);
		me.data("validity3", me.validateAlphaNum() ? true : false);
		me.styleField();
	});
	$("form#register input#pass1").bind('keyup blur focus change', function() {
		var me = $(this);
		me.data("validity1", me.validateRequired() ? true : false);
		me.data("validity2", me.validateLength(8) ? true : false);
		me.styleField();
	});
	$("form#register input#pass2").bind('keyup blur focus change', function() {
		var me = $(this);
		me.data("validity1", me.validateRequired() ? true : false);
		me.data("validity2", me.validateMatch('pass1') ? true : false);
		me.styleField();
	});
	$("form#register input#email").bind('keyup blur focus change', function() {
		var me = $(this);
		me.data("validity1", me.validateRequired() ? true : false);
		me.data("validity2", me.validateEmail() ? true : false);
		me.styleField();
	});
	$("form#register input#realname").bind('keyup blur focus change', function() {
		var me = $(this);
		me.data("validity1", me.validateRequired() ? true : false);
		me.styleField();
	});


	// Style an input as valid or invalid:
	$.fn.styleField = function() {
		// Look for any failed tests:
		for (var prop in this.data()) {	
			if (this.data(prop) === false) {
				$(this).addClass('invalid').removeClass('valid');
			}
			else {
				$(this).addClass('valid').removeClass('invalid');
			}
		}
	};


	// Required field validator function
	$.fn.validateRequired = function() {
		if (this.val().length == 0) {
			console.log('REQUIRED');
			return false;
		}
		else {
			console.log('required-ok');
			return true;
		}
	};
	// Field length validator function
	$.fn.validateLength = function(min, max) {
		if (this.val().length < min || this.val().length > max) {
			console.log('LENGTH');
			return false;
		}
		else {
			console.log('length-ok');
			return true;
		}
	};
	// Field chartype validator function
	$.fn.validateAlphaNum = function() {
		var patt = /^[\w\d\._-]+$/i;
		if (!this.val().match(patt)) {
			console.log('ALPHA');
			return false;
		}
		else {
			console.log('alpha-ok');
			return true;
		}
	};
	// Email validator function
	$.fn.validateEmail = function() {
		var patt = /^[\w.-]+@[\w.-]{2,}.[a-z]{2,6}$/i;
		if (!this.val().match(patt)) {
			console.log('EMAIL');
			return false;
		}
		else {
			console.log('email-ok');
			return true;
		}
	};
	// Matching fields validator function
	$.fn.validateMatch = function(confirmation) {
		if (this.val() != $("#"+confirmation).val()) {
			console.log('MATCH');
			return false;
		}
		else {
			console.log('match-ok');
			return true;
		}
	};


	// Registration submission:
	var register_form = $("#register");
	register_form.find('#submit').click(function() {
	
		// Disable form:
		this.disable();
		this.find('legend').beLoading();

		if (this.find('.invalid')) {
			// Don't submit:
			this.enable();
			return false;	
		}
		else {
			register_form.submit();
		}
	});
	
	
	// Reset password form:
	var reset_pw_form = $("#reset_pass");
	reset_pw_form.hide();
	
	$("#reset_link").click(function() {
		reset_pw_form.slideDown();
		return false;
	});

	
	// Reset password validation:
	reset_pw_form.submit(function() {			// REDO
		var email_input = this.find("#email");
		// Valid email:
		if (!preg_match('/^(\w.-)+@(\w.-){2,}.(a-z){2,6}$/', email_input.val())) {
			email_input.addClass('invalid');
			// Don't submit:
			return false;
		}
	});
	
	// Hints show/hide:
	$("form input").focus(function() {
		$(this).next('.hint').show();
	}).blur(function() {
		$(this).next('.hint').hide();
	});


}); // end jQuery.ready
