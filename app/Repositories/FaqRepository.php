<?php

namespace App\Repositories;

use App\Contracts\Repositories\FaqRepository as FaqRepositoryContract;
use App\Models\FAQs\Faq;
use App\Models\Users\User;
use App\Repositories\Concerns\Prioritizable;

class FaqRepository extends Repository implements FaqRepositoryContract
{
    use Prioritizable;

    public function __construct(Faq $faq)
    {
        parent::__construct($faq);
    }

    /**
     * @inheritDoc
     */
    public function createMany(array $faqs, User $user): array
    {
        $createdFaqs = [];

        foreach ($faqs as $faq) {
            $faq['user_id'] = $user->id;
            $faq['last_editor_id'] = $user->id;

            if (! $newFaq = $this->model->create($faq)) {
                break;
            }

            $createdFaqs[] = $newFaq;
        }

        return $createdFaqs;
    }

    /**
     * @inheritDoc
     */
    public function welcome(array $relations = [])
    {
        return $this->model->where('welcome', true)->with($relations)->get();
    }
}
