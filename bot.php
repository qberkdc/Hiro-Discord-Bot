<?php

/**
 * Copyright 2021 bariscodefx
 * 
 * This file part of project Hiro 016 Discord Bot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

include __DIR__ . '/vendor/autoload.php';

use hiro\Hiro;
use Discord\Parts\User\Activity;
use hiro\CommandLoader;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;
use hiro\ArgumentParser;
use hiro\interfaces\HiroInterface;
use hiro\PresenceManager;
use Discord\WebSockets\Intents;

if ( !isset( $_ENV['TOKEN'] ) ) {
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();
}

$ArgumentParser = new ArgumentParser($argv);
$shard_id = $ArgumentParser->getShardId();
$shard_count = $ArgumentParser->getShardCount();
$bot = new Hiro([
    'token' => $_ENV['TOKEN'],
    'prefix' => $_ENV['PREFIX'],
    'shardId' => $shard_id,
    'shardCount' => $shard_count,
    'caseInsensitiveCommands' => true,
    'loadAllMembers' => true,
    'intents' => Intents::getDefaultIntents() | Intents::GUILD_MEMBERS
]);

$bot->on('ready', function($discord) use ($shard_id, $shard_count) {
    echo "Bot is ready!", PHP_EOL;
    
    $commandLoader = new CommandLoader($discord);

    $presenceManager = new PresenceManager($discord);
    $presenceManager->setLoopTime(60.0)
    ->setPresenceType(Activity::TYPE_WATCHING)
    ->setPresences([
        "{$_ENV['PREFIX']}help | " . $discord->formatNumber(sizeof($discord->guilds)) . " guilds | Shard " . $shard_id  + 1 . " of $shard_count",
    ])
    ->startThread();

    /** fix discord guild count */
    $discord->getLoop()->addPeriodicTimer($presenceManager->looptime, function() use ($presenceManager, $discord, $shard_id, $shard_count)
    {
        $presenceManager->setPresences([
            "{$_ENV['PREFIX']}help | " . $discord->formatNumber(sizeof($discord->guilds)) . " guilds | Shard " . $shard_id + 1 . " of $shard_count",
        ]);
    });

});

$bot->run();
