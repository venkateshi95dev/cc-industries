
require([
        "jquery"
    ], function($){
        $(document).ready(function () {
			jQuery.fn.wait = function (func, times, interval) {
			    var _times = times || 100,
			        _interval = interval || 20,
			        _self = this,
			        _selector = this.selector,
			        _iIntervalID;
			    if( this.length ){
			        func && func.call(this);
			    } else {
			        _iIntervalID = setInterval(function() {
			            if(!_times) {
			                clearInterval(_iIntervalID);
			            }
			            _times <= 0 || _times--;
			            _self = $(_selector);
			            if( _self.length ) {
			                func && func.call(_self);
			                clearInterval(_iIntervalID);
			            }
			        }, _interval);
			    }
			    return this;
			};

			$(".admin__data-grid-loading-mask").wait(function(){this.hide();})
        });
    });
