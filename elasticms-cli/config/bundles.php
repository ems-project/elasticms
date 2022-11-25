<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use EMS\CommonBundle\EMSCommonBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
return [
    FrameworkBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    EMSCommonBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
];
