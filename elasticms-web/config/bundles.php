<?php

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Endroid\QrCodeBundle\EndroidQrCodeBundle;
use EMS\ClientHelperBundle\EMSClientHelperBundle;
use EMS\CommonBundle\EMSCommonBundle;
use EMS\FormBundle\EMSFormBundle;
use EMS\SubmissionBundle\EMSSubmissionBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;
return [
    DoctrineBundle::class => ['all' => true],
    DoctrineMigrationsBundle::class => ['all' => true],
    EndroidQrCodeBundle::class => ['all' => true],
    EMSClientHelperBundle::class => ['all' => true],
    EMSCommonBundle::class => ['all' => true],
    EMSFormBundle::class => ['all' => true],
    EMSSubmissionBundle::class => ['all' => true],
    SensioFrameworkExtraBundle::class => ['all' => true],
    DebugBundle::class => ['dev' => true, 'test' => true],
    FrameworkBundle::class => ['all' => true],
    MonologBundle::class => ['all' => true],
    SecurityBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    WebProfilerBundle::class => ['dev' => true, 'test' => true],
    TwigExtraBundle::class => ['all' => true],
];
