class AppCompatActivity{

    public window_url: string = window.location.href;

    // @todo getcookie( name )
    public _COOKIE(cookie_name: string){ var name = cookie_name + "="; var decodedCookie = decodeURIComponent(document.cookie); var ca = decodedCookie.split(';'); for(var i = 0; i <ca.length; i++) { var c = ca[i]; while (c.charAt(0) == ' ') { c = c.substring(1); } if (c.indexOf(name) == 0) { return c.substring(name.length, c.length); } } return ""; }

    // @todo setcookie( name, value, expires )
    public setcookie(cookie_name: string = "", value: string = "", days: number = 1){
        if(cookie_name){ var dateString = new Date(); dateString.setTime(dateString.getTime() + (days*24*60*60*1000)); var expires = "expires="+ dateString.toUTCString(); document.cookie = cookie_name + "=" + value + ";" + expires + ";path=/"; }
    }

    // @todo get ( name )
    public _GET(name: string){
        var url = new URL(this.window_url);
        var search = url.searchParams.get(name);
        return search ? search : "";
    }

}