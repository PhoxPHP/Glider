<?php
namespace Glider\Connection;

use StdClass;
use RuntimeException;
use ReflectionClass;
use Glider\Connection\ConnectionManager;
use Glider\Platform\Contract\PlatformProvider;
use Glider\Connectors\Contract\ConnectorProvider;
use Glider\Connection\Contract\ConnectionInterface;

class Connector
{

	/**
	* @var 		$connection
	* @access 	private
	*/
	private 	$connection;

	/**
	* @var 		$connectionManager
	* @access 	private
	*/
	private 	$connectionManager;

	/**
	* @var 		$connectionFailed
	* @access 	private
	*/
	private 	$connectonFailed;

	/**
	* @var 		$platformProvider
	* @access 	private
	*/
	private 	$platformProvider;

	/**
	* @param 	$contract Glider\Connectors\Contract\ConnectionInterface
	* @access 	public
	* @return 	void
	*/
	public function __construct(ConnectionInterface $contract)
	{
		$this->connectionManager = $contract;
		$this->connection = null;
		$this->connectionFailed = false;
	}

	/**
	* Resolve provided connection.
	*
	* @access 	public
	* @return 	Object
	*/
	public function resolveConnection()
	{
		if (!$this->connectionManager instanceof ConnectionInterface) {
			throw new RuntimeException('Connection must implement \ConnectionInterface');
		}
		$reflector = new \ReflectionClass($this->connectionManager);
		$connections = $reflector->getProperty('platformConnector');
		$connections->setAccessible('public');
		$connections = $connections->getValue($this->connectionManager);
		$resolvedConnection = null;

		if ($this->getPlatformProvider($connections) == false) {
			$resolvedConnection = $this->getPlatformProvider($this->connectionManager->getAlternativeId(ConnectionManager::USE_ALT_KEY));
		}

		if (!is_null($resolvedConnection) && $resolvedConnection == false) {
			throw new RuntimeException('Unable to start connection for database platform.');
		}
		return false;
	}

	/**
	* Resolves a connector's provider and returns it's object.
	*
	* @param 	$platform <Array>
	* @access 	private
	* @return 	Mixed
	*/
	private function getPlatformProvider(array $platform=[])
	{	
		$platformId = key($platform);
		$platform = current($platform);
		if (!isset($platform['connectsWith'])) {
			$this->connectionFailed = ':noPlatform';
			return false;
		}

		$connector = $platform['connectsWith'];
		if (!class_exists($connector)) {
			$this->connectionFailed = ':noConnectorProvider';
			return false;
		}

		$platformProvider = new $connector();
		if (!$platformProvider instanceof PlatformProvider) {
			return false;
		}

		$this->platformProvider = $platformProvider;

		print '<pre>';
		print_r($platform);

		return true;
	}

	/**
	*
	*
	*
	*/
	private function resolveAndSet(String $providerName)
	{

	}

}