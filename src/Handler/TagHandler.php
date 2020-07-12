<?php

namespace Handler;

use Google_Service_TagManager;
use Helper\GoogleServiceTagManagerHelper;
use Reader\ProjectReader;
use Reader\TagReader;
use Reader\VariableReader;
use Renderer\TagRenderer;

class TagHandler
{
	/** @var Google_Service_TagManager */
	private $googleServiceTagManager;
	/** @var ProjectReader */
	private $projectReader;
	/** @var GoogleServiceTagManagerHelper */
	private $googleServiceTagManagerHelper;
	/** @var TagReader */
	private $tagReader;
	/** @var TagRenderer */
	private $tagRenderer;

	/**
	 * @param Google_Service_TagManager $googleServiceTagManager
	 * @param GoogleServiceTagManagerHelper $googleServiceTagManagerHelper
	 * @param ProjectReader $projectReader
	 * @param TagReader $tagReader
	 * @param TagRenderer $tagRenderer
	 */
	public function __construct(Google_Service_TagManager $googleServiceTagManager,
	                            GoogleServiceTagManagerHelper $googleServiceTagManagerHelper,
	                            ProjectReader $projectReader,
	                            TagReader $tagReader,
	                            TagRenderer $tagRenderer)
	{
		$this->googleServiceTagManager = $googleServiceTagManager;
		$this->projectReader = $projectReader;
		$this->googleServiceTagManagerHelper = $googleServiceTagManagerHelper;
		$this->tagReader = $tagReader;
		$this->tagRenderer = $tagRenderer;
	}

	public function handle()
	{
		// $tagList = $this->getTabList();
		foreach ($this->projectReader->getTags() as $tagName => $tagSettings)
		{
			$tagItems = $this->tagReader->loadTagItemsByName($tagName);
			foreach ($tagItems as $tagItem)
			{

			}
			print_r($tagItems);
			exit;
		}
		//print_r($tagList);
		exit;
	}

	private function getTabList()
	{
		$tagList = $this->googleServiceTagManager->accounts_containers_workspaces_tags->listAccountsContainersWorkspacesTags($this->googleServiceTagManagerHelper->getParent());

		$tags = [];
		foreach (($tagList['tag'] ?? []) as $tag)
		{
			$tags[$tag['name']] = $tag;
		}

		return $tags;
	}

}