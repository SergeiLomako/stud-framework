<?php

namespace Mindk\Framework\Http\Response;

/**
 * Class RedirectResponse
 *
 * @package Mindk\Framework\Http\Response
 */
class RedirectResponse extends Response
{
    public function redirectTo($url)
    {
        header("Location: $url");
    }

    public function back()
    {
        $prev_url = $_SERVER['HTTP_REFERER'];
        header("Location: $prev_url");
    }

}