<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/optimized/stage-1', name: 'optimized_stage_1_page')]
#[Template('optimized-stage-1.html.twig')]
final readonly class OptimizedStage1Page
{
    public function __construct(
        private BookRepository $bookRepository,
    )
    {
    }

    public function __invoke(Request $request): array
    {
        $pager = new Pagerfanta(new QueryAdapter($this->bookRepository->getOrderedQueryBuilder()));
        $pager->setMaxPerPage($request->query->getInt('maxPerPage', 10));
        $pager->setCurrentPage($request->query->getInt('page', 1));

        return [
            'pager' => $pager,
        ];
    }
}
