<?php

namespace Masso\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        // Captura el nombre de la ruta si existe
        $route = '';
        if (!is_null($request->route())) {
            $route = $request->route()->getName();
        }

        // Compartir la ruta actual con las vistas
        View::share('currentRoute', $route);

        // Si es error de validación, dejar que Laravel lo maneje
        if ($e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        // Si es una excepción de modelo no encontrado, convertir a 404
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        // Si es un error 404 (NotFoundHttpException), mostrar la vista guest.error
        if ($e instanceof NotFoundHttpException) {
            $title = 'Error 404';
            return response()->view('guest.error', compact('e', 'title', 'route'));
        }

        // Si es un error de token CSRF, loguear y redirigir
        if ($e instanceof TokenMismatchException) {
            $session_token = $request->session()->token();
            $header_token = $request->header('X-CSRF-TOKEN');
            $post_token = $request->input('_token');

            // Solo registrar en el log si el header o el post traen algún token
            if ($header_token || $post_token) {
                Log::warning('CSRF token mismatch LOG', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'input' => $request->except('_token'),
                    'session_token' => $session_token,
                    'header_token' => $header_token,
                    'post_token' => $post_token,
                ]);
            } else {
                Log::warning('Other CSRF token mismatch LOG', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'input' => $request->except('_token'),
                    'session_token' => $session_token,
                    'header_token' => $header_token,
                    'post_token' => $post_token,
                ]);
            }

            return back()->withInput();
        }

        // Si es cualquier otro error general, enviar a la vista de error
        $title = 'Error Interno';
        Log::error('Unhandled Exception', ['exception' => $e]);

        if (config('app.env') == 'production') {
            return response()->view('guest.error', compact('e', 'title', 'route'));
        }

        return parent::render($request, $e);
    }
}
