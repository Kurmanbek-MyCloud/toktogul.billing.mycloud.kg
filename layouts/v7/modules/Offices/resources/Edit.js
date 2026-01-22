
// jeng

Vtiger_Edit_Js('Offices_Edit_Js', {}, {


    registerCalculateRentPrice : function (container) {

        var rentElem = container.find('input[name=cf_1379]');

        var areaElem = container.find('input[name=cf_1377]');
        var costElem = container.find('input[name=cf_1381]');

        areaElem.change(function () {

            rentElem.val(areaElem.val() * costElem.val());
        });

        costElem.change(function () {

            rentElem.val(areaElem.val() * costElem.val());
        });
    },


    registerBasicEvents : function(container) {

        this._super(container);
        this.registerCalculateRentPrice(container);
    }
});