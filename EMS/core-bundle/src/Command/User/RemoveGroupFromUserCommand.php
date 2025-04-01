<?php

namespace EMS\CoreBundle\Command\User;

use EMS\CoreBundle\Commands;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: Commands::USER_DELETE_GROUP,
    description: 'Remonve a group from a user.',
    hidden: false
)]
class RemoveGroupFromUserCommand extends AbstractUserCommand
{

}