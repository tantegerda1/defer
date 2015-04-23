<?php
namespace Netztechniker\Defer;

use Netztechniker\Defer\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Exception;


/**
 * DeferTrait
 *
 * @package Netztechniker\Defer
 * @author Ludwig Rafelsberger <info@netztechniker.at>, netztechniker.at
 *
 * @method string serialize()
 */
trait DeferTrait {

	/**
	 * Deferred state
	 *
	 * @var boolean
	 */
	protected $deferred = FALSE;

	/**
	 * Token to later resume work on this deferred action, object or whatever
	 *
	 * @var string
	 */
	protected $token = NULL;



	/**
	 * Defer an action, object or whatever
	 *
	 * @param \DateTime $validTill Point in time up to which the deferred action, object or whatever might be deferred
	 * @param integer $length Desired token length (range 1-64; take uniqueness, guessability and userfriendlyness into account)
	 * @return string Token to later resume work on this deferred action, object or whatever
	 * @throws \InvalidArgumentException 1426287601 If desired token length is not inside the bounds
	 * @throws \InvalidArgumentException 1426287602 If the point in time given by $validTill is in the past
	 * @throws \RuntimeException 1426287600 If the deferred action, object or whatever is already deferred
	 * @throws \RuntimeException 1426287603 If the deferred action, object or whatever to defer cannot be persisted
	 */
	public function defer(\DateTime $validTill, $length) {
		if ($this->isDeferred()) {
			throw new \RuntimeException('Cannot defer an action, object or whatever more than once', 1426287600);
		}
		if (!MathUtility::canBeInterpretedAsInteger($length) || !MathUtility::isIntegerInRange((int)$length, 1, 64)) {
			throw new \InvalidArgumentException(
				sprintf('Argument $length must be a positive integer, with 0 < $length <= 64, %d given', $length), 1426287601);
		}

		if (DateTimeUtility::fromNow($validTill) < 0) {
			throw new \InvalidArgumentException(sprintf('Cannot defer an action, object or whatever to the past: '
				. 'You tried to defer to %s', $validTill->format('Y-m-d h:i:s')), 1426287602);
		}

		$token = GeneralUtility::getRandomHexString($length);
		$result = $this->getDatabaseConnection()->exec_INSERTquery('tx_defer_deferred', [
			'token' => $token,
			'type' => get_class($this),
			'data' => $this->serialize(),
			'valid_till' => $validTill->format('Y-m-d H:i:s'),
		]);
		if (TRUE !== $result) {
			throw new \RuntimeException('Cannot store deferred: ' . $this->getDatabaseConnection()->sql_error(), 1426287603);
		}

		$this->deferred = TRUE;
		$this->token = $token;
		return $token;
	}


	/**
	 * Mark this deferred action, object or whatever as being done
	 *
	 * @return Deferrable $this for fluent calls
	 * @throws \RuntimeException 1426287604 If the action, object or whatever is not deferred
	 * @throws \RuntimeException 1426287605 If a database error occurs
	 */
	public function resolve() {
		if (!$this->isDeferred()) {
			throw new \RuntimeException('Cannot resolve an action, object or whatever that is not deferred', 1426287604);
		}

		$result = $this->getDatabaseConnection()->exec_DELETEquery('tx_defer_deferred',
			'token=' . $this->getDatabaseConnection()->fullQuoteStr($this->getToken(), 'tx_defer_deferred'));
		if (TRUE !== $result) {
			throw new \RuntimeException('Cannot remove deferred: ' . $this->getDatabaseConnection()->sql_error(), 1426287605);
		}

		$this->deferred = FALSE;
		return $this;
	}


	/**
	 * Test whether this deferred action, object or whatever is already deferred
	 *
	 * @return boolean TRUE if this deferred action, object or whatever is already deferred, FALSE otherwise
	 */
	public function isDeferred() {
		return $this->deferred;
	}


	/**
	 * Get the token to later resume work on this deferred action, object or whatever
	 *
	 * @return string Token to later resume work on this deferred action, object or whatever
	 * @throws \RuntimeException 1426287606 If the action, object or whatever is not yet deferred
	 * @throws \RuntimeException 1426287607 If an internal error occurred that made the token be no string
	 */
	public function getToken() {
		if (!$this->isDeferred()) {
			throw new \RuntimeException('Cannot get token of not-yet deferred action, object or whatever', 1426287606);
		}
		if (!is_string($this->token)) {
			throw new \RuntimeException('Invalid token in deferred action, object or whatever', 1426287607);
		}
		return $this->token;
	}


	/**
	 * Helper method to set the token after deserialization
	 *
	 * TODO: Get rid of Deferrable::_setToken()
	 *
	 * @param string $token Token that has been used to reconstruct this deferred action, object or whatever
	 * @return Deferrable $this for fluent calls
	 * @internal
	 */
	public function _setToken($token) {
		$this->token = $token;
		return $this;
	}





	// ---------------------- global object accessors -----------------------
	/**
	 * Get the TYPO3 Database connection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection The TYPO3 Database connection as stored in $GLOBALS['TYPO3_DB']
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}