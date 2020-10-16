class HttpRequest{

    url;
    options:any = {};
    method:any  = "GET";
    json:any    = "";
    crossdomain;

    onResponse() {

        return jQuery.ajax({
            type        : this.method,
            url         : this.url,
            dataType    : this.json,
            data        : this.options,
            crossDomain : this.crossdomain,
            beforeSend : function(){
                // Before Send Request
            },
            success : function(responseText){

                if(responseText.indexOf("Fatal error") != -1){
                    console.log("%c"+responseText, 'background: #222; color: #fff; font-size:14px');
                    return false;
                }

            },
            statusCode : {
                404 : () => {
                    console.log("%cDatei: " + this.url + " wurde nicht gefunden. Manifest.xml prüfen.", 'background: #222; color: #fff; font-size:18px');
                },
                405 : () => {
                    console.log("%cDu verwendest die Methode ("+this.method+"), diese ist nicht erlaubt für diese Anfrage.", 'background: #222; color: #fff; font-size:18px');
                },
                500 : (response) => {
                    console.log("%cAktiviere Error-Reporting und die Display-Console, um den Fehler in der Console anzuzeigen.", 'background: #222; color: #fff; font-size:18px');
                }
            }
        });

    }

}