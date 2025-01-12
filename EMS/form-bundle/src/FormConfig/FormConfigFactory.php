<?php

declare(strict_types=1);

namespace EMS\FormBundle\FormConfig;

use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestInterface;
use EMS\ClientHelperBundle\Contracts\Elasticsearch\ClientRequestManagerInterface;
use EMS\CommonBundle\Common\EMSLink;
use EMS\CommonBundle\Elasticsearch\Document\Document;
use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Twig\TextRuntime;
use EMS\FormBundle\DependencyInjection\Configuration;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class FormConfigFactory
{
    private readonly ClientRequestInterface $client;
    /** @var array{type_form_validation: string, name: string, cacheable: bool, domain: string, load_from_json: bool, submission_field: string, theme_field: string, form_template_field: string, form_field: string, form_subform_field: string, type_form_choice: string, type_form_subform: string, type_form_markup: string, type_form_field: string, type: string} */
    private array $emsConfig;
    private readonly bool $loadFromJson;

    /**
     * @param array{type_form_validation: string, name: string, cacheable: bool, domain: string, load_from_json: bool, submission_field: string, theme_field: string, form_template_field: string, form_field: string, form_subform_field: string, type_form_choice: string, type_form_subform: string, type_form_markup: string, type_form_field: string, type: string} $emsConfig
     */
    public function __construct(
        ClientRequestManagerInterface $manager,
        private readonly AdapterInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly TextRuntime $textRuntime,
        array $emsConfig,
    ) {
        $this->client = $manager->getDefault();
        $this->loadFromJson = $emsConfig[Configuration::LOAD_FROM_JSON];
        $this->emsConfig = $emsConfig;
    }

    public function create(string $ouuid, string $locale, bool $useCache = true): FormConfig
    {
        $validityTags = $this->getValidityTags();
        $cacheKey = $this->client->getCacheKey(\sprintf('formconfig_%s_%s_', $ouuid, $locale));
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($this->emsConfig[Configuration::CACHEABLE] && $useCache && $cacheItem->isHit()) {
            $data = $cacheItem->get();

            $cacheValidityTags = $data['validity_tags'] ?? null;
            $formConfig = $data['form_config'] ?? null;

            if ($formConfig && $cacheValidityTags === $validityTags) {
                return $formConfig;
            }
        }

        if ($this->loadFromJson) {
            $formConfig = $this->buildFromJson($ouuid, $locale);
        } else {
            $formConfig = $this->buildFromDocuments($ouuid, $locale);
        }

        $this->cache->save($cacheItem->set([
            'validity_tags' => $validityTags,
            'form_config' => $formConfig,
        ]));

        return $formConfig;
    }

    private function getValidityTags(): string
    {
        $validityTags = '';
        foreach ($this->emsConfig as $key => $value) {
            if (!\str_starts_with($key, 'type')) {
                continue;
            }

            if (\is_string($value) && null !== $contentType = $this->client->getContentType($value)) {
                $validityTags .= $contentType->getCacheValidityTag();
            }
        }

        return $validityTags;
    }

    private function buildFromDocuments(string $ouuid, string $locale): FormConfig
    {
        $source = $this->client->get($this->emsConfig[Configuration::TYPE], $ouuid)['_source'];
        $formConfig = new FormConfig($ouuid, $locale, $this->client->getCacheKey());

        if (isset($source[$this->emsConfig[Configuration::THEME_FIELD]])) {
            $formConfig->addTheme($source[$this->emsConfig[Configuration::THEME_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::FORM_TEMPLATE_FIELD]])) {
            $formConfig->setTemplate($source[$this->emsConfig[Configuration::FORM_TEMPLATE_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::DOMAIN_FIELD]])) {
            $this->addDomain($formConfig, $source[$this->emsConfig[Configuration::DOMAIN_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::SUBMISSION_FIELD]])) {
            $formConfig->setSubmissions($source[$this->emsConfig[Configuration::SUBMISSION_FIELD]]);
        }

        if (isset($source[$this->emsConfig[Configuration::FORM_FIELD]])) {
            $this->addForm($formConfig, $source[$this->emsConfig[Configuration::FORM_FIELD]], $locale);
        }

        return $formConfig;
    }

    private function buildFromJson(string $ouuid, string $locale): FormConfig
    {
        $source = $this->client->get($this->emsConfig[Configuration::TYPE], $ouuid)['_source'];
        $formConfig = new FormConfig($ouuid, $locale, $this->client->getCacheKey());
        if (isset($source[$this->emsConfig[Configuration::THEME_FIELD]])) {
            $formConfig->addTheme($source[$this->emsConfig[Configuration::THEME_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::FORM_TEMPLATE_FIELD]])) {
            $formConfig->setTemplate($source[$this->emsConfig[Configuration::FORM_TEMPLATE_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::DOMAIN_FIELD]])) {
            if (\is_string($source[$this->emsConfig[Configuration::DOMAIN_FIELD]])) {
                $this->addDomain($formConfig, $source[$this->emsConfig[Configuration::DOMAIN_FIELD]]);
            } elseif (\is_array($source[$this->emsConfig[Configuration::DOMAIN_FIELD]])) {
                foreach ($source[$this->emsConfig[Configuration::DOMAIN_FIELD]] as $domain) {
                    $formConfig->addDomain($domain);
                }
            } else {
                throw new \RuntimeException('Unexpected domain type');
            }
        }
        if (isset($source[$this->emsConfig[Configuration::NAME_FIELD]])) {
            $formConfig->setName($source[$this->emsConfig[Configuration::NAME_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::SUBMISSION_FIELD]])) {
            $this->loadJsonSubmissions($formConfig, $source[$this->emsConfig[Configuration::SUBMISSION_FIELD]]);
        }
        if (isset($source[$this->emsConfig[Configuration::FORM_FIELD]])) {
            $this->loadFormFromJson($formConfig, $source[$this->emsConfig[Configuration::FORM_FIELD]], $locale);
        }

        return $formConfig;
    }

    private function addDomain(FormConfig $formConfig, string $emsLinkDomain): void
    {
        $domain = $this->getDocument($emsLinkDomain, ['allowed_domains'])->getSource();
        $allowedDomains = $domain['allowed_domains'] ?? [];

        foreach ($allowedDomains as $allowedDomain) {
            $formConfig->addDomain($allowedDomain['domain']);
        }
    }

    private function addFieldChoices(FieldConfig $fieldConfig, string $emsLink, string $locale): void
    {
        $choices = $this->getDocument($emsLink, ['values', 'labels_'.$locale, 'choice_sort']);

        $decoder = fn (string $input) => Json::decode($input);

        $source = $choices->getSource();
        $fieldChoicesConfig = new FieldChoicesConfig(
            $choices->getId(),
            $decoder($source['values']),
            $decoder($source['labels_'.$locale])
        );

        if (isset($source['choice_sort'])) {
            $fieldChoicesConfig->setSort($source['choice_sort']);
        }

        $fieldConfig->setChoices($fieldChoicesConfig);
    }

    /**
     * @param array<array<mixed>> $typeValidations
     * @param array<array<mixed>> $fieldValidations
     */
    private function addFieldValidations(FieldConfig $fieldConfig, array $typeValidations = [], array $fieldValidations = []): void
    {
        $allValidations = \array_merge($typeValidations, $fieldValidations);

        foreach ($allValidations as $v) {
            try {
                $validation = $this->getDocument($v['validation'], ['name', 'classname', 'default_value']);
                $fieldConfig->addValidation(new ValidationConfig(
                    $validation->getId(),
                    $validation->getSource()['name'],
                    $validation->getSource()['classname'],
                    $fieldConfig->getLabel(),
                    $validation->getSource()['default_value'] ?? null,
                    $v['value'] ?? null
                ));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }
    }

    private function addForm(FormConfig $formConfig, string $emsLinkForm, string $locale): void
    {
        $form = $this->getDocument($emsLinkForm, ['name', 'elements']);
        $formConfig->setName($form->getSource()['name']);
        $this->createElements($formConfig, $form->getSource()['elements'], $locale);
    }

    private function createElement(Document $element, string $locale, AbstractFormConfig $config): ElementInterface
    {
        return match ($element->getContentType()) {
            $this->emsConfig[Configuration::TYPE_FORM_FIELD] => $this->createFieldConfig($element, $locale, $config),
            $this->emsConfig[Configuration::TYPE_FORM_MARKUP] => new MarkupConfig($element->getId(), $element->getSource()['name'], $element->getSource()['markup_'.$locale]),
            $this->emsConfig[Configuration::TYPE_FORM_SUBFORM] => $this->createSubFormConfig($element, $locale, $config->getTranslationDomain()),
            default => throw new \RuntimeException(\sprintf('Implementation for configuration with name %s is missing', $element->getContentType())),
        };
    }

    /** @param string[] $elementEmsLinks */
    private function createElements(AbstractFormConfig $config, array $elementEmsLinks, string $locale): void
    {
        $elements = $this->getElements($elementEmsLinks);

        foreach ($elements as $element) {
            try {
                $element = $this->createElement($element, $locale, $config);
                $config->addElement($element);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }
    }

    private function createFieldConfig(Document $document, string $locale, AbstractFormConfig $config): FieldConfig
    {
        $source = $document->getSource();
        $fieldType = $this->getDocument($source['type'], ['name', 'class', 'classname', 'validations'])->getSource();
        $fieldConfig = new FieldConfig($document->getId(), $source['name'], $fieldType['name'], $fieldType['classname'], $config);

        if (isset($source['choices'])) {
            $this->addFieldChoices($fieldConfig, $source['choices'], $locale);
        }
        if (isset($source['default'])) {
            $fieldConfig->setDefaultValue($source['default']);
        }
        if (isset($source['placeholder_'.$locale])) {
            $fieldConfig->setPlaceholder($source['placeholder_'.$locale]);
        }
        if (isset($source['label_'.$locale])) {
            $fieldConfig->setLabel($source['label_'.$locale]);
        }
        if (isset($source['help_'.$locale])) {
            $fieldConfig->setHelp($source['help_'.$locale]);
        }
        if (isset($fieldType['class'])) {
            $fieldConfig->addClass($fieldType['class']);
        }

        $this->addFieldValidations($fieldConfig, $fieldType['validations'] ?? [], $source['validations'] ?? []);

        return $fieldConfig;
    }

    private function createSubFormConfig(Document $document, string $locale, string $translationDomain): SubFormConfig
    {
        $source = $document->getSource();
        $subFormConfig = new SubFormConfig(
            $document->getId(),
            $locale,
            $translationDomain,
            $source['name'],
            $source['label_'.$locale]
        );
        $this->createElements($subFormConfig, $source['elements'], $locale);

        return $subFormConfig;
    }

    /** @param string[] $fields */
    private function getDocument(string $emsLink, array $fields = []): Document
    {
        $document = $this->client->getByEmsKey($emsLink, $fields);

        if (!$document) {
            throw new \LogicException(\sprintf('Document type "%s" not found!', $emsLink));
        }

        return Document::fromArray($document);
    }

    /**
     * @param string[] $emsLinks
     *
     * @return Document[]
     */
    private function getElements(array $emsLinks): array
    {
        $emsLinks = \array_map(fn ($emsLink) => EMSLink::fromText($emsLink), $emsLinks);

        $documentIds = \array_reduce($emsLinks, function (array $carry, EMSLink $emsLink) {
            $carry[$emsLink->getContentType()][] = $emsLink->getOuuid();

            return $carry;
        }, []);

        $documents = [];
        foreach ($documentIds as $contentType => $ouuids) {
            $search = $this->client->getByOuuids($contentType, $ouuids);
            $documents = \array_merge($documents, $search['hits']['hits'] ?? []);
        }

        $indexedDocuments = \array_reduce($documents, function (array $carry, array $hit) {
            $carry[$hit['_id']] = Document::fromArray($hit);

            return $carry;
        }, []);

        return \array_reduce($emsLinks, function (array $carry, EMSLink $emsLink) use ($indexedDocuments) {
            if ($indexedDocuments[$emsLink->getOuuid()]) {
                $carry[] = $indexedDocuments[$emsLink->getOuuid()];
            }

            return $carry;
        }, []);
    }

    private function loadJsonSubmissions(FormConfig $formConfig, string $submissionsJson): void
    {
        $submissions = $this->textRuntime->jsonMenuNestedDecode($submissionsJson);
        foreach ($submissions as $submission) {
            $formConfig->addSubmissions(new SubmissionConfig($submission->getObject()['type'], $submission->getObject()['endpoint'], $submission->getObject()['message']));
        }
    }

    private function loadFormFromJson(FormConfig $formConfig, string $json, string $locale): void
    {
        $config = $this->textRuntime->jsonMenuNestedDecode($json);
        foreach ($config->getChildren() as $element) {
            try {
                $element = $this->createElementFromJson($element, $locale, $formConfig);
                $formConfig->addElement($element);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }
    }

    private function createElementFromJson(JsonMenuNested $element, string $locale, AbstractFormConfig $formConfig): ElementInterface
    {
        return match ($element->getType()) {
            $this->emsConfig[Configuration::TYPE_FORM_FIELD] => $this->createFieldConfigFromJson($element, $locale, $formConfig),
            $this->emsConfig[Configuration::TYPE_FORM_MARKUP] => $this->createMarkupFromJson($element, $locale),
            $this->emsConfig[Configuration::TYPE_FORM_SUBFORM] => $this->createSubFormConfigFromJson($element, $locale, $formConfig->getTranslationDomain()),
            default => throw new \RuntimeException(\sprintf('Implementation for configuration with name %s is missing', $element->getType())),
        };
    }

    private function createMarkupFromJson(JsonMenuNested $document, string $locale): MarkupConfig
    {
        return new MarkupConfig($document->getId(), $document->getObject()['name'] ?? $document->getLabel(), $document->getObject()[$locale]['markup'] ?? '', $document->getObject());
    }

    private function createFieldConfigFromJson(JsonMenuNested $document, string $locale, AbstractFormConfig $config): FieldConfig
    {
        $fieldConfig = new FieldConfig($document->getId(), $document->getObject()['name'], $document->getObject()['name'], $document->getObject()['classname'], $config, $document->getObject());

        if (isset($document->getObject()['class'])) {
            $fieldConfig->addClass($document->getObject()['class']);
        }
        if (isset($document->getObject()['default'])) {
            $fieldConfig->setDefaultValue($document->getObject()['default']);
        }
        if (isset($document->getObject()[$locale]['placeholder'])) {
            $fieldConfig->setPlaceholder($document->getObject()[$locale]['placeholder']);
        }
        if (isset($document->getObject()[$locale]['label'])) {
            $fieldConfig->setLabel($document->getObject()[$locale]['label']);
        }
        if (isset($document->getObject()[$locale]['help'])) {
            $fieldConfig->setHelp($document->getObject()[$locale]['help']);
        }
        $this->addFieldChoicesFromJson($fieldConfig, $document, $locale);
        $this->addFieldValidationsFromJson($fieldConfig, $document);

        return $fieldConfig;
    }

    private function createSubFormConfigFromJson(JsonMenuNested $jsonMenuNested, string $locale, string $translationDomain): SubFormConfig
    {
        $jsonObject = $jsonMenuNested->getObject();
        $emsLink = $jsonObject[$this->emsConfig[Configuration::FORM_SUBFORM_FIELD]];
        $document = $this->getDocument($emsLink);
        $source = $document->getSource();

        $subFormConfig = new SubFormConfig(
            $document->getId(),
            $locale,
            $translationDomain,
            $source['name'],
            $jsonObject[$locale]['label'] ?? $source['name']
        );

        $subFormJson = $source[$this->emsConfig[Configuration::FORM_FIELD]];

        $config = $this->textRuntime->jsonMenuNestedDecode($subFormJson);
        foreach ($config->getChildren() as $element) {
            try {
                $element = $this->createElementFromJson($element, $locale, $subFormConfig);
                $subFormConfig->addElement($element);
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }

        return $subFormConfig;
    }

    private function addFieldValidationsFromJson(FieldConfig $fieldConfig, JsonMenuNested $document): void
    {
        foreach ($document->getChildren() as $child) {
            if ($child->getType() !== $this->emsConfig[Configuration::TYPE_FORM_VALIDATION]) {
                continue;
            }
            try {
                $fieldConfig->addValidation(new ValidationConfig(
                    $child->getId(),
                    $child->getLabel(),
                    $child->getObject()['classname'],
                    $child->getLabel(),
                    null,
                    $child->getObject()['value'] ?? null
                ));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }
    }

    private function addFieldChoicesFromJson(FieldConfig $fieldConfig, JsonMenuNested $document, string $locale): void
    {
        $children = $document->getChildren();
        $choiceType = $this->emsConfig[Configuration::TYPE_FORM_CHOICE];
        $choiceChildren = \array_filter($children, static fn (JsonMenuNested $c) => $c->getType() === $choiceType);

        if (0 === \count($choiceChildren)) {
            return;
        }

        $values = $labels = [];
        $id = $sort = null;

        foreach ($choiceChildren as $child) {
            try {
                $id = $child->getId();
                $object = $child->getObject();

                $childValues = $object['values'] ?? null;
                $childLabels = $object[$locale]['labels'] ?? $childValues;

                if (null === $childValues) {
                    continue;
                }

                $values = [...$values, ...Json::decode($childValues)];
                $labels = [...$labels, ...Json::decode($childLabels)];

                $sort ??= $object['choice_sort'] ?? null;
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage(), [$e]);
            }
        }

        if (!empty($values) && null !== $id) {
            $fieldChoicesConfig = new FieldChoicesConfig(
                $id,
                $values,
                $labels
            );
            if (null !== $sort) {
                $fieldChoicesConfig->setSort($sort);
            }
            $fieldConfig->setChoices($fieldChoicesConfig);
        }
    }
}
