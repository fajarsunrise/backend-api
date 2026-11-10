<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\HeroController as ApiHeroController;

/**
 * Alias HeroController di root Http/Controllers
 * Mengarahkan seluruh fungsi ke Api\HeroController
 */
class HeroController extends ApiHeroController
{
}

