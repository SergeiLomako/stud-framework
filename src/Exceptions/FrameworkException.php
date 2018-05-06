<?php

namespace Mindk\Framework\Exceptions;

use Mindk\Framework\Http\Response\Response;
use Mindk\Framework\Http\Response\JsonResponse;

/**
 * Common Class FrameworkException
 * @package Exceptions
 */
abstract class FrameworkException extends \Exception
{
    /**
     * Make ready to use http response
     *
     * @return Response
     */
    public function toResponse(): Response {

        return new JsonResponse(['error' => $this->getMessage()], 500);
    }
}