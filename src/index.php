<?php

//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

define(
'RMP_MDOC_BASE_DIR',
	__DIR__
);

define(
'RMP_MDOC_BASE_URL',
	$_SERVER['HTTP_HOST'] . str_replace( $_SERVER['DOCUMENT_ROOT'], '', getcwd() )
);

define(
'RMP_MDOC_WORKING_DIR',
	$_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI']
);

require_once __DIR__ . '/Rmp/Helper/Composer.php';

\Rmp\Helper\Composer::load();

$server = new \Rmp\Mdoc\Server();
$server->serve();