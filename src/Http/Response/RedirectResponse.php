<?php

namespace Mindk\Framework\Http\Response;

/**
 * Class RedirectResponse
 *
 * @package Mindk\Framework\Http\Response
 */
class RedirectResponse extends Response
{
    /**
     * Redirect to url
     *
     * @param $url
     */
    public function redirectTo($url) {

        $this->setHeader('Location', $url);
    }

    /**
     * Return back
     */
    public function back() {

        $prev_url = $_SERVER['HTTP_REFERER'];
        $this->redirectTo($prev_url);
    }

}
