<?php

declare(strict_types=1);

namespace App\Application\Http\Controller\Demo;

use App\Application\Http\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends Controller
{
    #[Route('/', name: 'demo.index')]
    public function __invoke(): Response
    {
        return $this->renderHxTarget('demo/index.html.twig');
    }
}
