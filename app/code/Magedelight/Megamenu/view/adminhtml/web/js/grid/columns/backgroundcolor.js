define([
   'underscore',
   'Magento_Ui/js/grid/columns/select'
   ], function (_, Column) {
   'use strict';

   return Column.extend({
      defaults: {
         bodyTmpl: 'Magedelight_Megamenu/ui/grid/cells/backgroundcolor'
      },
      getBackgroundColor: function (row) 
      {
         document.getElementById("bgcolor-row-"+row.label_id).style.backgroundColor = row.background_color;
         return row.background_color;
      },
      getId:function(row){
        return "bgcolor-row-"+row.label_id;
      }
   });
});