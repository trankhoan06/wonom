<?php
namespace App\Controllers;

use \App\Models\Floorcate;
use \TypeRocket\Controllers\WPTermController;

class FloorcateController extends WPTermController
{
    protected $modelClass = Floorcate::class;
}