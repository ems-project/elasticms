<?php

declare(strict_types=1);

namespace App\CLI\Tests\Command\Web;

use App\CLI\Command\Web\DeadLinksCommand;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Contracts\Spreadsheet\SpreadsheetGeneratorServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DeadLinksCommandTest extends TestCase
{
    /**
     * @param array{
     *     level: string,
     *     url: string,
     *     scheme: string,
     *     status: int,
     *     message: string,
     *     referer: string,
     *     text: string,
     *     location: ?string,
     *     error: ?string
     * } $input
     */
    #[DataProvider('provideProblemDescriptionCases')]
    public function testGetProblemDescriptionReturnsTranslatedMessageForConfiguredLocale(array $input, string $translation): void
    {
        $command = new DeadLinksCommand(
            $this->createStub(AdminHelper::class),
            $this->createStub(SpreadsheetGeneratorServiceInterface::class),
            $this->createStub(TranslatorInterface::class),
        );

        $method = new \ReflectionMethod($command, 'getProblemDescription');

        $this->assertSame(
            $translation,
            $method->invoke(
                $command,
                $input['level'],
                $input['url'],
                $input['scheme'],
                $input['status'],
                $input['message'],
                $input['referer'],
                $input['text'],
                $input['location'],
                $input['error'],
            )->getMessage()
        );
    }

    /**
     * @return iterable<string, array{
     *     input: array{
     *         level: string,
     *         url: string,
     *         scheme: string,
     *         status: int,
     *         message: string,
     *         referer: string,
     *         text: string,
     *         location: ?string,
     *         error: ?string
     *     },
     *     translation: string
     * }>
     */
    public static function provideProblemDescriptionCases(): iterable
    {
        yield 'Broken link' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'https',
                'status' => 404,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.page-not-found',
        ];
        yield 'Problem without solution' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'https',
                'status' => 999,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.problem-witout-solution',
        ];
        yield 'Document missing or not published' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'ems',
                'status' => 0,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.missing-document',
        ];
        yield 'Internal server error' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'http',
                'status' => 500,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.internal-server-error',
        ];
        yield 'Server gone 502' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'http',
                'status' => 502,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.server-gone',
        ];
        yield 'Server gone 503' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'http',
                'status' => 503,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.server-gone',
        ];
        yield 'Server gone 504' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'http',
                'status' => 504,
                'message' => 'Broken link',
                'referer' => 'https://referer.test',
                'text' => 'Link text',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.server-gone',
        ];
        yield 'Permanent redirect 301' => [
            'input' => [
                'level' => 'Error',
                'url' => 'https://example.test/missing',
                'scheme' => 'http',
                'status' => 301,
                'message' => 'Broken link',
                'referer' => 'http://referer.test',
                'text' => 'Permanent redirect',
                'location' => 'https://referer.test',
                'error' => null,
            ],
            'translation' => 'web.audit.permanent-redirect',
        ];
        yield 'Blocked by enterprise policy' => [
            'input' => [
                'level' => 'Warning',
                'url' => 'https://example.test/blocked',
                'scheme' => 'https',
                'status' => 302,
                'message' => 'Redirection to location',
                'referer' => 'https://referer.test',
                'text' => 'Blocked link',
                'location' => 'https://block.sse.cisco.com/swg?server=swg-ngi.example.test&url=https%3A%2F%2Fexample.test%2Fblocked',
                'error' => null,
            ],
            'translation' => 'web.audit.blocked-by-enterprise-policy',
        ];

        foreach ([
            'block.opendns.com',
            'malware.opendns.com',
            'phish.opendns.com',
            'www1.dlinksearch.com',
            'bpb.opendns.com',
        ] as $host) {
            yield \sprintf('Cisco Umbrella block page %s', $host) => [
                'input' => [
                    'level' => 'Warning',
                    'url' => 'https://example.test/blocked',
                    'scheme' => 'https',
                    'status' => 302,
                    'message' => 'Redirection to location',
                    'referer' => 'https://referer.test',
                    'text' => 'Blocked link',
                    'location' => \sprintf('https://%s/?url=https%%3A%%2F%%2Fexample.test%%2Fblocked', $host),
                    'error' => null,
                ],
                'translation' => 'web.audit.blocked-by-enterprise-policy',
            ];
        }

        yield 'FortiGuard block page url.fortinet.net' => [
            'input' => [
                'level' => 'Warning',
                'url' => 'https://example.test/blocked',
                'scheme' => 'https',
                'status' => 302,
                'message' => 'Redirection to location',
                'referer' => 'https://referer.test',
                'text' => 'Blocked link',
                'location' => 'https://url.fortinet.net/rate/submit.php?cat=34&id=49074a1c590f6a297a336925287e3e6d&loc=example.test%2Fblocked&ver=8',
                'error' => null,
            ],
            'translation' => 'web.audit.blocked-by-enterprise-policy',
        ];

        yield 'Domain could be resolved (HTTP)' => [
            'input' => [
                'level' => 'Warning',
                'url' => 'https://example.test/blocked',
                'scheme' => 'http',
                'status' => 0,
                'message' => 'Redirection to location',
                'referer' => 'https://referer.test',
                'text' => 'Blocked link',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.server-gone',
        ];

        yield 'Domain could be resolved (HTTPS)' => [
            'input' => [
                'level' => 'Warning',
                'url' => 'https://example.test/blocked',
                'scheme' => 'https',
                'status' => 0,
                'message' => 'Redirection to location',
                'referer' => 'https://referer.test',
                'text' => 'Blocked link',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.server-gone',
        ];

        yield 'Local file' => [
            'input' => [
                'level' => 'Warning',
                'url' => 'https://example.test/blocked',
                'scheme' => 'file',
                'status' => 0,
                'message' => 'Redirection to location',
                'referer' => 'https://referer.test',
                'text' => 'Blocked link',
                'location' => null,
                'error' => null,
            ],
            'translation' => 'web.audit.local-file',
        ];
    }
}
