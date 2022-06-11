<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class Faq extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $canEditFaqs = Auth::user() ? Auth::user()->can('edit faqs') : false;

        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'welcome' => $this->welcome,
            'priority' => $this->priority,
            'user' => $this->when($canEditFaqs, function () {
                return $this->user;
            }),
            'last_editor' => $this->when($canEditFaqs, function () {
                return $this->lastEditor;
            }),
        ];
    }
}
