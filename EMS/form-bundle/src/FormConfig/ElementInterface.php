<?php

namespace EMS\FormBundle\FormConfig;

interface ElementInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getClassName(): string;
}
