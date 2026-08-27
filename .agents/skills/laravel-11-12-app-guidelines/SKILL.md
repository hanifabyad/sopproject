\---

name: laravel-11-12-app-guidelines

description: Laravel 11/12 development guidelines for safe backend, Blade, database, routing, validation, and testing work.

\---



\# Laravel 11/12 Application Guidelines



Use this skill whenever working on Laravel backend, controllers, models,

migrations, Blade templates, routing, validation, database logic, or tests.



\## Project Stack



Assume the project uses:



\- Laravel 12

\- PHP 8.2+

\- Blade

\- Tailwind CSS 4

\- DaisyUI

\- MySQL / MariaDB



Always inspect composer.json and package.json before assuming package versions.



\## Development Principles



Follow existing project conventions before introducing new patterns.



Prefer:

\- small focused changes

\- Laravel-native solutions

\- existing helpers and services

\- existing naming conventions

\- existing route and controller structure



Avoid unnecessary architecture changes.



\## Controllers



Controllers should remain focused on request handling and orchestration.



Before modifying a controller:



1\. Read the complete affected method.

2\. Trace related model/database behavior.

3\. Check related routes and views.

4\. Understand existing side effects.

5\. Modify only what is required.



Do not refactor unrelated controller methods.



\## Models



Use Eloquent relationships and existing model conventions.



Do not change fillable fields, casts, relationships, or scopes

unless required by the current task.



\## Database



Database changes must be additive whenever possible.



Never automatically run:



\- migrate:fresh

\- db:wipe

\- DROP TABLE

\- TRUNCATE

\- destructive mass DELETE



without explicit user authorization.



Prefer backward-compatible migrations.



Never silently modify production data.



\## Routing



Preserve existing:

\- route names

\- URLs

\- HTTP methods

\- middleware



unless the task explicitly requires changing them.



Use named routes where the existing project already uses them.



\## Blade



Preserve:

\- input names

\- route targets

\- Blade variables

\- form methods

\- CSRF protection

\- validation error behavior



A UI redesign must not silently change backend contracts.



\## Validation



Use Laravel validation mechanisms.



Validation rules must reflect actual business requirements.



Do not loosen validation only to make a failing test pass.



\## Error Handling



Do not hide application errors blindly.



Catch exceptions only when the application has a meaningful recovery path.



Log useful diagnostic information without leaking sensitive data.



\## File Handling



When processing uploads:



\- validate file type and size

\- preserve original files when required

\- use Laravel Storage APIs when possible

\- clean temporary files

\- avoid overwriting user files accidentally



\## Testing



After modifications:



1\. Run PHP syntax validation when appropriate.

2\. Run targeted tests for the affected flow.

3\. Verify database changes.

4\. Verify generated files.

5\. Perform browser/manual verification when UI behavior is involved.



Never claim PASS without testing the relevant behavior.



\## Security



Never weaken:

\- authentication

\- authorization

\- CSRF protection

\- role checks

\- validation



for convenience.



Never expose secrets, passwords, API keys, or .env contents.

