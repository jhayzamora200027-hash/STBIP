<?php

namespace App\Http\Middleware;

use App\Support\InputValueGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RejectMarkupInput
{
    /**
     * @var array<int, string>
     */
    private const IGNORED_FIELDS = [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'g-recaptcha-response',
    ];

    private const SECURITY_ERROR_MESSAGE = 'Unsafe or potentially malicious input was detected. Remove HTML or script content and try again.';

    public function handle(Request $request, Closure $next)
    {
        $violation = InputValueGuard::findFirstViolation($request->all(), self::IGNORED_FIELDS);

        if ($violation !== null) {
            return $this->buildViolationResponse($request, $violation['attribute'], $violation['message']);
        }

        return $next($request);
    }

    private function buildViolationResponse(Request $request, string $attribute, string $attributeMessage): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => self::SECURITY_ERROR_MESSAGE,
                'errors' => [
                    $attribute => [$attributeMessage],
                ],
            ], 422);
        }

        if ($request->isMethod('get')) {
            return redirect($request->url())
                ->with('error', self::SECURITY_ERROR_MESSAGE)
                ->with('security_error', self::SECURITY_ERROR_MESSAGE)
                ->withErrors([$attribute => $attributeMessage]);
        }

        return back()
            ->withInput($request->except(self::IGNORED_FIELDS))
            ->with('error', self::SECURITY_ERROR_MESSAGE)
            ->with('security_error', self::SECURITY_ERROR_MESSAGE)
            ->withErrors([$attribute => $attributeMessage]);
    }
}