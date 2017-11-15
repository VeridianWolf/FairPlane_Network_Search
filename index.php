<?php
include ("app/includes/php/template.php");

$template = getTemplate("index.html");

$header = getTemplate("app/templates/header.html");
$footer = getTemplate("app/templates/footer.html");

$template = str_replace("{{header}}", $header, $template);
$template = str_replace("{{footer}}", $footer, $template);

echo $template;