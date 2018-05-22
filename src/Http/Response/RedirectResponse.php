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
        $this->setHeader('Location', $url);
    }

    public function back()
    {
        $prev_url = $_SERVER['HTTP_REFERER'];
        $this->redirectTo($prev_url);
    }

}
