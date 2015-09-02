/* 
 * Send E-Mails
 */

var prepareUrl = function(url){
        var str = url;
        if (typeof(str) != 'undefined'){
            return str.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));
        } else {
            return url;
        }       
}

var sendEmail = function(params){
    if (typeof(params) != 'undefined'){
       
       var loader_id = 'loading-mask';
       var total = $(params.total).value;
       
       var url = prepareUrl(params.url.replace('{{total}}', total).replace('{{copy_to}}', ($(params.copy_to).checked ? '1' : '0') ));
       
       new Ajax.Request(url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ), {
                 parameters: {},
                 onCreate: function(obj) { Element.show(loader_id); },
                 onFailure: function (){  },
                 onComplete: function (transport) { Element.hide(loader_id); },
                 onSuccess: (function(transport) { 
                    try{
                         var response = eval('(' + transport.responseText + ')');
                         
                         if (response.success){
                              if ($(params.container_id)) {
                                $(params.container_id).innerHTML = response.content;
                              }                             
                         }

                         if (response.error){
                             console.error(response.message);
                         }
                     }
                     catch (e) {
                         response = {};
                     }

                 }).bind(this)
             });


        

        
        
        
        
        
        
        $('create_invoice').style.display = 'none';
    }            
};




var createInvoice = function(){
    if ($('create_invoice')){
        if ($('create_invoice').style.display == 'block'){
            $('create_invoice').style.display = 'none';
        } else {
            $('create_invoice').style.display = 'block';
        }
    }
};