<?php

namespace Mindk\Framework\Exceptions;

use Mindk\Framework\Http\Response\Response;

/**
 * Class NotFoundException
 * @package Exceptions
 */
class NotFoundException extends FrameworkException
{
    /**
     * @inheritdoc
     */
    public function toResponse(): Response
    {
        $resp = parent::toResponse();
        $resp->code = 404;

        return $resp;
    }
}