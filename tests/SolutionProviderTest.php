<?php

namespace Junges\StackOverflowPTBR\Tests;

use Exception;
use Throwable;
use ReflectionClass;
use Facade\IgnitionContracts\BaseSolution;
use Junges\StackOverflowPTBR\StackOverflowPTBRSolutionProvider;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class SolutionProviderTest extends TestCase
{
    public function test_if_it_can_solve_every_throwable() : void
    {
        $this->assertTrue($this->callMethod('canSolve', [
            new Exception(),
        ]));
    }

    public function test_if_it_will_call_all_tested_methods_to_get_solutions() : void
    {
        $provider = $this->getMockBuilder(StackOverflowPTBRSolutionProvider::class)
            ->onlyMethods([
                'getUrl',
                'getResponse',
                'getQuestionsByResponse',
                'getSolutionByQuestion',
            ])
            ->getMock();

        $exception = new Exception('Exception message');

        $url = 'https://api.stackexchange.com/2.2/search/advanced?page=1&pagesize=5&order=desc&sort=relevance&site=pt.stackoverflow&accepted=True&filter=!9YdnSJ*_T&q=my+exception+message';
        $questions = [
            [
                'title' => 'question_title',
                'body_markdown' => 'question_body',
                'link' => 'https://pt.stackoverflow.com',
            ],
        ];
        $response = json_encode([
            'items' => $questions,
        ]);

        $provider->expects($this->once())->method('getUrl')->with($exception)->willReturn($url);
        $provider->expects($this->once())->method('getResponse')->with($url)->willReturn($response);
        $provider->expects($this->once())->method('getQuestionsByResponse')->with($response)->willReturn($questions);
        $provider->expects($this->once())->method('getSolutionByQuestion')->with($questions[0]);

        $provider->getSolutions($exception);
    }

    public function test_it_will_return_empty_array_on_exception() : void
    {
        $provider = $this->getMockBuilder(StackOverflowPTBRSolutionProvider::class)
            ->onlyMethods([
                'getUrl',
            ])
            ->getMock();

        $exception = new Exception('Exception message');

        $provider->expects($this->once())->method('getUrl')
            ->with($exception)
            ->willReturnCallback(function (Throwable $throwable) : string {
                throw $throwable;
            });

        $this->assertEquals([], $provider->getSolutions($exception));
    }

    public function test_if_it_will_return_stackoverflow_api_url_with_exception_class() : void
    {
        $this->assertEquals(
            'https://api.stackexchange.com/2.2/search/advanced?page=1&pagesize=5&order=desc&sort=relevance&site=pt.stackoverflow&accepted=True&filter=!9YdnSJ*_T&q=Symfony%5CComponent%5CRouting%5CException%5CRouteNotFoundException',
            $this->callMethod('getUrl', [
                new RouteNotFoundException(),
            ])
        );
    }

    public function test_it_will_return_null_on_curl_error() : void
    {
        $this->assertNull($this->callMethod('getResponse', [
            'https://unexisting-domain-to-test.curl-error',
        ]));
    }

    public function test_it_will_return_null_on_timeout() : void
    {
        $this->assertNull($this->callMethod('getResponse', [
            'https://reqres.in/api/users?delay=2',
        ]));
    }

    public function it_will_return_json(): void
    {
        $this->assertJson($this->callMethod('getResponse', [
            'https://reqres.in/api/users',
        ]));
    }

    public function test_it_will_return_empty_array_if_response_is_null(): void
    {
        $this->assertEquals([], $this->callMethod('getQuestionsByResponse', [
            null,
        ]));
    }

    public function test_it_will_return_empty_array_if_response_is_invalid_json(): void
    {
        $this->assertEquals([], $this->callMethod('getQuestionsByResponse', [
            '{"this is": "invalid JSON"',
        ]));
    }

    public function test_it_will_return_empty_array_if_no_items_key_present(): void
    {
        $this->assertEquals([], $this->callMethod('getQuestionsByResponse', [
            '{"this is": "valid JSON"}',
        ]));
    }

    public function test_it_will_return_questions_array(): void
    {
        $questions = [
            [
                'title' => 'question title',
                'body_markdown' => 'question body',
                'link' => 'https://stackoverflow.com',
            ],
        ];
        $this->assertEquals($questions, $this->callMethod('getQuestionsByResponse', [
            json_encode(['items' => $questions]),
        ]));
    }

    public function test_it_will_return_null_if_title_is_missing(): void
    {
        $question = [
            'body_markdown' => 'question body',
            'link' => 'https://pt.stackoverflow.com',
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_null_if_title_is_empty(): void
    {
        $question = [
            'title' => null,
            'body_markdown' => 'question body',
            'link' => 'https://pt.stackoverflow.com',
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_null_if_body_is_missing(): void
    {
        $question = [
            'title' => 'question title',
            'link' => 'https://pt.stackoverflow.com',
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_null_if_body_is_empty(): void
    {
        $question = [
            'title' => 'question title',
            'body_markdown' => '',
            'link' => 'https://stackoverflow.com',
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_null_if_link_is_missing(): void
    {
        $question = [
            'title' => 'question title',
            'body_markdown' => 'question body',
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_null_if_link_is_empty(): void
    {
        $question = [
            'title' => 'question title',
            'body_markdown' => 'question body',
            'link' => null,
        ];
        $this->assertNull($this->callMethod('getSolutionByQuestion', [
            $question,
        ]));
    }

    public function test_it_will_return_base_solution(): void
    {
        $question = [
            'title' => 'question title',
            'body_markdown' => 'question body',
            'link' => 'https://stackoverflow.com',
        ];
        /** @var BaseSolution $solution */
        $solution = $this->callMethod('getSolutionByQuestion', [
            $question,
        ]);
        $this->assertInstanceOf(BaseSolution::class, $solution);
        $this->assertEquals('question title', $solution->getSolutionTitle());
        $this->assertEquals('question body', $solution->getSolutionDescription());
        $this->assertEquals([
            'question title' => 'https://stackoverflow.com',
        ], $solution->getDocumentationLinks());
    }

    public function test_it_will_return_base_solution_with_processed_properties(): void
    {
        $question = [
            'title' => 'question &quot;title&quot;',
            'body_markdown' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            'link' => 'https://pt.stackoverflow.com',
        ];

        $solution = $this->callMethod('getSolutionByQuestion', [
            $question,
        ]);

        $this->assertInstanceOf(BaseSolution::class, $solution);
        $this->assertEquals('question "title"', $solution->getSolutionTitle());
        $this->assertEquals('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. ...', $solution->getSolutionDescription());
        $this->assertEquals([
            'question "title"' => 'https://pt.stackoverflow.com',
        ], $solution->getDocumentationLinks());
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \ReflectionException
     */
    protected function callMethod(string $method, array $args)
    {
        $class = new ReflectionClass(StackOverflowPTBRSolutionProvider::class);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs(new StackOverflowPTBRSolutionProvider(), $args);
    }
}
