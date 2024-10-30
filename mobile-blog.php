<?php

/*
  Plugin Name: Mobile Blog
  Plugin URI: http://techlive.org/wordpress/plugins-wordpress/mobile-blog-agora-seu-blog-pode-ser-acessado-de-qualquer-lugar
  Description: Permite que seu blog seja acessado através de dispositivos móveis.
  Author: Lenon Marcel
  Version: 0.1
  Author URI: http://techlive.org/
  
  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.
  
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
*/

//Título do blog
define("MBLOG_TITULO", get_option('mblog_titulo'));

//Descrição do blog (é exibida na página principal)
define("MBLOG_INTRO", get_option('mblog_descricao'));

add_action('template_redirect', 'mblog_detecta','1');

// Detecta o dispositivo
function mblog_detecta(){
	if(mblog_detect_mobile_device()):
		mblog_show();
	else:
		header('Pragma: Public');
		header('Cache-Control: no-cache, must-revalidate, no-transform');
		header('Vary: User-Agent, Accept');
	endif;
}

// Pega os valores da requisição atual
foreach($_GET as $valor => $get){
	$_GET[$valor] = htmlentities(htmlspecialchars(strip_tags($get)));
	$req .= '&'.$valor.'='.urlencode(stripslashes($get));
}

// Lista os links de navegação
function mblog_links_nav(){
	
	$links = array();
	$links[] = '<a href="'.get_option('home').'">Home</a>';
	
	if (get_option('mblog_show_categorias') == 'yes'){$links[] = '<a href="'.get_option('home').'?ver=cat">Categorias</a>';}
	if (get_option('mblog_show_pages') == 'yes'){$links[] = '<a href="'.get_option('home').'?ver=pags">Páginas</a>';}
	if (get_option('mblog_show_arquivo') == 'yes'){$links[] = '<a href="'.get_option('home').'?ver=arq">Arquivo</a>';}
	if (get_option('mblog_show_tags') == 'yes'){$links[] = '<a href="'.get_option('home').'?ver=tags">Tags</a>';}

	$br = '<br/>';
	$lista_links = '<p class="ftlinks">';
	foreach ($links as $lk){
		$lista_links .= $lk . $br;
	}
	$lista_links .= '</p>';

  return $lista_links;
}

// Lista as categorias
function mblog_list_cats(){
	global $wpdb;
	$titulo = MBLOG_TITULO . ' | ' . 'Categorias';
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>Categorias</h1>';
	$return .= '<ul>'.mblog_get_categorias('title_li=').'</ul>';

  return array($titulo, $intro, $return);
}

// Lista as tags
function mblog_list_tags(){
	global $wpdb;
	$titulo = MBLOG_TITULO . ' | ' . 'Tags';
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>Tags</h1>';
	$return .= mblog_get_tags('smallest=8&largest=16&number=30&order=RAND');

  return array($titulo, $intro, $return);
}

// Lista as páginas
function mblog_list_pags(){
	global $wpdb;
	$titulo = MBLOG_TITULO . ' | ' . 'Páginas';
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>Páginas</h1>';
	$return .= '<ul>';

	$pags = get_pages();
	foreach($pags as $pag) {
		$pg_titulo = $pag->post_title;
		$pg_link =  get_page_link($pag->ID);
		$return .= '<li><a href="' . $pg_link . '">' . $pg_titulo . '</a></li>';
	}

	$return .= '</ul>';

  return array($titulo, $intro, $return);
}

// Lista os arquivos
function mblog_list_arquivo(){
	global $wpdb;
	$titulo = MBLOG_TITULO . ' | ' . 'Arquivo';
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>Arquivo</h1>';
	$return .= '<ul>';

	$return .= str_replace('<li>','',str_replace('</li>','<br />',mblog_get_arquivo('type=monthly')));

	$return .= '</ul>';

  return array($titulo, $intro, $return);
}

// Exibe o erro 404
function mblog_show_404(){
	global $wpdb;
	$titulo = MBLOG_TITULO . ' | ' . '404';
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>Erro 404</h1>';
	$return .= 'Erro 404 - Não encontrado =(';

  return array($titulo, $intro, $return);
}

// Exibe a página principal
function mblog_show_home(){
	global $wpdb, $req, $post;
	$titulo = MBLOG_TITULO;
	$intro = '<h1>' . MBLOG_TITULO . '</h1><span class="sm">' . MBLOG_INTRO . '</span>';
	$br = '<br/>';
	$pgd = (get_query_var('paged')) ? get_query_var('paged') : 1;
	
	query_posts("$req&paged=$pgd&offset=0");

	if (have_posts()&&$proc==false){
		while (have_posts()){
			the_post();
			$info_p = '<span class="bl"><a href="'.get_permalink().'">'.get_the_title().'</a></span>'.$br;
			$info_p .= '<span class="sm">Em: '.mblog_time('d/m/Y').'</span>'.$br;
			foreach((get_the_category()) as $cats) {
				$info_p .= '<span class="sm"><a href="'.get_category_link($cats->cat_ID).'">'.$cats->cat_name.'</a></span> ';
			}
			$return .= '<p class="conteudo">'.$info_p.'</p><hr></hr>';
		}
	}
	
	$nav_next = mblog_next('Próx&raquo;', '', '');
	$nav_prev = mblog_previous('&laquo;Ant', '', '');
	
	if($nav_prev!=''&&$nav_next!=''){
		$sep = ' | ';
	} else {
		$sep = '';
	}
	
	$nav_in = '<p class="naveg">';
	$nav_fn = '</p>';
	
	$ret_in = '<div class="page">';
	$ret_fn = '</div>';
	
	$return = $nav_in . $nav_prev . $sep . $nav_next . $nav_fn . $ret_in . $return . $ret_fn . $nav_in . $nav_prev . $sep . $nav_next . $nav_fn;

  return array($titulo, $intro, $return);
}

// Exibe um post
function mblog_show_post(){
	global $wpdb, $post;
	$titulo = MBLOG_TITULO . ' | ' . get_the_title();
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>' . get_the_title() . '</h1>';

	$return = '<span class="sm">Por: ' . mblog_get_autor() . ' em ' . mblog_the_time('d/m/Y') . '</span>';
	$return .= get_the_content();
	$return .= '<hr></hr>';
	$return .= '<span class="sm">Cat.:' . mblog_the_category() . '</span>';
	$return = mblog_external($return);

	if (mblog_previous_post()!=''):
		$nav_prev = '<span class="sm">' . mblog_previous_post() . '</span>';
	else:
		$nav_prev = '';
	endif;
	
	if (mblog_next_post()!=''):
		$nav_next = '<span class="sm">' . mblog_next_post() . '</span>';
	else:
		$nav_next = '';
	endif;
	
	if($nav_prev!=''&&$nav_next!=''){
		$sep = ' | ';
	} else {
		$sep = '';
	}

	$nav_in = '<p class="naveg">';
	$nav_fn = '</p>';

	$ret_in = '<div class="page">';
	$ret_fn = '</div>';

	$return = $ret_in . $return . $ret_fn . $nav_in . $nav_prev . $sep . $nav_next . $nav_fn;

  return array($titulo, $intro, $return);
}

// Exibe uma página
function mblog_show_page(){
	global $wpdb, $post;
	$titulo = MBLOG_TITULO . ' | ' . get_the_title();
	$intro = '<span class="sm">' . MBLOG_TITULO . '</span><h1>' . get_the_title() . '</h1>';
	$return .= get_the_content();
	$return = mblog_external($return);

	$ret_in = '<div class="page">';
	$ret_fn = '</div>';

	$return = $ret_in . $return . $ret_fn;

  return array($titulo, $intro, $return);
}

// Exibe uma categoria
function mblog_show_cat($cat){
	global $wpdb, $req, $post;
	$pgd = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$titulo = MBLOG_TITULO . ' | ' . 'Categorias';
	$intro = '<span class="sm">' . MBLOG_TITULO . ' | Pg ' . $pgd . '</span><h1>' . $cat . '</h1>';
	$br = '<br/>';
	$req .= 'category_name='.$cat;

	query_posts("$req&paged=$pgd&offset=0");

	if (have_posts()&&$proc==false){
		while (have_posts()){
			the_post();
			$info_p = '<span class="bl"><a href="'.get_permalink().'">'.get_the_title().'</a></span>'.$br;
			$info_p .= '<span class="sm">Em: '.mblog_time('d/m/Y').'</span>'.$br;
			foreach((get_the_category()) as $cats) {
				$info_p .= '<span class="sm"><a href="'.get_category_link($cats->cat_ID).'">'.$cats->cat_name.'</a></span> ';
			}
			$return .= '<p class="conteudo">'.$info_p.'</p><hr></hr>';
		}
	}
	
	$nav_next = mblog_next('Próx&raquo;', '', '');
	$nav_prev = mblog_previous('&laquo;Ant', '', '');
	
	if($nav_prev!=''&&$nav_next!=''){
		$sep = ' | ';
	} else {
		$sep = '';
	}
	
	$nav_in = '<p class="naveg">';
	$nav_fn = '</p>';
	
	$ret_in = '<div class="page">';
	$ret_fn = '</div>';
	
	$return = $nav_in . $nav_prev . $sep . $nav_next . $nav_fn . $ret_in . $return . $ret_fn . $nav_in . $nav_prev . $sep . $nav_next . $nav_fn;

  return array($titulo, $intro, $return);
}

// Exibe um arquivo
function mblog_show_arquivo(){
	global $wpdb, $req, $post;
	$pgd = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$titulo = MBLOG_TITULO . ' | ' . 'Arquivo';
	$intro = '<span class="sm">' . MBLOG_TITULO . ' | Pg ' . $pgd . '</span><h1>' . get_the_time('F Y') . '</h1>';
	$br = '<br/>';
	$req .='&year='.get_the_time('Y').'&monthnum='.get_the_time('m');

	query_posts("$req&paged=$pgd&offset=0");

	if (have_posts()&&$proc==false){
		while (have_posts()){
			the_post();
			$info_p = '<span class="bl"><a href="'.get_permalink().'">'.get_the_title().'</a></span>'.$br;
			$info_p .= '<span class="sm">Em: '.mblog_time('d/m/Y').'</span>'.$br;
			foreach((get_the_category()) as $cats) {
				$info_p .= '<span class="sm"><a href="'.get_category_link($cats->cat_ID).'">'.$cats->cat_name.'</a></span> ';
			}
			$return .= '<p class="conteudo">'.$info_p.'</p><hr></hr>';
		}
	}
	
	$nav_next = mblog_next('Próx&raquo;', '', '');
	$nav_prev = mblog_previous('&laquo;Ant', '', '');
	
	if($nav_prev!=''&&$nav_next!=''){
		$sep = ' | ';
	} else {
		$sep = '';
	}
	
	$nav_in = '<p class="naveg">';
	$nav_fn = '</p>';
	
	$ret_in = '<div class="page">';
	$ret_fn = '</div>';
	
	$return = $nav_in . $nav_prev . $sep . $nav_next . $nav_fn . $ret_in . $return . $ret_fn . $nav_in . $nav_prev . $sep . $nav_next . $nav_fn;

  return array($titulo, $intro, $return);
}

// Exibe uma tag
function mblog_show_tag(){
	global $wpdb, $req, $post;
	$pgd = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$titulo = MBLOG_TITULO . ' | ' . 'Tags';
	$intro = '<span class="sm">' . MBLOG_TITULO . ' | Pg ' . $pgd . '</span><h1>' . single_tag_title('',false) . '</h1>';
	$br = '<br/>';
	$req .='&tag='.ucwords(single_tag_title('',false));

	query_posts("$req&paged=$pgd");

	if (have_posts()&&$proc==false){
		while (have_posts()){
			the_post();
			$info_p = '<span class="bl"><a href="'.get_permalink().'">'.get_the_title().'</a></span>'.$br;
			$info_p .= '<span class="sm">Em: '.mblog_time('d/m/Y').'</span>'.$br;
			foreach((get_the_category()) as $cats) {
				$info_p .= '<span class="sm"><a href="'.get_category_link($cats->cat_ID).'">'.$cats->cat_name.'</a></span> ';
			}
			$return .= '<p class="conteudo">'.$info_p.'</p><hr></hr>';
		}
	}
	
	$nav_next = mblog_next('Próx&raquo;', '', '');
	$nav_prev = mblog_previous('&laquo;Ant', '', '');
	
	if($nav_prev!=''&&$nav_next!=''){
		$sep = ' | ';
	} else {
		$sep = '';
	}
	
	$nav_in = '<p class="naveg">';
	$nav_fn = '</p>';
	
	$ret_in = '<div class="page">';
	$ret_fn = '</div>';
	
	$return = $nav_in . $nav_prev . $sep . $nav_next . $nav_fn . $ret_in . $return . $ret_fn . $nav_in . $nav_prev . $sep . $nav_next . $nav_fn;

  return array($titulo, $intro, $return);
}

// Exibe a versão móvel de acordo com o tipo de página
function mblog_show(){
	start_wp();
	global $wpdb, $post, $req;

	$links_nav = mblog_links_nav();

	switch(true){
		case ($_GET['ver'] == 'tags');
			$return = mblog_list_tags();
		break;
		case ($_GET['ver'] == 'arq');
			$return = mblog_list_arquivo();
		break;
		case ($_GET['ver'] == 'cat');
			$return = mblog_list_cats();
		break;
		case ($_GET['ver'] == 'pags');
			$return = mblog_list_pags();
		break;
		case is_home();
			$return = mblog_show_home();
		break;
		case is_paged();
			if (is_home()):
				$return = mblog_show_home();
			elseif (is_category()):
				$return = mblog_show_cat(single_cat_title('',false));
			elseif (is_month()):
				$return = mblog_show_arquivo();
			elseif (is_tag()):
				$return = mblog_show_tag();
			endif;
		break;
		case is_category();
			$return = mblog_show_cat(single_cat_title('',false));
		break;
		case is_single();
			$return = mblog_show_post();
		break;
		case is_404();
			$return = mblog_show_404();
		break;
		case is_tag();
			$return = mblog_show_tag();
		break;
		case is_page();
			$return = mblog_show_page();
		break;
		case is_month();
			$return = mblog_show_arquivo();
		break;
	}
	
	if (get_option('mblog_show_original') == 'yes'):
		$link_orig = '<span class="sm"><a rel="nofollow" href="?nmb">Versão original</a></span>';
	else:
		$link_orig = '';
	endif;

	header ("Cache-Control: max-age=200 ");

	$return = <<<ENDOFDOC
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="generator" content="Mobile Blog - versão 0.1" />
		<title>{$return[0]}</title>
		<style type="text/css">
		body{
			background:#fff;
		}
		.intro{
			background:#002b79;
			padding:0.3em;
			color:#fff;
			font-family:Verdana, Arial, Helvetica, sans-serif;
		}
		.naveg {
			background:#b3ceff;
			border-bottom:1px solid #4184ff;
			border-top:1px solid #4184ff;
			padding:0.1em;
			color:#000;
			text-align:center;
		}
		a{
			text-decoration: none;
			color:#051481
		}
		a:hover{
			text-decoration:underline;
			color:#fff;
		}
		.page{
			padding:0.3em;
		}
		.ftlinks{
			background:#d9dadb;
			padding:0.3em;
		}
		ul{
			list-style-image:none;
			list-style-position:outside;
			list-style-type:circle;
		}
		.bl{
			font-weight:bold;
		}
		.sm{
			font-size:smaller;
		}
		</style>
	</head>
	<body>
		<div class="intro">{$return[1]}</div>
		{$return[2]}
		{$links_nav}
		{$link_orig}
	</body>
</html>
ENDOFDOC;

	
	echo mblog_clean($return);
	exit;
};

function mblog_clean($return){
	$return = str_replace(array("\n", "\r", "\t", '<br /><br />', ' align="center"'), '', $return);
	$return = ereg_replace (' +', ' ', trim($return));
	$return = ereg_replace('<atitle>','<title>',$return);
	$return = ereg_replace('<br /><br /><br /><br /></div>','</div>',$return);
	$return = ereg_replace('& ','&amp; ',$return);
	$return = ereg_replace('&amp;amp; ','&amp; ',$return);
	$return = ereg_replace('<br /><br /></p>','</p>',$return);
	$return = ereg_replace('<br /></p>','</p>',$return);
	$return = ereg_replace('</li><br />','</li>',$return);
	$return = ereg_replace('<ul><br />','<ul>',$return);
	$return = ereg_replace('</ul><br />','</ul>',$return);
	$return = ereg_replace('<ol><br />','<ol>',$return);
	$return = ereg_replace('</ol><br />','</ol>',$return);
	$return = ereg_replace('</p><br />','</p>',$return);
	$return = ereg_replace('</blockquote><br />','</blockquote>',$return);
	$return = ereg_replace('<blockquote><br />','<blockquote>',$return);
	$return = ereg_replace('<br /><br /><blockquote>','<blockquote>',$return);
	$return = ereg_replace('<br /></strong><br /><br />','</strong><br /><br />',$return);
	$return = ereg_replace('<br /></a></strong><br /><br />','</a></strong><br /><br />',$return);
	$return = ereg_replace('</blockquote><br />','</blockquote>',$return);
  return $return;
}

function mblog_time($command) { // http://wordpress.org/support/topic/126522
	ob_start();
	the_time($command);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_previous($label = '', $pre = '', $post = '') { // http://wordpress.org/support/topic/126522
	ob_start();
	previous_posts_link($label);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$pre$buffer$post";
}

function mblog_next($label = '', $pre = '', $post = '') { // http://wordpress.org/support/topic/126522
	ob_start();
	next_posts_link($label);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$pre$buffer$post";
}

function mblog_external($text) { // http://txfx.net/files/wordpress/identify-external-links.phps
	$text = preg_replace_callback("/<img([^>]*)>/", "mblog_img_replace", $text);
	$pattern = '/<a (.*?)href="(.*?)\/\/(.*?)"(.*?)>(.*?)<\/a>/i';
	$text = preg_replace_callback($pattern,'mblog_parse_external',$text);
	$pattern2 = '/<a (.*?) class="extlink"(.*?)>(.*?)<img (.*?)<\/a>/i';
	$text = preg_replace($pattern2, '<a $1 $2>$3<img $4</a>', $text);
	$text = str_replace('target="_blank"','',nl2br($text));
  return $text;
}

function mblog_get_categorias($command) {
	ob_start();
	wp_list_categories($command);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_get_tags($command) {
	ob_start();
	wp_tag_cloud($command);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_get_arquivo($command) {
	ob_start();
	wp_get_archives($command);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_get_autor() {
	ob_start();
	the_author();
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_the_category() {
	ob_start();
	the_category(' ');
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_the_tags() {
	ob_start();
	the_tags(' ');
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_the_time($command) {
	ob_start();
	the_time($command);
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer";
}

function mblog_parse_external($matches){ // http://txfx.net/files/wordpress/identify-external-links.phps
	if ( mblog_get_domain($matches[3]) != mblog_get_domain($_SERVER["HTTP_HOST"]) ){
		return '<a href="http://wordpressmobile.mobi/transcoder-redirect.php?u=' . $matches[2] . '//' . $matches[3] . '" ' . $matches[1] . $matches[4] . ' class="extlink">' . $matches[5] . '</a>';
	} else {
		return '<a href="' . $matches[2] . '//' . $matches[3] . '" ' . $matches[1] . $matches[4] . '>' . $matches[5] . '</a>';
	}
}

function mblog_get_domain($uri){ // http://txfx.net/files/wordpress/identify-external-links.phps
  preg_match("/^(http:\/\/)?([^\/]+)/i", $uri, $matches);
  $host = $matches[2];
  preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
  return $matches[0];    
}

function mblog_previous_post() { // http://wordpress.org/support/topic/126522
	ob_start();
	previous_post('%','');
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "&laquo;" . "$buffer";
}

function mblog_next_post() { // http://wordpress.org/support/topic/126522
	ob_start();
	next_post('%','');
	$buffer = ob_get_contents();
	ob_end_clean();
	if(!empty($buffer)) return "$buffer" . "&raquo;";
}

function mblog_img_replace($treffer){
	$document_root=$_SERVER['DOCUMENT_ROOT'] . '/wp';
	$phpthumbspath = get_option('upload_path');
	$max_size=100;
	if(eregi ( 'alt="([^\"]+)"' , $treffer[1] , $regs )){
		$alt=' alt="'.$regs[1].'"';
	}
	if(eregi ( 'title="([^\"]+)"' , $treffer[1] , $regs )){
		$title=' title="'.$regs[1].'"';
	}
	if(eregi ( 'border="([1-9]+)"' , $treffer[1] , $regs )){
		$border=' border="'.$regs[1].'"';
	}
	if(eregi ( 'src="([^\"]+)"' , $treffer[1] , $regs )){
		$src=$regs[1];
	}
	if(eregi ( 'style="([^\"]+)"' , $treffer[1] , $regs )){
		$style=$regs[1];
	}
	if(eregi ( 'width="([^\"]+)"' , $treffer[1] , $regs )){
		$html_width=$regs[1];
	}
	$src = ' src="' . get_option('home') . '/wp-content/plugins/mobile-blog/thumbs/timthumb.php?w=' . $max_size . '&h=150&q=150&url=' . $src .'"';
	$ret='<img '.$src.' width="'.$max_size.'" />';
	return $ret;
}

// Detecta se o dispositivo é móvel
function mblog_detect_mobile_device(){ // Função adaptada de script do Andy Moore (http://www.andymoore.info/php-to-detect-mobile-phones/)
  if (isset($_GET['mb'])){
  	return true;
  }
  if (isset($_GET['nmb'])){
  	return false;
  }
  if(stristr($_SERVER['HTTP_USER_AGENT'],'windows')&&!stristr($_SERVER['HTTP_USER_AGENT'],'windows ce')){
    return false;
  }
  if(eregi('iphone',$_SERVER['HTTP_USER_AGENT'])){
    return true;
  }
  if(eregi('up.browser|up.link|windows ce|iemobile|mini|mmp|symbian|midp|wap|phone|pocket|mobile|pda|psp',$_SERVER['HTTP_USER_AGENT'])){
    return true;
  }
  if(stristr($_SERVER['HTTP_ACCEPT'],'text/vnd.wap.wml')||stristr($_SERVER['HTTP_ACCEPT'],'application/vnd.wap.xhtml+xml')){
    return true;
  }
  if(isset($_SERVER['HTTP_X_WAP_PROFILE'])||isset($_SERVER['HTTP_PROFILE'])||isset($_SERVER['X-OperaMini-Features'])||isset($_SERVER['UA-pixels'])){
    return true;
  }
  $a = array(
                    'acs-'=>'acs-',
                    'alav'=>'alav',
                    'alca'=>'alca',
                    'amoi'=>'amoi',
                    'audi'=>'audi',
                    'aste'=>'aste',
                    'avan'=>'avan',
                    'benq'=>'benq',
                    'bird'=>'bird',
                    'blac'=>'blac',
                    'blaz'=>'blaz',
                    'brew'=>'brew',
                    'cell'=>'cell',
                    'cldc'=>'cldc',
                    'cmd-'=>'cmd-',
                    'dang'=>'dang',
                    'doco'=>'doco',
                    'eric'=>'eric',
                    'hipt'=>'hipt',
                    'inno'=>'inno',
                    'ipaq'=>'ipaq',
                    'java'=>'java',
                    'jigs'=>'jigs',
                    'kddi'=>'kddi',
                    'keji'=>'keji',
                    'leno'=>'leno',
                    'lg-c'=>'lg-c',
                    'lg-d'=>'lg-d',
                    'lg-g'=>'lg-g',
                    'lge-'=>'lge-',
                    'maui'=>'maui',
                    'maxo'=>'maxo',
                    'midp'=>'midp',
                    'mits'=>'mits',
                    'mmef'=>'mmef',
                    'mobi'=>'mobi',
                    'mot-'=>'mot-',
                    'moto'=>'moto',
                    'mwbp'=>'mwbp',
                    'nec-'=>'nec-',
                    'newt'=>'newt',
                    'noki'=>'noki',
                    'opwv'=>'opwv',
                    'palm'=>'palm',
                    'pana'=>'pana',
                    'pant'=>'pant',
                    'pdxg'=>'pdxg',
                    'phil'=>'phil',
                    'play'=>'play',
                    'pluc'=>'pluc',
                    'port'=>'port',
                    'prox'=>'prox',
                    'qtek'=>'qtek',
                    'qwap'=>'qwap',
                    'sage'=>'sage',
                    'sams'=>'sams',
                    'sany'=>'sany',
                    'sch-'=>'sch-',
                    'sec-'=>'sec-',
                    'send'=>'send',
                    'seri'=>'seri',
                    'sgh-'=>'sgh-',
                    'shar'=>'shar',
                    'sie-'=>'sie-',
                    'siem'=>'siem',
                    'smal'=>'smal',
                    'smar'=>'smar',
                    'sony'=>'sony',
                    'sph-'=>'sph-',
                    'symb'=>'symb',
                    't-mo'=>'t-mo',
                    'teli'=>'teli',
                    'tim-'=>'tim-',
                    'tosh'=>'tosh',
                    'treo'=>'treo',
                    'tsm-'=>'tsm-',
                    'upg1'=>'upg1',
                    'upsi'=>'upsi',
                    'vk-v'=>'vk-v',
                    'voda'=>'voda',
                    'wap-'=>'wap-',
                    'wapa'=>'wapa',
                    'wapi'=>'wapi',
                    'wapp'=>'wapp',
                    'wapr'=>'wapr',
                    'webc'=>'webc',
                    'winw'=>'winw',
                    'winw'=>'winw',
                    'xda-'=>'xda-'
                  );
  if(isset($a[substr($_SERVER['HTTP_USER_AGENT'],0,4)])){
    return true;
  }
}

// Exibe o painel de configurações
function mblog_painel(){
	$checked = array();
	
	if (isset($_POST['mblog_opts_salvar']) ) {
		if (isset($_POST['box_show_tags'])) :update_option('mblog_show_tags', 'yes');else:update_option('mblog_show_tags', 'no');endif;
		if (isset($_POST['box_show_original'])) :update_option('mblog_show_original', 'yes');else:update_option('mblog_show_original', 'no');endif;
		if (isset($_POST['box_show_categorias'])) :update_option('mblog_show_categorias', 'yes');else:update_option('mblog_show_categorias', 'no');endif;
		if (isset($_POST['box_show_pages'])) :update_option('mblog_show_pages', 'yes');else:update_option('mblog_show_pages', 'no');endif;
		if (isset($_POST['box_show_arquivo'])) :update_option('mblog_show_arquivo', 'yes');else:update_option('mblog_show_arquivo', 'no');endif;
		
		update_option('mblog_titulo', $_POST['txt_titulo']);
		update_option('mblog_descricao', $_POST['txt_desc']);
		
		echo "<div class='updated'><p><b>Salvo</b></p></div>";
	}

	if(get_option('mblog_show_tags') == 'yes'): $checked[0] = 'checked'; else: $checked[0] = ''; endif;
	if(get_option('mblog_show_original') == 'yes'): $checked[1] = 'checked'; else: $checked[1] = ''; endif;
	if(get_option('mblog_show_categorias') == 'yes'): $checked[2] = 'checked'; else: $checked[2] = ''; endif;
	if(get_option('mblog_show_pages') == 'yes'): $checked[3] = 'checked'; else: $checked[3] = ''; endif;
	if(get_option('mblog_show_arquivo') == 'yes'): $checked[4] = 'checked'; else: $checked[4] = ''; endif;

?>
<div style="padding:0 15px 15px 15px;">
	<h2>Mobile Blog</h2>
	<p>Nesta página você poderá editar as configurações de exibição do seu blog móvel</p>
	<p>
	<form method="post">
		<h4>Links de navegação</h4>
		<input type="checkbox" name="box_show_tags" id="box_show_tags" <?php echo $checked[0];?>/>Chuva de tags <span style="color:red;">(Beta)</span><br/>
		<small>Exibe um link para a chuva de tags (ainda em testes)</small><br/><br/>

		<input type="checkbox" name="box_show_original" id="box_show_original" <?php echo $checked[1];?>/>Versão original<br/>
		<small>Exibe um link para a versão PC da página</small><br/><br/>

		<input type="checkbox" name="box_show_categorias" id="box_show_categorias" <?php echo $checked[2];?>/>Categorias<br/>
		<small>Exibe um link para a página de categorias do blog</small><br/><br/>

		<input type="checkbox" name="box_show_pages" id="box_show_pages" <?php echo $checked[3];?>/>Páginas<br/>
		<small>Exibe um link para a lista de páginas</small><br/><br/>

		<input type="checkbox" name="box_show_arquivo" id="box_show_arquivo" <?php echo $checked[4];?>/>Arquivo<br/>
		<small>Exibe um link para os arquivos do blog</small><br/><br/>
		
		<h4>Outros</h4>
		<label for="txt_titulo">Título da versão móvel do blog:</label>
		<input type="text" name="txt_titulo" id="txt_titulo" size="25" value="<?php echo get_option('mblog_titulo');?>" /><br/>
		<small>O padrão é o título da versão para PC</small><br/><br/>

		<label for="txt_desc">Descrição:</label>
		<input type="text" name="txt_desc" id="txt_desc" size="25" value="<?php echo get_option('mblog_descricao');?>" /><br/>
		<small>Exemplo: "Versão móvel"</small><br/><br/>
		
		<input type="submit" value="Salvar" name="mblog_opts_salvar" class="button"/>
	</form>
	</p>
</div>
<?php
}

function mblog_install(){
	add_option('mblog_titulo', get_option('blogname'), '', 'yes');
	add_option('mblog_descricao', 'Versão móvel', '', 'yes');
	add_option('mblog_show_tags', 'no', '', 'yes');
	add_option('mblog_show_original', 'yes', '', 'yes');
	add_option('mblog_show_categorias', 'yes', '', 'yes');
	add_option('mblog_show_pages', 'yes', '', 'yes');
	add_option('mblog_show_arquivo', 'yes', '', 'yes');
}

function mblog_uninstall(){
	delete_option('mblog_titulo');
	delete_option('mblog_descricao');
	delete_option('mblog_show_tags');
	delete_option('mblog_show_original');
	delete_option('mblog_show_categorias');
	delete_option('mblog_show_pages');
	delete_option('mblog_show_arquivo');
}

function mblog_menu(){
	add_menu_page('Mobile Blog', 'Mobile Blog', 8, __FILE__, 'mblog_painel');
	add_submenu_page(__FILE__, 'Mobile Blog', 'Mobile Blog', 8, __FILE__, 'mblog_painel');
	add_submenu_page(__FILE__, 'Emuladores', 'Emuladores', 8, 'mobile-blog/emulador.php');
}

add_action('admin_menu', 'mblog_menu');

register_activation_hook(__FILE__, 'mblog_install');
register_deactivation_hook(__FILE__, 'mblog_uninstall');
?>
