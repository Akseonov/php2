<?php

namespace Akseonov\Php2\http\Actions;

use Akseonov\Php2\http\Request;
use Akseonov\Php2\http\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;


}