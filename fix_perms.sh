#!/bin/sh
find . -type d -exec chmod 775 {} +
find . -type f ! -name '*.sh' -exec chmod 664 {} +
find . -type f -name '*.sh' -exec chmod 775 {} +
find . -exec chown apache:apache {} +
