$(function(){
	var sending = 0;
	var start = 100;
	var elm_id = 0;
	var event = {
		"comment": '',
		"contacts": [],
	}

	var cash_app = {
		init: function() {

		},
		addUser: function(operation, amount, contact_id, contact_name) {
			if(amount <= 0) {
				alert("Значение штрафа/премии должно быть больше нуля.");
				return false;
			}
			if( !operation || !amount || !contact_id ) {
				alert('Проверьте правильность заполнения полей.');
				return;
			}
			// Проверка кратности суммы
			if( amount % 50 != 0 ) {
				alert('Сумма штрафа/премии должна быть кратна 50ти.');
				return false;
			}
			// Проверка суммы штрафа
			if( amount > 5000 ) {
				alert('Сумма штрафа/премии не должна привышать 5000р.');
				return false;
			}

			/**
			 * Проверка лимита
			 */
			var limit = {
				awards 	: $(".add-event .details").data('limit-awards'),
				fine 	: $(".add-event .details").data('limit-fine'),
				man 	: $(".add-event .details").data('limit-man'),
			}

			if(limit.man != null && limit.man == 0 && operation == '+') {
				alert('Вы превысили лимит премий за месяц.');
				return false;
			}

			if(limit.awards != null && (amount > limit.awards && operation == '+')) {
				alert('Вы превысили лимит премий за месяц на '+(amount - limit.awards)+' руб.');
				return false;
			}

			if(limit.fine != null && (amount > limit.fine && operation == '-')) {
				alert('Вы превысили лимит штрафов за месяц на '+(amount - limit.fine)+' руб.');
				return false;
			}

			event.contacts[elm_id] = {
				"operation": operation,
				"amount": amount,
				"contact_id": contact_id
			}
			// Добавляем запись в DOM
			if( $(".no_contact").css('display') != "none" ){
				$(".no_contact").css('display', 'none');
				$(".contact_list").css('display', 'block');
			}

			var contact_elm = "<li><a href=\"#\" data-list-id='"+elm_id+"' class=\"delcontact\"><i class=\"icon16 no\"></i></a> "+operation+amount+"р., "+contact_name+"</li>";
			$(".contact_list").append(contact_elm);

			// Обновляем лимиты
			if(operation == '+' && limit.awards != null)
				$(".add-event .details").data('limit-awards', limit.awards - amount);
			else if(operation == '-' && limit.fine != null)
				$(".add-event .details").data('	limit-fine', limit.fine - amount);
			if(limit.man != null)
				$(".add-event .details").data('limit-man', limit.man - 1);

			elm_id++;
			return false;
		}
	}

	$(".cash-app").on('click', '.add_contact', function(){
		var operation = $("input[name=operation]:checked").val();
		var amount = parseInt($("input[name=amount]").val());
		var contact_id = $("select[name=contact_id] option:selected").val();
		var contact_name = $("select[name=contact_id] option:selected").text();

		cash_app.addUser(operation, amount, contact_id, contact_name);
		return false;
	});

	// Удаление контакта
	$(".cash-app").on('click', ".delcontact", function(e) {
		e.preventDefault();
		// Получаем ID в списке
		var list_id = $(this).data('list-id');
		var limit = {
			awards 	: $(".add-event .details").data('limit-awards'),
			fine 	: $(".add-event .details").data('limit-fine'),
			man 	: $(".add-event .details").data('limit-man'),
		}
		
		// Обновляем лимиты
		if( event.contacts[list_id].operation == '+' && limit.awards != null)
			$(".add-event .details").data('limit-awards', limit.awards + event.contacts[list_id].amount);
		else if(event.contacts[list_id].operation == '-' && limit.fine != null)
			$(".add-event .details").data('limit-fine', limit.fine - event.contacts[list_id].amount);
		if(limit.man != null && event.contacts[list_id].operation == '+')
			$(".add-event .details").data('limit-man', limit.man + 1);
		// Удаляем из массива
		delete event.contacts[list_id];
		// Проверяем последний ли элемент в дереве
		if( $(".contact_list").find("li").length == 1 ) {
			$(".no_contact").css('display', 'block');
			$(".contact_list").css('display', 'none');
		}
		$(this).parent().remove();
		//console.log(event.contacts);
	});

	$(".cash-app").on('click', '#addevent', function(){
		// Проверка на пустоту
		if( !$("#editor").val() ) {
			alert('Поле "Комментарий" не может быть пустым.');
			return false;
		}

		if( event.contacts.length == 0 ) {
			alert("Добавьте хотя бы одного участника")
			return false;
		}

		if( sending == 1 )
			return false;
		sending = 1;
		event.comment = $("#editor").val();
		$.post('?module=events&action=add', event, function(response){
			if( response.status != 'ok' )
				return false;
			// Обновляем страницу
			window.location.reload();
		});
	});

	/**
	 * Отлавливаем изменения в комментрии
	 */
	$(".cash-app").on('keyup', ".redactor-editor", function(e){
		// Текст с HTML тегами
		var comment_html = $("#editor").redactor('code.get');
		var regexp = /[+-]([хx]?)([0-9]+), ([а-я]+)/gi;
		var comment_event_list = comment_html.match(regexp);
		if( comment_event_list instanceof Object ) {
			// Если найдено выражение
			$.each(comment_event_list, function(index, man){
				// -100=Микуляк +х10=Аст
				var man_list = man.split(', ');
				// man_list[0] = -х100; man_list[1] = Мику
				// Ищем участника
				if( people_list[man_list[1]] != undefined ) {
					var user_id = people_list[man_list[1]].contact_id;
					var user_name = people_list[man_list[1]].name;
					var operation = man_list[0][0];
					// Вытаскиваем сумму 100 или х100
					var presumm = man_list[0].substring(1,man_list[0].length);
					if( presumm[0] == 'x' || presumm[0] == 'х' ) {
						var presumm = presumm.substring(1, presumm.length);
						presumm = presumm * 50;
					}

					cash_app.addUser(operation, presumm, user_id, user_name);
					$("#editor").redactor('code.set', comment_html.replace(regexp, ''));
				}
			});
		}
	});


	// Получение событий с другими параметрами
	$('.cash-app').on('click', '[data-action-id="get_events"]', function(e){
		var url = $(this).attr('href');
		$.get(url).done(function(response){
			$('.events-content').html(response.data);
			if( url != "?module=events&action=all" )
				$('.events-content').data('load', 0);
			else
				$('.events-content').data('load', 1);
			start = 10;
		});
		return false;
	});

	/**
	 * Изменения типа операции
	 */
	$('.cash-app').on('change', '[name="operation"]', function(e){
		var operation = $(this).val();
		var amount = $("[name='amount']");
		if( operation == "+" && (amount.val() == '50' || !amount.val()) )
			amount.val('100');
		else if(operation == '-' && (amount.val() == '100' || !amount.val()) )
			amount.val('50');
	});

	// Получение событий с другими параметрами
	$("[data-action-form]").submit(function(e){
		var url = $(this).attr('action');
		$.post(url, $(this).serialize(), function(response){
			$(".events-content").data('load', 0);
			$('.events-content').html(response.data);
			start = 10;
		});
		e.preventDefault();
		return false;
	});

	var loading = false;
	$(window).scroll(function(){
	   if( (($(window).scrollTop()+$(window).height())+250)>=$(document).height() && $(".events-content").data('load') ){
	      if(loading == false){
	         loading = true;
	         $('#loadingbar').css("display","block");
	         $.post("?module=events&action=all", {start:start,limit:10}, function(response){
	         	if(response.status == 'ok') {
	         		$('.events-content').append(response.data);
	    			start = start+10;
	        	}
	        	$('#loadingbar').css("display","none");
	            loading = false;
	         });
	      }
	   }
	});

	// Функция копирования
	$(".cash-app").on('click', '.copy-button', function(e){
		var el = $(this).data('copy');
		var str = $(el).val();
		let tmp   = document.createElement('INPUT'), // Создаём новый текстовой input
	  		focus = document.activeElement; // Получаем ссылку на элемент в фокусе (чтобы не терять фокус)
		tmp.value = str; // Временному input вставляем текст для копирования
		document.body.appendChild(tmp); // Вставляем input в DOM
		tmp.select(); // Выделяем весь текст в input
		document.execCommand('copy'); // Магия! Копирует в буфер выделенный текст (см. команду выше)
		document.body.removeChild(tmp); // Удаляем временный input
		focus.focus(); // Возвращаем фокус туда, где был
		$(this).slideUp().delay(1000).slideDown(300);
	});

	/**
	 * Функция редактирования комментария
	 */
	var edit_flags = [];
	$.editEvent = function(event_id) {
		var event = $("#event"+event_id);
		var comment = event.find('.event-comment');
		if(edit_flags[event_id] == 1)
			return false;
		// Подключаем редактор
		console.log($(comment));
		$(comment).redactor({
			buttons: ['format', 'bold', 'italic', 'deleted', 'unorderedlist', 'orderedlist', 'alignment', 'link'],
		});
		// Устанавливаем режим редактирования
		// Меняем ссылку на сохранить
		var link_save = "<a href=\"javascript:$.saveEvent("+event_id+");\"><i class=\"icon10 yes\"></i> Сохранить</a>";
		event.find('.edit-button').html(link_save);
		edit_flags[event_id] = 1;
		console.log(111);
	}

	// Сохраняем отредактированнный комментарий
	$.saveEvent = function(event_id) {
		if(edit_flags[event_id] != 1)
			return false;
		var event = $("#event"+event_id);
		var comment = event.find('.event-comment');
		$.post('?module=events&action=save', {
			"id" : event_id,
			"comment": $(comment).redactor('code.get'),
		}).done(function(response){
			if( response.status == 'bad' )
				return false;
			// Если все успешно сохранилось
			var link_edit = "<a href=\"javascript:$.editEvent("+event_id+");\"><i class=\"icon10 edit\"></i> Редактировать</a>";
			// Восстанавливаем ссылку
			event.find('.edit-button').html(link_edit);
			$(comment).redactor('core.destroy');
			//comment.html($(comment).redactor('code.get'));
			// Разрещаем редактирование
			edit_flags[event_id] = 0;
		});
	}

	/* Добавление комментария по Ctrl + Enter */
	/*$(window).keypress(function(e){
		e.preventDefault();
		console.log(e.type);
		if( e.keyCode == 10 ) {
			$('#addevent').trigger('click');
			return false;
		}

	}); */
});