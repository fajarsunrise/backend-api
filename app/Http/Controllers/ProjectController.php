<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ProjectController as ApiProjectController;

/**
 * Alias ProjectController di root Http/Controllers
 * Mengarahkan seluruh fungsi ke Api\ProjectController
 */
class ProjectController extends ApiProjectController
{
}

