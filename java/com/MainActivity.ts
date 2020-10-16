/// <reference path="HttpRequest.ts"/>
/// <reference path="Toast.ts"/>
/// <reference path="ContextMenu.ts"/>
/// <reference path="AppCompatActivity.ts"/>
/// <reference path="globals/jquery/index.d.ts" />

class MainActivity extends AppCompatActivity{

    Toast;
    ContextMenu;

    constructor(){
        super();

        this.Toast = new Toast();
        this.ContextMenu = new ContextMenu();

        // Language String
        // console.log(lang("language_title") + "Hi :D");

    }

    tableReader(password, column_id:any = ""){

        let httpRequest:HttpRequest = new HttpRequest();
        httpRequest.url = "table-reader";
        httpRequest.json = "json";
        httpRequest.options = { "password" : password, "column_id" : column_id };

        jQuery
            .when(httpRequest.onResponse())
            .then(function(response){
                console.table(response);
            }
        );

    }

}

let Service:MainActivity = new MainActivity();