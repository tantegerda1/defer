<?php
namespace Netztechniker\Defer\Property\TypeConverter;

use Netztechniker\Defer\Deferrable;
use Netztechniker\Defer\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Database\PreparedStatement;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;


/**
 * TypeConverter for Deferred actions, objects or whatever
 *
 * @package Netztechniker\Defer
 * @author Ludwig Rafelsberger <info@netztechniker.at>, netztechniker.at
 */
class DeferredConverter extends AbstractTypeConverter {

	protected $sourceTypes = ['string'];
	protected $targetType = 'Netztechniker\\Defer\\Deferrable';
	protected $priority = 50;


	/**
	 * Test if conversion is possible
	 *
	 * @param string $source Source data
	 * @param string $targetType Target type name (simple type or fully qualified class name)
	 * @return boolean TRUE if this TypeConverter can convert the given $source into the target type, FALSE otherwise
	 */
	public function canConvertFrom($source, $targetType) {
		if (!in_array(Deferrable::class, array_merge([$targetType], class_implements($targetType)))) {
			return FALSE;
		}
		return is_string($source);
	}


	/**
	 * Convert a deferrable action, object or whatever by using a token
	 *
	 * @param string $source Source data (token)
	 * @param string $targetType Target type name (simple type or fully qualified class name)
	 * @param array $convertedChildProperties Unused
	 * @param PropertyMappingConfigurationInterface $configuration Unused
	 * @return Deferrable
	 * @throws TypeConverterException 1426287608 If an error (most likely programming, or data) happens during persistence
	 * @throws TypeConverterException 1426287609 If no data is found during reconstruction
	 * @throws TypeConverterException 1426287610 If an unexpected exception happens during reconstruction
	 */
	public function convertFrom(
		$source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = NULL
	) {

		$stmt = $this->getDatabaseConnection()->prepare_SELECTquery(
			'type, data', 'tx_defer_deferred', 'token=:token AND valid_till>=:now', '', '', 1);

		if (TRUE !== $stmt->execute([':token' => $source, ':now' => DateTimeUtility::now()->format('Y-m-d H:i:s')])) {
			throw new TypeConverterException('Error retrieving deferred data', 1426287608);
		}

		$result = $stmt->fetch(PreparedStatement::FETCH_ASSOC);
		if (!is_array($result) || !array_key_exists('type', $result) || !array_key_exists('data', $result)) {
			throw new TypeConverterException('No deferred data found', 1426287609);
		}

		try {
			// TODO: optimize performance / use Extbases cached Reflection mechanisms
			$rc = new \ReflectionClass($result['type']);
			/** @var Deferrable $target */
			$target = $rc->newInstanceWithoutConstructor();
			$target->unserialize($result['data']);
			// TODO: get rid of Deferrable::_setToken()
			if (method_exists($target, '_setToken')) {
				call_user_func([$target, '_setToken'], $source);
			}
			return $target;
		} catch (\Exception $e) {
			throw new TypeConverterException(sprintf('Could not convert to %s', $targetType), 1426287610, $e);
		}
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
