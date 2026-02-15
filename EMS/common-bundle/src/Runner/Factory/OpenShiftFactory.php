<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Runner\Factory;

use EMS\CommonBundle\Runner\Service\OpenShift;
use EMS\CommonBundle\Runner\Service\RunnerInterface;

class OpenShiftFactory extends AbstractFactory
{
    final public const string RUNNER_TYPE = 'openshift';
    final public const string RUNNER_OPENSHIFT_BASE_URL = 'base-url';
    final public const string RUNNER_OPENSHIFT_AUTH_KEY = 'auth-key';
    final public const string RUNNER_OPENSHIFT_AUTH_KEY_FILE = 'auth-key-file';
    final public const string RUNNER_OPENSHIFT_NAMESPACE = 'namespace';
    final public const string RUNNER_OPENSHIFT_IMAGE = 'image';
    final public const string RUNNER_OPENSHIFT_IMAGE_TAG = 'image-tag';
    final public const string RUNNER_OPENSHIFT_TTL_SECONDS_AFTER_FINISHED = 'ttl-seconds-after-finished';
    final public const string RUNNER_OPENSHIFT_BACKOFF_LIMIT = 'backoff-limit';
    final public const string RUNNER_OPENSHIFT_ACTIVE_DEADLINE_SECONDS = 'active-deadline-seconds';
    final public const string RUNNER_OPENSHIFT_LABELS = 'labels';
    final public const string RUNNER_OPENSHIFT_ENV = 'env';
    final public const string RUNNER_OPENSHIFT_VERIFY_SSL = 'verify-ssl';

    public function getRunnerType(): string
    {
        return self::RUNNER_TYPE;
    }

    public function createRunner(array $runnerConfig): RunnerInterface
    {
        $resolver = $this->getDefaultOptionsResolver();
        $resolver
            ->setDefaults([
                self::RUNNER_OPENSHIFT_IMAGE_TAG => null,
                self::RUNNER_OPENSHIFT_AUTH_KEY => null,
                self::RUNNER_OPENSHIFT_AUTH_KEY_FILE => null,
                self::RUNNER_OPENSHIFT_VERIFY_SSL => true,
                self::RUNNER_OPENSHIFT_TTL_SECONDS_AFTER_FINISHED => 3600,
                self::RUNNER_OPENSHIFT_BACKOFF_LIMIT => 0,
                self::RUNNER_OPENSHIFT_ACTIVE_DEADLINE_SECONDS => 60,
                self::RUNNER_OPENSHIFT_LABELS => [],
                self::RUNNER_OPENSHIFT_ENV => [],
            ])
            ->setRequired([
                self::RUNNER_OPENSHIFT_BASE_URL,
                self::RUNNER_OPENSHIFT_NAMESPACE,
                self::RUNNER_OPENSHIFT_IMAGE,
            ])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_BASE_URL, ['string'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_NAMESPACE, ['string'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_AUTH_KEY, ['string', 'null'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_AUTH_KEY_FILE, ['string', 'null'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_IMAGE, ['string'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_IMAGE_TAG, ['string', 'null'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_TTL_SECONDS_AFTER_FINISHED, ['int'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_BACKOFF_LIMIT, ['int'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_ACTIVE_DEADLINE_SECONDS, ['int'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_LABELS, ['array'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_ENV, ['array'])
            ->setAllowedTypes(self::RUNNER_OPENSHIFT_VERIFY_SSL, ['bool', 'string'])
        ;
        /** @var array{
         *     type: string,
         *     tag: string,
         *     worker-command: string|null,
         *     base-url: string,
         *     auth-key: string|null,
         *     auth-key-file: string|null,
         *     namespace: string,
         *     image: string,
         *     image-tag: string|null,
         *     ttl-seconds-after-finished: int,
         *     backoff-limit: int,
         *     active-deadline-seconds: int,
         *     labels: array<string, string>,
         *     env: array<array{name: string, value: string}>,
         *     verify-ssl: bool,
         *         } $resolvedConfig */
        $resolvedConfig = $resolver->resolve($runnerConfig);

        if (self::RUNNER_TYPE !== $resolvedConfig[self::RUNNER_CONFIG_TYPE]) {
            throw new \RuntimeException(\sprintf('Config mismatched for openshift factory: %s', $resolvedConfig[self::RUNNER_CONFIG_TYPE]));
        }

        $authKey = $resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY];
        if (null !== $resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY_FILE]) {
            if (!\file_exists($resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY_FILE])) {
                throw new \RuntimeException(\sprintf('File %s not found', $resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY_FILE]));
            }
            $authKey = \file_get_contents($resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY_FILE]);
            if (!$authKey) {
                throw new \RuntimeException(\sprintf('Unexpected file %s content', $resolvedConfig[self::RUNNER_OPENSHIFT_AUTH_KEY_FILE]));
            }
        }
        if (null === $authKey) {
            throw new \RuntimeException(\sprintf('An %s or %s is required.', self::RUNNER_OPENSHIFT_AUTH_KEY, self::RUNNER_OPENSHIFT_AUTH_KEY_FILE));
        }

        $imageTag = $this->getCommonVersionTag($resolvedConfig[self::RUNNER_OPENSHIFT_IMAGE_TAG]);

        return new OpenShift(
            $resolvedConfig[self::RUNNER_CONFIG_TAG],
            $resolvedConfig[self::RUNNER_CONFIG_WORKER_COMMAND],
            $resolvedConfig[self::RUNNER_OPENSHIFT_BASE_URL],
            $resolvedConfig[self::RUNNER_OPENSHIFT_VERIFY_SSL],
            $authKey,
            $resolvedConfig[self::RUNNER_OPENSHIFT_NAMESPACE],
            $resolvedConfig[self::RUNNER_OPENSHIFT_IMAGE],
            $imageTag,
            $resolvedConfig[self::RUNNER_OPENSHIFT_TTL_SECONDS_AFTER_FINISHED],
            $resolvedConfig[self::RUNNER_OPENSHIFT_BACKOFF_LIMIT],
            $resolvedConfig[self::RUNNER_OPENSHIFT_ACTIVE_DEADLINE_SECONDS],
            $resolvedConfig[self::RUNNER_OPENSHIFT_LABELS],
            $resolvedConfig[self::RUNNER_OPENSHIFT_ENV],
        );
    }
}
