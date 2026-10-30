<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StartCartSession
{
    public function __construct(private CartService $carts) {}

    /**
     * Resolve (and create when needed) the cart for the current visitor.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->carts->resolve($request);

        return $next($request);
    }
}
