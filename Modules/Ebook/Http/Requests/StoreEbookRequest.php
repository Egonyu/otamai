<?php

namespace Modules\Ebook\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Ebook\Entities\Ebook;
use Modules\Base\Http\Requests\Request;

class StoreEbookRequest extends Request
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        
        if(auth()->user())
        {
            $rules= [
                'slug' => $this->getSlugRules(),
                'title' => 'required',
                'description' => 'required',
                'publication_year' => 'nullable|integer|min:1900',
                'categories' => 'required',
                'authors' => 'required',
                'book_cover' => 'required|mimes:jpeg,png,jpg',
                'file_type' => ['required', Rule::in('upload', 'embed_code', 'external_link')],
                'book_file' => 'required_if:file_type,upload|mimes:pdf,epub,docx,doc,txt,pptx,ppt,xls,xlsx',
                'file_url' => 'required_if:file_type,external_link|nullable|url|url_ext',
                'embed_code' => 'required_if:file_type,embed_code',
                
                
            ];
        }else{
            $rules= [
                'slug' => $this->getSlugRules(),
                'title' => 'required',
                'description' => 'required',
                'publication_year' => 'nullable|integer|min:1900',
                'categories' => 'required',
                'authors' => 'required',
                'book_cover' => 'required|mimes:jpeg,png',
                'file_type' => ['required', Rule::in('upload', 'embed_code', 'external_link')],
                'book_file' => 'required_if:file_type,upload|mimes:pdf,epub,docx,doc,txt,pptx,ppt,xls,xlsx',
                'file_url' => 'required_if:file_type,external_link|nullable|url|url_ext',
                'embed_code' => 'required_if:file_type,embed_code',
                'first_name' => 'required',
                'last_name' => 'required',
                'username' => 'required|alpha_dash|unique:users,username',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
                
            ];
        }
        return $rules;
    }

    private function getSlugRules()
    {
        $slug = ebook::withoutGlobalScope('active')->where('id', $this->id)->value('slug');

        $rules[] = Rule::unique('ebooks', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
