<?php
namespace Mlaphp;

class FakeResponse extends Response
{
    public $fake_headers;

    public function fakeHeader()
    {
        $args = func_get_args();
        array_unshift($args, array($this, 'sendFakeHeader'));
        $this->headers[] = $args;
    }

    public function sendFakeHeader($string)
    {
        $this->fake_headers .= $string;
    }
}
