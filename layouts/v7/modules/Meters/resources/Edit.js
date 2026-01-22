
Vtiger_Edit_Js("Meters_Edit_Js",{},{

    /**
         * Function which will register event for Reference Fields Selection
         */
        registerReferenceSelectionEvent : function(container) {
            this._super(container);
            var thisInstance = this;
            
            jQuery('input[name="cf_1319"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
                thisInstance.referenceSelectionEventHandler(data, container);
                // console.log(data);
    
            });
        },
    
        /**
         * Reference Fields Selection Event Handler
         * On Confirmation It will copy the address details
         */
        referenceSelectionEventHandler :  function(data, container) {
            var thisInstance = this;
            var message = app.vtranslate('OVERWRITE_EXISTING_MSG1')+app.vtranslate('SINGLE_'+data['source_module'])+' ('+data['selectedName']+') '+app.vtranslate('OVERWRITE_EXISTING_MSG2');
            // app.helper.showConfirmationBox({'message' : message}).then(function(e){
            //     thisInstance.copyAddressDetails(data, container);
            // },
            // function(error,err){});
            // console.log(data);
            thisInstance.setFlatsDetails(data, container);
        },
    
        /**
         * Function which will copy the address details - without Confirmation
         */
        setFlatsDetails : function(data, container) {
            var thisInstance = this;
            thisInstance.getRecordDetails(data).then(
                function(data){
                    // var response = data['result'];
                    // thisInstance.mapAddressDetails(thisInstance.memberOfAddressFieldsMapping, response['data'], container);
                    // console.log(data);
                    $('[name="cf_1424"]').val(data.data.cf_1235);
                    $('[name="cf_1424_display"]').val(data.data.cf_1420);
                },
                function(error, err){
    
                });
        },
    
    HideShowField : function(){
    
    },	
    registerHideShowField : function(){
        var editViewForm = this.getForm();
        var thisInstance = this;
    
            // editViewForm.find('[name="cf_991"]').on('change', function (e) {
            //     thisInstance.SetName();
            // });
    
            // $("input[name='cf_1003']").click( function(){
            //         thisInstance.CalcKurs();
            // });
    },	
    registerBasicEvents: function(container){
            this._super(container);
            this.registerReferenceSelectionEvent(container);
            // this.registerHideShowField();
            //SalesPlatform.ru end
        }
    });