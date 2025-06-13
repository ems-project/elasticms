<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Core\Submission;

use Symfony\Component\OptionsResolver\OptionsResolver;
use EMS\Helpers\Standard\Json;

final readonly class ExportConfig
{
    /**
     * @param mixed[] $columns
     * @param string[] $emailsTo
     */
    public function __construct(
        public array $columns,
        public ?string $filter,
        public ?string $filename,
        public array $emailsTo,
        public string $subject,
        public ?string $format,
    ) {
    }

    public static function fromJson(string $json): self
    {
        $raw = Json::decode($json);
        $resolver = new OptionsResolver();

        $resolver->setRequired(['columns', 'emails-to', 'subject']);
        $resolver->setDefaults([
            'filter' => null,
            'filename' => null,
            'format' => null,
        ]);

        $resolver->setAllowedTypes('columns', 'array');
        $resolver->setAllowedTypes('emails-to', 'array');
        $resolver->setAllowedTypes('subject', 'string');
        $resolver->setAllowedTypes('filter', ['null', 'string']);
        $resolver->setAllowedTypes('filename', ['null', 'string']);
        $resolver->setAllowedTypes('format', ['null', 'string']);

        $resolver->setNormalizer('emails-to', function ($options, $value) {
            foreach ($value as $email) {
                if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new \InvalidArgumentException(sprintf('Invalid email: %s', $email));
                }
            }
            return $value;
        });

        $options = $resolver->resolve($raw);

        return new self(
            $options['columns'],
            $options['filter'],
            $options['filename'],
            $options['emails-to'],
            $options['subject'],
            $options['format']
        );
    }
}
