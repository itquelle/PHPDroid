<?php if(!class_exists('itquelletpl')){exit;}?><?php $tpl = new ITQuelleTPL;$tpl->assign( $this->var );$tpl->draw( "layout/header/index" );?><link rel="stylesheet" href="css/index.css">


<?php $tpl = new ITQuelleTPL;$tpl->assign( $this->var );$tpl->draw( "layout/footer/index" );?>