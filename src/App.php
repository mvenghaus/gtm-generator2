<?php

use Handler\TagHandler;
use Handler\VariableHandler;
use Reader\ProjectReader;

class App
{
	/** @var ProjectReader */
	private $projectReader;
	/** @var VariableHandler */
	private $variableHandler;
	/** @var TagHandler */
	private $tagHandler;

	/**
	 * @param ProjectReader $projectReader
	 * @param VariableHandler $variableHandler
	 * @param TagHandler $tagHandler
	 */
	public function __construct(ProjectReader $projectReader,
	                            VariableHandler $variableHandler,
	                            TagHandler $tagHandler)
	{
		$this->projectReader = $projectReader;
		$this->variableHandler = $variableHandler;
		$this->tagHandler = $tagHandler;
	}

	public function build()
	{
		$this->projectReader->loadFile(__DIR__ . '/../projects/inklammern/www.inklammern.de.json');

		$this->variableHandler->handle();
		// $this->tagHandler->handle();

		exit;

		echo $client->getAccessToken();

		$tagManagerService = new Google_Service_TagManager($client);

		$tag = new \Google_Service_TagManager_Tag();
		$tag->setName('blub');
		$tag->setType('html');

		$tag->setParameter([
			['type' => 'template', 'key' => 'html', 'value' => 'blabla123']
		]);

		print_r($tagManagerService->accounts_containers_workspaces_tags->create('accounts/6001596084/containers/31889690/workspaces/1', $tag));
	}

}