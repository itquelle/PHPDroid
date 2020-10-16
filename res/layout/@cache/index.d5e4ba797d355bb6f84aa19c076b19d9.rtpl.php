<?php if(!class_exists('itquelletpl')){exit;}?><?php $tpl = new ITQuelleTPL;$tpl->assign( $this->var );$tpl->draw( "layout/header/index" );?>


<div class="Hello" <?php if( _Get('number') == '1' ){ ?> style="display: none" <?php } ?>>
    <?php echo $test_var;?>

</div>

<?php $tpl = new ITQuelleTPL;$tpl->assign( $this->var );$tpl->draw( "layout/footer/index" );?>