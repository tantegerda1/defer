<?php
namespace Netztechniker\Defer;


/**
 * An action, object or whatever that can be deferred
 *
 * @package Netztechniker\Defer
 * @author Ludwig Rafelsberger <info@netztechniker.at>, netztechniker.at
 */
interface Deferrable extends \Serializable {

	/**
	 * Defer this action, object or whatever
	 *
	 * @param \DateTime $validTill Point in time up to which the deferred action, object or whatever might be deferred
	 * @param integer $length Desired token length (take uniqueness, guessability and userfriendlyness into account)
	 * @return string Token to later resume work on this deferred action, object or whatever
	 * @throws \RuntimeException 1426287600 If the deferred action, object or whatever is already deferred
	 */
	public function defer(\DateTime $validTill, $length);


	/**
	 * Mark this action, object or whatever as being done
	 *
	 * @return Deferrable $this for fluent calls
	 * @throws \RuntimeException 1427367120 If the action, object or whatever is not deferred
	 */
	public function resolve();


	/**
	 * Test whether this action, object or whatever is already deferred
	 *
	 * @return boolean TRUE if this action, object or whatever is already deferred, FALSE otherwise
	 */
	public function isDeferred();


	/**
	 * Get the token to later resume work on this action, object or whatever
	 *
	 * @return string Token to later resume work on this action, object or whatever
	 * @throws \RuntimeException 1426287606 If the action, object or whatever is not yet deferred
	 */
	public function getToken();
}
