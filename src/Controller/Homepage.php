<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'homepage')]
#[Template('homepage.html.twig')]
final readonly class Homepage
{
    public function __invoke(): array
    {
        return [];
    }
}
