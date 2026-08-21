<?php

return [

  /*
   |--------------------------------------------------------------------------
   | Sentry/Bugsink tunnel DSN
   |--------------------------------------------------------------------------
   |
   | DSN of the frontend (GestSIS_APP) Bugsink project. Used by
   | MonitoringTunnelController to relay error reports server-side, since
   | direct browser -> Bugsink requests are commonly blocked by ad-blockers.
   |
   */
  'dsn' => env('SENTRY_TUNNEL_DSN', ''),
];
