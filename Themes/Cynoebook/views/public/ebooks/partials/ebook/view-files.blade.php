<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
    @if ($ebook->password=='' &&  $unlock)
        @if($ebook->file_type=='upload' || $ebook->file_type=='external_link')
            @if(in_array($fileExt,$extension))
                <object width="100%" height="500" id="ipdf" class="print-file" data="https://docs.google.com/gview?embedded=true&amp;url={{ $fileURL }}"></object>
            @endif
            @if($fileExt=='pdf')
                <div id="disp-pdf" class="print-file">
                {!! $pdfHtml !!}
                </div>
                @push('scripts')
                    <script>
                        (function () {
                            "use strict";
                            
                            $(document).ready(function() {
                                $("#pdfviewer-xloder").remove();
                                
                            });
                        })(); 
                    </script>
                @endpush
            @endif
            @if($fileExt=='epub')
                <iframe class="print-file" src="{{ route('ebooks.epubReader',$ebook->slug) }}" id="ipdf" frameborder="0" style="width: 100%;height: 500px;"></iframe>
            @endif
        @endif
        @if($ebook->file_type=='embed_code' && !is_null($ebook->embed_code) )
            {!! $ebook->embed_code !!}
        @endif
        
    @else
            <button type="submit" class="btn btn-danger btn-lg btn-right-actions"  data-target="#unlockBook" id="btn-unlockBook" ><i class="fa fa-lock"></i> {{ clean(trans('cynoebook::ebook.unlock')) }}</button>
            
            <div class="right-actions" id="unlockBook">
                <div class="title">{{ clean(trans('cynoebook::ebook.password_header')) }}</div>
                <div class="action-content">
                    <div id="action-movefile">
                        <form method="POST" action="{{ route('ebooks.unlock',$ebook->slug) }}" id="ebook-unlock-form" name="ebook-unlock-form">    
                            {{ csrf_field() }}
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 ">
                                <div class="form-group row  {{ $errors->has('unlockpassword') ? 'has-error' : '' }}" >
                                    <label for="unlockpassword" class="col-md-12 text-left">{{ clean(trans('cynoebook::ebook.password')) }}</label>
                                    <div class="col-md-12 p-0">
                                    <input type="password" class="form-control" name="unlockpassword" required>
                                    @if($errors->has('unlockpassword'))
                                        <span class="error-message text-left">{{ clean($errors->first('unlockpassword')) }}</span>
                                    @endif
                                    </div>
                                </div>
                                <div class="form-group text-left">
                                    <button type="submit" class="btn btn-primary btn-lg" data-loading>{{ clean(trans('cynoebook::ebook.unlock')) }}</button>
                                </div>
                            </div>    
                        </form>
                    </div>
                </div>
                <div class="action-toggle">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </div>
            </div>

                
    @endif
</div>

@push('scripts')
    
    <script>
        (function () {
            "use strict";
            
            $(document).ready(function() {
                /* $('.btin-print').on("click", function () {
                  $('.print-file').printThis({canvas:true});
                }); */
                @if ($ebook->password!='' &&  !$unlock)
                    $('body').append('<div class="right-actions-overlay"></div>');
                    $("#unlockBook").addClass('open');
                    $(".right-actions-overlay").show();
                @endif
                
                $("#ebook-unlock-form").bind("keypress", function(e) {
                    if (e.keyCode == 13) {
                        return false;
                    }
                });
                
            });
        })();  
    </script>

@endpush


