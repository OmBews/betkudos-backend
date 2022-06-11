<?php

namespace App\Http\Controllers\API;

use App\Contracts\Repositories\FaqRepository;
use App\Http\Controllers\Controller;
use App\Models\FAQs\Faq;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Faq as FaqResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FaqController extends Controller
{
    protected $repository;

    public function __construct(FaqRepository $repository)
    {
        $this->repository = $repository;

        $this->middleware('permission:create faqs')->only('store');
        $this->middleware('permission:edit faqs')->only('update');
        $this->middleware('permission:delete faqs')->only('destroy');
        $this->middleware(['auth:api', 'role:bookie'])->except('index', 'welcome');
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return FaqResource::collection($this->repository->orderedByPriority($this->relations()));
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function welcome()
    {
        return FaqResource::collection($this->repository->welcome($this->relations()));
    }

    private function relations()
    {
        $relations = [];

        if (Auth::user() && Auth::user()->can('edit faqs')) {
            $relations[] = 'user';
            $relations[] = 'lastEditor';
        }

        return $relations;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validateStore($request);

        $faqs = $request->input('faqs');
        $user = $request->user();

        try {
            $newFaqs = $this->repository->createMany($faqs, $user);

            return response()->json(['faqs' => $newFaqs], 201);
        } catch (\Exception $e) {
            $data = [
                'message' => trans('bost.faqs.failed', ['action' => 'save'])
            ];

            return response()->json($data, 500);
        }
    }

    private function validateStore(Request $request)
    {
        $request->validate([
            'faqs' => 'required|array',
            'faqs.*.question' => 'required|string|unique:faqs',
            'faqs.*.answer' => 'required|string',
            'faqs.*.welcome' => 'required|boolean',
            'faqs.*.priority' => 'required|integer',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Faq $faq
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Faq $faq)
    {
        $this->validateUpdate($request);

        $user  = $request->user();

        $attrs = $request->only([
            'question', 'answer',
            'welcome', 'priority'
        ]);

        $attrs['last_editor_id'] = $user->id;

        $updateFail = [
            'message' => trans('bost.faqs.failed', ['action' => 'update'])
        ];

        try {
            if (! $this->repository->update($faq->id, $attrs)) {
                return response()->json($updateFail, 500);
            }

            $faq->refresh();

            return response()->json(['faq' => $faq]);
        } catch (QueryException $queryException) {
            if ($queryException->getCode() === "23000") {
                throw ValidationException::withMessages([
                    'question' => trans('validation.unique', ['attribute' => 'question'])
                ]);
            }

            return response()->json($updateFail, 500);
        } catch (\Exception $exception) {
            return response()->json($updateFail, 500);
        }
    }

    private function validateUpdate(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'welcome' => 'required|boolean',
            'priority' => 'required|integer'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Faq  $faq
     * @return JsonResponse
     */
    public function destroy(Faq $faq)
    {
        try {
            return response()->json(['deleted' => $faq->delete()]);
        } catch (\Exception $e) {
            return response()->json(['deleted' => false], 500);
        }
    }
}
