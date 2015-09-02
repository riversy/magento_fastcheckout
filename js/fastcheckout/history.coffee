loading_mask = "loading-mask"

refreshHistory = (params) ->

    new Ajax.Request params.url.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, '')), {
        onSuccess: (transport) =>
            try
                response = eval '(' + transport.responseText + ')'
                {success, content, error} = response
                if success
                    if $(params.container_id)
                        $(params.container_id).innerHTML = content

            catch error
                
            if error
                console.log error
    }



#showLoader = (show) ->
#    if show
#        Element.show loading_mask
#    else 
#        Element.hide loading_mask