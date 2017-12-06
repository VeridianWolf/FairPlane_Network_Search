<?php
#include these files from codelibrary
require_once($_SERVER["DOCUMENT_ROOT"]	.	"/codelibrary/includes/php/autoload.php");
require_once($_SERVER["DOCUMENT_ROOT"]	.	"/codelibrary/includes/php/common.php");
require_once("/config.inc.php");


include ("app/includes/php/template.php");


# mysql = new connection to Mysql_Library()
$gMysql				=	new Mysql_Library();

# variable template = function getTemplate points to file index.html
$template = getTemplate("home.html");


# variable template = function getTemplate points to file header.html
# variable template = function getTemplate points to file footer.html
$header = getTemplate("app/templates/header.html");
$footer = getTemplate("app/templates/footer.html");


# variable template = string replace {{header}} with the variable $header
# variable template = string replace {{footer}} with the variable $footer
$template = str_replace("{{header}}", $header, $template);
$template = str_replace("{{footer}}", $footer, $template);

echo $template;