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

class Ban extends Command
{

    /**
     * configure
     *
     * @return void
     */
    public function configure(): void
    {
        $this->command = "ban";
        $this->description = "Bans mentioned user.";
        $this->aliases = [];
        $this->category = "mod";
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
        if(@$msg->member->getPermissions()['ban_members'])
        {
            $user = @$msg->mentions->first();
            if($user)
            {
                $banner = $msg->author;
                if(!isset($msg->channel->guild->members[$user->id])) 
                {
                    $embed = new Embed($this->discord);
                    $embed->setColor('#ff0000');
                    $embed->setDescription("User couldn't found.");
                    $embed->setTimestamp();
                    $msg->channel->sendEmbed($embed);
                    return;
                }
                $roles_men = max($this->rolePositionsMap($msg->channel->guild->members[$user->id]->roles));
                $roles_self = max($this->rolePositionsMap($msg->member->roles));
                if($banner->id == $user->id)
                {
                    $embed = new Embed($this->discord);
                    $embed->setColor('#ff0000');
                    $embed->setDescription("You cant ban yourself");
                    $embed->setTimestamp();
                    $msg->channel->sendEmbed($embed);
                    return;
                }else {
                    if( $roles_self < $roles_men )
                    {
                        $embed = new Embed($this->discord);
                        $embed->setColor('#ff0000');
                        $embed->setDescription("Your role position too low!");
                        $embed->setTimestamp();
                        $msg->channel->sendEmbed($embed);
                        return;
                        }else {
                            $msg->channel->guild->members[$user->id]->ban(null, null);
                            $embed = new Embed($this->discord);
                            $embed->setColor('#ff0000');
                            $embed->setDescription("$user banned by $banner.");
                            $embed->setTimestamp();
                            $msg->channel->sendEmbed($embed);
                        }
                    }
            }else {
                $embed = new Embed($this->discord);
                $embed->setColor('#ff0000');
                $embed->setDescription("If you want ban a user you must mention a user.");
                $embed->setTimestamp();
                $msg->channel->sendEmbed($embed);
            }
        }else {
            $embed = new Embed($this->discord);
            $embed->setColor('#ff0000');
            $embed->setDescription("If you want ban a user u must have `ban_members` permission.");
            $embed->setTimestamp();
            $msg->channel->sendEmbed($embed);
        }
    }

    /**
     * rolePositionsMap
     * 
     * This function returns descending list role positions of server for user.
     *
     * @param [type] $rolesCollision
     * @return void
     */
    protected function rolePositionsMap($rolesCollision)
    {
        $rolesArray = $rolesCollision->toArray();
        $new = [];
        foreach($rolesArray as $role)
        {
            $new[] = $role->position;
        }
        return $new;
    }
}
