<?php
/*
 * Extension Manager configuration file
 */

/** @var string $_EXTKEY */

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Defer actions, objects or whatever',
	'description' => 'Interface and code to help creating objects that need to be "paused" until some later point in time',
	'category' => 'misc',
	'author' => 'Ludwig Rafelsberger',
	'author_email' => 'info@netztechniker.at',
	'author_company' => 'netztechniker.at',
	'state' => 'alpha',
	'version' => '0.0.5',
	'constraints' => array(
		'depends' => array(
			'php' => '5.5',
			'typo3' => '6.2',
		),
	),
);
