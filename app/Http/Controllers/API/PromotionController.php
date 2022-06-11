<?php

namespace App\Http\Controllers\API;

use App\Contracts\Repositories\PromotionRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion as PromotionResource;
use App\Models\Promotions\Promotion;
use App\Rules\Base64Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class PromotionController extends Controller
{
    private $repository;

    /**
     * Temporary limit, should be removed soon.
     */
    private const MAX_PROMOTIONS = 3;

    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;

        $this->middleware(['auth:api', 'role:bookie'])->except('index');
        $this->middleware('permission:create promotions')->only('store');
        $this->middleware('permission:edit promotions')->only('update');
        $this->middleware('permission:delete promotions')->only('delete');
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return PromotionResource::collection($this->repository->orderedByPriority());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validateStore($request);

        if (count($this->repository->all()) === self::MAX_PROMOTIONS) {
            throw ValidationException::withMessages([
                'promotions' => trans('bost.promotions.max', ['max' => self::MAX_PROMOTIONS])
            ]);
        }

        $promotions = collect();

        foreach ($request->promotions as $promotion) {
            $image = Image::make($promotion['image'])->encode('jpg', 100);

            $promotion['image'] = Promotion::genFileName($image, $promotion['name']);

            $promotions->add($this->repository->create($promotion));

            Storage::disk('promotions')->put($promotion['image'], $image);
        }

        return $this->storedResponse($promotions);
    }

    private function validateStore(Request $request)
    {
        return $request->validate([
            'promotions' => 'required|array|max:3',
            'promotions.*.name' => 'required|string|unique:promotions',
            'promotions.*.image' => ['required','string', $this->base64ImageRule()],
            'promotions.*.priority' => 'required|integer',
        ]);
    }

    private function base64ImageRule()
    {
        return new Base64Image(Promotion::IMAGE_WIDTH, Promotion::IMAGE_HEIGHT);
    }

    private function storedResponse(Collection $promotions)
    {
        return PromotionResource::collection($promotions)
               ->response()
               ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $promotion
     * @return PromotionResource
     */
    public function show(Promotion $promotion)
    {
        return new PromotionResource($promotion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Promotion $promotion
     * @return mixed
     */
    public function update(Request $request, Promotion $promotion)
    {
        $this->validateUpdate($request);
        try {
            $promotion->name = $request->name;
            $promotion->priority = $request->priority;

            if ($promotion->image !== $request->image) {
                $this->validatePromoImage($request);
                $image = Image::make($request->image)->encode('jpg');
                $promotion->image = Promotion::genFileName($image, $request->name);
                Storage::disk('promotions')->put($promotion->image, $image->getEncoded());
            }

            if (! $promotion->save()) {
                return $this->sendUpdateFailedResponse();
            }

            return new PromotionResource($promotion);
        } catch (\Exception $exception) {
            return $this->sendUpdateFailedResponse();
        }
    }

    private function validateUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'priority' => 'required|integer',
            'image' => 'required|string',
        ]);
    }

    private function validatePromoImage(Request $request)
    {
        $request->validate([
            'image' => ['required','string', $this->base64ImageRule()],
        ]);
    }

    private function sendUpdateFailedResponse()
    {
        return response()->json(['message', 'Unable to update promotion'], 500);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
