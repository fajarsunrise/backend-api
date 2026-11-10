<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AboutController as ApiAboutController;

/**
 * Alias AboutController di root Http/Controllers
 * Mengarahkan seluruh fungsi ke Api\AboutController
 */
class AboutController extends ApiAboutController
{
}

