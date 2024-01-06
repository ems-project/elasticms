<?php

declare(strict_types=1);

namespace EMS\FormBundle\Service\Confirmation;

use EMS\Helpers\Standard\Json;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfirmationRequest
{
    /** @var string */
    private $codeField;
    private readonly string $locale;
    /** @var string */
    private $token;
    private readonly string $value;

    public function __construct(Request $request)
    {
        $json = Json::decode((string) $request->getContent());
        $data = $this->resolveOptions(\array_filter($json));

        $this->codeField = $data['code-field'];
        $this->locale = $request->getLocale();
        $this->token = $data['token'];
        $this->value = $data['value'] ?? '';
    }

    public function getCodeField(): string
    {
        return $this->codeField;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    private function resolveOptions(array $options): array
    {
        $jsonResolver = new OptionsResolver();
        $jsonResolver
            ->setDefaults(['value' => null])
            ->setRequired(['code-field', 'token']);

        return $jsonResolver->resolve($options);
    }
}
