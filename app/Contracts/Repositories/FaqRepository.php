<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\Concerns\Prioritizable;
use App\Models\FAQs\Faq;
use App\Models\Users\User;

/**
 * Interface FaqRepository
 * @package App\Contracts\Repositories
 */
interface FaqRepository extends Prioritizable
{
    public function __construct(Faq $faq);

    /**
     * @param array $faqs
     * @param User $user
     * @return array
     */
    public function createMany(array $faqs, User $user): array;

    /**
     * @return mixed
     */
    public function welcome(array $relations = []);
}
