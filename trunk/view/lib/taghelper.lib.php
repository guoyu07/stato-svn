<?php

function content_tag($name, $content, $options = array())
{
    return "<$name".tag_options($options).">$content</$name>";
}

function tag($name, $options = array(), $open = False)
{
    return "<$name".tag_options($options).($open ? ">" : "/>");
}

function img_tag($filename, $options = array())
{
    return '<img src="'.BASE_DIR.'/public/images/'.$filename.'"'.tag_options($options).' />';
}

function tag_options($options = array())
{
    if (count($options) == 0) return;
    $set = array();
    foreach($options as $key => $value) $set[] = $key.'="'.$value.'"';
    return ' '.implode(" ", $set);
}

function js_tag($code)
{
    return '<script type="text/javascript">'.$code.'</script>';
}

function style_tag($code)
{
    return '<style type="text/css">'.$code.'</style>';
}

function js_include_tag($sources)
{
    if (!is_array($sources)) $sources = array($sources);
    $html = '';
    foreach($sources as $source)
        $html.= '<script src="'.BASE_DIR.'/public/js/'.$source.'" type="text/javascript"></script>';
    
    return $html;
}

function css_include_tag($source)
{
    if (!file_exists($source))
        $source = ROOT_DIR.'/public/styles/'.$source;
        
    return style_tag(file_get_contents($source));
}

function css_link_tag($source)
{
    return '<link href="'.BASE_DIR.'/public/styles/'.$source.'" rel="stylesheet" type="text/css" media="screen" />';
}

?>