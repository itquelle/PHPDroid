var HttpRequest = /** @class */ (function () {
    function HttpRequest() {
        this.options = {};
        this.method = "GET";
        this.json = "";
    }
    HttpRequest.prototype.onResponse = function () {
        var _this = this;
        return jQuery.ajax({
            type: this.method,
            url: this.url,
            dataType: this.json,
            data: this.options,
            crossDomain: this.crossdomain,
            beforeSend: function () {
                // Before Send Request
            },
            success: function (responseText) {
                if (responseText.indexOf("Fatal error") != -1) {
                    console.log("%c" + responseText, 'background: #222; color: #fff; font-size:14px');
                    return false;
                }
            },
            statusCode: {
                404: function () {
                    console.log("%cDatei: " + _this.url + " wurde nicht gefunden. Manifest.xml prüfen.", 'background: #222; color: #fff; font-size:18px');
                },
                405: function () {
                    console.log("%cDu verwendest die Methode (" + _this.method + "), diese ist nicht erlaubt für diese Anfrage.", 'background: #222; color: #fff; font-size:18px');
                },
                500: function (response) {
                    console.log("%cAktiviere Error-Reporting und die Display-Console, um den Fehler in der Console anzuzeigen.", 'background: #222; color: #fff; font-size:18px');
                }
            }
        });
    };
    return HttpRequest;
}());
//# sourceMappingURL=HttpRequest.js.map