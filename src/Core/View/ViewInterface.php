<?php
namespace App\Core;

interface ViewInterface
{
    public function getContent(): string;
}