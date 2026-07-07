# Hand-rolled JWT (firebase/php-jwt) instead of Laravel Sanctum for mobile auth

The Flutter app authenticates against the API using stateless JWTs (HS256, `JWT_SECRET` from `.env`, 24h expiry, no refresh token; claims `sub` = account id and `role`) signed/verified with the `firebase/php-jwt` package and a custom auth middleware — not Laravel Sanctum. Web (Blade) auth stays on Laravel's default session guard; only the `/api/v1/*` routes use JWT.

We chose this despite Sanctum being the obvious default because the requirement calls for an explicit JWT token, and a self-managed JWT flow is the pedagogical point of the exercise. A future reader seeing two auth mechanisms (sessions for web, hand-verified JWT for API) and no Sanctum should know this split is deliberate.
