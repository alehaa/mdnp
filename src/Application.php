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

use Lokhman\Silex\Provider\ConfigServiceProvider;
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
	 * \param array $values Predefined values for \ref Application.
	 */
	public function __construct(array $values = array())
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
		parent::__construct($values);


		/*
		 * Register all required providers.
		 */

		/* Config */
		$this->register(new ConfigServiceProvider(__DIR__ . '/../config', array(
			'%root%' => __DIR__ . '/..'
		)));

		/* Twig */
		$this->register(new TwigServiceProvider, array(
			'twig.path' => $this->get_twig_viewpath()
		));

		/* Doctrine ORM. */
		$this->register(new DoctrineServiceProvider);

		/* Note provider. */
		$this->register(new NoteProvider);


		/*
		 * Mount all required controllers.
		 */
		$this->mount('', new NoteController);
	}


	/** \brief Return the location of twig views.
	 *
	 * \details Twig requires a path where to find the view files. Instead of
	 *  static setting this path, we allow the user to define a theme name, so
	 *  he may switch the theme dynamically. Twig must not get this path until
	 *  templates are rendered. Otherwise the user is not able to define the
	 *  theme after the constructor has been called, so we'll pack it into a
	 *  callable.
	 *
	 *
	 * \return callable A callable method to get the current twig views path.
	 */
	private function get_twig_viewpath(): callable
	{
		return function () {
			/* If no theme has been defined by the user, the default theme will
			 * be used. */
			return 'themes/'.($this['theme'] ?? 'default').'/views';
		};
	}
}

?>
