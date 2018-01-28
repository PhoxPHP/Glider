<?php
namespace Kit\Glider\Transactions\Pdo;

use Kit\Glider\Platform\Contract\PlatformProvider;
use Kit\Glider\Transactions\Contract\TransactionProvider;

class PdoTransaction implements TransactionProvider
{

	/**
	* @var 		$provider
	* @access 	protected
	*/
	protected 	$provider;

	/**
	* {@inheritDoc}
	*/
	public function __construct(PlatformProvider $platformProvider)
	{
		$this->provider = $platformProvider;
	}

	/**
	* {@inheritDoc}
	*/
	public function begin($connection)
	{
		$connection->beginTransaction();
	}

	/**
	* {@inheritDoc}
	*/
	public function commit($connection)
	{
		$connection->commit();
	}

	/**
	* {@inheritDoc}
	*/
	public function rollback($connection)
	{
		$connection->rollBack();
	}

}