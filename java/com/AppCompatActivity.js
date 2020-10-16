var AppCompatActivity = /** @class */ (function () {
    function AppCompatActivity() {
        this.window_url = window.location.href;
    }
    // @todo getcookie( name )
    AppCompatActivity.prototype._COOKIE = function (cookie_name) { var name = cookie_name + "="; var decodedCookie = decodeURIComponent(document.cookie); var ca = decodedCookie.split(';'); for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    } return ""; };
    // @todo setcookie( name, value, expires )
    AppCompatActivity.prototype.setcookie = function (cookie_name, value, days) {
        if (cookie_name === void 0) { cookie_name = ""; }
        if (value === void 0) { value = ""; }
        if (days === void 0) { days = 1; }
        if (cookie_name) {
            var dateString = new Date();
            dateString.setTime(dateString.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "expires=" + dateString.toUTCString();
            document.cookie = cookie_name + "=" + value + ";" + expires + ";path=/";
        }
    };
    // @todo get ( name )
    AppCompatActivity.prototype._GET = function (name) {
        var url = new URL(this.window_url);
        var search = url.searchParams.get(name);
        return search ? search : "";
    };
    return AppCompatActivity;
}());
//# sourceMappingURL=AppCompatActivity.js.map