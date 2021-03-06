<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016
 * @package Slim
 * @subpackage Base
 */

namespace Aimeos\Slim\Base;

use Interop\Container\ContainerInterface;


/**
 * Service providing the context objects
 *
 * @package Slim
 * @subpackage Base
 */
class Context
{
	private $container;
	private $context;


	/**
	 * Initializes the object
	 *
	 * @param ContainerInterface $container Dependency container
	 */
	public function __construct( ContainerInterface $container )
	{
		$this->container = $container;
	}


	/**
	 * Returns the current context
	 *
	 * @param boolean $locale True to add locale object to context, false if not
	 * @param array $attributes Associative list of URL parameter
	 * @param string $type Configuration type ("frontend" or "backend")
	 * @return \Aimeos\MShop\Context\Item\Iface Context object
	 */
	public function get( $locale = true, array $attributes = array(), $type = 'frontend' )
	{
		$config = $this->container->get( 'aimeos_config' )->get( $type );

		if( $this->context === null )
		{
			$context = new \Aimeos\MShop\Context\Item\Standard();
			$context->setConfig( $config );

			$this->addDataBaseManager( $context );
			$this->addFilesystemManager( $context );
			$this->addMessageQueueManager( $context );
			$this->addLogger( $context );
			$this->addCache( $context );
			$this->addMailer( $context);
			$this->addSession( $context );
			$this->addUser( $context );

			$this->context = $context;
		}

		$this->context->setConfig( $config );

		if( $locale === true )
		{
			$localeItem = $this->container->get( 'aimeos_locale' )->get( $this->context, $attributes );
			$this->context->setLocale( $localeItem );
			$this->context->setI18n( $this->container->get( 'aimeos_i18n' )->get( array( $localeItem->getLanguageId() ) ) );
		}

		return $this->context;
	}


	/**
	 * Adds the cache object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object including config
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addCache( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$cache = new \Aimeos\MAdmin\Cache\Proxy\Standard( $context );
		$context->setCache( $cache );

		return $context;
	}


	/**
	 * Adds the database manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addDatabaseManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$dbm = new \Aimeos\MW\DB\Manager\DBAL( $context->getConfig() );
		$context->setDatabaseManager( $dbm );

		return $context;
	}


	/**
	 * Adds the filesystem manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addFilesystemManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$fs = new \Aimeos\MW\Filesystem\Manager\Standard( $context->getConfig() );
		$context->setFilesystemManager( $fs );

		return $context;
	}


	/**
	 * Adds the logger object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addLogger( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$logger = \Aimeos\MAdmin\Log\Manager\Factory::createManager( $context );
		$context->setLogger( $logger );

		return $context;
	}



	/**
	 * Adds the mailer object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addMailer( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$mail = new \Aimeos\MW\Mail\Swift( $this->container->get( 'mailer' ) );
		$context->setMail( $mail );

		return $context;
	}


	/**
	 * Adds the message queue manager object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addMessageQueueManager( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$mq = new \Aimeos\MW\MQueue\Manager\Standard( $context->getConfig() );
		$context->setMessageQueueManager( $mq );

		return $context;
	}


	/**
	 * Adds the session object to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addSession( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$session = new \Aimeos\MW\Session\PHP();
		$context->setSession( $session );

		return $context;
	}


	/**
	 * Adds user information to the context
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context object
	 * @return \Aimeos\MShop\Context\Item\Iface Modified context object
	 */
	protected function addUser( \Aimeos\MShop\Context\Item\Iface $context )
	{
		$ipaddr = $this->container->request->getAttribute('ip_address');
		$context->setEditor( $ipaddr );

		return $context;
	}
}
