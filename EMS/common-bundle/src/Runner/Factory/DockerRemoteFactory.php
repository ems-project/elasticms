<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\DockerRemote;
use EMS\CommonBundle\Runner\Service\RunnerInterface;

class DockerRemoteFactory extends AbstractFactory
{
    final public const string RUNNER_TYPE = 'docker-remote';
    final public const string RUNNER_REMOTE_DOCKER_BASE_URL = 'base-url';
    final public const string RUNNER_REMOTE_DOCKER_IMAGE = 'image';
    final public const string RUNNER_REMOTE_DOCKER_IMAGE_TAG = 'image-tag';
    final public const string RUNNER_REMOTE_DOCKER_ENV = 'env';
    final public const string RUNNER_REMOTE_DOCKER_SOCKET_PATH = 'socket-path';

    public function getRunnerType(): string
    {
        return self::RUNNER_TYPE;
    }

    public function createRunner(array $runnerConfig): RunnerInterface
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver->setDefaults([
            self::RUNNER_REMOTE_DOCKER_BASE_URL => 'http://localhost:2375',
            self::RUNNER_REMOTE_DOCKER_IMAGE_TAG => null,
            self::RUNNER_REMOTE_DOCKER_ENV => [],
            self::RUNNER_REMOTE_DOCKER_SOCKET_PATH => null,
        ])
            ->setRequired([
                self::RUNNER_REMOTE_DOCKER_IMAGE,
            ])
            ->setAllowedTypes(self::RUNNER_REMOTE_DOCKER_BASE_URL, ['string'])
            ->setAllowedTypes(self::RUNNER_REMOTE_DOCKER_IMAGE, ['string'])
            ->setAllowedTypes(self::RUNNER_REMOTE_DOCKER_IMAGE_TAG, ['string', 'null'])
            ->setAllowedTypes(self::RUNNER_REMOTE_DOCKER_ENV, ['array'])
            ->setAllowedTypes(self::RUNNER_REMOTE_DOCKER_SOCKET_PATH, ['string', 'null'])
        ;
        /** @var array{
         *     type: string,
         *     tag: string,
         *     worker-command: string|null,
         *     base-url: string,
         *     image: string,
         *     image-tag: string|null,
         *     env: string[],
         *     socket-path: string|null,
         *  } $resolvedConfig */
        $resolvedConfig = $resolver->resolve($runnerConfig);

        if (self::RUNNER_TYPE !== $resolvedConfig[self::RUNNER_CONFIG_TYPE]) {
            throw new \RuntimeException(\sprintf('Config mismatched for DockerRemote factory: %s', $resolvedConfig[self::RUNNER_CONFIG_TYPE]));
        }

        return new DockerRemote(
            $resolvedConfig[self::RUNNER_CONFIG_TAG],
            $resolvedConfig[self::RUNNER_CONFIG_WORKER_COMMAND],
            $resolvedConfig[self::RUNNER_REMOTE_DOCKER_BASE_URL],
            $resolvedConfig[self::RUNNER_REMOTE_DOCKER_IMAGE],
            $resolvedConfig[self::RUNNER_REMOTE_DOCKER_IMAGE_TAG],
            $resolvedConfig[self::RUNNER_REMOTE_DOCKER_ENV],
            $resolvedConfig[self::RUNNER_REMOTE_DOCKER_SOCKET_PATH],
        );
    }
}
