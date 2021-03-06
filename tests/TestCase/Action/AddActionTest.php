<?php
namespace Crud\Test\TestCase\Action;

use Cake\Routing\DispatcherFactory;
use Cake\Routing\Router;
use Crud\TestSuite\IntegrationTestCase;

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class AddActionTest extends IntegrationTestCase
{

    /**
     * fixtures property
     *
     * @var array
     */
    public $fixtures = ['plugin.crud.blogs'];

    /**
     * Table class to mock on
     *
     * @var string
     */
    public $tableClass = 'Crud\Test\App\Model\Table\BlogsTable';

    /**
     * Test the normal HTTP GET flow of _get
     *
     * @return void
     */
    public function testActionGet()
    {
        $this->get('/blogs/add');
        $result = $this->_response->body();

        $expected = ['tag' => 'legend', 'content' => 'New Blog'];
        $this->assertTag($expected, $result, 'legend do not match the expected value');

        $expected = ['id' => 'id', 'attributes' => ['value' => '']];
        $this->assertTag($expected, $result, '"id" do not match the expected value');

        $expected = ['id' => 'name', 'attributes' => ['value' => '']];
        $this->assertTag($expected, $result, '"name" do not match the expected value');

        $expected = ['id' => 'body', 'attributes' => ['value' => '']];
        $this->assertTag($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test the normal HTTP GET flow of _get with query args
     *
     * Providing ?name=test should fill out the value in the 'name' input field
     *
     * @return void
     */
    public function testActionGetWithQueryArgs()
    {
        $this->get('/blogs/add?name=test');
        $result = $this->_response->body();

        $expected = ['tag' => 'legend', 'content' => 'New Blog'];
        $this->assertTag($expected, $result, 'legend do not match the expected value');

        $expected = ['id' => 'id', 'attributes' => ['value' => '']];
        $this->assertTag($expected, $result, '"id" do not match the expected value');

        $expected = ['id' => 'name', 'attributes' => ['value' => 'test']];
        $this->assertTag($expected, $result, '"name" do not match the expected value');

        $expected = ['id' => 'body', 'attributes' => ['value' => '']];
        $this->assertTag($expected, $result, '"body" do not match the expected value');
    }

    /**
     * Test POST will create a record
     *
     * @return void
     */
    public function testActionPost()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);

        $this->assertRedirect('/blogs');
    }

    /**
     * Test POST will create a record and redirect to /blogs/add again
     * if _POST['_add'] is present
     *
     * @return void
     */
    public function testActionPostWithAddRedirect()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_add' => 1
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertRedirect('/blogs/add');
    }

    /**
     * Test POST will create a record and redirect to /blogs/edit/$id
     * if _POST['_edit'] is present
     *
     * @return void
     */
    public function testActionPostWithEditRedirect()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Successfully created blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message success', 'original' => 'Successfully created blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body',
            '_edit' => 1
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertRedirect('/blogs/edit/6');
    }

    /**
     * Test POST with unsuccessful save()
     *
     * @return void
     */
    public function testActionPostErrorSave()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs = $this->getMockForModel(
                    $this->tableClass,
                    ['save'],
                    ['alias' => 'Blogs', 'table' => 'blogs']
                );

                $this->_controller->Blogs
                    ->expects($this->once())
                    ->method('save')
                    ->will($this->returnValue(false));
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->post('/blogs/add', [
            'name' => 'Hello World',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);
        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);
    }

    /**
     * Test POST with validation errors
     *
     * @return void
     */
    public function testActionPostValidationErrors()
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->once())
                    ->method('set')
                    ->with(
                        'Could not create blog',
                        [
                            'element' => 'default',
                            'params' => ['class' => 'message error', 'original' => 'Could not create blog'],
                            'key' => 'flash'
                        ]
                    );

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->post('/blogs/add', [
            'name' => 'Hello',
            'body' => 'Pretty hot body'
        ]);

        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRender']);

        $this->assertFalse($this->_subject->success);
        $this->assertFalse($this->_subject->created);

        $expected = [
            'class' => 'error-message',
            'content' => 'Name need to be at least 10 characters long'
        ];
        $this->assertTag($expected, $this->_response->body(), 'Could not find validation error in HTML');
    }

    /**
     * Data provider with GET and DELETE verbs
     *
     * @return array
     */
    public function apiGetHttpMethodProvider()
    {
        return [
            ['get'],
            ['delete']
        ];
    }

    /**
     * Test HTTP & DELETE verbs using API Listener
     *
     * @dataProvider apiGetHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiGet($method)
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Crud->addListener('api', 'Crud.Api');
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        Router::extensions(['json']);
        Router::scope('/', function ($routes) {
            $routes->extensions(['json']);
            $routes->fallbacks();
        });

        $this->{$method}('/blogs/add.json');

        $this->assertResponseError();
        $this->assertResponseContains($this->_response->body(), 'Wrong request method');
    }

    /**
     * Data provider with PUT and POST verbs
     *
     * @return array
     */
    public function apiUpdateHttpMethodProvider()
    {
        return [
            ['put'],
            ['post']
        ];
    }

    /**
     * Test POST & PUT verbs using API Listener
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreate($method)
    {
        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');
                $this->_controller->RequestHandler->ext = 'json';
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        Router::extensions('json');

        $this->{$method}('/blogs/add.json', [
            'name' => '6th blog post',
            'body' => 'Amazing blog post'
        ]);
        $this->assertEvents(['beforeSave', 'afterSave', 'setFlash', 'beforeRedirect']);
        $this->assertTrue($this->_subject->success);
        $this->assertTrue($this->_subject->created);
        $this->assertEquals(
            ['success' => true, 'data' => ['id' => 6]],
            json_decode($this->_response->body(), true)
        );
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation error
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreateError($method)
    {
        Router::extensions('json');

        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short',
            'body' => 'Amazing blog post'
        ]);

        $this->assertResponseCode(412);
        $this->assertResponseContains($this->_response->body(), 'A validation error occurred');
    }

    /**
     * Test POST & PUT verbs using API Listener
     * with data validation errors
     *
     * @dataProvider apiUpdateHttpMethodProvider
     * @param  string $method
     * @return void
     */
    public function testApiCreateErrors($method)
    {
        Router::extensions('json');

        $this->_eventManager->attach(
            function ($event) {
                $this->_controller->Flash = $this->getMock(
                    'Cake\Controller\Component\Flash',
                    ['set']
                );

                $this->_controller->Flash
                    ->expects($this->never())
                    ->method('set');

                $this->_subscribeToEvents($this->_controller);

                $this->_controller->Crud->addListener('api', 'Crud.Api');

                $this->_controller->Blogs
                    ->validator()
                    ->requirePresence('name')
                    ->requirePresence('body')
                    ->add('name', [
                        'length' => [
                            'rule' => ['minLength', 10],
                            'message' => 'Name need to be at least 10 characters long',
                        ]
                    ]);
            },
            'Dispatcher.beforeDispatch',
            ['priority' => 1000]
        );

        $this->{$method}('/blogs/add.json', [
            'name' => 'too short'
        ]);

        $this->assertResponseError();
        $this->assertResponseContains('2 validation errors occurred');
    }
}
