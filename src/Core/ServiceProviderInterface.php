<?php
namespace App\Core;

include_once '../src/Core/ServiceInterface.php';

use App\Core\ServiceInterface;

interface ServiceProviderInterface
{
    public function getServiceName(): string;
    public function getService(): ServiceInterface;
}