/*+***********************************************************************************
 * MetersData Edit View JavaScript
 * Показывает предупреждение если показание используется в счетах
 ************************************************************************************/

Vtiger_Edit_Js("MetersData_Edit_Js", {}, {

    registerBasicEvents: function(container) {
        this._super(container);
        this.checkUsedInInvoices();
    },

    /**
     * Проверяет используется ли показание в счетах и показывает предупреждение
     */
    checkUsedInInvoices: function() {
        var self = this;
        var recordId = jQuery('[name="record"]').val();

        if (!recordId) {
            return; // Новая запись, проверять нечего
        }

        var params = {
            module: 'MetersData',
            action: 'CheckUsedInInvoices',
            record: recordId
        };

        app.request.post({data: params}).then(
            function(error, response) {
                if (error === null && response && response.success && response.invoices && response.invoices.length > 0) {
                    self.showInvoiceWarning(response.invoices);
                }
            }
        );
    },

    /**
     * Показывает предупреждение со списком счетов
     */
    showInvoiceWarning: function(invoices) {
        var invoiceLinks = [];
        jQuery.each(invoices, function(index, invoice) {
            invoiceLinks.push('<a href="index.php?module=Invoice&view=Detail&record=' + invoice.id + '" target="_blank">' + invoice.subject + '</a>');
        });

        var message = '<div class="alert alert-warning" style="margin: 10px 0;">' +
            '<strong>⚠️ Внимание!</strong> Это показание используется в счетах:<br>' +
            invoiceLinks.join(', ') + '<br><br>' +
            '<small>После изменения показания необходимо открыть счёт, нажать "Изменить" и "Сохранить" для применения изменений.</small>' +
            '</div>';

        // Вставляем предупреждение в начало формы
        jQuery('.editViewBody').prepend(message);
    }
});
