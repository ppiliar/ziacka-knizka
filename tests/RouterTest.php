<?php
/**
 * Testy triedy Router
 */

namespace App;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {

    public function testGetControllerByName() {

    }

    public function testExecuteAction() {

    }

    /**
     * @dataProvider urlProvider
     */
    public function testParseUrl($url, $expectedResult) {
        $this->assertEquals($expectedResult, Request::parseUrl($url));
    }

    /**
     * Data provider pre testParseUrl
     * @return array
     */
    public function urlProvider() {
        return [
            'url1' => [
                'url'            => "/index/index",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => []
                ]
            ],
            'url2' => [
                'url'            => "/index/",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => []
                ]
            ],
            'url3' => [
                'url'            => "/index",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => []
                ]
            ],
            'url4' => [
                'url'            => "/index/index/p1/v1",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => [
                        'p1' => 'v1'
                    ]
                ]
            ],
            'url5' => [
                'url'            => "/index/index/p1/",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => [
                        'p1' => null
                    ]
                ]
            ],
            'url6' => [
                'url'            => "/index/index/p1",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => [
                        'p1' => null
                    ]
                ]
            ],
            'url7' => [
                'url'            => "/index/index/p1/v1?a=5",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => [
                        'p1' => 'v1'
                    ]
                ]
            ],
            'url8' => [
                'url'            => "/",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => []
                ]
            ],
            'url9' => [
                'url'            => "",
                'expectedResult' => [
                    'controller' => 'index',
                    'action'     => 'index',
                    'params'     => []
                ]
            ],
            'url10' => [
                'url'            => "/links",
                'expectedResult' => [
                    'controller' => 'links',
                    'action'     => 'index',
                    'params'     => []
                ]
            ]
        ];
    }
}
