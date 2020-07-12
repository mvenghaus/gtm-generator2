<?php

namespace DI;

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([

	\Google_Service_TagManager::class => function ()
	{
		$client = new \Google_Client();
		$client->setApplicationName('gtm generator2');
		$client->setAuthConfig(__DIR__ . '/etc/google_client_credentials.json');
		$client->addScope(\Google_Service_TagManager::TAGMANAGER_EDIT_CONTAINERS);

		return new \Google_Service_TagManager($client);
	}

]);

return $containerBuilder->build();
