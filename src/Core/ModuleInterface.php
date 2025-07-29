<?php
namespace App\Core;

include_once '../src/Core/MessageBusInterface.php';

use App\Core\MessageBusInterface;

interface ModuleInterface
{
    public function process(MessageBusInterface $messageBus): MessageBusInterface;
}
