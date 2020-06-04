@extends('public.layout')

@section('title', $ebook->name)

@push('meta')
    <meta name="title" content="{{ $ebook->meta->meta_title }}">
    <meta name="keywords" content="{{ implode(',', $ebook->meta->meta_keywords) }}">
    <meta name="description" content="{{ $ebook->meta->meta_description }}">
    <meta property="image" content="{{ $ebook->bookCover->path }}">
    <meta property="og:title" content="{{ $ebook->meta->meta_title }}">
    <meta property="og:description" content="{{ $ebook->meta->meta_description }}">
    <meta property="og:image" content="{{ $ebook->bookCover->path }}">
@endpush

@section('breadcrumb')
    <li><a href="{{ route('ebooks.index') }}">{{ clean(trans('cynoebook::ebooks.ebooks')) }}</a></li>
    <li class="active">{{ $ebook->title }}</li>
@endsection

@section('content')
    @if (setting('cynoebook_ad1_section_enabled'))
        @include('public.home.sections.advertisement',['ad'=>setting('cynoebook_ad_1')])
    @endif 
    <div class="ebook-details-wrapper">
        <div class="row m-b-20">
            @include('public.ebooks.partials.ebook.images')
            @include('public.ebooks.partials.ebook.details')
        </div>
        
        <div class="row">
            @include('public.ebooks.partials.ebook.view-files')
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="tab ebook-tab clearfix">
                    <ul class="nav nav-tabs">
                        
                        <li class="{{ request()->has('reviews') || review_form_has_error($errors) ? '' : 'active' }}">
                            <a data-toggle="tab" href="#description">{{ clean(trans('cynoebook::ebook.tabs.description')) }}</a>
                        </li>
                        
                        @if (setting('reviews_enabled'))
                            <li class="{{ request()->has('reviews') || review_form_has_error($errors) ? 'active' : '' }} {{ review_form_has_error($errors) ? 'error' : '' }}">
                                <a data-toggle="tab" href="#reviews">{{ clean(trans('cynoebook::ebook.tabs.reviews')) }}</a>
                            </li>
                        @endif
                        
                        
                    </ul>

                    <div class="tab-content">
                        @include('public.ebooks.partials.ebook.tab_contents.description')

                        @includeWhen(setting('reviews_enabled'), 'public.ebooks.partials.ebook.tab_contents.reviews')
                          
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (setting('cynoebook_ad2_section_enabled'))
        @include('public.home.sections.advertisement',['ad'=>setting('cynoebook_ad_2')])
    @endif 
    
    @include('public.ebooks.partials.ebook_carousel', [
        'title' => clean(trans('cynoebook::ebook.related_ebooks')),
        'ebooks' => $relatedEbooks
    ])
    
    @if (setting('cynoebook_ad3_section_enabled'))
        @include('public.home.sections.advertisement',['ad'=>setting('cynoebook_ad_3')])
    @endif 
    
@endsection
