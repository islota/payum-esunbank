<?php

namespace PayumTW\Esunbank\Action\Api;

use PayumTW\Esunbank\Request\Api\RefundTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class RefundTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request RefundTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty(['ONO']);

        $details->replace($this->api->refundTransaction((array) $details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RefundTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
