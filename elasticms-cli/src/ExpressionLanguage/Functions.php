<?php

declare(strict_types=1);

namespace App\CLI\ExpressionLanguage;

use App\CLI\Helper\Pa11yWrapper;
use EMS\CommonBundle\Common\Standard\Json;
use Ramsey\Uuid\Uuid;

class Functions
{
    public static function domToJsonMenu(string $html, string $tag, string $fieldName, string $typeName, ?string $labelField = null): string
    {
        if ('' === \preg_replace('!\s+!', ' ', $html)) {
            return '[]';
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        \libxml_use_internal_errors(true);
        if (true !== $document->loadHTML(\sprintf('<?xml encoding="utf-8" ?><body>%s</body>', $html))) {
            \libxml_clear_errors();
            throw new \RuntimeException(\sprintf('Unexpected error while loading this html %s', $html));
        }
        \libxml_clear_errors();

        $nodeList = $document->getElementsByTagName('body');
        if (1 !== $nodeList->count()) {
            throw new \RuntimeException('Unexpected number of body node');
        }
        $body = $nodeList->item(0);
        if (!$body instanceof \DOMNode) {
            throw new \RuntimeException('Unexpected XLIFF type');
        }
        $current = [];
        $output = [];
        $body = self::trimDivContainers($body);
        foreach ($body->childNodes as $child) {
            if ($child instanceof \DOMNode && $tag === $child->nodeName) {
                self::addNodeToJsonMenu($document, $current, $output, $tag, $fieldName, $typeName, $labelField);
                $current = [$child];
            } else {
                $current[] = $child;
            }
        }
        self::addNodeToJsonMenu($document, $current, $output, $tag, $fieldName, $typeName, $labelField);

        return Json::encode($output);
    }

    /**
     * @param \DOMNode[] $current
     * @param mixed[]    $output
     */
    private static function addNodeToJsonMenu(\DOMDocument $document, array $current, array &$output, string $tag, string $fieldName, string $typeName, ?string $labelField): void
    {
        if (empty($current)) {
            return;
        }
        $label = '';
        $body = '';
        foreach ($current as $child) {
            if ($child instanceof \DOMNode && $tag === $child->nodeName) {
                $label = $child->textContent;
            } else {
                $body .= $document->saveHTML($child);
            }
        }

        $item = [
            'id' => Uuid::uuid4()->toString(),
            'label' => $label,
            'type' => $typeName,
            'object' => [
                'label' => $label,
                $fieldName => $body,
            ],
        ];
        if (null !== $labelField) {
            $item['object'][$labelField] = $label;
        }
        $output[] = $item;
    }

    private static function trimDivContainers(\DOMElement $body): \DOMElement
    {
        $list = [];
        foreach ($body->childNodes as $child) {
            if ($child instanceof \DOMText && '' === \preg_replace('!\s+!', '', $child->textContent)) {
                continue;
            }
            $list[] = $child;
        }
        if (1 === \count($list) && $list[0] instanceof \DOMElement && 'div' === $list[0]->nodeName) {
            return self::trimDivContainers($list[0]);
        }

        return $body;
    }

    public static function pa11y(string $url): string
    {
        $wrapper = new Pa11yWrapper($url);
        $wrapper->start();

        return $wrapper->getOutput();
    }

    /**
     * @param array<string, string[]> $values,
     * @param array<string, string[]> $labels
     */
    public static function listToJsonMenuNested(array $values, string $fieldName, string $typeName, ?array $labels, ?string $labelField, bool $multiplex = false): string
    {
        $data = [];
        if ($multiplex) {
            foreach ($values as $key => $fields) {
                foreach ($fields as $keyField => $field) {
                    $item = [
                        'id' => Uuid::uuid4()->toString(),
                        'type' => $typeName,
                        'label' => $labels[$key][$keyField] ?? '',
                        'object' => [$key => [$fieldName => $field]],
                    ];
                    if (null !== $labelField) {
                        $item['object'][$key][$labelField] = $labels[$key][$keyField] ?? '';
                    }
                    $data[] = $item;
                }
            }
        } else {
            foreach ($values as $keyField => $field) {
                $item = [
                    'id' => Uuid::uuid4()->toString(),
                    'type' => $typeName,
                    'object' => [$fieldName => $field],
                ];
                if (null !== $labelField) {
                    $item['object'][$labelField] = $labels[$keyField] ?? '';
                }
                $data[] = $item;
            }
        }

        return JSON::encode($data);
    }

    /**
     * @param array<string, string[]> $values,
     * @param array<string, string[]> $keys,
     */
    public static function arrayToJsonMenuNested(array $values, array $keys): string
    {
        $data = [];
        foreach ($keys as $key => $key_val) {
            if (is_array($key_val)) {
             /*   dump($values[$key]);
                dump($key_val);*/
                $objects = self::mergeArrayForJsonMenuNested($values[$key], $key_val);
                foreach ($objects as $object) {
                    $item = [
                        'id' => Uuid::uuid4()->toString(),
                        'type' => $key,
                        'object' => $object
                    ];
                    $data[] = $item;
                }
            } else {
                foreach ($values[$key][$key_val] as $value)
                {
                    $item = [
                        'id' => Uuid::uuid4()->toString(),
                        'type' => $key,
                        'object' => [ $key_val => $value]
                    ];
                    $data[] = $item;
                }
            }
        }
      /*  dump('184');
        dump($data);*/
        return JSON::encode($data);
    }

    /**
     * @param array<string, string[]> $values,
     * @param array<string, string[]> $keys,
     * @return array<string, string[]>
     */
    static private function mergeArrayForJsonMenuNested(array $values, array $keys): array
    {   $data = [];
        foreach ($keys as $key => $key_val) {
            if (is_array($key_val)) {
                if (\array_key_exists($key, $values)) {
//                    dump('key 195');
//                    dump($key);
//                    dump($data);
                    $results = self::mergeArrayForJsonMenuNested($values[$key], $key_val);
                    $array = [];
                    foreach ($results as $k => $result) {
                        $array[$k][$key] = $result;
                    }
//                    dump('array');
//                    dump($array);
                    $data = \array_merge($data, $array);

                } else {
//                    dump('$key_val 198');
//                    dump($key_val);
                    $data = \array_merge_recursive($data, self::mergeArrayForJsonMenuNested($values, $key_val));
                }
            }
            else {
//                dump('$key_val 203');
//                dump($key_val);
                foreach ($values[$key_val] as $k => $value) {
//                    dump($k);
                    if (\array_key_exists($k, $data)) {
                        $data[$k] = \array_merge( $data[$k], [$key_val => $value]);
                    } else if (\array_key_exists('i_' . $k, $data)) {
                        $data['i_' . $k] = \array_merge( $data['i_' . $k], [$key_val => $value]);
                    } else {
                        $data['i_' . $k] = [$key_val => $value];
                    }
                }
            }
//            dump('show data for');
//            dump($data);
        }
        return $data;
    }
}
