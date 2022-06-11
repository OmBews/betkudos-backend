<?php

namespace App\Jobs;

use App\Models\Casino\Games\Game;
use App\Models\Casino\Providers\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSlotegratorGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private object $game) {}

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $game = Game::where('aggregator_uuid', $this->game->uuid)->first();

        if (!$game) {
            $game = new Game();

            $game->name = $this->game->name;
            $game->provider = $this->game->provider;
            $game->aggregator_uuid = $this->game->uuid;
            $game->type = $this->game->type;
            $game->technology = $this->game->technology;
            $game->has_lobby = $this->game->has_lobby;
            $game->is_mobile = $this->game->is_mobile;
            $game->freespin_valid_until_full_day = $this->game->freespin_valid_until_full_day;
            $game->image = $this->game->image; // '/default.jpg';

            $game->save();

            $provider = Provider::firstWhere('name', $this->game->provider);

            if (!$provider) {
                $provider = new Provider();
                $provider->name = $this->game->provider;
                $provider->save();
            }

            // $mimeType = Arr::last(explode('.', $this->game->image));

            // if (!$mimeType) {
            //     return;
            // }

            // try {
            //     $name = Str::kebab($game->name);
            //     $image = file_get_contents($this->game->image);
            //     $path = "/casino/betkudos_{$game->getKey()}_$name.".$mimeType;

            //     Storage::disk('digital_ocean')->put($path, $image, 'public');

            //     $game->image = $path;
            //     $game->save();
            // } catch (\Exception $exception) {
            //     if (str_contains($exception->getMessage(), 'HTTP/1.1 404')) {
            //         return;
            //     }

            //     throw $exception;
            // }
        }
    }
}
