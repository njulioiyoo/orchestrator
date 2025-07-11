<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $menuId = $this->route('id')->id ?? $this->route('id');
        
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('menus', 'name')->ignore($menuId)],
            'label' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'route' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:menus,id', Rule::notIn([$menuId])],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.type' => ['required_with:permissions', 'string', 'in:role,permission'],
            'permissions.*.name' => ['required_with:permissions', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Menu name is required.',
            'name.unique' => 'Menu name already exists.',
            'label.required' => 'Menu label is required.',
            'parent_id.exists' => 'Selected parent menu does not exist.',
            'parent_id.not_in' => 'Menu cannot be parent of itself.',
            'sort_order.required' => 'Sort order is required.',
            'sort_order.min' => 'Sort order must be at least 0.',
            'permissions.*.type.in' => 'Permission type must be either role or permission.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'menu name',
            'label' => 'menu label',
            'parent_id' => 'parent menu',
            'sort_order' => 'sort order',
            'is_active' => 'active status',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'sort_order' => $this->integer('sort_order', 0),
        ]);
    }
}