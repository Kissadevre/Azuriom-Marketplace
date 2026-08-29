<?php
namespace Azuriom\Plugin\Marketplace\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CategoryRequest extends FormRequest {
 public function authorize(): bool{return $this->user()?->can('marketplace.admin') ?? false;}
 public function rules(): array { $id=$this->route('category')?->id; return ['name'=>['required','string','max:100'],'slug'=>['required','alpha_dash','max:100',Rule::unique('marketplace_categories')->ignore($id)],'icon'=>['nullable','string','max:100'],'description'=>['nullable','string','max:1000'],'roles'=>['nullable','array'],'roles.*'=>['integer',Rule::exists('roles','id')],'publish_roles'=>['nullable','array'],'publish_roles.*'=>['integer',Rule::exists('roles','id')],'position'=>['required','integer','min:0'],'is_enabled'=>['sometimes','boolean']]; }
 protected function prepareForValidation(): void { $this->merge(['roles'=>$this->boolean('is_private')?$this->input('roles',[]):null,'publish_roles'=>$this->boolean('restrict_publishing')?$this->input('publish_roles',[]):null,'is_enabled'=>$this->boolean('is_enabled')]); }
}
