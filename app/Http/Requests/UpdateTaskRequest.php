<?php

namespace App\Http\Requests;

use App\Models\Status;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Разрешаем доступ всем (настройте по необходимости)
    }

    public function rules(): array
    {
        $taskId = $this->route('id');

        $rules = [
            'title' => [
                'required',
                'string',
                'max:255'
            ],
            'description' => 'nullable|string',
            'status_id' => 'required|integer|exists:statuses,id',
        ];
        if ($taskId !== null) {
            // Игнорируем текущую Таску
            $rules['title'][] = Rule::unique('tasks', 'title')->ignore($taskId, 'id') ;
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Задача с таким заголовком уже существует.',
            'title.required' => 'Поле Title задачи обязателен.',
            'title.string' => 'Заголовок должен быть текстом.',
            'title.max' => 'Заголовок не может превышать 255 символов.',
            'description.string' => 'Описание должно быть текстом.',
            'status_id.sometimes' => 'Поле status может отсутствовать.',
            'status_id.exists' => 'Недопустимый статус. Доступные значения: '.$this->getAllowedStatusValues()
        ];
    }

    private function getAllowedStatusValues(): string
    {
        return Cache::remember('allowed_status_ids', 3600, function () {
            return Status::query()->orderBy('id')->pluck('id')->implode(', ');
        });
    }
}
