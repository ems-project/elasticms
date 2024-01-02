<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true],
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
    EMS\AdminUIBundle\EMSAdminUIBundle::class => ['all' => true],
    EMS\CoreBundle\EMSCoreBundle::class => ['all' => true],
    EMS\CommonBundle\EMSCommonBundle::class => ['all' => true],
    EMS\ClientHelperBundle\EMSClientHelperBundle::class => ['all' => true],
    EMS\FormBundle\EMSFormBundle::class => ['all' => true],
    EMS\SubmissionBundle\EMSSubmissionBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
];
