CKEDITOR.plugins.add("iframedialog",{requires:"dialog",onLoad:function(){var e;CKEDITOR.dialog.addIframe=function(e,t,n,o,i,d,a){(n={type:"iframe",src:n,width:"100%",height:"100%"}).onContentLoad="function"==typeof d?d:function(){var e=this.getElement().$.contentWindow;if(e.onDialogEvent){var t=this.getDialog(),n=function(t){return e.onDialogEvent(t)};t.on("ok",n),t.on("cancel",n),t.on("resize",n),t.on("hide",(function(e){t.removeListener("ok",n),t.removeListener("cancel",n),t.removeListener("resize",n),e.removeListener()})),e.onDialogEvent({name:"load",sender:this,editor:t._.editor})}};var r,l={title:t,minWidth:o,minHeight:i,contents:[{id:"iframe",label:t,expand:!0,elements:[n],style:"width:"+n.width+";height:"+n.height}]};for(r in a)l[r]=a[r];this.add(e,(function(){return l}))},e=function(e,t,n){if(!(3>arguments.length)){var o=this._||(this._={}),i=t.onContentLoad&&CKEDITOR.tools.bind(t.onContentLoad,this),d=CKEDITOR.tools.cssLength(t.width),a=CKEDITOR.tools.cssLength(t.height);o.frameId=CKEDITOR.tools.getNextId()+"_iframe",e.on("load",(function(){CKEDITOR.document.getById(o.frameId).getParent().setStyles({width:d,height:a})}));var r={src:"%2",id:o.frameId,frameborder:0,allowtransparency:!0},l=[];"function"==typeof t.onContentLoad&&(r.onload="CKEDITOR.tools.callFunction(%1);"),CKEDITOR.ui.dialog.uiElement.call(this,e,t,l,"iframe",{width:d,height:a},r,""),n.push('<div style="width:'+d+";height:"+a+';" id="'+this.domId+'"></div>'),l=l.join(""),e.on("show",(function(){var e=CKEDITOR.document.getById(o.frameId).getParent(),n=CKEDITOR.tools.addFunction(i);n=l.replace("%1",n).replace("%2",CKEDITOR.tools.htmlEncode(t.src)),e.setHtml(n)}))}},e.prototype=new CKEDITOR.ui.dialog.uiElement,CKEDITOR.dialog.addUIElement("iframe",{build:function(t,n,o){return new e(t,n,o)}})}});