<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $task = Task::with('status')->get();
        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($task)
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $clientIp = $request->ip();
        $taskCountToday = 0;

        if (Task::count() >= 50) {
            return response()->json([
                'success' => false,
                'message' => 'Лимит сохранения Тасков исчерпан. Свяжитесь с Оператором'
            ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
        }
        try {
            $task = DB::transaction(function () use ($request, $clientIp, &$taskCountToday) {
                $user = User::where('client_ip', $clientIp)->first();
                if (!$user) {
                    $slug = Str::slug($clientIp);
                    $user = User::create([
                        'client_ip' => $clientIp,
                        'name' => 'loginFor_' . $slug,
                        'email' => $clientIp . "@localhost",
                        'password' => Hash::make($clientIp) // хешируем пароль!
                    ]);
                }

                // 2. Проверяем лимит задач за сутки
                $todayStartUnix = Carbon::today()->timestamp;
                $todayEndUnix = Carbon::tomorrow()->timestamp - 1;

                // Проверяем лимит: не более 10 задач в сутки
                $taskCountToday = Task::query()
                    ->where('user_id', $user->id)
                    ->whereBetween('created_at', [$todayStartUnix, $todayEndUnix])
                    ->count();

                if ($taskCountToday >= 10) {
                    throw new \Exception('Вы достигли лимита в 10 задач за сутки.');
                }

                // 3. Создаём задачу
                $validated = $request->validated();
                $validated['user_id'] = $user->id;
                return Task::create($validated);
            });

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'taskCountToday' => $taskCountToday + 1, // Количество задач после создания новой
                'remainingTasks' => 10 - ($taskCountToday + 1) // Оставшиеся задачи в лимите
            ], ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'лимита')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], ResponseAlias::HTTP_TOO_MANY_REQUESTS);
            }

            // Для других ошибок — общий ответ
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении задачи. Проверьте правильно ли указаны поля!',
                'example' => ["title:title",
                    "description:(не обязательно)",
                    "status_id: (integer, от 1 до 3)"]
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $task = Task::query()->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => new TaskResource($task)
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        try {
            // 1. Находим задачу или выбрасываем 404
            $task = Task::query()->findOrFail($id);

            // 2. Проверяем права доступа: IP пользователя должен совпадать
            if ($task->user->client_ip != $request->ip()) {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для редактирования этой Таски '
                ], ResponseAlias::HTTP_FORBIDDEN);
            }
            $validated = $request->validated();

            // 3. Обновляем задачу валидированными данными
            $task->update($validated);

            // 4. Проверяем лимит задач за сутки
            $todayStartUnix = Carbon::today()->timestamp;
            $todayEndUnix = Carbon::tomorrow()->timestamp - 1;
            $taskCountToday = Task::query()
                ->where('user_id', $task->user->id)
                ->whereBetween('created_at', [$todayStartUnix, $todayEndUnix])
                ->count();

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
                'taskCountToday' => $taskCountToday,
                'remainingTasks' => 10 - $taskCountToday // Оставшиеся задачи в лимите
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 403 ? ResponseAlias::HTTP_FORBIDDEN : ResponseAlias::HTTP_INTERNAL_SERVER_ERROR;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request): JsonResponse
    {

        $task = Task::query()->findOrFail($id);
        if ($task->user->client_ip != $request->ip()) {
            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав для редактирования этой Таски '
            ], ResponseAlias::HTTP_FORBIDDEN);
        }
        $task->delete();
        return response()->json([], ResponseAlias::HTTP_NO_CONTENT);
    }
}
