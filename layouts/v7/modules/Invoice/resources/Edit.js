/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Edit_Js("Invoice_Edit_Js",{},{
    
    accountRefrenceField : false,
    
    initializeVariables : function() {
        this._super();
        var form = this.getForm();
        this.accountReferenceField = form.find('[name="account_id"]');
    },
    
    /**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		var self = this;
		
		this.accountReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			self.referenceSelectionEventHandler(data, container);
		});
	},
    
    /**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		if(!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField',container);
		}

		if(sourceFieldElement.attr('name') == 'contact_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="account_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
        }
        return params;
    },
    
    /**
	 * Function to search module names
	 */
	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}
		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}
		
		if(typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if(record.length) {
				params.base_record = record.val();
			} else if(recordId) {
				params.base_record = recordId;
			} else if(app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if(editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}

		if (params.search_module == 'Contacts') {
			var form = this.getForm();
			if(this.accountReferenceField.length > 0 && this.accountReferenceField.val().length > 0) {
				var closestContainer = this.accountReferenceField.closest('td');
				params.parent_id = this.accountReferenceField.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}
        
        // Added for overlay edit as the module is different
        if(params.search_module == 'Products' || params.search_module == 'Services') {
            params.module = 'Invoice';
        }
        
		app.request.get({'data':params}).then(
			function(error, data) {
                if(error == null) {
                    aDeferred.resolve(data);
                }
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},
        
        registerBasicEvents: function(container){
            this._super(container);
            this.registerForTogglingBillingandShippingAddress();
            this.registerEventForCopyAddress();
            this.registerMeterReadingsEvents(container);
        },

        /**
         * Регистрация событий для работы с показаниями счётчиков
         */
        registerMeterReadingsEvents: function(container) {
            var self = this;
            var form = this.getForm();

            // Загружаем показания при загрузке страницы
            var flatIdField = form.find('[name="cf_1265"]');
            if (flatIdField.length && flatIdField.val()) {
                self.loadMeterReadings(flatIdField.val());
            }

            // Обновляем показания при изменении объекта (дома)
            flatIdField.on('change', function() {
                var flatId = jQuery(this).val();
                if (flatId) {
                    self.loadMeterReadings(flatId);
                } else {
                    self.clearReadingSelects();
                }
            });

            // Следим за событием выбора референса для cf_1265
            form.find('[name="cf_1265"]').on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data) {
                if (data && data.id) {
                    self.loadMeterReadings(data.id);
                }
            });

            // Обработка выбора показания в селекте
            jQuery(document).on('change', '.readingSelect', function() {
                var select = jQuery(this);
                var row = select.data('row');
                var type = select.data('type'); // 'previous' или 'current'
                var selectedOption = select.find('option:selected');
                var readingId = select.val();
                var readingValue = selectedOption.data('value') || '';

                // Обновляем скрытые поля
                if (type === 'previous') {
                    jQuery('#previousReading' + row).val(readingValue);
                    jQuery('#previousReadingId' + row).val(readingId);
                } else if (type === 'current') {
                    jQuery('#currentReading' + row).val(readingValue);
                    jQuery('#currentReadingId' + row).val(readingId);
                }

                // Пересчитываем количество (разницу между показаниями)
                self.recalculateQuantity(row);
            });
        },

        /**
         * Загрузка показаний счётчиков по ID дома
         */
        loadMeterReadings: function(flatId) {
            var self = this;
            var params = {
                module: 'Invoice',
                action: 'GetMeterReadings',
                flat_id: flatId
            };

            app.request.post({data: params}).then(
                function(error, response) {
                    if (error === null && response && response.success) {
                        self.populateReadingSelects(response.readings);
                    }
                }
            );
        },

        /**
         * Заполнение выпадающих списков показаниями
         */
        populateReadingSelects: function(readings) {
            var self = this;
            var prevSelects = jQuery('.previousReadingSelect');
            var currSelects = jQuery('.currentReadingSelect');

            // Сохраняем текущие выбранные значения
            var selectedPrev = {};
            var selectedCurr = {};

            prevSelects.each(function() {
                var row = jQuery(this).data('row');
                selectedPrev[row] = jQuery(this).val();
            });
            currSelects.each(function() {
                var row = jQuery(this).data('row');
                selectedCurr[row] = jQuery(this).val();
            });

            // Формируем опции
            var options = '<option value="">-- Выберите --</option>';
            jQuery.each(readings, function(index, reading) {
                options += '<option value="' + reading.id + '" ' +
                    'data-value="' + reading.value + '" ' +
                    'data-date="' + reading.date + '" ' +
                    'data-meter="' + reading.meter_id + '" ' +
                    'data-well="' + reading.well_name + '">' +
                    reading.label +
                    '</option>';
            });

            // Обновляем все селекты и синхронизируем скрытые поля
            prevSelects.each(function() {
                var row = jQuery(this).data('row');
                var currentVal = selectedPrev[row];
                jQuery(this).html(options);
                if (currentVal) {
                    jQuery(this).val(currentVal);
                    // Синхронизируем скрытое поле с актуальным значением из выбранной опции
                    var selectedOption = jQuery(this).find('option:selected');
                    var readingValue = selectedOption.data('value') || '';
                    jQuery('#previousReading' + row).val(readingValue);
                }
            });

            currSelects.each(function() {
                var row = jQuery(this).data('row');
                var currentVal = selectedCurr[row];
                jQuery(this).html(options);
                if (currentVal) {
                    jQuery(this).val(currentVal);
                    // Синхронизируем скрытое поле с актуальным значением из выбранной опции
                    var selectedOption = jQuery(this).find('option:selected');
                    var readingValue = selectedOption.data('value') || '';
                    jQuery('#currentReading' + row).val(readingValue);
                }
            });
        },

        /**
         * Очистка выпадающих списков показаний
         */
        clearReadingSelects: function() {
            var emptyOption = '<option value="">-- Выберите --</option>';
            jQuery('.readingSelect').html(emptyOption);
        },

        /**
         * Пересчёт количества (разницы показаний)
         */
        recalculateQuantity: function(row) {
            var self = this;
            var prevValue = parseFloat(jQuery('#previousReading' + row).val()) || 0;
            var currValue = parseFloat(jQuery('#currentReading' + row).val()) || 0;
            var quantity = currValue - prevValue;

            if (quantity < 0) {
                quantity = 0;
            }

            jQuery('#qty' + row).val(quantity.toFixed(3));

            // Получаем строку line item и вызываем пересчёт итогов
            var lineItemRow = jQuery('#row' + row);
            if (lineItemRow.length) {
                self.quantityChangeActions(lineItemRow);
            }
        },
});
    

