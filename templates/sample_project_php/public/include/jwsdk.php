<?php

define('JWSDK_SNIPPETS_PATH', '../jwsdk-config/snippets');

$jwsdk_page = null;

function jwsdk_page($page) {
	global $jwsdk_page;
	$jwsdk_page = $page;
}

function jwsdk_js() {
	global $jwsdk_page;
	echo file_get_contents(JWSDK_SNIPPETS_PATH . "/$jwsdk_page.js.html");
}

function jwsdk_css() {
	global $jwsdk_page;
	echo file_get_contents(JWSDK_SNIPPETS_PATH . "/$jwsdk_page.css.html");
}
