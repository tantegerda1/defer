<?php
defined('TYPO3_MODE') or die();
/** @var string $_EXTKEY */
/** @var string $_EXTCONF */

call_user_func(function() {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
		\Netztechniker\Defer\Property\TypeConverter\DeferredConverter::class);
}, $_EXTKEY);
