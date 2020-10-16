var Toast = /** @class */ (function () {
    function Toast() {
        this.Timer = 5000;
    }
    Toast.prototype.hideToast = function () {
        var _this = this;
        clearTimeout(this.TimerEvent);
        jQuery(".Toast").animate({ "margin-right": "-=1000" }).promise().done(function () {
            jQuery(_this).remove();
        });
    };
    Toast.prototype.setTemplate = function (type, text, title) {
        if (type === void 0) { type = "Success"; }
        if (title === void 0) { title = "Erledigt"; }
        clearTimeout(this.TimerEvent);
        switch (type) {
            case "Success":
                this.ToastIcon = '<i class="fas fa-check"></i>';
                break;
            case "Info":
                this.ToastIcon = '<i class="fas fa-info"></i>';
                break;
            case "Error":
                this.ToastIcon = '<i class="fas fa-exclamation"></i>';
                break;
        }
        jQuery(".Toast").remove();
        jQuery("body").append('<div class="Toast ' + type + '">' +
            '<div class="Toast-Inner">' +
            '<div class="Toast-Icon"><div class="Icon-Inner">' + this.ToastIcon + '</div></div> ' +
            '<div class="Toast-Text"><b>' + title + '</b><i>' + text + '</i></div>' +
            '<div class="Toast-Close" onclick="Service.Toast.hideToast()"><i class="fas fa-times"></i></div>' +
            '</div>' +
            '</div>');
        this.TimerEvent = setTimeout(function () {
            var _this = this;
            jQuery(".Toast").animate({ "margin-right": "-=1000" }).promise().done(function () {
                jQuery(_this).remove();
            });
        }, this.Timer);
    };
    Toast.prototype.Success = function (SuccessText) {
        if (SuccessText === void 0) { SuccessText = ""; }
        this.setTemplate("Success", SuccessText, "Erfolgreich");
    };
    Toast.prototype.Info = function (SuccessText) {
        if (SuccessText === void 0) { SuccessText = ""; }
        this.setTemplate("Info", SuccessText, "Info");
    };
    Toast.prototype.Error = function (SuccessText) {
        if (SuccessText === void 0) { SuccessText = ""; }
        this.setTemplate("Error", SuccessText, "Fehler");
    };
    return Toast;
}());
//# sourceMappingURL=Toast.js.map