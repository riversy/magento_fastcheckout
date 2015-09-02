var loading_mask, refreshHistory;
var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };
loading_mask = "loading-mask";
refreshHistory = function(params) {
  return new Ajax.Request(params.url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')), {
    onSuccess: __bind(function(transport) {
      var content, error, response, success;
      try {
        response = eval('(' + transport.responseText + ')');
        success = response.success, content = response.content, error = response.error;
        if (success) {
          if ($(params.container_id)) {
            $(params.container_id).innerHTML = content;
          }
        }
      } catch (error) {

      }
      if (error) {
        return console.log(error);
      }
    }, this)
  });
};