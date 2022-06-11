<?php

namespace App\Services;

use App\Contracts\Repositories\MatchRepository;
use App\Http\Requests\BetSlip\UpdateRequest;
use App\Contracts\Services\BetSlipService as BetSlipServiceInterface;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;

class BetSlipService implements BetSlipServiceInterface
{
    private $matchRepository;

    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @inheritDoc
     */
    public function update(UpdateRequest $request)
    {
        $validated = $request->input('selections') ?? [];

        $selections = [];

        foreach ($validated as $selection) {
            $selections[] = $this->updateSelection($selection);
        }

        return $selections;
    }

    private function updateSelection($selection): array
    {
        $selectionId = $selection['id'];
        $matchId = $selection['match_id'];

        $match = $this->matchRepository->get($matchId);
        $marketSelection = MarketOdd::query()->with('market')->find($selectionId);

        return [
            'id' => $selectionId,
            'match_id' => $matchId,
            'odds' => $marketSelection->odds,
            'changed' => $marketSelection->odds <> $selection['odds'],
            'suspended' => $this->isSelectionSuspended($marketSelection),
            'available' => $this->isSelectionAvailable($marketSelection, $match),
        ];
    }

    private function isSelectionSuspended(MarketOdd $selection): bool
    {
        return $selection->is_suspended || !$selection->market->on_live_betting;
    }

    private function isSelectionAvailable(MarketOdd $selection, Event $match): bool
    {
        return ($match->isLive() && $selection->is_live) || ($match->isNotStarted() && !$selection->is_live);
    }
}
