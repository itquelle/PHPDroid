/// <reference path="HttpRequest.ts"/>
/// <reference path="Toast.ts"/>
/// <reference path="ContextMenu.ts"/>
/// <reference path="AppCompatActivity.ts"/>
/// <reference path="globals/jquery/index.d.ts" />
var __extends = (this && this.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var MainActivity = /** @class */ (function (_super) {
    __extends(MainActivity, _super);
    function MainActivity() {
        var _this = _super.call(this) || this;
        _this.Toast = new Toast();
        _this.ContextMenu = new ContextMenu();
        return _this;
        // Language String
        // console.log(lang("language_title") + "Hi :D");
    }
    MainActivity.prototype.tableReader = function (password, column_id) {
        if (column_id === void 0) { column_id = ""; }
        var httpRequest = new HttpRequest();
        httpRequest.url = "table-reader";
        httpRequest.json = "json";
        httpRequest.options = { "password": password, "column_id": column_id };
        jQuery
            .when(httpRequest.onResponse())
            .then(function (response) {
            console.table(response);
        });
    };
    return MainActivity;
}(AppCompatActivity));
var Service = new MainActivity();
//# sourceMappingURL=MainActivity.js.map