<?php

namespace App\Http\Clients\BetsAPI\Responses\Bet365\Entities;

trait Paginated
{
    public function hasPages(): bool
    {
        return ! is_null($this->pager);
    }

    public function pages(): int
    {
        if (! $this->hasPages()) {
            return 0;
        }

        return (int) ceil($this->totalOfItems() / $this->perPage());
    }

    public function hasMorePages(): bool
    {
        if (! $this->hasPages()) {
            return false;
        }

        return $this->getPage() < $this->pages();
    }

    public function getPage(): int
    {
        return $this->pager->page;
    }

    public function perPage(): int
    {
        return $this->pager->per_page;
    }

    public function totalOfItems(): int
    {
        return $this->pager->total;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function nextPage(): int
    {
        if (! $this->hasMorePages()) {
            throw new \Exception("Insufficient total of pages");
        }

        return $this->getPage() + 1;
    }
}
