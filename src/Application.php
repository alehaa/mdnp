<?php

/* This file is part of MDNP.
 *
 * MDNP is free software: you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MDNP is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program. If not, see
 *
 *  http://www.gnu.org/licenses/
 *
 *
 * Copyright (C)
 *  2016 Alexander Haase <ahaase@alexhaase.de>
 */

namespace MDNP;

use MDNP\Notes\NoteController;
use MDNP\Notes\NoteProvider;
use Silex\Application as Silex_Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;


/** \brief Core application.
 *
 * \details This class is a wrapper around the Silex Application to register all
 *  required providers.
 */
class Application extends Silex_Application
{
	/** \brief Constructor.
	 *
	 * \details Creates a new MDNP \ref Application object and initializes all
	 *  modules.
	 *
	 *
	 * \param $options Option array.
	 */
	public function __construct(array $options = array())
	{
		/* Silex catches exceptions that are thrown from within a request /
		 * response cycle. However, it does not catch PHP errors and notices.
		 * The Symfony/Debug package has an ErrorHandler class that solves this
		 * problem. To handle fatal errors, the ExceptionHandler will be used in
		 * addition.
		 *
		 * For more information see
		 * http://silex.sensiolabs.org/doc/cookbook/error_handler.html */
		ErrorHandler::register();
		ExceptionHandler::register();


		/* Initialize the Silex Application class. This has to be done first, so
		 * we can work with this class in the following steps. */
		parent::__construct($options);


		/* Register all required providers. */
		$this->register(new TwigServiceProvider, array(
			'twig.path' => 'themes/'.($options['theme'] ?? 'default').'/views'
		));
		$this->register(new DoctrineServiceProvider, $options['db.options']);
		$this->register(new NoteProvider);


		/* Mount all required controllers. */
		$this->mount('', new NoteController);
	}
}

?>
