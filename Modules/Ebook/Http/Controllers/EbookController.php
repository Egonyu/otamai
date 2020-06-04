<?php

namespace Modules\Ebook\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Modules\Category\Entities\Category;
use Modules\Author\Entities\Author;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Emails\Welcome;
use Modules\User\Contracts\Authentication;
use Modules\Ebook\Entities\Ebook;
use Modules\Ebook\Events\EbookViewed;
use Modules\Ebook\Filters\EbookFilter;
use Modules\Ebook\Events\ShowingEbookList;
use Modules\Ebook\Http\Middleware\SetEbookSortOption;
use Modules\Ebook\Http\Requests\StoreEbookRequest;
use Modules\Ebook\Http\Requests\UpdateEbookRequest;
use Modules\Files\Entities\Files;
use DB;
use Illuminate\Support\Facades\Log;
use Response;

class EbookController extends Controller
{
    /**
     * The Authentication instance.
     *
     * @var \Modules\User\Contracts\Authentication
     */
    protected $auth;

    /**
     * @param \Modules\User\Contracts\Authentication $auth
     */
    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;

    }
    /**
     * Display a listing of the resource.
     *
     * @param \Modules\Ebook\Entities\Ebook $model
     * @param \Modules\Ebook\Filters\EbookFilter $ebookFilter
     * @return \Illuminate\Http\Response
     */
    public function index(Ebook $model, EbookFilter $ebookFilter)
    {
        $ebookIds = [];

        if (request()->has('query')) {
            $model = $model->search(request('query'));
            $ebookIds = $model->keys();
        }

        $query = $model->filter($ebookFilter);

        if (request()->has('category')) {
            $ebookIds = (clone $query)->select('ebooks.id')->resetOrders()->pluck('id');
        }

        $ebooks = $query->paginate(9)
            ->appends(request()->query());

        if (request()->wantsJson()) {
            return response()->json($ebooks);
        }

        event(new ShowingEbookList($ebooks));

        return view('public.ebooks.index', compact('ebooks', 'ebookIds'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!setting('enable_ebook_upload'))
        {
              return redirect()->route('home');
        }
        if(!auth()->user())
        {
            return redirect()->route('login');
        }
        return view('public.account.ebooks.create',['categories' => Category::treeList(),'authors' => Author::list()]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param \Modules\Ebook\Http\Requests\StoreEbookRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEbookRequest $request)
    {
        
        if(!auth()->user())
        {
            if (setting('auto_approve_user')) {
                $user = $this->auth->registerAndActivate($request->only([
                    'first_name',
                    'last_name',
                    'username',
                    'email',
                    'password',
                ]));
            }else{
                $user = $this->auth->register($request->only([
                    'first_name',
                    'last_name',
                    'username',
                    'email',
                    'password',
                ]));
            }
            
            $role = Role::findOrNew(setting('user_role'));

            if ($role->exists) {
                $this->auth->assignRole($user, $role);
            }
            
            if (setting('welcome_email')) {
                try{
                    Mail::to($request->email)
                        ->send(new Welcome($request->first_name));
                }catch(\Exception $e)
                {
                   //
                }
                
            } 
            $user_id=$user->id;
            
        }else{
            $user_id=auth()->user()->id;
        }
        
        if (setting('auto_approve_ebook') && setting('auto_approve_user')) {
           $is_active=1;
        }else{
            $is_active=0;
        }
        
        
        $data=[
            'title'=>$request->title,
            'description'=>strip_tags($request->description),
            'short_description'=>strip_tags($request->short_description),
            'publication_year'=>$request->publication_year,
            'publisher'=>$request->publisher,
            'price'=>$request->price,
            'buy_url'=>$request->buy_url,
            'isbn'=>$request->isbn,
            'file_type'=>$request->file_type,
            'file_url'=>$request->file_url,
            'embed_code'=>$request->embed_code,
            'categories'=>$request->categories,
            'is_private'=>$request->is_private,
            'is_active'=>$is_active,
            'password'=>$request->password_protected,
            'user_id'=>$user_id,
            'is_featured'=>0,
        ];
        
        $ebook=Ebook::create($data);
        if($request->hasFile('book_cover'))
        {
            $file_image = $request->file('book_cover');
            $path_image = Storage::putFile('media', $file_image);
            $book_cover=Files::create([
                'user_id' => $user_id,
                'disk' => config('filesystems.default'),
                'filename' => $file_image->getClientOriginalName(),
                'path' => $path_image,
                'extension' => $file_image->guessClientExtension() ?? '',
                'mime' => $file_image->getClientMimeType(),
                'size' => $file_image->getClientSize(),
            ]);
            DB::table('entity_files')->insert([
                'files_id' => $book_cover->id,
                'entity_type'=>'Modules\Ebook\Entities\Ebook',
                'entity_id'=>$ebook->id,
                'zone'=>'book_cover',
                'created_at'=>$book_cover->created_at,
                'updated_at'=>$book_cover->updated_at,
            ]);
        }
        if($request->hasFile('book_file'))
        {
            $file_pdf = $request->file('book_file');
            $path_pdf = Storage::putFile('media', $file_pdf);
            $book_file=Files::create([
                'user_id' => $user_id,
                'disk' => config('filesystems.default'),
                'filename' => $file_pdf->getClientOriginalName(),
                'path' => $path_pdf,
                'extension' => $file_pdf->guessClientExtension() ?? '',
                'mime' => $file_pdf->getClientMimeType(),
                'size' => $file_pdf->getClientSize(),
            ]);
            DB::table('entity_files')->insert([
                'files_id' => $book_file->id,
                'entity_type'=>'Modules\Ebook\Entities\Ebook',
                'entity_id'=>$ebook->id,
                'zone'=>'book_file',
                'created_at'=>$book_file->created_at,
                'updated_at'=>$book_file->updated_at,
            ]);
        }
       
        
       
        return back()->withSuccess(clean(trans('cynoebook::ebook.upload_success')));
    }
    
    /**
     * Show the form for edit resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        if(!auth()->user())
        {
            return abort(404);
        }
        $ebook = Ebook::with([
            'files'
        ])->withoutGlobalScope('active')->where(['slug'=>$slug,'user_id'=>auth()->user()->id])->firstOrFail();
        $ebook->password_protected=$ebook->password;
        return view('public.account.ebooks.edit',['categories' => Category::treeList(),'ebook'=> $ebook,'authors' => Author::list()]);
        
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,UpdateEbookRequest $request)
    {
        $user_id=auth()->user()->id;
        $ebook = Ebook::where(['user_id'=>$user_id])->findOrFail($id);
        
        $ebook->title=$request->title;
        $ebook->description=strip_tags($request->description);
        $ebook->short_description=strip_tags($request->short_description);
        $ebook->publication_year=$request->publication_year;
        $ebook->publisher=$request->publisher;
        $ebook->price=$request->price;
        $ebook->buy_url=$request->buy_url;
        $ebook->isbn=$request->isbn;
        $ebook->file_type=$request->file_type;
        $ebook->file_url=$request->file_url;
        $ebook->embed_code=$request->embed_code;
        //$ebook->categories()->sync($request->categories);
        $ebook->is_private=$request->is_private;
        $ebook->password=$request->password_protected;
        $ebook->save();
        
        if($request->hasFile('book_cover'))
        {
            $file_image = $request->file('book_cover');
            $path_image = Storage::putFile('media', $file_image);
            $book_cover=Files::create([
                'user_id' => $user_id,
                'disk' => config('filesystems.default'),
                'filename' => $file_image->getClientOriginalName(),
                'path' => $path_image,
                'extension' => $file_image->guessClientExtension() ?? '',
                'mime' => $file_image->getClientMimeType(),
                'size' => $file_image->getClientSize(),
            ]);
            $ebook->files()->wherePivot('zone', 'book_cover')->detach();
            DB::table('entity_files')->insert([
                [
                    'files_id' => $book_cover->id,
                    'entity_type'=>'Modules\Ebook\Entities\Ebook',
                    'entity_id'=>$ebook->id,
                    'zone'=>'book_cover',
                    'created_at'=>$book_cover->created_at,
                    'updated_at'=>$book_cover->updated_at,
                ]
            ]);
        }
        if($request->hasFile('book_file'))
        {
            $file_pdf = $request->file('book_file');
            $path_pdf = Storage::putFile('media', $file_pdf);
            $book_file=Files::create([
                'user_id' => $user_id,
                'disk' => config('filesystems.default'),
                'filename' => $file_pdf->getClientOriginalName(),
                'path' => $path_pdf,
                'extension' => $file_pdf->guessClientExtension() ?? '',
                'mime' => $file_pdf->getClientMimeType(),
                'size' => $file_pdf->getClientSize(),
            ]);
            $ebook->files()->wherePivot('zone', 'book_file')->detach();
            DB::table('entity_files')->insert([
                [
                    'files_id' => $book_file->id,
                    'entity_type'=>'Modules\Ebook\Entities\Ebook',
                    'entity_id'=>$ebook->id,
                    'zone'=>'book_file',
                    'created_at'=>$book_file->created_at,
                    'updated_at'=>$book_file->updated_at,
                ]
            ]);
        }
           
        
        return back()->withSuccess(clean(trans('cynoebook::ebook.upload_success')));
        
    }
    
    /**
     * Show the specified resource.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $ebook = Ebook::findBySlug($slug);
        
        $reviews = $this->getReviews($ebook);
        $cats=$ebook->categories->pluck('slug')->toArray();
        
        $relatedEbooks=Ebook::ForCard()->where('id','!=',$ebook->id)->whereHas('categories', function ($categoryQuery) use ($cats) {
            $categoryQuery->whereIn('slug', $cats);
        })->limit(10)->get();
       
        if (setting('reviews_enabled')) {
            $ebook->load('reviews:ebook_id,rating');
        }

        event(new EbookViewed($ebook));
        
        $unlock=1;
        if($ebook->password!='')
        {
            $unlock=0;
        }
       
        if(request()->session()->get('unlock'))
        {
            $passw=Crypt::decryptString(request()->session()->get('unlock'));
            if($ebook->password==$passw)
            {
                $unlock=1;
                $ebook->password='';
            }
          
        }
        
        if(is_null($ebook->file_type))  {
            if(!is_null($ebook->file_url)) $ebook->file_type='external_link';
            if($ebook->book_file->exists) $ebook->file_type='upload';
            
        }
        
        $extension = ['docx','doc','txt','pptx','ppt','xls','xlsx'];
        $fileURL='';
        $fileExt='';
        $b64Doc='';
        $pdfHtml='';
        
        if ($ebook->book_file->exists && $ebook->file_type=='upload'){
            $fileURL = $ebook->book_file->path;
            $fileExt = $ebook->book_file->extension;
        }
        if (!is_null($ebook->file_url) && $ebook->file_type=='external_link'){
            $fileURL = $ebook->file_url;
            $fileExt = get_file_extension($ebook->file_url);
        }
        
        if($fileExt=='pdf')
        {
            if (!is_null($ebook->file_url)){
                $b64Doc = base64_encode(file_get_contents($fileURL));
            }
            $pdfHtml=view('public.ebooks.viewer.pdfviewer',compact('ebook','fileURL','b64Doc'))->render();
        }
        
        return view('public.ebooks.show', compact('ebook','reviews','relatedEbooks','unlock','extension','fileURL','fileExt','pdfHtml'));
         
    }
    
    public function pdfviewer($slug) {
        $ebook = Ebook::findBySlug($slug);
        $b64Doc='';
        $fileURL='';
        if ($ebook->book_file->exists){
            $fileURL = $ebook->book_file->path;
            $fileExt = $ebook->book_file->extension;
        }
        if (!is_null($ebook->file_url)){
            $fileURL = $ebook->file_url;
            $fileExt = get_file_extension($ebook->file_url);
            $b64Doc = base64_encode(file_get_contents($fileURL));
        }
        $returnData=view('public.ebooks.viewer.pdfviewer',compact('ebook','fileURL','b64Doc'))->render();
        return Response::json(['success' => true,'html'=>$returnData]);
    }
    
    public function epubReader($slug)
    {
        $ebook = Ebook::findBySlug($slug);
        $file_url='';
        if ($ebook->book_file->exists){
            $file_url = $ebook->book_file->path;
        }
        if (!is_null($ebook->file_url)){
            $file_url=$ebook->file_url;
        }
        return view('public.ebooks.viewer.epub', compact('ebook','file_url'));
    }
    
    public function unlock($slug) {
        $ebook = Ebook::findBySlug($slug);
        
        
        if(request()->has('unlockpassword'))
        {
            
            $request=request()->all();
            if($ebook->password!=$request['unlockpassword'])
            {
                return redirect()->route('ebooks.show',$ebook->slug)->withErrors(['unlockpassword'=>clean(trans('ebook::messages.please_enter_valid_password'))]);
            }
            
            request()->session()->put('unlock',Crypt::encryptString($request['unlockpassword']));
            
            return redirect()->route('ebooks.show',$ebook->slug);
        }
        return redirect()->route('ebooks.show',$ebook->slug)->withErrors(['unlockpassword'=>trans(clean('Ebook::messages.please_enter_password_to_view_this_ebook'))]);
    }
    
    
    /**
     * Get reviews for the given ebook.
     *
     * @param \Modules\Ebook\Entities\Ebook $ebook
     * @return \Illuminate\Support\Collection
     */
    private function getReviews($ebook)
    {
        if (! setting('reviews_enabled')) {
            return collect();
        }

        return $ebook->reviews()->orderBy('id','desc')->paginate(5, ['*'], 'reviews');
    }
    
    public function download($slug)
    {
        
        $ebook = Ebook::with([
            'files'
        ])->withoutGlobalScope('active')->where('slug', $slug)->firstOrFail();
        
        if($ebook->file_type=='upload')
        {
            if($ebook->book_file->exists)
            {
                //dd($ebook);
                $headers = [
                  'Content-Type' => $ebook->book_file->mime,
                ];
                $file=$ebook->book_file->path;
                $ebook->book_file->increment('download');
                $temp = tempnam(sys_get_temp_dir(), $ebook->book_file->filename);
                copy($file, $temp);
                return response()->download($temp, $ebook->book_file->filename, $headers);
            }
        }
        
        if($ebook->file_type=='external_link')
        {
            $filename = basename($ebook->file_url);
            $temp = tempnam(sys_get_temp_dir(), $filename);
            copy($ebook->file_url, $temp);

            return response()->download($temp, $filename);
            
        }
            return back();
        
        
    }
    
    /**
     * Destroy resources by given ids.
     *
     * @param string $ids
     * @return void
     */
    public function destroy($slug)
    {
        
        $ebook = Ebook::with([
            'files'
            ])->withoutGlobalScope('active')->where('slug', $slug)->firstOrFail();
        if($ebook)
        {
            if(auth()->user())
            {
                if(auth()->user()->id==$ebook->user_id)
                {
                    $ebook->delete();
                    return back()->withSuccess(clean(trans('ebook::messages.ebook_removed')));
                }
            }
        }
        return back()->withErrors(clean(trans('ebook::messages.ebook_removed_error')));
        
    }
    
}
