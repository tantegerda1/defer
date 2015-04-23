<?php
namespace Netztechniker\Defer\Utility;


/**
 * Utility for PHP DateTime objects
 *
 * @package Netztechniker\Defer
 * @author Ludwig Rafelsberger <info@netztechniker.at>, netztechniker.at
 */
class DateTimeUtility {

	/**
	 * Get a PHP DateTime object representing the (normalized) current time
	 *
	 * Uses a simulated time that stays the same throughout the whole request.
	 *
	 * @return \DateTime A DateTime object representing the initial time of the request ("now")
	 */
	static public function now() {

		// note: when creating a DateTime from timestamp, PHP ignores timezones and converts the given time into UTC.
		$temp = \DateTime::createFromFormat('U', $GLOBALS['EXEC_TIME']);

		// we cannot rely on $GLOBALS['TYPO3_CONF_VARS']['SYS']['phpTimeZone'] to be set (because users don't *have* to
		// set that value in the Install Tool. But we can rely on TYPO3 to have configured a default timezone based on either
		// that value or a generic fallback
		return $temp->setTimezone(new \DateTimeZone(date_default_timezone_get()));
	}


	/**
	 * Calculate "how far" a point in time is away from now
	 *
	 * @param \DateTime $time The point in time to examine
	 * @param boolean $absolute If set to TRUE, return the distance (>= 0) rather than the difference (negative if $time is past)
	 * @return integer Number of seconds between now and $time
	 */
	static public function fromNow(\DateTime $time, $absolute = FALSE) {
		$difference = $time->getTimestamp() - self::now()->getTimestamp();
		return $absolute ? abs($difference) : $difference;
	}
}
 