<?php
/**
 * @copyright ITQuelle GmbH
 * @date 01.10.2020
 */
require __DIR__.DIRECTORY_SEPARATOR . "svg.php";

interface ITQuelleCompact{
    public function assign($variable, $value = null);
    public function draw($tpl_name, $return_string = false);
    public function check_template($tpl_name);
}

class ITQuelleTPL implements ITQuelleCompact {

    // Error Messages
    var $template_not_found = "Template ''%s'' not found";
    var $permission_cache_dir = "Cache directory %s doesn't have write permission. Set write permission or set ". '$tpl->setCheckTemplateUpdate(true)' ." to false.";

    var $tpl_dir, $cache_dir, $base_url, $tpl_ext, $path_relace, $path_replace_list = []
            , $black_list = [], $check_template_update, $php_enabled, $debug, $root_dir, $var = []
            , $tpl = [], $cache, $cache_id, $config_name_sum = [], $cache_expire_time, $regexp = [], $path;

    public function __construct(){
        $this->setTplDir(view_dir);
        $this->setCacheDir(cache_dir);
        $this->setBaseUrl(base_url);
        $this->setTplExt("html");
        $this->setPathRelace(true);
        $this->setCheckTemplateUpdate(true);
        $this->setPhpEnabled(false);
        $this->setDebug(false);
        $this->setCache(false);
        $this->setRootDir("");
        $this->setCacheId(null);
        $this->setCacheExpireTime(3600);
        $this->setBlackList([
            '\$this',
            'ITQuelleTPL::',
            'self::',
            '_SESSION',
            '_SERVER',
            '_ENV',
            'eval',
            'exec',
            'unlink',
            'rmdir'
        ]);
    }

    /**
     * @info Template Functions
     */

    public function assign($variable, $value = null){
        if (is_array($variable)) $this->var = $variable + $this->var;
        else $this->var[$variable] = $value;
    }

    public function check_template($tpl_name){

        if (!isset($this->tpl['checked']))
        {
            $tpl_basename = basename($tpl_name);
            $tpl_basedir = strpos($tpl_name, "/") ? dirname($tpl_name) . '/' : null;
            $this->tpl['template_directory'] = $this->tpl_dir . $tpl_basedir;
            $this->tpl['tpl_filename'] = $this->root_dir . $this->tpl['template_directory'] . $tpl_basename . '.' . $this->tpl_ext;
            $temp_compiled_filename = $this->root_dir . $this->cache_dir . $tpl_basename . "." . md5($this->tpl['template_directory'] . serialize($this->config_name_sum));
            $this->tpl['compiled_filename'] = $temp_compiled_filename . '.rtpl.php';
            $this->tpl['cache_filename'] = $temp_compiled_filename . '.s_' . $this->cache_id . '.rtpl.php';
            $this->tpl['checked'] = true;

            // Check if template exists
            if ($this->check_template_update && !file_exists($this->tpl['tpl_filename']) && !preg_match('/http/', $tpl_name)) {
                $e = new ITQuelleTPL_NotFoundException(sprintf($this->template_not_found, $this->tpl_dir . $tpl_basename . "." . $this->tpl_ext));
                throw $e->setTemplateFile($this->tpl['tpl_filename']);
            }

            if (preg_match('/http/', $tpl_name)) {
                $this->compileFile('', '', $tpl_name, $this->root_dir . $this->cache_dir, $this->tpl['compiled_filename']);
                return true;
            } elseif (!file_exists($this->tpl['compiled_filename']) || ($this->check_template_update && filemtime($this->tpl['compiled_filename']) < filemtime($this->tpl['tpl_filename']))) {
                $this->compileFile($tpl_basename, $tpl_basedir, $this->tpl['tpl_filename'], $this->root_dir . $this->cache_dir, $this->tpl['compiled_filename']);
                return true;
            }
        }

    }
    public function draw($tpl_name, $return_string = false)
    {
        try {
            $this->check_template($tpl_name);
        } catch(ITQuelleTPL_Exception $e) {
            $output = $this->printDebug($e);
            die($output);
        }

        if (!$this->cache && !$return_string) {
            extract($this->var);
            include $this->tpl['compiled_filename'];
            unset($this->tpl);
        } else {
            ob_start();
            extract($this->var);
            include $this->tpl['compiled_filename'];
            $itQuelle_contents = ob_get_clean();

            if($this->cache){
                file_put_contents($this->tpl['cache_filename'], "<?php if(!class_exists('ITQuelleTPL')){exit;}?>" . $itQuelle_contents);
            }
            unset($this->tpl);

            if($return_string){
                return $itQuelle_contents;
            }else{
                return $itQuelle_contents;
            }

        }
    }
    public function cache($tpl_name, $expire_time, $cache_id = null){
        $expire_time    = $this->cache_expire_time;
        $this->cache_id = $cache_id;
        if (!$this->check_template($tpl_name) && file_exists($this->tpl['cache_filename']) && (time() - filemtime($this->tpl['cache_filename']) < $expire_time)) {
            return substr(file_get_contents($this->tpl['cache_filename']) , 43);
        } else {
            if (file_exists($this->tpl['cache_filename'])) unlink($this->tpl['cache_filename']);
            $this->cache = true;
        }
    }

    // Code Compiler
    public function setRegexp($line){
        array_push($this->regexp, $line);
    }
    public function compileFile($tpl_basename, $tpl_basedir, $tpl_filename, $cache_dir, $compiled_filename)
    {
        $this->tpl['source'] = $template_code = file_get_contents($tpl_filename);
        $template_code = preg_replace("/<\?xml(.*?)\?>/s", "##XML\\1XML##", $template_code);

        if(!$this->php_enabled){
            $template_code = str_replace(["<?", "?>"],["&lt;?", "?&gt;"], $template_code);
        }

        $template_code = preg_replace_callback("/##XML(.*?)XML##/s", [$this, "xml_reSubstitution"], $template_code);

        $template_compiled = "<?php if(!class_exists('itquelletpl')){exit;}?>" . $this->compileTemplate($template_code, $tpl_basedir);
        $template_compiled = str_replace("?>\n", "?>\n\n", $template_compiled);

        // Cache Dir Exists?
        if(!is_dir($cache_dir)){
            mkdir($cache_dir, 0755, true);
        }
        // Cache Dir Writeable?
        if(!is_writable($cache_dir)){
            throw new ITQuelleTPL_Exception(sprintf($this->permission_cache_dir, $cache_dir));
        }

        file_put_contents($compiled_filename, $template_compiled);

    }
    public function compileTemplate($template_code, $tpl_basedir){

        // Example: <loop id="$list">{$value.id}</loop>
        $this->setRegexp('(\<loop(?: name){0,1} id="\${0,1}[^"]*"\>)');
        $this->setRegexp('(\<\/loop\>)');

        // Example: <debug></debug>
        $this->setRegexp('(\<\debug\>)');
        $this->setRegexp('(\<\/debug\>)');

        // Example: <get name="getQueryName"></get>
        $this->setRegexp('(\<get name="[^"]*"\>)');
        $this->setRegexp('(\<\/get\>)');

        // Example: <post name="getQueryName"></post>
        $this->setRegexp('(\<post name="[^"]*"\>)');
        $this->setRegexp('(\<\/post\>)');

        // Example: <function call="date('d.m.Y')"></function>
        $this->setRegexp('(\<function call="(\w*?)(?:.*?)"\>)');
        $this->setRegexp('(\<\/function\>)');

        // Example <chart type="donut|line|bar" layout_width="px|match_parent"></chart>
        $this->setRegexp('(\<chart(\w*?)(?:.*?)\>)');
        $this->setRegexp('(\<\/chart\>)');

        // Example: <number int="100,90"></number>  (-> 100.90)
        $this->setRegexp('(\<number int="(\w*?)(?:.*?)"\>)');
        $this->setRegexp('(\<\/number\>)');

        // Example: <date time="1601569910" format="d.m.Y"></date>
        $this->setRegexp('(\<date time="(\w*?)(?:.*?)" format="(\w*?)(?:.*?)"\>)');
        $this->setRegexp('(\<\/date\>)');

        // Example: <if case="condition">1<elseif case="condition">2</elseif><else>0</else></if>
        // Example: {if="condition"}...{/if}
        // Example: <div ?if="condition" ... ?endif>..</div>
        $this->setRegexp('(\<if(?: case){0,1}="[^"]*"\>)');
        $this->setRegexp('(\<elseif(?: case){0,1}="[^"]*"\>)');
        $this->setRegexp('(\<else\>)');
        $this->setRegexp('(\<\/else\>)');
        $this->setRegexp('(\<\/elseif\>)');
        $this->setRegexp('(\<\/if\>)');
        $this->setRegexp('(\{if(?: condition){0,1}="[^"]*"\})');
        $this->setRegexp('(\{elseif(?: condition){0,1}="[^"]*"\})');
        $this->setRegexp('(\{else\})');
        $this->setRegexp('(\{\/if\})');
        $this->setRegexp('(\?if(?: case){0,1}="[^"]*")');
        $this->setRegexp('(\?endif)');

        // Example: <include file="template_name"></include>
        // Example: {include="template_name"}
        $this->setRegexp('(\<include(?: file){0,1}="[^"]*"(?: cache="[^"]*")?\>)');
        $this->setRegexp('(\<\/include\>)');
        $this->setRegexp('(\{include="[^"]*"(?: cache="[^"]*")?\})');

        // Example: <help>Help informations</help> (Only in code visible)
        $this->setRegexp('(\<help\>|\{\*)');
        $this->setRegexp('(\<\/help\>|\*\>)');

        // Example: <for each="$i=0;$i<10;$i++">{$i}</for>
        $this->setRegexp('(\<for(?: each){0,1}="[^"]*"\>)');
        $this->setRegexp('(\<\/for\>)');

        // Example: {break}
        $this->setRegexp('(\{break\})');

        // Example: {noparse}...{/noparse}
        $this->setRegexp('(\{noparse\})');
        $this->setRegexp('(\{\/noparse\})');

        $tag_regexp = "/" . join("|", $this->regexp) . "/";
        $template_code = $this->path_replace($template_code, $tpl_basedir);
        $template_code = preg_split($tag_regexp, $template_code, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $compiled_code = $this->compileCode($template_code);
        return $compiled_code;

    }
    public function compileCode($parsed_code)
    {
        global $svg_pie_chart, $svg_line_chart, $svg_bar_chart;

        if (!$parsed_code) return "";
        $compiled_code = $open_if = $comment_is_open = $ignore_is_open = null;
        $loop_level = 0;
        foreach ($parsed_code as $html)
        {
            if (!$comment_is_open && (strpos($html, '</help>') !== false || strpos($html, '*}') !== false)) $ignore_is_open = false;
            elseif ($ignore_is_open)
            {
            }
            elseif (strpos($html, '{/noparse}') !== false) $comment_is_open = false;
            elseif ($comment_is_open) $compiled_code .= $html;
            elseif (strpos($html, '<help>') !== false || strpos($html, '{*') !== false) $ignore_is_open = true;
            elseif (strpos($html, '{noparse}') !== false) $comment_is_open = true;
            elseif (preg_match('/\{include="([^"]*)"(?: cache="([^"]*)"){0,1}\}/', $html, $code))
            {
                if (preg_match("/http/", $code[1]))
                {
                    $content = file_get_contents($code[1]);
                    $compiled_code .= $content;
                }
                else
                {
                    $include_var = $this->var_replace($code[1], $left_delimiter = null, $right_delimiter = null, $php_left_delimiter = '".', $php_right_delimiter = '."', $loop_level);
                    $actual_folder = substr($this->tpl['template_directory'], strlen(self::$tpl_dir));
                    $include_template = $actual_folder . $include_var;
                    $include_template = $this->reduce_path($include_template);
                    if (isset($code[2]))
                    {
                        $compiled_code .= '<?php $tpl = new ' . get_called_class() . ';' . 'if( $cache = $tpl->cache( "' . $include_template . '" ) )' . '	echo $cache;' . 'else{' . '$tpl->assign( $this->var );' . (!$loop_level ? null : '$tpl->assign( "key", $key' . $loop_level . ' ); $tpl->assign( "value", $value' . $loop_level . ' );') . '$tpl->draw( "' . $include_template . '" );' . '}' . '?>';
                    }
                    else
                    {
                        $compiled_code .= '<?php $tpl = new ' . get_called_class() . ';' . '$tpl->assign( $this->var );' . (!$loop_level ? null : '$tpl->assign( "key", $key' . $loop_level . ' ); $tpl->assign( "value", $value' . $loop_level . ' );') . '$tpl->draw( "' . $include_template . '" );' . '?>';
                    }
                }
            }
            elseif (preg_match('/\<include(?: file){0,1}="([^"]*)"(?: cache="([^"]*)"){0,1}\>/', $html, $code))
            {
                if (preg_match("/http/", $code[1]))
                {
                    $content = file_get_contents($code[1]);
                    $compiled_code .= $content;
                }
                else
                {
                    $include_var = $this->var_replace($code[1], $left_delimiter = null, $right_delimiter = null, $php_left_delimiter = '".', $php_right_delimiter = '."', $loop_level);
                    $actual_folder = substr($this->tpl['template_directory'], strlen($this->tpl_dir));
                    $include_template = $actual_folder . $include_var;
                    $include_template = $this->reduce_path($include_template);
                    if (isset($code[2]))
                    {
                        $compiled_code .= '<?php $tpl = new ' . get_called_class() . ';' . 'if( $cache = $tpl->cache( "' . $include_template . '" ) )' . '	echo $cache;' . 'else{' . '$tpl->assign( $this->var );' . (!$loop_level ? null : '$tpl->assign( "key", $key' . $loop_level . ' ); $tpl->assign( "value", $value' . $loop_level . ' );') . '$tpl->draw( "' . $include_template . '" );' . '}' . '?>';
                    }
                    else
                    {
                        $compiled_code .= '<?php $tpl = new ' . get_called_class() . ';' . '$tpl->assign( $this->var );' . (!$loop_level ? null : '$tpl->assign( "key", $key' . $loop_level . ' ); $tpl->assign( "value", $value' . $loop_level . ' );') . '$tpl->draw( "' . $include_template . '" );' . '?>';
                    }
                }
            }
            elseif (preg_match('/\<loop(?: name){0,1} id="\${0,1}([^"]*)"\>/', $html, $code))
            {
                $loop_level++;
                $var = $this->var_replace('$' . $code[1], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level - 1);
                $counter = "\$counter$loop_level";
                $key = "\$key$loop_level";
                $value = "\$value$loop_level";
                $compiled_code .= "<?php $counter=-1; if( !is_null($var) && is_array($var) && sizeof($var) ) foreach( $var as $key => $value ){ $counter++; ?>";
            }
            elseif (strpos($html, '{break}') !== false)
            {
                $compiled_code .= '<?php break; ?>';
            }
            elseif (strpos($html, '</loop>') !== false)
            {
                $counter = "\$counter$loop_level";
                $loop_level--;
                $compiled_code .= "<?php } ?>";
            }
            elseif (preg_match('/\<if(?: case){0,1}="([^"]*)"\>/', $html, $code))
            {
                $open_if++;
                $tag = $code[0];
                $condition = $code[1];
                $this->function_check($tag);
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php if( $parsed_condition ){ ?>";
            }
            elseif (preg_match('/\?if(?: condition){0,1}="([^"]*)"/', $html, $code))
            {
                $open_if++;
                $tag = $code[0];
                $condition = $code[1];
                $this->function_check($tag);
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php if( $parsed_condition ){ ?>";
            }
            elseif (preg_match('/\{if(?: condition){0,1}="([^"]*)"\}/', $html, $code))
            {
                $open_if++;
                $tag = $code[0];
                $condition = $code[1];
                $this->function_check($tag);
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php if( $parsed_condition ){ ?>";
            }
            elseif (preg_match('/\<for(?: each){0,1}="([^"]*)"\>/', $html, $code))
            {
                $tag = $code[0];
                $condition = $code[1];
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= '<?php for('.$parsed_condition.'){ ?>';
            }
            elseif (preg_match('/\<elseif(?: case){0,1}="([^"]*)"\>/', $html, $code))
            {
                $tag = $code[0];
                $condition = $code[1];
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php }elseif( $parsed_condition ){ ?>";
            }
            elseif (preg_match('/\{elseif(?: condition){0,1}="([^"]*)"\}/', $html, $code))
            {
                $tag = $code[0];
                $condition = $code[1];
                $parsed_condition = $this->var_replace($condition, $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php }elseif( $parsed_condition ){ ?>";
            }
            elseif (strpos($html, '</for>') !== false) { $compiled_code .= '<?php } ?>'; }
            elseif (strpos($html, '</chart>') !== false) { $compiled_code .= ''; }
            elseif (strpos($html, '</get>') !== false) { $compiled_code .= ''; }
            elseif (strpos($html, '</post>') !== false) { $compiled_code .= ''; }
            elseif (strpos($html, '</elseif>') !== false) { $compiled_code .= ''; }
            elseif (strpos($html, '</else>') !== false) { $compiled_code .= ''; }
            elseif (strpos($html, '<else>') !== false) { $compiled_code .= '<?php }else{ ?>'; }
            elseif (strpos($html, '{else}') !== false) { $compiled_code .= '<?php }else{ ?>'; }
            elseif (strpos($html, '</bigpipe>') !== false) {}
            elseif (strpos($html, '</function>') !== false) {}
            elseif (strpos($html, '</include>') !== false) {}
            elseif (strpos($html, '?endif') !== false) {
                $open_if--;
                $compiled_code .= '<?php } ?>';
            }
            elseif (strpos($html, '</if>') !== false)
            {
                $open_if--;
                $compiled_code .= '<?php } ?>';
            }
            elseif (strpos($html, '{/if}') !== false)
            {
                $open_if--;
                $compiled_code .= '<?php } ?>';
            }
            elseif (preg_match('/\<post name="(\w*)(.*?)"\>/', $html, $code))
            {
                $compiled_code .= "<?php echo _Post('".$code[1]."') ?>";
            }
            elseif (preg_match('/\<get name="(\w*)(.*?)"\>/', $html, $code))
            {
                $compiled_code .= "<?php echo _Get('".$code[1]."') ?>";
            }
            elseif (preg_match('/\<number int="(\w*)(.*?)"\>/', $html, $code))
            {
                if (!empty($code[2]))
                {
                    $parsed_function = $this->var_replace($code[2], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                }
                else
                {
                    $parsed_function = $code[1];
                }
                $compiled_code .= "<?php echo number_format($parsed_function,2,',','.'); ?>";
            }
            elseif (strpos($html, '</number>') !== false)
            {
                $compiled_code .= "";
            }
            elseif (preg_match('/\<date time="(\w*)(.*?)"\>/', $html, $code))
            {
                $NumberFormat = date('d.m.Y', $code[1]);
                $compiled_code .= "<?php echo '$NumberFormat'; ?>";
            }
            elseif (strpos($html, '</date>') !== false)
            {
                $compiled_code .= "";
            }
            elseif (strpos($html, '</TabLayout>') !== false)
            {
                $compiled_code .= "<?php echo '</div>'; ?>";
            }
            // Chart -> Item
            elseif(preg_match('/\<chart(.*?)"\>/', $html, $code)){
                $getTags = $code[1] . '"';
                preg_match('/type="(.*?)"/', $getTags, $setType);
                preg_match('/layout_width="(.*?)"/', $getTags, $setWidth);
                preg_match('/layout_height="(.*?)"/', $getTags, $setHeight);

                $chartCSS = "width:512px;";
                $chartTemplate = "";

                switch(strtolower($setType[1])){
                    case 'donut': $chartTemplate = $svg_pie_chart; break;
                    case 'line': $chartTemplate = $svg_line_chart; break;
                    case 'bar': $chartTemplate = $svg_bar_chart; break;
                }

                if($setWidth[1]){
                    if($setWidth[1] == "match_parent") {
                        $chartCSS = "width:100%;";
                    }else{
                        $chartCSS = "width:" . $setWidth[1] . "px;";
                    }
                    if($setHeight[1]){
                        $chartCSS .= "height:".$setHeight[1]."px;";
                    }
                }else{
                    if($setHeight[1]){
                        $chartCSS .= "height:".$setHeight[1]."px;";
                    }
                }

                $tabItem_Text = $code[1];
                $compiled_code .= "<?php echo '<div class=\"ChartLayout\" style=\"$chartCSS\">$chartTemplate</div>'; ?>";
            }

            elseif (preg_match('/\<function call="(\w*)(.*?)"\>/', $html, $code))
            {
                $tag = $code[0];
                $function = $code[1];
                $this->function_check($tag);
                if (empty($code[2])) $parsed_function = $function . "()";
                else $parsed_function = $function . $this->var_replace($code[2], $tag_left_delimiter = null, $tag_right_delimiter = null, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level);
                $compiled_code .= "<?php echo $parsed_function; ?>";
            }
            elseif (strpos($html, '<debug>') !== false)
            {
                $tag = '<debug>';
                $compiled_code .= '<?php echo "<pre>"; print_r( $this->var ); echo "</pre>"; ?>';
            }
            elseif (strpos($html, '</debug>') !== false)
            {
                $compiled_code .= "";
            }
            else
            {
                $html = $this->var_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true);
                $html = $this->const_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true);
                $compiled_code .= $this->func_replace($html, $left_delimiter = '\{', $right_delimiter = '\}', $php_left_delimiter = '<?php ', $php_right_delimiter = ';?>', $loop_level, $echo = true);
            }
        }
        if ($open_if > 0)
        {
            $e = new ITQuelleTPL_SyntaxException('Error! You need to close an {if} tag in ' . $this->tpl['tpl_filename'] . ' template');
            throw $e->setTemplateFile($this->tpl['tpl_filename']);
        }
        return $compiled_code;
    }

    // Path Compiler
    public function path_replace($html, $tpl_basedir){
        if ($this->path_relace) {

            $tpl_dir    = $this->base_url . $this->tpl_dir . $tpl_basedir;
            $this->path = $this->reduce_path($tpl_dir);
            $url        = '(?:(?:\\{.*?\\})?[^{}]*?)*?';
            $exp        = [];
            $tags       = array_intersect(["link", "a"], $this->path_replace_list);
            $exp[]      = '/<(' . join('|', $tags) . ')(.*?)(href)="(' . $url . ')"/i';
            $tags       = array_intersect(["img", "script", "input"], $this->path_replace_list);
            $exp[]      = '/<(' . join('|', $tags) . ')(.*?)(src)="(' . $url . ')"/i';
            $tags       = array_intersect(["form"], $this->path_replace_list);
            $exp[]      = '/<(' . join('|', $tags) . ')(.*?)(action)="(' . $url . ')"/i';

            return preg_replace_callback($exp, 'self::single_path_replace', $html);

        }else{
            return $html;
        }
    }
    public function single_path_replace($matches){
        $tag        = $matches[1];
        $_          = $matches[2];
        $attr       = $matches[3];
        $url        = $matches[4];
        $new_url    = $this->rewrite_url($url, $tag, $this->path);
        return "<$tag$_$attr=\"$new_url\"";
    }
    public function rewrite_url($url, $tag, $path){
        if (!in_array($tag, $this->path_replace_list)) { return $url; }
        $protocol = 'http|https|ftp|file|apt|magnet';
        if ($tag == 'a') { $protocol .= '|mailto|javascript'; }
        $no_change = "/(^($protocol)\:)|(#$)/i";
        if (preg_match($no_change, $url)) { return rtrim($url, '#'); }
        $base_only = '/^\//';
        if ($tag == 'a' or $tag == 'form') { $base_only = '//'; }
        if (preg_match($base_only, $url)) { return rtrim($this->base_url, '/') . '/' . ltrim($url, '/'); }
        return $path . $url;
    }
    public function reduce_path($path){
        $path = str_replace("://", "@not_replace@", $path);
        $path = preg_replace("#(/+)#", "/", $path);
        $path = preg_replace("#(/\./+)#", "/", $path);
        $path = str_replace("@not_replace@", "://", $path);
        while (preg_match('#\.\./#', $path)) { $path = preg_replace('#\w+/\.\./#', '', $path); }
        return $path;
    }

    //
    public function printDebug(ITQuelleTPL_Exception $e)
    {
        if (!$this->debug)
        {
            throw $e;
        }
        $output = sprintf('<h2>Exception: %s</h2><h3>%s</h3><p>template: %s</p>', get_class($e) , $e->getMessage() , $e->getTemplateFile());
        if ($e instanceof ITQuelleTPL_SyntaxException)
        {
            if (null != $e->getTemplateLine())
            {
                $output .= '<p>line: ' . $e->getTemplateLine() . '</p>';
            }
            if (null != $e->getTag())
            {
                $output .= '<p>in tag: ' . htmlspecialchars($e->getTag()) . '</p>';
            }
            if (null != $e->getTemplateLine() && null != $e->getTag())
            {
                $rows = explode("\n", htmlspecialchars($this->tpl['source']));
                $rows[$e->getTemplateLine() ] = '<font color=red>' . $rows[$e->getTemplateLine() ] . '</font>';
                $output .= '<h3>template code</h3>' . implode('<br />', $rows) . '</pre>';
            }
        }
        $output .= sprintf('<h3>trace</h3><p>In %s on line %d</p><pre>%s</pre>', $e->getFile() , $e->getLine() , nl2br(htmlspecialchars($e->getTraceAsString())));
        return $output;
    }
    public function xml_reSubstitution($capture){
        return "<?php echo '<?xml " . stripslashes($capture[1]) . " ?>'; ?>";
    }

    // Replace
    public function const_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null){
        return preg_replace('/\{\#(\w+)\#{0,1}\}/', $php_left_delimiter . ($echo ? " echo " : null) . '\\1' . $php_right_delimiter, $html);
    }
    public function func_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null)
    {
        preg_match_all('/' . '\{\#{0,1}(\"{0,1}.*?\"{0,1})(\|\w.*?)\#{0,1}\}' . '/', $html, $matches);
        for ($i = 0, $n = count($matches[0]);$i < $n;$i++)
        {
            $tag = $matches[0][$i];
            $var = $matches[1][$i];
            $extra_var = $matches[2][$i];
            $this->function_check($tag);
            $extra_var = $this->var_replace($extra_var, null, null, null, null, $loop_level);
            $is_init_variable = preg_match("/^(\s*?)\=[^=](.*?)$/", $extra_var);
            $function_var = ($extra_var and $extra_var[0] == '|') ? substr($extra_var, 1) : null;
            $temp = preg_split("/\.|\[|\-\>/", $var);
            $var_name = $temp[0];
            $variable_path = substr($var, strlen($var_name));
            $variable_path = str_replace('[', '["', $variable_path);
            $variable_path = str_replace(']', '"]', $variable_path);
            $variable_path = preg_replace('/\.\$(\w+)/', '["$\\1"]', $variable_path);
            $variable_path = preg_replace('/\.(\w+)/', '["\\1"]', $variable_path);
            if ($function_var)
            {
                $function_var = str_replace("::", "@double_dot@", $function_var);
                if ($dot_position = strpos($function_var, ":"))
                {
                    $function = substr($function_var, 0, $dot_position);
                    $params = substr($function_var, $dot_position + 1);
                }
                else
                {
                    $function = str_replace("@double_dot@", "::", $function_var);
                    $params = null;
                }
                $function = str_replace("@double_dot@", "::", $function);
                $params = str_replace("@double_dot@", "::", $params);
            }
            else $function = $params = null;
            $php_var = $var_name . $variable_path;
            if (isset($function))
            {
                if ($php_var) $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? 'echo ' : null) . ($params ? "( $function( $php_var, $params ) )" : "$function( $php_var )") . $php_right_delimiter;
                else $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? 'echo ' : null) . ($params ? "( $function( $params ) )" : "$function()") . $php_right_delimiter;
            }
            else $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? 'echo ' : null) . $php_var . $extra_var . $php_right_delimiter;
            $html = str_replace($tag, $php_var, $html);
        }
        return $html;
    }
    public function var_replace($html, $tag_left_delimiter, $tag_right_delimiter, $php_left_delimiter = null, $php_right_delimiter = null, $loop_level = null, $echo = null)
    {
        if (preg_match_all('/' . $tag_left_delimiter . '\$(\w+(?:\.\${0,1}[A-Za-z0-9_]+)*(?:(?:\[\${0,1}[A-Za-z0-9_]+\])|(?:\-\>\${0,1}[A-Za-z0-9_]+))*)(.*?)' . $tag_right_delimiter . '/', $html, $matches))
        {
            for ($parsed = array() , $i = 0, $n = count($matches[0]);$i < $n;$i++) $parsed[$matches[0][$i]] = array(
                'var' => $matches[1][$i],
                'extra_var' => $matches[2][$i]
            );
            foreach ($parsed as $tag => $array)
            {
                $var = $array['var'];
                $extra_var = $array['extra_var'];
                $this->function_check($tag);
                $extra_var = $this->var_replace($extra_var, null, null, null, null, $loop_level);
                $is_init_variable = preg_match("/^[a-z_A-Z\.\[\](\-\>)]*=[^=]*$/", $extra_var);
                $function_var = ($extra_var and $extra_var[0] == '|') ? substr($extra_var, 1) : null;
                $temp = preg_split("/\.|\[|\-\>/", $var);
                $var_name = $temp[0];
                $variable_path = substr($var, strlen($var_name));
                $variable_path = str_replace('[', '["', $variable_path);
                $variable_path = str_replace(']', '"]', $variable_path);
                $variable_path = preg_replace('/\.(\${0,1}\w+)/', '["\\1"]', $variable_path);
                if ($is_init_variable) $extra_var = "=\$this->var['{$var_name}']{$variable_path}" . $extra_var;
                if ($function_var)
                {
                    $function_var = str_replace("::", "@double_dot@", $function_var);
                    if ($dot_position = strpos($function_var, ":"))
                    {
                        $function = substr($function_var, 0, $dot_position);
                        $params = substr($function_var, $dot_position + 1);
                    }
                    else
                    {
                        $function = str_replace("@double_dot@", "::", $function_var);
                        $params = null;
                    }
                    $function = str_replace("@double_dot@", "::", $function);
                    $params = str_replace("@double_dot@", "::", $params);
                }
                else $function = $params = null;
                if ($loop_level)
                {
                    if ($var_name == 'key') $php_var = '$key' . $loop_level;
                    elseif ($var_name == 'value') $php_var = '$value' . $loop_level . $variable_path;
                    elseif ($var_name == 'counter') $php_var = '$counter' . $loop_level;
                    else $php_var = '$' . $var_name . $variable_path;
                }
                else $php_var = '$' . $var_name . $variable_path;
                if (isset($function)) $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? 'echo ' : null) . ($params ? "( $function( $php_var, $params ) )" : "$function( $php_var )") . $php_right_delimiter;
                else $php_var = $php_left_delimiter . (!$is_init_variable && $echo ? 'echo ' : null) . $php_var . $extra_var . $php_right_delimiter;
                $html = str_replace($tag, $php_var, $html);
            }
        }
        return $html;
    }
    public function function_check($code)
    {
        $preg = '#(\W|\s)' . implode('(\W|\s)|(\W|\s)', $this->black_list) . '(\W|\s)#';
        if (count($this->black_list) && preg_match($preg, $code, $match))
        {
            $line   = 0;
            $rows   = explode("\n", $this->tpl['source']);

            while (!strpos($rows[$line], $code)) $line++;
            $e = new ITQuelleTPL_SyntaxException('Unallowed syntax in ' . $this->tpl['tpl_filename'] . ' template');
            throw $e->setTemplateFile($this->tpl['tpl_filename'])->setTag($code)->setTemplateLine($line);
        }
    }

    /**
     * @info Setter
     */

    public function setTplDir($tpl_dir)
    {
        $this->tpl_dir = $tpl_dir;
    }
    public function setCacheDir($cache_dir)
    {
        $this->cache_dir = $cache_dir;
    }
    public function setBaseUrl($base_url)
    {
        $this->base_url = $base_url;
    }
    public function setTplExt($tpl_ext)
    {
        $this->tpl_ext = $tpl_ext;
    }
    public function setPathRelace($path_relace)
    {
        $this->path_relace = $path_relace;
    }
    public function setCheckTemplateUpdate($check_template_update)
    {
        $this->check_template_update = $check_template_update;
    }
    public function setPhpEnabled($php_enabled)
    {
        $this->php_enabled = $php_enabled;
    }
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }
    public function setCache($cache)
    {
        $this->cache = $cache;
    }
    public function setRootDir($root_dir)
    {
        $this->root_dir = $root_dir;
    }
    public function setCacheId($cache_id)
    {
        $this->cache_id = $cache_id;
    }
    public function setCacheExpireTime($cache_expire_time)
    {
        $this->cache_expire_time = $cache_expire_time;
    }
    public function setBlackList($black_list){
        $this->black_list = $black_list;
    }


}

class ITQuelleTPL_Exception extends Exception
{
    protected $templateFile = '';
    public function getTemplateFile()
    {
        return $this->templateFile;
    }
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = (string)$templateFile;
        return $this;
    }
}
class ITQuelleTPL_NotFoundException extends ITQuelleTPL_Exception{ /** return */ }
class ITQuelleTPL_SyntaxException extends ITQuelleTPL_Exception
{
    protected $templateLine = null;
    protected $tag = null;
    public function getTemplateLine()
    {
        return $this->templateLine;
    }
    public function setTemplateLine($templateLine)
    {
        $this->templateLine = (int)$templateLine;
        return $this;
    }
    public function getTag()
    {
        return $this->tag;
    }
    public function setTag($tag)
    {
        $this->tag = (string)$tag;
        return $this;
    }
}
