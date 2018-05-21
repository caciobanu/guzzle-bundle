<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\Tests\DependencyInjection;

use Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Configuration;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * @covers \Caciobanu\Symfony\GuzzleBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    public function testConfigMinimal(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration(),
            [
                'caciobanu_guzzle' => [
                    'clients' => [
                        'default' => [
                            'base_uri' => 'https://google.ro',
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(
            [
                'clients' => [
                    'default' => [
                        'client_class' => Client::class,
                        'base_uri'     => 'https://google.ro',
                        'logging'      => false,
                        'options'      => [],
                    ],
                ],
            ],
            $config
        );
    }

    public function testConfigClientClass(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration(),
            [
                'caciobanu_guzzle' => [
                    'clients' => [
                        'default' => [
                            'client_class' => Client::class,
                            'base_uri'     => 'https://test.com/path/?query=1#fragment',
                        ],
                    ],
                ],
            ]
        );

        $this->assertEquals(
            [
                'clients' => [
                    'default' => [
                        'client_class' => Client::class,
                        'base_uri'     => 'https://test.com/path/?query=1#fragment',
                        'logging'      => false,
                        'options'      => [],
                    ],
                ],
            ],
            $config
        );
    }

    public function testConfigWithOptions(): void
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration(),
            [
                'caciobanu_guzzle' => [
                    'clients' => [
                        'default' => [
                            'client_class' => Client::class,
                            'base_uri'     => 'https://test.com/path/?query=1#fragment',
                            'logging'      => null,
                            'options'      => [
                                'timeout'      => 30,
                                'read_timeout' => 30,
                                'headers'      => [
                                    'X-Header' => 'foo',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );


        $this->assertEquals(
            [
                'clients' => [
                    'default' => [
                        'client_class' => Client::class,
                        'base_uri'     => 'https://test.com/path/?query=1#fragment',
                        'logging'      => false,
                        'options'      => [
                            'timeout'      => 30,
                            'read_timeout' => 30,
                            'headers'      => [
                                'X-Header' => 'foo',
                            ],
                        ],
                    ],
                ],
            ],
            $config
        );
    }
}
