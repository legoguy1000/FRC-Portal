<?php
declare(strict_types=1);
/**
 * This file is part of PSR-7 & PSR-15 JWT Authentication middleware
 *
 * Copyright (c) 2015-2018 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-jwt-auth
 *   https://appelsiini.net/projects/slim-jwt-auth
 *  https://github.com/tuupola/slim-jwt-auth/tree/3.x/src/JwtAuthentication
 */
namespace Tuupola\Middleware\JwtAuthentication;
use Psr\Http\Message\ServerRequestInterface;

final class RequestPathMethodRule implements RuleInterface
{
    /**
     * Stores all the options passed to the rule
     */
    private $options = [
        "passthrough" => []
    ];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }
    public function __invoke(ServerRequestInterface $request): bool
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);
        /* If request path is matches ignore should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $path=>$methods) {
            $ignore = rtrim($path, "/");
            if (!!preg_match("@^{$ignore}$@", $uri) && in_array($request->getMethod(),$methods)) {
                return false;
            }
        }
        return true;
    }
}


?>
