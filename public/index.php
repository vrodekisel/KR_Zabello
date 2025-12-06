<?php

declare(strict_types=1);

echo "app.status.ok\n";
echo "env=" . (getenv('APP_ENV') ?: 'undefined') . "\n";
echo "url=" . (getenv('APP_URL') ?: 'undefined') . "\n";
