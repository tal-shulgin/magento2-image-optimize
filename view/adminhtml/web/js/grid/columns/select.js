define([
    'Magento_Ui/js/grid/columns/select',
    'mage/translate'
], function (Column, $t) {
    'use strict';

    return Column.extend({
        /**
         *
         * @param record
         * @returns {string}
         */
        getLabel: function (record) {
            var result;

            switch (record[this.index]){
                case 'pending':
                    result = '<span class="mp-grid-severity mp-grid-severity-pending">' +
                        '<span>' + $t('PENDING') + '</span></span>';
                    break;
                case 'error':
                    result = '<span class="mp-grid-severity mp-grid-severity-error">' +
                        '<span>' + $t('ERROR') + '</span></span>';
                    break;
                case 'success':
                    result = '<span class="mp-grid-severity mp-grid-severity-success">' +
                        '<span>' + $t('SUCCESS') + '</span></span>';
                    break;
                case 'skipped':
                    result = '<span class="mp-grid-severity mp-grid-severity-skip">' +
                        '<span>' + $t('SKIPPED') + '</span></span>';
                    break;
            }

            return result;
        }
    });
});
