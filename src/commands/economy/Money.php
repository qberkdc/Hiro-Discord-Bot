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

namespace hiro\commands;

use Discord\Parts\Embed\Embed;
use hiro\database\Database;

/**
 * Money
 */
class Money extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "money";
        $this->description = "Displays your money.";
        $this->aliases = ["cash"];
        $this->category = "economy";
        $this->cooldown = 10 * 1000;
    }

    /**
     * handle
     *
     * @param [type] $msg
     * @param [type] $args
     * @return void
     */
    public function handle($msg, $args): void
    {
        $database = new Database();
        if (!$database->isConnected) {
            $msg->channel->sendMessage("Couldn't connect to database.");
            return;
        }
        $user = $msg->mentions->first();
        if (!$user) $user = $msg->author;
        $user_money = $database->getUserMoney($database->getUserIdByDiscordId($user->id));
        if (!is_numeric($user_money)) {
            echo "money is empty" . PHP_EOL;
            if (!$database->addUser([
                "discord_id" => $user->id
            ])) {
                $embed = new Embed($this->discord);
                $embed->setTitle('You are couldnt added to database.');
                $msg->channel->sendEmbed($embed);
                echo "cant added" . PHP_EOL;
                return;
            } else {
                echo "User added" . PHP_EOL;
                $user_money = 0;
            }
        }
        setlocale(LC_MONETARY, 'en_US');
        $user_money = number_format($user_money, 2, ',', '.');
        $embed = new Embed($this->discord);
        $embed->setTitle("Money: $" . $user_money);
        $embed->setTimestamp();
        $embed->setColor('#7CFC00');
        $msg->channel->sendEmbed($embed);
        $database = NULL;
    }
}