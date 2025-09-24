<?php

namespace App\Controller;
use App\Repository\BookRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/non-optimized', name: 'non_optimized_page')]
#[Template('non_optimized_page.html.twig')]
final readonly class NonOptimizedPage
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
