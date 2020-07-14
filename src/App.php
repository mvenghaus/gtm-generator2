<?php

use Handler\TagHandler;
use Handler\TriggerHandler;
use Handler\VariableHandler;
use Reader\ProjectReader;

class App
{
	/** @var ProjectReader */
	private $projectReader;
	/** @var VariableHandler */
	private $variableHandler;
	/** @var TriggerHandler */
	private $triggerHandler;
	/** @var TagHandler */
	private $tagHandler;

	/**
	 * @param ProjectReader $projectReader
	 * @param VariableHandler $variableHandler
	 * @param TriggerHandler $triggerHandler
	 * @param TagHandler $tagHandler
	 */
	public function __construct(ProjectReader $projectReader,
	                            VariableHandler $variableHandler,
	                            TriggerHandler $triggerHandler,
	                            TagHandler $tagHandler)
	{
		$this->projectReader = $projectReader;
		$this->variableHandler = $variableHandler;
		$this->triggerHandler = $triggerHandler;
		$this->tagHandler = $tagHandler;
	}

	public function build()
	{
		$this->projectReader->loadFile(__DIR__ . '/../projects/inklammern/www.inklammern.de.json');

		// $this->variableHandler->handle();
		$this->triggerHandler->handle();
		$this->tagHandler->handle();
	}

}