<?php

namespace PayumTW\Esunbank\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Esunbank\Action\CaptureAction;

class CaptureActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture(new ArrayObject([])));
        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) {
            return $httpRequest instanceof GetHttpRequest;
        }));

        $request->shouldReceive('getToken')->once()->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo');

        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Esunbank\Request\Api\CreateTransaction'));
        $action->execute($request);
    }

    public function testCaptured()
    {
        $action = new CaptureAction();
        $request = new Capture(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = [
            'DATA' => 'foo',
            'MACD' => 'foo',
            'RC' => '00',
        ];

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) use ($response) {
            $httpRequest->request = $response;

            return $httpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('parseResponse')->once()->with($response)->andReturn($response);
        $api->shouldReceive('verifyHash')->once()->with($response)->andReturn(true);

        $action->execute($request);
        $this->assertSame($response, (array) $request->getModel());
    }

    public function testCaptureFail()
    {
        $action = new CaptureAction();
        $request = new Capture(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = [
            'DATA' => 'foo',
            'MACD' => 'foo',
            'RC' => '00',
        ];

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) use ($response) {
            $httpRequest->request = $response;

            return $httpRequest instanceof GetHttpRequest;
        }));

        $action->setApi(
            $api = m::mock('PayumTW\Esunbank\Api')
        );

        $api->shouldReceive('parseResponse')->once()->with($response)->andReturn($response);
        $api->shouldReceive('verifyHash')->once()->with($response)->andReturn(false);

        $action->execute($request);
        $this->assertSame(array_merge($response, ['RC' => 'G9']), (array) $request->getModel());
    }
}
