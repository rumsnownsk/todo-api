<?php

namespace App\Http\Requests;

use App\Models\Status;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;


class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Разрешаем доступ всем
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:tasks,title',
            'description' => 'nullable|string',
            'status_id' => 'required|integer|exists:statuses,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок задачи обязателен',
            'title.unique' => 'Таска с таким Заголовком уже существует',
            'title.string' => 'Заголовок должен быть текстом',
            'title.max' => 'Заголовок не может превышать 255 символов',
            'description.string' => 'Описание должно быть текстом',
            'status_id.required' => 'Поле статус Обязательно',
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
