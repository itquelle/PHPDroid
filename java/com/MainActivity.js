/// <reference path="Toast.ts"/>
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
        return _this;
        // Language String
        // console.log(lang("language_title") + "Hi :D");
    }
    return MainActivity;
}(AppCompatActivity));
var Service = new MainActivity();
//# sourceMappingURL=MainActivity.js.map