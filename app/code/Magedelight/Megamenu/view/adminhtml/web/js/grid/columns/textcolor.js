define([
   'underscore',
   'Magento_Ui/js/grid/columns/select'
   ], function (_, Column) {
   'use strict';

   return Column.extend({
      defaults: {
         bodyTmpl: 'Magedelight_Megamenu/ui/grid/cells/textcolor'
      },
      getTextColor: function (row) 
      {
         document.getElementById("textcolor-row-"+row.label_id).style.backgroundColor = row.text_color;
         return row.text_color;
      },
      getId:function(row){
        return "textcolor-row-"+row.label_id;
      }
   });
});