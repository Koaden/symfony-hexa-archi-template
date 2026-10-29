<?php

declare(strict_types=1);

namespace App\Application\Http\Controller\Demo;

use App\Application\Http\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NowController extends Controller
{
    #[Route('/demo/now', name: 'demo.now', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->renderHxTarget('demo/now.html.twig', [
            'now' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }
}
