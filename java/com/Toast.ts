class Toast{

    Timer = 5000;
    TimerEvent;
    ToastIcon;

    constructor() {}

    hideToast(){
        clearTimeout(this.TimerEvent);
        jQuery(".Toast").animate({ "margin-right" : "-=1000" }).promise().done(() => {
            jQuery(this).remove();
        });
    }

    setTemplate(type = "Success", text: any, title = "Erledigt"){

        clearTimeout(this.TimerEvent);

        switch (type) {
            case "Success": this.ToastIcon = '<i class="fas fa-check"></i>'; break;
            case "Info": this.ToastIcon = '<i class="fas fa-info"></i>'; break;
            case "Error": this.ToastIcon = '<i class="fas fa-exclamation"></i>'; break;
        }

        jQuery(".Toast").remove();
        jQuery("body").append('<div class="Toast '+type+'">' +
            '<div class="Toast-Inner">' +
            '<div class="Toast-Icon"><div class="Icon-Inner">'+this.ToastIcon+'</div></div> ' +
            '<div class="Toast-Text"><b>'+title+'</b><i>'+text+'</i></div>' +
            '<div class="Toast-Close" onclick="Service.Toast.hideToast()"><i class="fas fa-times"></i></div>' +
            '</div>' +
            '</div>');
        this.TimerEvent = setTimeout(function () {
            jQuery(".Toast").animate({ "margin-right" : "-=1000" }).promise().done(() => {
                jQuery(this).remove();
            });
        }, this.Timer);
    }

    Success(SuccessText = ""){
        this.setTemplate("Success", SuccessText, "Erfolgreich");
    }

    Info(SuccessText = ""){
        this.setTemplate("Info", SuccessText, "Info");
    }

    Error(SuccessText = ""){
        this.setTemplate("Error", SuccessText, "Fehler");
    }


}