'use strict';


window.requestNotification = function(element, tId, envName, ctId, id){
    const data = { templateId : tId, environmentName : envName, contentTypeId : ctId, ouuid : id};
    window.ajaxRequest.post(element.getAttribute("data-url") , data, 'modal-notifications');
};
