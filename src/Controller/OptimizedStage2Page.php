<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\ReviewRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/optimized/stage-2', name: 'optimized_stage_2_page')]
#[Template('optimized-stage-2.html.twig')]
final readonly class OptimizedStage2Page
{
    public function __construct(
        private BookRepository $bookRepository,
        private ReviewRepository $reviewRepository,
    )
    {
    }

    public function __invoke(Request $request): array
    {
        $pager = new Pagerfanta(new QueryAdapter($this->bookRepository->getOrderedQueryBuilder()));
        $pager->setMaxPerPage($request->query->getInt('maxPerPage', 10));
        $pager->setCurrentPage($request->query->getInt('page', 1));

        $reviewsByBook = $this->reviewRepository->getBooksReviewsWithAuthorAndReviewCount(
            iterator_to_array($pager->getCurrentPageResults()),
        );

        return [
            'pager' => $pager,
            'reviews_by_book' => $reviewsByBook,
        ];
    }
}
