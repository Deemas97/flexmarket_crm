<?php
namespace App\Core;

include_once '../src/Core/View/ViewInterface.php';

class View implements ViewInterface
{
    public function __construct(
        private string $content = ""
    )
    {}

    public function getContent(): string
    {
        return $this->content;
    }
}