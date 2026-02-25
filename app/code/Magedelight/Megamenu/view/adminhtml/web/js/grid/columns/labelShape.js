define([
   'underscore',
   'Magento_Ui/js/grid/columns/select'
   ], function (_, Column) {
   'use strict';

   return Column.extend({
      defaults: {
         bodyTmpl: 'Magedelight_Megamenu/ui/grid/cells/labelShape'
      },
      getLabelText: function (row) 
      {
         return row.text;
      },
      getId:function(row){
        return row.shape;
      }
   });
});