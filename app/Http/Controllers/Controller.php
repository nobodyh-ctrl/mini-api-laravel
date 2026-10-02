<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Mini API - Club',
    description: 'API REST con la información de los jugadores del club.'
)]
abstract class Controller
{
    //
}
