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
 * Coinflip
 */
class Coinflip extends Command
{
    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "coinflip";
        $this->description = "An economy game";
        $this->aliases = ["cf"];
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
        $embed = new Embed($this->discord);
        $usermoney = $database->getUserMoney($database->getUserIdByDiscordId($msg->member->id));
        if (!is_numeric($usermoney)) {
            echo "money is empty" . PHP_EOL;
            if (!$database->addUser([
                "discord_id" => $msg->member->id
            ])) {
                $embed->setTitle('You are couldnt added to database.');
                $msg->channel->sendEmbed($embed);
                echo "cant added" . PHP_EOL;
                return;
            } else {
                echo "User added" . PHP_EOL;
                $usermoney = 0;
            }
        }
        if (!$args[0] || !is_numeric($args[0])) {
            $embed->setColor('#ff0000');
            $embed->setDescription('You should type payment amount.');
        } else {
            if ($args[0] <= 0) {
                $embed->setDescription("You should give a value greater than zero.");
                $embed->setColor('#ff0000');
            } else if ($args[0] > $usermoney) {
                $embed->setDescription("Your money isn't enough.");
                $embed->setColor('#ff0000');
            } else {
                $payamount = $args[0];
                $rand = random_int(0, 1);

                // delete user money from payamount
                $database->setUserMoney($database->getUserIdByDiscordId($msg->member->id), $usermoney - $payamount);
                $usermoney = $usermoney - $payamount;

                setlocale(LC_MONETARY, 'en_US');
                if ($rand) {
                    $database->setUserMoney($database->getUserIdByDiscordId($msg->member->id), $usermoney + $payamount * 2);
                    $embed->setTitle("You Won!");
                    $embed->setDescription("$ " . number_format($payamount * 2, 2, ',', '.'));
                    $embed->setColor('#7CFC00');
                } else {
                    $embed->setTitle("You Lose!");
                    $embed->setDescription("$ " . number_format($payamount, 2, ',', '.'));
                    $embed->setColor('#ff0000');
                }
            }
        }
        $embed->setTimestamp();
        $msg->channel->sendEmbed($embed);
    }
}
