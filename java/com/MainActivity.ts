/// <reference path="Toast.ts"/>
/// <reference path="AppCompatActivity.ts"/>
/// <reference path="globals/jquery/index.d.ts" />

class MainActivity extends AppCompatActivity{

    Toast;
    ContextMenu;

    constructor(){
        super();

        this.Toast = new Toast();

        // Language String
        // console.log(lang("language_title") + "Hi :D");

    }

}

let Service:MainActivity = new MainActivity();