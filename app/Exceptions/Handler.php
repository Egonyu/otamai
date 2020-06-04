<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Swift_TransportException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof Swift_TransportException) {
            if ($request->ajax()) {
                return response(clean(trans('base::messages.mail_is_not_configured')), 406);
            }

            return back()->withInput()
                ->with('error', clean(trans('base::messages.mail_is_not_configured')));
        }

         if ($this->shouldRedirectToAdminDashboard($exception)) {
            return redirect()->route('admin.dashboard.index');
        } 

        if ($this->shouldShowNotFoundPage($exception)) {
            return response()->view('errors.404');
        }

        return parent::render($request, $exception);
        
    }
    
    private function shouldRedirectToAdminDashboard(Exception $exception)
    {
        if (config('app.installed') || config('app.debug') || ! $this->container['inAdmin']) {
            return false;
        }

        return $exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException;
    }
    
    private function shouldShowNotFoundPage(Exception $exception)
    {
        if (config('app.installed')  && $this->container['inAdmin']) {
            return false;
        }

        return $exception instanceof NotFoundHttpException || $exception instanceof ModelNotFoundException;
    }
}
