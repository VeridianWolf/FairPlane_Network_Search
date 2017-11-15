<?php
/**
 * Created by PhpStorm.
 * User: Cedric
 * Date: 08/01/14
 * Time: 18:54
 */

# test for login, or go to login page
require_once("../m/library/Am/Lite.php");

$amLite			=	Am_Lite::getInstance();
$amLite->checkAccess(Am_Lite::ONLY_LOGIN, '');

# now find out a list of products, so we can check variables for member,credits etc

$bActive		=	$amLite->isUserActive();
$products		=	$amLite->getProducts();
$bSubscribed	=	$amLite->haveSubscriptions(array(3));
$user			=	$amLite->getUser();
$member_id		=	$user['user_id'];

