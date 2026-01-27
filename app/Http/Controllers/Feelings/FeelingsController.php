<?php
namespace App\Http\Controllers\Feelings;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeelingResource;
use App\Repositories\Feelings\FeelingsRepository;
use Inertia\Inertia;

class FeelingsController extends Controller
{
    protected FeelingsRepository $feelingsRepository;

    public function __construct(FeelingsRepository $feelingsRepository)
    {
        $this->feelingsRepository = $feelingsRepository;
    }

    public function index() {
        $feelings = $this->feelingsRepository->getAllFeelings();

        return Inertia::render('Feelings', [
            'feelings' => FeelingResource::collection($feelings)->resolve(),
        ]);
    }
}