<?php

namespace Handler;

use Google_Service_TagManager;
use Helper\GoogleServiceTagManagerHelper;
use Psr\Log\LoggerInterface;
use Reader\ProjectReader;
use Reader\TagReader;

class TriggerHandler
{
	/** @var Google_Service_TagManager */
	private $googleServiceTagManager;
	/** @var GoogleServiceTagManagerHelper */
	private $googleServiceTagManagerHelper;
	/** @var ProjectReader */
	private $projectReader;
	/** @var TagReader */
	private $tagReader;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * @param Google_Service_TagManager $googleServiceTagManager
	 * @param GoogleServiceTagManagerHelper $googleServiceTagManagerHelper
	 * @param ProjectReader $projectReader
	 * @param TagReader $tagReader
	 * @param LoggerInterface $logger
	 */
	public function __construct(Google_Service_TagManager $googleServiceTagManager,
	                            GoogleServiceTagManagerHelper $googleServiceTagManagerHelper,
	                            ProjectReader $projectReader,
	                            TagReader $tagReader,
	                            LoggerInterface $logger)
	{

		$this->googleServiceTagManager = $googleServiceTagManager;
		$this->googleServiceTagManagerHelper = $googleServiceTagManagerHelper;
		$this->projectReader = $projectReader;
		$this->tagReader = $tagReader;
		$this->logger = $logger;
	}

	public function handle()
	{
		$triggerList = $this->getTriggerList();
		foreach ($this->projectReader->getTags() as $tagName => $tagSettings)
		{
			$tagItems = $this->tagReader->loadTagItemsByName($tagName);
			foreach ($tagItems as $tagItem)
			{
				$params = $this->parseParams($tagItem['firingTriggerId'][0] ?? '');

				$triggerName = $tagItem['name'];
				$triggerHash = md5(sprintf('%s#%s', $tagName, json_encode($params)));

				$trigger = new \Google_Service_TagManager_Trigger();
				$trigger->setName($triggerName);
				$trigger->setType('custom_event');
				$trigger->setNotes($triggerHash);
				$trigger->setCustomEventFilter($this->createCustomEventFilter($tagName));
				$trigger->setFilter($this->createFilterFromParams($params));

				if (isset($triggerList[$triggerName]))
				{
					if ($triggerList[$triggerName]['notes'] != $triggerHash)
					{
						$this->logger->debug('trigger update', ['name' => $triggerName]);

						$this->googleServiceTagManager->accounts_containers_workspaces_triggers->update(
							$this->googleServiceTagManagerHelper->getParent() . '/triggers/' . $triggerList[$triggerName]['triggerId'],
							$trigger
						);
					} else
					{
						$this->logger->debug('trigger skip', ['name' => $triggerName]);
					}
				} else
				{
					$this->logger->debug('trigger create', ['name' => $triggerName]);

					$this->googleServiceTagManager->accounts_containers_workspaces_triggers->create(
						$this->googleServiceTagManagerHelper->getParent(),
						$trigger
					);
				}

				unset($triggerList[$triggerName]);
			}
		}

		foreach ($triggerList as $triggerData)
		{
			$this->logger->debug('trigger delete', ['name' => $triggerData['name']]);

			$this->googleServiceTagManager->accounts_containers_workspaces_triggers->delete(
				$this->googleServiceTagManagerHelper->getParent() . '/triggers/' . $triggerData['triggerId']
			);
		}
	}

	private function getTriggerList()
	{
		$triggerList = $this->googleServiceTagManager->accounts_containers_workspaces_triggers->listAccountsContainersWorkspacesTriggers($this->googleServiceTagManagerHelper->getParent());

		$triggers = [];
		foreach (($triggerList['trigger'] ?? []) as $trigger)
		{
			$triggers[$trigger['name']] = $trigger;
		}

		return $triggers;
	}

	private function createFilterFromParams(array $params): array
	{
		$filter = [];
		foreach ($params as $name => $value)
		{
			$condition = new \Google_Service_TagManager_Condition();
			$condition->setType('equals');
			$condition->setParameter([
				['type' => 'template', 'key' => 'arg0', 'value' => $name],
				['type' => 'template', 'key' => 'arg1', 'value' => $value]
			]);

			$filter[] = $condition;
		}

		return $filter;
	}

	private function createCustomEventFilter(string $tagName): array
	{
		$condition = new \Google_Service_TagManager_Condition();
		$condition->setType('equals');
		$condition->setParameter([
			['type' => 'template', 'key' => 'arg0', 'value' => '{{_event}}'],
			['type' => 'template', 'key' => 'arg1', 'value' => $tagName]
		]);

		return [$condition];
	}

	private function parseParams(string $triggerQuery): array
	{
		preg_match_all("/([^=]+)='([^']+)'/is", $triggerQuery, $results);

		$params = [];
		foreach ($results[1] as $key => $name)
		{
			$name = trim($name);
			$value = $results[2][$key] ?? '';
			if ($name == '{{Page Type}}' && $value == 'All Pages')
			{
				continue;
			}

			$params[$name] = $value;
		}

		return $params;
	}

}