var // constants
	active = 'ui-state-active',
	hover = 'ui-state-hover',
	disabled = 'ui-state-disabled',
	
	msie = $.browser.msie,
	mouseWheelEventName = $.browser.mozilla ? 'DOMMouseScroll' : 'mousewheel';
	
(function($){
	$.widget("ui.combobox",{
		"options": {
			"mouseWheel": true,
			"select" : false,
			"nuevosValores" : false,
			
			"className": null,
			"width": 20,
			"iconClass": "ui-icon-triangle-1-s",
			"claseInput": "ui-widget ui-widget-content"
		},
		
		"_create": function(){
			// shortcuts
			if (this.element.hasClass("hiddenComboBox")){
				var input = this.element.data("elementos").input;
				var select = this.element.data("elementos").select;
				
				return this;
			}
			
			var self = this,
				options = self.options,
				select = this.element.hide(),
				type = select.attr('type')
				selected = select.children(":selected"),
				value = selected.val() ? selected.text() : "",
				clase = select.attr("class"),
				claseInput = options.claseInput,
				id = select.attr("id"),
				name = select.attr("name"),
				oculto = $("<input />").attr("name", name).attr("type", "hidden").addClass("hiddenComboBox");
			
			if (!select.is('select')){
				console.error('El Elemento no es un Select');
				return;
			}
			
			var input = this.input = $("<input>")
				.attr("id", this.randomString())
				.insertAfter(select)
				.val(value)
				.autocomplete({
					"delay": 0,
					"minLength": 0,
					"source": function(request, response){
						var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
						response(select.children("option").map(function(){
							var text = $(this).text();
							if (this.value && (!request.term || matcher.test(text)))
								return{
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong>$1</strong>"),
									value: text,
									option: this
								};
						}));
					},
					"select": function(event, ui){
						ui.item.option.selected = true;
						oculto.val(ui.item.option.value);
						
						self._trigger("selected", event,{
							item: ui.item.option
						});
					},
					"change": function(event, ui){
						if (!ui.item){
							self._eventChange(this);
						}
					}
				}).bind("blur", function(){
					self._eventChange(this);
				}).bind("keyup keydown keypress", function(e){
					if (e.which == 13) return false;
				})
				.addClass(claseInput)
				.addClass("ui-corner-left")
				.width(select.width() + 1);
				
			input.data("autocomplete")._renderItem = function(ul, item){
					return $("<li></li>")
						.data("item.autocomplete", item)
						.append("<a>" + item.label + "</a>")
						.appendTo(ul);
				};
			
			self._createButtons(input);
	
			if (!input.is(':enabled')) self.disable();
			
			select.removeAttr("class")
				.removeAttr("name")
				.removeAttr("title")
				.change(function(){
					console.log("cambie!");
				});
				
			oculto
			.data("valor", '')
			.data("elementos", {
				"select" : select, "input" : input
			})
			.insertAfter(select);
		},
		
		"_eventChange":function(input){
			input = $(input);
			var valor = input.val().toLowerCase(),
				valid = false;
			
			this.element.children("option").each(function(){
				if ($(this).text().toLowerCase() == valor){
					oculto.val($(this).val());
					this.selected = valid = true;
					return false;
				}
			});
			
			if (!valid){
				console.log('nada!');
				if (!this.options.nuevosValores){
					input.val('');
				}
				
				oculto.val(input.val()).data("valor", input.val());
				input.data("autocomplete").term = "";
			}else{
				console.log('lo tengo!!!');
			}
		},
		
		"agregarLista": function($lista){
			optionLista = $("<option></option>");
			optionLista.attr("value", $lista.valor);
			optionLista.html($lista.texto);
			
			$elemento = this.element.data("elementos").select.find("[value='" + $lista.valor + "']");
			if ($elemento.length == 0)
				optionLista.appendTo(this.element.data("elementos").select);
				
			return this;
		},
		
		"_createButtons": function(input) {
			function getMargin(margin) {
				// IE8 returns auto if no margin specified
				return margin == 'auto' ? 0 : parseInt(margin);
			}
			
			var self = this,
				options = self.options,
				className = options.className,
				buttonWidth = options.width,
				box = $.support.boxModel,
				height = input.outerHeight(),
				rightMargin = self.oMargin = getMargin(input.css('margin-right')), // store original width and right margin for later destroy
				wrapper = self.wrapper = input.css({ width: (self.oWidth = (box ? input.width() : input.outerWidth())) - buttonWidth, 
													 marginRight: rightMargin + buttonWidth})
					.after('<span class="ui-combobox ui-widget" style="margin-left: -1px"></span>').next(),
				btnContainer = self.btnContainer = $(
					'<div class="ui-combobox-buttons">' + 
						'<div class="ui-combobox-up ui-combobox-button ui-state-default ui-corner-right"><span class="ui-icon '+options.iconClass+'">&nbsp;</span></div>' +  
					'</div>'),
	
				// object shortcuts
				upButton, downButton, buttons, icons,
	
				hoverDelay,
				hoverDelayCallback,
				
				// current state booleans
				hovered, inKeyDown, inSpecialKey, inMouseDown,
							
				// used to reverse left/right key directions
				rtl = input[0].dir == 'rtl';
			
			// apply className before doing any calculations because it could affect them
			if (className) wrapper.addClass(className);
			
			wrapper.append(btnContainer.css({ height: height, left: -buttonWidth-rightMargin,
				// use offset calculation to fix vertical position in Firefox
				top: (input.offset().top - wrapper.offset().top) + 'px' }));
			
			buttons = self.buttons = btnContainer.find('.ui-combobox-button');
			buttons.css({ width: buttonWidth - (box ? buttons.outerWidth() - buttons.width() : 0), height: height - (box ? buttons.outerHeight() - buttons.height() : 0), cursor:"pointer" });
			upButton = buttons[0];
			downButton = buttons[1];
	
			// fix icon centering
			icons = buttons.find('.ui-icon');
			icons.css({ marginLeft: (buttons.innerWidth() - icons.width()) / 2, marginTop:  (buttons.innerHeight() - icons.height()) / 2 });
			
			// set width of btnContainer to be the same as the buttons
			btnContainer.width(buttons.outerWidth());
		
			buttons.hover(function() {
						// ensure that both buttons have hover removed, sometimes they get left on
						self.buttons.removeClass(hover);
						
						if (!options.disabled)
							$(this).addClass(hover);
					}, function() {
						$(this).removeClass(hover);
					})
				.mousedown(mouseDown)
				.mouseup(mouseUp)
				.mouseout(mouseUp);
				
			if (msie)
				// fixes dbl click not firing second mouse down in IE
				buttons.dblclick(function() {
						if (!options.disabled) {
							// make sure any changes are posted
							self._change();
							self._doSpin((this === upButton ? 1 : -1) * options.step);
						}
						
						return false;
					}) 
					
					// fixes IE8 dbl click selection highlight
					.bind('selectstart', function() {return false;});
					
			function isSpecialKey(keyCode) {
				for (var i=0; i<validKeys.length; i++) // predefined list of special keys
					if (validKeys[i] == keyCode) return true;
					
				return false;
			}
				
			function invalidKey(keyCode, charCode) {
				if (inSpecialKey) return false;				
				
				var ch = String.fromCharCode(charCode || keyCode),
					options = self.options;
					
				if ((ch >= '0') && (ch <= '9') || (ch == '-')) return false;
				if (((self.places > 0) && (ch == options.point))
					|| (ch == options.group)) return false;
							
				return true;
			}
			
			// used to delay start of hover show/hide by 100 milliseconds
			function setHoverDelay(callback) {
				if (hoverDelay) {
					// don't do anything if trying to set the same callback again
					if (callback === hoverDelayCallback) return;
					
					clearTimeout(hoverDelay);
				}
				
				hoverDelayCallback = callback;
				hoverDelay = setTimeout(execute, 100);
				
				function execute() {
					hoverDelay = 0;
					callback();
				}
			}
			
			function mouseDown() {
				// close if already visible
				if (input.autocomplete("widget").is(":visible")){
					input.autocomplete("close");
					return;
				}
				
				// work around a bug (likely same cause as #5265)
				$(this).blur();
				
				// pass empty string as value to search for, displaying all results
				input.autocomplete("search", "");
				input.focus();
				
	
				return false;
			}
			
			function mouseUp() {
				if (inMouseDown) {
					$(this).removeClass(active);
					self._stopSpin();
					inMouseDown = false;
				}
				return false;
			}
		},
		
		"randomString": function(sl, c) {
			var c  = c  == undefined ? "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz" : c;
			var sl = sl == undefined ? 14 : sl;
			var rt = '';
			for (var i = 0; i < sl; i++) {
				var rnum = Math.floor(Math.random() * c.length);
				rt += c.substring(rnum,rnum+1);
			}

			return rt;
		},
		
		"destroy": function(){
			this.input.remove();
			this.button.remove();
			this.element.show();
			$.Widget.prototype.destroy.call(this);
		}
	});
})(jQuery);