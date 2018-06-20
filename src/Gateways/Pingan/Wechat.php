<?php
namespace Runner\NezhaCashier\Gateways\Pingan;

use Runner\NezhaCashier\Requests\Charge;
use Runner\NezhaCashier\Requests\Close;
use Runner\NezhaCashier\Requests\Query;
use Runner\NezhaCashier\Requests\Refund;

abstract class Wechat extends AbstractPinganGateway
{
    public function refund(Refund $form): array
    {
        // TODO: Implement refund() method.
    }

    public function close(Close $form): array
    {
        // TODO: Implement close() method.
    }

    public function query(Query $form): array
    {
        // TODO: Implement query() method.
    }

    public function chargeNotify(array $receives): array
    {
        // TODO: Implement chargeNotify() method.
    }

    public function refundNotify(array $receives): array
    {
        // TODO: Implement refundNotify() method.
    }

    public function closeNotify(array $receives): array
    {
        // TODO: Implement closeNotify() method.
    }

    public function verify($receives): bool
    {
        // TODO: Implement verify() method.
    }

    public function success(): string
    {
        // TODO: Implement success() method.
    }

    public function fail(): string
    {
        // TODO: Implement fail() method.
    }

    public function receiveNotificationFromRequest()
    {
        // TODO: Implement receiveNotificationFromRequest() method.
    }

    public function convertNotificationToArray($receives): array
    {
        // TODO: Implement convertNotificationToArray() method.
    }
}