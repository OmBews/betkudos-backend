<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use KgBot\LaravelLocalization\Facades\ExportLocalizations;
use Tests\TestCase;

class LocalizationControllerTest extends TestCase
{
    use WithFaker;

    protected function localesRoute(string $lang = 'en')
    {
        return route('api.locales', ['locale' => $lang]);
    }

    public function testCanRetrieveLocaleMessages()
    {
        $response = $this->get($this->localesRoute());

        $response->assertSuccessful();
        $response->assertJson([
            'messages' => Lang::get('web_client')
        ]);
    }

    public function testCanRetrieveFallbackLocaleMessagesIfInvalidLocaleIsProvided()
    {
        $response = $this->get($this->localesRoute('xyz'));

        $response->assertSuccessful();
        $response->assertJson([
            'messages' => Lang::get('web_client')
        ]);
    }

    public function testCanRetrieveFallbackLocaleIfLocaleDontHaveMessages()
    {
        $locale = 'pt';
        $fallbackLocale = config('app.locale');

        $mockedLocalizations = [
            $locale => [],
            $fallbackLocale => [
                'web_client' => [
                    'foo' => 'bar'
                ]
            ]
        ];

        ExportLocalizations::shouldReceive('export')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('toArray')
            ->andReturn($mockedLocalizations);

        $response = $this->get($this->localesRoute($locale));

        $response->assertSuccessful();
        $response->assertJson([
            'messages' => $mockedLocalizations[$fallbackLocale]['web_client']
        ]);
    }
}
