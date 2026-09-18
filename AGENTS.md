# Project instructions

Run project commands, including tests, static analysis, code-style checks, and Composer commands, inside the project's Docker containers.

- Use the `coding-standards-php-min` container by default.
- When a command verifies behavior specific to a PHP version, use the corresponding container, such as `coding-standards-php8.4` or `coding-standards-php8.5`.
- Start the required service with Docker Compose if it is not already running.
- Do not use the host PHP or Composer installation for project validation.
