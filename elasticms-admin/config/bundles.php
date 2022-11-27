<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use EMS\CoreBundle\EMSCoreBundle;
use EMS\CommonBundle\EMSCommonBundle;
use EMS\ClientHelperBundle\EMSClientHelperBundle;
use EMS\FormBundle\EMSFormBundle;
use EMS\SubmissionBundle\EMSSubmissionBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
return [
    FrameworkBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    EMSCoreBundle::class => ['all' => true],
    EMSCommonBundle::class => ['all' => true],
    EMSClientHelperBundle::class => ['all' => true],
    EMSFormBundle::class => ['all' => true],
    EMSSubmissionBundle::class => ['all' => true],
    SensioFrameworkExtraBundle::class => ['all' => true],
    TwigExtraBundle::class => ['all' => true],
];
