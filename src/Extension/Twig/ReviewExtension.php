<?php

namespace App\Extension\Twig;

use App\Repository\ReviewRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ReviewExtension extends AbstractExtension
{
    public function __construct(
        private readonly ReviewRepository $reviewRepository,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'get_book_reviews_with_author_and_review_count',
                $this->reviewRepository->getBookReviewsWithAuthorAndReviewCount(...),
            ),
        ];
    }
}
