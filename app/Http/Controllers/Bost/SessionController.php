<?php

namespace App\Http\Controllers\Bost;

use App\Contracts\Repositories\NotAllowedIpRepository;
use App\Contracts\Repositories\SessionRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Session;
use App\Models\Users\User;
use App\Http\Resources\SessionResource;
use App\Services\AuthService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    protected $sessions;
    protected $users;

    private const USERNAME_FIELD = 'username';
    private const USER_ID_FIELD = 'user_id';
    private const IP_FIELD = 'IP';

    private const SEARCH_FIELDS = [
        self::USERNAME_FIELD,
        self::USER_ID_FIELD,
        self::IP_FIELD
    ];

    private const SEARCH_FILTERS = [
        SessionRepository::ALL_SESSIONS_FILTER,
        SessionRepository::TODAY_SESSIONS_FILTER
    ];

    public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
    {
        $this->sessions = $sessionRepository;
        $this->users = $userRepository;

        $this->middleware('role:bookie');
        $this->middleware('permission:list sessions')->only('index', 'show');
        $this->middleware('permission:search sessions')->only('search');
        $this->middleware('permission:filter sessions')->only('filter');
        $this->middleware('permission:create not allowed ips')->only('blockIp');
        $this->middleware('permission:delete not allowed ips')->only('blockIp');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return SessionResource::collection($this->sessions->paginate());
    }

    /**
     * @param $value
     * @param string $field
     * @param string $filter
     * @return mixed
     * @throws ValidationException
     */
    public function search($value, $field, $filter = SessionRepository::DEFAULT_FILTER)
    {
        $this->validateSearch(\request());

        $sessions = null;

        if ($field === self::USER_ID_FIELD) {
            $sessions = $this->sessions->filterByUserId($value, $filter);
        } elseif ($field === self::USERNAME_FIELD) {
            $sessions = $this->sessions->filterByUsername($value, $filter);
        } elseif ($field === self::IP_FIELD) {
            $sessions = $this->sessions->filterByIpAddress($value, $filter);
        }

        if ($sessions instanceof LengthAwarePaginator && $sessions->isNotEmpty()) {
            return SessionResource::collection($sessions);
        }

        return $this->sessionsNotFound();
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    private function validateSearch(Request $request)
    {
        $validator = Validator::make(
            $request->route()->parameters,
            $this->searchRules()
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages(
                $validator->getMessageBag()->toArray()
            );
        }
    }

    private function searchRules(): array
    {
        return [
            'field' => [
                'required',
                'string',
                Rule::in(self::SEARCH_FIELDS)
            ],
            'filter' => [
                'required',
                'string',
                Rule::in(self::SEARCH_FILTERS)
            ],
        ];
    }

    private function sessionsNotFound()
    {
        return response()->json(['message' => 'Sessions not found'], 404);
    }

    /**
     * @param string $filter
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws ValidationException
     */
    public function filter($filter = SessionRepository::DEFAULT_FILTER, Request $request)
    {
        $request->validate([
            'user_status' => 'string|nullable'
        ]);
        
        $this->validateFilter($filter);
        
        $userStatus = $request->user_status;

        $sessions = $this->sessions->filter($filter, $userStatus);

        if ($sessions instanceof LengthAwarePaginator) {
            return SessionResource::collection($sessions);
        }

        return $this->sessionsNotFound();
    }

    /**
     * @param string $filter
     * @throws ValidationException
     */
    public function validateFilter(string $filter)
    {
        $validator = Validator::make([
            'filter' => $filter
        ], [
            'filter' => [
                'required',
                'string',
                Rule::in(self::SEARCH_FILTERS)
            ],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(
                $validator->getMessageBag()->toArray()
            );
        }
    }

    public function blockIp(Session $session, Request $request, NotAllowedIpRepository $ipRepository, AuthService $auth)
    {
        $block = $request->input('block');
        $ipAddress = $session->getAttribute('ip_address');

        if ($block) {
            $ipRepository->addFromSession($session);
            $auth->revokeSessionTokenByIp($ipAddress);

            return $this->blockedIpResponse($ipAddress);
        } elseif ($ipRepository->find($ipAddress) instanceof NotAllowedIp) {
            $ipRepository->remove($ipAddress);

            return $this->unBlockedIpResponse($ipAddress);
        }

        return $this->canNotBlockIpResponse($ipAddress);
    }

    private function blockedIpResponse(string $ipAddress)
    {
        $message = ['message' => "$ipAddress has been added to the black list"];

        return response()->json($message);
    }

    private function unBlockedIpResponse(string $ipAddress)
    {
        $message = ['message' => "$ipAddress has been removed from the black list"];

        return response()->json($message);
    }

    private function canNotBlockIpResponse(string $ipAddress)
    {
        $message = ['message' => "$ipAddress not found in black list"];

        return response()->json($message, 404);
    }

    /**
     * Display the specified resource.
     *
     * @param Session $session
     * @return SessionResource
     */
    public function show(Session $session)
    {
        return new SessionResource($session);
    }
}
